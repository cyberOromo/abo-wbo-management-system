<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Utils\Database;
use Exception;

class AttachmentController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        if (!auth_check()) {
            $this->redirect('/auth/login');
            exit;
        }

        $this->db = Database::getInstance();
    }

    public function show(string $resource, int $id, int $index): void
    {
        try {
            $attachment = $this->resolveAttachment($resource, $id, $index);

            $this->render('attachments/view', [
                'title' => 'Attachment Viewer',
                'attachment' => $attachment,
                'preview_kind' => $this->determinePreviewKind((string) ($attachment['mime_type'] ?? 'application/octet-stream')),
            ]);
        } catch (Exception $e) {
            $this->notFoundResponse($e->getMessage());
        }
    }

    public function stream(string $resource, int $id, int $index): void
    {
        try {
            $attachment = $this->resolveAttachment($resource, $id, $index);
            $this->outputAttachment($attachment, false);
        } catch (Exception $e) {
            http_response_code(404);
            echo 'Attachment not found';
        }

        exit;
    }

    public function download(string $resource, int $id, int $index): void
    {
        try {
            $attachment = $this->resolveAttachment($resource, $id, $index);
            $this->outputAttachment($attachment, true);
        } catch (Exception $e) {
            http_response_code(404);
            echo 'Attachment not found';
        }

        exit;
    }

    private function resolveAttachment(string $resource, int $id, int $index): array
    {
        return match ($resource) {
            'tasks' => $this->resolveTaskAttachment($id, $index),
            'task-comments' => $this->resolveTaskCommentAttachment($id, $index),
            default => throw new Exception('Unsupported attachment resource.'),
        };
    }

    private function resolveTaskAttachment(int $taskId, int $index): array
    {
        $task = $this->db->fetch(
            'SELECT id, title, created_by, assigned_to, attachments FROM tasks WHERE id = ?',
            [$taskId]
        );

        if (!$task) {
            throw new Exception('Task not found.');
        }

        $this->assertTaskAccess($task);

        $attachments = json_decode((string) ($task['attachments'] ?? '[]'), true);
        if (!is_array($attachments) || !isset($attachments[$index])) {
            throw new Exception('Attachment not found.');
        }

        return $this->hydrateAttachment($attachments[$index], 'tasks', $taskId, $index, (string) ($task['title'] ?? 'Task attachment'));
    }

    private function resolveTaskCommentAttachment(int $commentId, int $index): array
    {
        $comment = $this->db->fetch(
            'SELECT tc.id, tc.task_id, tc.attachments, t.title, t.created_by, t.assigned_to
             FROM task_comments tc
             INNER JOIN tasks t ON t.id = tc.task_id
             WHERE tc.id = ?',
            [$commentId]
        );

        if (!$comment) {
            throw new Exception('Comment attachment not found.');
        }

        $this->assertTaskAccess($comment);

        $attachments = json_decode((string) ($comment['attachments'] ?? '[]'), true);
        if (!is_array($attachments) || !isset($attachments[$index])) {
            throw new Exception('Attachment not found.');
        }

        return $this->hydrateAttachment($attachments[$index], 'task-comments', $commentId, $index, (string) ($comment['title'] ?? 'Task comment attachment'));
    }

    private function hydrateAttachment(array $attachment, string $resource, int $resourceId, int $index, string $contextTitle): array
    {
        $relativePath = ltrim((string) ($attachment['relative_path'] ?? ''), '/\\');
        if ($relativePath === '') {
            throw new Exception('Attachment path is missing.');
        }

        $storageRoot = rtrim((string) (function_exists('storage_path') ? storage_path('') : APP_ROOT . '/storage/'), '/\\');
        $candidatePath = $storageRoot . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        $resolvedPath = realpath($candidatePath);

        if ($resolvedPath === false || !is_file($resolvedPath)) {
            throw new Exception('Attachment file is unavailable.');
        }

        $normalizedRoot = str_replace('\\', '/', realpath($storageRoot) ?: $storageRoot);
        $normalizedPath = str_replace('\\', '/', $resolvedPath);
        if (strpos($normalizedPath, $normalizedRoot . '/') !== 0 && $normalizedPath !== $normalizedRoot) {
            throw new Exception('Attachment path is invalid.');
        }

        $attachment['resource'] = $resource;
        $attachment['resource_id'] = $resourceId;
        $attachment['index'] = $index;
        $attachment['context_title'] = $contextTitle;
        $attachment['absolute_path'] = $resolvedPath;
        $attachment['display_name'] = (string) ($attachment['original_name'] ?? $attachment['stored_name'] ?? 'Attachment');
        $attachment['mime_type'] = (string) ($attachment['mime_type'] ?? mime_content_type($resolvedPath) ?: 'application/octet-stream');
        $attachment['size'] = (int) ($attachment['size'] ?? filesize($resolvedPath) ?: 0);
        $attachment['view_url'] = '/attachments/' . $resource . '/' . $resourceId . '/' . $index;
        $attachment['stream_url'] = '/attachments/' . $resource . '/' . $resourceId . '/' . $index . '/stream';
        $attachment['download_url'] = '/attachments/' . $resource . '/' . $resourceId . '/' . $index . '/download';

        return $attachment;
    }

    private function outputAttachment(array $attachment, bool $forceDownload): void
    {
        header('Content-Type: ' . $attachment['mime_type']);
        header('Content-Length: ' . (string) $attachment['size']);
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Content-Disposition: ' . ($forceDownload ? 'attachment' : 'inline') . '; filename="' . addslashes($attachment['display_name']) . '"');
        readfile($attachment['absolute_path']);
    }

    private function determinePreviewKind(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        if ($mimeType === 'application/pdf') {
            return 'pdf';
        }

        if (str_starts_with($mimeType, 'text/') || in_array($mimeType, ['application/json', 'application/xml'], true)) {
            return 'text';
        }

        return 'binary';
    }

    private function assertTaskAccess(array $task): void
    {
        $user = auth_user() ?? [];
        $normalizedRole = function_exists('normalized_user_role') ? normalized_user_role($user) : ($user['role'] ?? null);

        if ($normalizedRole === 'admin') {
            return;
        }

        $userId = (int) ($user['id'] ?? 0);
        if ($userId > 0 && (int) ($task['created_by'] ?? 0) === $userId) {
            return;
        }

        $assignedTo = json_decode((string) ($task['assigned_to'] ?? '[]'), true);
        if (is_array($assignedTo) && in_array($userId, $assignedTo, false)) {
            return;
        }

        $userScope = $this->getTaskUserScope($userId);
        if ($this->creatorMatchesScope((int) ($task['created_by'] ?? 0), $userScope)) {
            return;
        }

        throw new Exception('You do not have access to this attachment.');
    }

    private function getTaskUserScope(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $scope = $this->db->fetch(
            "SELECT ua.*, gl.name AS global_name, gd.name AS godina_name, ga.name AS gamta_name, gu.name AS gurmu_name
             FROM user_assignments ua
             LEFT JOIN globals gl ON ua.global_id = gl.id
             LEFT JOIN godinas gd ON ua.godina_id = gd.id
             LEFT JOIN gamtas ga ON ua.gamta_id = ga.id
             LEFT JOIN gurmus gu ON ua.gurmu_id = gu.id
             WHERE ua.user_id = ? AND ua.status = 'active'
             LIMIT 1",
            [$userId]
        ) ?: [];

        if (!empty($scope)) {
            $scope['user_id'] = $userId;
        }

        return $scope;
    }

    private function creatorMatchesScope(int $creatorUserId, array $userScope): bool
    {
        if ($creatorUserId <= 0) {
            return false;
        }

        $scopeColumn = $this->getHierarchyScopeColumn((string) ($userScope['level_scope'] ?? ''));
        $scopeValue = $scopeColumn !== null ? ($userScope[$scopeColumn] ?? null) : null;
        if ($scopeColumn === null || $scopeValue === null) {
            return false;
        }

        $assignment = $this->db->fetch(
            'SELECT global_id, godina_id, gamta_id, gurmu_id FROM user_assignments WHERE user_id = ? AND status = ? LIMIT 1',
            [$creatorUserId, 'active']
        );

        return !empty($assignment) && (int) ($assignment[$scopeColumn] ?? 0) === (int) $scopeValue;
    }

    private function getHierarchyScopeColumn(string $levelScope): ?string
    {
        return match ($levelScope) {
            'global' => 'global_id',
            'godina' => 'godina_id',
            'gamta' => 'gamta_id',
            'gurmu' => 'gurmu_id',
            default => null,
        };
    }
}