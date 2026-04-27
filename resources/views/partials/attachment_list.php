<?php
$attachments = is_array($attachments ?? null) ? $attachments : [];
$resource = (string) ($resource ?? 'tasks');
$resourceId = (int) ($resourceId ?? 0);
$emptyMessage = (string) ($emptyMessage ?? 'No attachments uploaded yet.');
$contextLabel = (string) ($contextLabel ?? 'attachment');
?>

<?php if (!empty($attachments)): ?>
    <div class="module-stack-list">
        <?php foreach ($attachments as $index => $attachment): ?>
            <?php
            $displayName = (string) ($attachment['original_name'] ?? $attachment['stored_name'] ?? 'Attachment');
            $sizeLabel = !empty($attachment['size']) ? number_format(((int) $attachment['size']) / 1024, 1) . ' KB' : 'File';
            $mimeType = (string) ($attachment['mime_type'] ?? 'application/octet-stream');
            $viewUrl = '/attachments/' . $resource . '/' . $resourceId . '/' . $index;
            $downloadUrl = $viewUrl . '/download';
            ?>
            <div class="module-stack-item attachment-card">
                <div>
                    <div class="module-row-title"><a href="<?= htmlspecialchars($viewUrl) ?>" class="attachment-link"><?= htmlspecialchars($displayName) ?></a></div>
                    <div class="module-row-meta"><?= htmlspecialchars($contextLabel) ?> preview and secure download</div>
                    <div class="module-row-meta small"><?= htmlspecialchars($mimeType) ?></div>
                </div>
                <div class="attachment-actions text-end">
                    <div class="module-stack-value"><?= htmlspecialchars($sizeLabel) ?></div>
                    <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">
                        <a href="<?= htmlspecialchars($viewUrl) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye me-1"></i>View</a>
                        <a href="<?= htmlspecialchars($downloadUrl) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download me-1"></i>Download</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="module-empty py-4"><i class="bi bi-paperclip"></i><p class="mb-0 mt-2"><?= htmlspecialchars($emptyMessage) ?></p></div>
<?php endif; ?>