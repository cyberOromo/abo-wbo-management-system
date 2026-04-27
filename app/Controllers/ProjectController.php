<?php

namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Project;
use App\Services\AttachmentUploadService;
use Exception;

class ProjectController extends BaseController
{
    private Project $projectModel;
    private AttachmentUploadService $attachmentUploadService;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
        $this->attachmentUploadService = new AttachmentUploadService();
    }

    public function index()
    {
        try {
            $user = $this->getAuthUser();
            if (!$user) {
                return $this->redirect('/auth/login');
            }

            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projects = $this->projectModel->getProjectsForScope($scope);
            $stats = $this->projectModel->getProjectStats($scope);

            return $this->render('projects.index_shell', [
                'title' => 'Projects & Initiatives',
                'projects' => $projects,
                'stats' => $stats,
                'scope' => $scope,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to load project workspace: ' . $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $user = $this->getAuthUser();
            if (!$user) {
                return $this->redirect('/auth/login');
            }

            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));

            return $this->render('projects.create', [
                'title' => 'Create Project',
                'scope' => $scope,
                'availableUsers' => $this->projectModel->getAssignableUsersForScope($scope),
                'project' => [],
                'selectedTeamUserIds' => [],
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to load project creation form: ' . $e->getMessage());
        }
    }

    public function store()
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            if (empty($scope)) {
                throw new Exception('An active organizational assignment is required before creating scoped projects.');
            }

            $title = trim((string) ($_POST['title'] ?? ''));
            if ($title === '') {
                throw new Exception('Project title is required.');
            }

            $ownerUserId = (int) ($_POST['owner_user_id'] ?? ($user['id'] ?? 0));
            $teamUserIds = $this->extractUserIds($_POST['team_user_ids'] ?? []);
            $attachments = $this->attachmentUploadService->uploadMany($_FILES['attachments'] ?? [], 'project-attachments');

            $payload = [
                'title' => $title,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'project_code' => trim((string) ($_POST['project_code'] ?? '')),
                'status' => trim((string) ($_POST['status'] ?? 'proposed')),
                'priority' => trim((string) ($_POST['priority'] ?? 'medium')),
                'project_type' => trim((string) ($_POST['project_type'] ?? 'initiative')),
                'start_date' => ($_POST['start_date'] ?? '') !== '' ? (string) $_POST['start_date'] : null,
                'target_date' => ($_POST['target_date'] ?? '') !== '' ? (string) $_POST['target_date'] : null,
                'completion_percentage' => max(0, min(100, (int) ($_POST['completion_percentage'] ?? 0))),
                'budget_amount' => ($_POST['budget_amount'] ?? '') !== '' ? (float) $_POST['budget_amount'] : null,
                'owner_user_id' => $ownerUserId,
                'created_by' => (int) ($user['id'] ?? 0),
                'level_scope' => (string) ($scope['level_scope'] ?? 'global'),
                'global_id' => $scope['global_id'] ?? null,
                'godina_id' => $scope['godina_id'] ?? null,
                'gamta_id' => $scope['gamta_id'] ?? null,
                'gurmu_id' => $scope['gurmu_id'] ?? null,
                'success_metrics' => trim((string) ($_POST['success_metrics'] ?? '')),
                'delivery_notes' => trim((string) ($_POST['delivery_notes'] ?? '')),
                'status_notes' => trim((string) ($_POST['status_notes'] ?? '')),
                'metadata' => [
                    'source' => 'projects_module_v1',
                    'scope_name' => $scope['scope_name'] ?? null,
                    'attachments' => $attachments,
                ],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $projectId = $this->projectModel->createProject($payload, $teamUserIds, (int) ($user['id'] ?? 0));
            $this->redirectWithMessage('/projects/' . $projectId, 'Project created successfully.', 'success');
        } catch (Exception $e) {
            $this->redirectWithMessage('/projects/create', $e->getMessage(), 'error');
        }
    }

    public function show($id = null)
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projectId = (int) $id;
            $project = $this->projectModel->getProject($projectId, $scope);

            if (!$project) {
                $this->redirectWithMessage('/projects', 'Project not found in your current scope.', 'error');
                return;
            }

            $project = $this->hydrateProjectForView($project);

            $assignments = $this->projectModel->getProjectAssignments($projectId);
            $milestones = $this->projectModel->getProjectMilestones($projectId);
            $tasks = $this->projectModel->getProjectTasksHierarchy($projectId);
            $activity = $this->projectModel->getProjectActivities($projectId);
            $taskParentOptions = $this->projectModel->getProjectTaskOptions($projectId);

            return $this->render('projects.show', [
                'title' => $project['title'] ?? 'Project Detail',
                'project' => $project,
                'scope' => $scope,
                'assignments' => $assignments,
                'milestones' => $milestones,
                'tasks' => $tasks,
                'activity' => $activity,
                'availableUsers' => $this->projectModel->getAssignableUsersForProject($project),
                'taskScopeOptions' => $this->projectModel->getAvailableTaskScopes($project),
                'taskParentOptions' => $taskParentOptions,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to load project detail: ' . $e->getMessage());
        }
    }

    public function edit($id = null)
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projectId = (int) $id;
            $project = $this->projectModel->getProject($projectId, $scope);

            if (!$project) {
                $this->redirectWithMessage('/projects', 'Project not found in your current scope.', 'error');
                return;
            }

            $project = $this->hydrateProjectForView($project);

            $assignments = $this->projectModel->getProjectAssignments($projectId);
            $selectedTeamUserIds = [];
            foreach ($assignments as $assignment) {
                if ((int) ($assignment['user_id'] ?? 0) !== (int) ($project['owner_user_id'] ?? 0)) {
                    $selectedTeamUserIds[] = (int) $assignment['user_id'];
                }
            }

            return $this->render('projects.edit', [
                'title' => 'Edit Project',
                'scope' => $scope,
                'project' => $project,
                'availableUsers' => $this->projectModel->getAssignableUsersForProject($project),
                'selectedTeamUserIds' => $selectedTeamUserIds,
            ]);
        } catch (Exception $e) {
            return $this->errorResponse('Failed to load project editing workspace: ' . $e->getMessage());
        }
    }

    public function update($id = null)
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projectId = (int) $id;
            $project = $this->projectModel->getProject($projectId, $scope);

            if (!$project) {
                throw new Exception('Project not found in your current scope.');
            }

            $project = $this->hydrateProjectForView($project);

            $title = trim((string) ($_POST['title'] ?? ''));
            if ($title === '') {
                throw new Exception('Project title is required.');
            }

            $existingMetadata = is_array($project['metadata'] ?? null) ? $project['metadata'] : [];
            $existingAttachments = is_array($project['attachments'] ?? null) ? $project['attachments'] : [];
            $newAttachments = $this->attachmentUploadService->uploadMany($_FILES['attachments'] ?? [], 'project-attachments');
            $existingMetadata['attachments'] = array_values(array_merge($existingAttachments, $newAttachments));

            $payload = [
                'title' => $title,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'project_code' => trim((string) ($_POST['project_code'] ?? '')),
                'status' => trim((string) ($_POST['status'] ?? ($project['status'] ?? 'proposed'))),
                'priority' => trim((string) ($_POST['priority'] ?? ($project['priority'] ?? 'medium'))),
                'project_type' => trim((string) ($_POST['project_type'] ?? ($project['project_type'] ?? 'initiative'))),
                'start_date' => ($_POST['start_date'] ?? '') !== '' ? (string) $_POST['start_date'] : null,
                'target_date' => ($_POST['target_date'] ?? '') !== '' ? (string) $_POST['target_date'] : null,
                'completion_percentage' => max(0, min(100, (int) ($_POST['completion_percentage'] ?? ($project['completion_percentage'] ?? 0)))),
                'budget_amount' => ($_POST['budget_amount'] ?? '') !== '' ? (float) $_POST['budget_amount'] : null,
                'owner_user_id' => (int) ($_POST['owner_user_id'] ?? ($project['owner_user_id'] ?? $user['id'] ?? 0)),
                'success_metrics' => trim((string) ($_POST['success_metrics'] ?? '')),
                'delivery_notes' => trim((string) ($_POST['delivery_notes'] ?? '')),
                'status_notes' => trim((string) ($_POST['status_notes'] ?? '')),
                'metadata' => $existingMetadata,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $teamUserIds = $this->extractUserIds($_POST['team_user_ids'] ?? []);
            $this->projectModel->updateProject($projectId, $payload, $teamUserIds, (int) ($user['id'] ?? 0));

            $this->redirectWithMessage('/projects/' . $projectId, 'Project updated successfully.', 'success');
        } catch (Exception $e) {
            $this->redirectWithMessage('/projects/' . (int) $id . '/edit', $e->getMessage(), 'error');
        }
    }

    public function storeMilestone($id = null)
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projectId = (int) $id;
            $project = $this->projectModel->getProject($projectId, $scope);
            if (!$project) {
                throw new Exception('Project not found in your current scope.');
            }

            $title = trim((string) ($_POST['title'] ?? ''));
            if ($title === '') {
                throw new Exception('Milestone title is required.');
            }

            $this->projectModel->createMilestone($projectId, [
                'title' => $title,
                'summary' => trim((string) ($_POST['summary'] ?? '')),
                'due_date' => ($_POST['due_date'] ?? '') !== '' ? (string) $_POST['due_date'] : null,
                'status' => trim((string) ($_POST['status'] ?? 'planned')),
                'completion_percentage' => max(0, min(100, (int) ($_POST['completion_percentage'] ?? 0))),
                'sort_order' => max(0, (int) ($_POST['sort_order'] ?? 0)),
            ], (int) ($user['id'] ?? 0));

            $this->redirectWithMessage('/projects/' . $projectId, 'Milestone added successfully.', 'success');
        } catch (Exception $e) {
            $this->redirectWithMessage('/projects/' . (int) $id, $e->getMessage(), 'error');
        }
    }

    public function storeTask($id = null)
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projectId = (int) $id;
            $project = $this->projectModel->getProject($projectId, $scope);
            if (!$project) {
                throw new Exception('Project not found in your current scope.');
            }

            $this->projectModel->createProjectTask($projectId, [
                'title' => trim((string) ($_POST['title'] ?? '')),
                'description' => trim((string) ($_POST['description'] ?? '')),
                'scope_selection' => trim((string) ($_POST['scope_selection'] ?? '')),
                'parent_task_id' => !empty($_POST['parent_task_id']) ? (int) $_POST['parent_task_id'] : null,
                'category' => trim((string) ($_POST['category'] ?? 'administrative')),
                'priority' => trim((string) ($_POST['priority'] ?? 'medium')),
                'start_date' => ($_POST['start_date'] ?? '') !== '' ? (string) $_POST['start_date'] : null,
                'due_date' => ($_POST['due_date'] ?? '') !== '' ? (string) $_POST['due_date'] : null,
                'assigned_to' => $this->extractUserIds($_POST['assigned_to'] ?? []),
            ], (int) ($user['id'] ?? 0));

            $message = !empty($_POST['parent_task_id']) ? 'Project subtask created successfully.' : 'Project task created successfully.';
            $this->redirectWithMessage('/projects/' . $projectId, $message, 'success');
        } catch (Exception $e) {
            $this->redirectWithMessage('/projects/' . (int) $id, $e->getMessage(), 'error');
        }
    }

    public function archive($id = null)
    {
        try {
            $this->requireAuth();
            $user = $this->getAuthUser();
            $scope = $this->projectModel->getResolvedScope((int) ($user['id'] ?? 0));
            $projectId = (int) $id;
            $project = $this->projectModel->getProject($projectId, $scope);
            if (!$project) {
                throw new Exception('Project not found in your current scope.');
            }

            $this->projectModel->archiveProject($projectId, (int) ($user['id'] ?? 0));
            $this->redirectWithMessage('/projects/' . $projectId, 'Project archived successfully.', 'success');
        } catch (Exception $e) {
            $this->redirectWithMessage('/projects/' . (int) $id, $e->getMessage(), 'error');
        }
    }

    private function extractUserIds($rawValue): array
    {
        $values = is_array($rawValue) ? $rawValue : [$rawValue];
        $userIds = [];

        foreach ($values as $value) {
            $userId = (int) $value;
            if ($userId > 0) {
                $userIds[] = $userId;
            }
        }

        return array_values(array_unique($userIds));
    }

    private function hydrateProjectForView(array $project): array
    {
        $metadata = $project['metadata'] ?? [];

        if (is_string($metadata)) {
            $decodedMetadata = json_decode($metadata, true);
            $metadata = is_array($decodedMetadata) ? $decodedMetadata : [];
        }

        $project['metadata'] = $metadata;
        $project['attachments'] = is_array($metadata['attachments'] ?? null) ? $metadata['attachments'] : [];

        return $project;
    }
}