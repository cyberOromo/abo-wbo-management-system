<?php
require_once dirname(__DIR__) . '/partials/module_surface.php';

$attachment = $attachment ?? [];
$previewKind = (string) ($preview_kind ?? 'binary');
$displayName = (string) ($attachment['display_name'] ?? 'Attachment');
$contextTitle = (string) ($attachment['context_title'] ?? 'Attachment');
$mimeType = (string) ($attachment['mime_type'] ?? 'application/octet-stream');
$sizeLabel = !empty($attachment['size']) ? number_format(((int) $attachment['size']) / 1024, 1) . ' KB' : 'Unknown size';
?>

<div class="module-surface theme-tasks attachment-viewer-shell">
    <section class="module-hero">
        <div class="module-hero-content">
            <span class="module-kicker"><i class="bi bi-paperclip"></i> Attachment Viewer</span>
            <div class="d-flex flex-column flex-xl-row justify-content-between gap-4 align-items-xl-center">
                <div>
                    <h1 class="module-title"><i class="bi bi-file-earmark-richtext me-2"></i><?= htmlspecialchars($displayName) ?></h1>
                    <p class="module-subtitle">Attached to <?= htmlspecialchars($contextTitle) ?>. Preview inline when the file type supports it, or download the original file directly.</p>
                </div>
                <div class="module-actions">
                    <a href="<?= htmlspecialchars((string) ($attachment['stream_url'] ?? '#')) ?>" class="btn btn-outline-primary" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1"></i>Open Raw</a>
                    <a href="<?= htmlspecialchars((string) ($attachment['download_url'] ?? '#')) ?>" class="btn btn-primary"><i class="bi bi-download me-1"></i>Download</a>
                </div>
            </div>
            <div class="module-chip-row">
                <span class="module-chip"><i class="bi bi-filetype-bin"></i><?= htmlspecialchars($mimeType) ?></span>
                <span class="module-chip"><i class="bi bi-hdd"></i><?= htmlspecialchars($sizeLabel) ?></span>
            </div>
        </div>
    </section>

    <div class="module-panel">
        <div class="module-panel-header">
            <h2 class="module-panel-title"><i class="bi bi-eye me-2"></i>Preview</h2>
        </div>
        <div class="module-panel-body">
            <?php if ($previewKind === 'image'): ?>
                <div class="attachment-preview-frame image-frame">
                    <img src="<?= htmlspecialchars((string) ($attachment['stream_url'] ?? '#')) ?>" alt="<?= htmlspecialchars($displayName) ?>">
                </div>
            <?php elseif ($previewKind === 'video'): ?>
                <div class="attachment-preview-frame media-frame">
                    <video controls preload="metadata" src="<?= htmlspecialchars((string) ($attachment['stream_url'] ?? '#')) ?>"></video>
                </div>
            <?php elseif ($previewKind === 'audio'): ?>
                <div class="attachment-preview-empty">
                    <i class="bi bi-file-earmark-music"></i>
                    <p class="mb-3">Audio preview is available directly in the browser.</p>
                    <audio controls preload="metadata" src="<?= htmlspecialchars((string) ($attachment['stream_url'] ?? '#')) ?>" class="w-100"></audio>
                </div>
            <?php elseif (in_array($previewKind, ['pdf', 'text'], true)): ?>
                <div class="attachment-preview-frame">
                    <iframe src="<?= htmlspecialchars((string) ($attachment['stream_url'] ?? '#')) ?>" title="<?= htmlspecialchars($displayName) ?>"></iframe>
                </div>
            <?php else: ?>
                <div class="attachment-preview-empty">
                    <i class="bi bi-file-earmark-lock2"></i>
                    <p class="mb-2">This file type is not previewable inline yet.</p>
                    <p class="module-muted-note mb-3">Use the raw-open or download actions above to inspect the original attachment safely.</p>
                    <a href="<?= htmlspecialchars((string) ($attachment['download_url'] ?? '#')) ?>" class="btn btn-primary"><i class="bi bi-download me-1"></i>Download File</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .attachment-card .attachment-link {
        color: inherit;
        text-decoration: none;
    }

    .attachment-card .attachment-link:hover {
        color: var(--module-accent);
    }

    .attachment-actions {
        min-width: 10rem;
    }

    .attachment-viewer-shell .module-panel-body {
        padding: 1rem;
    }

    .attachment-preview-frame {
        min-height: 70vh;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.18);
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.98));
    }

    .attachment-preview-frame iframe,
    .attachment-preview-frame img,
    .attachment-preview-frame video {
        width: 100%;
        height: 70vh;
        border: 0;
        display: block;
        background: #fff;
    }

    .attachment-preview-frame.image-frame {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .attachment-preview-frame.image-frame img {
        height: auto;
        max-height: 70vh;
        object-fit: contain;
    }

    .attachment-preview-empty {
        min-height: 22rem;
        border-radius: 20px;
        border: 1px dashed rgba(148, 163, 184, 0.35);
        background: rgba(248, 250, 252, 0.9);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 2rem;
    }

    .attachment-preview-empty i {
        font-size: 2.5rem;
        color: var(--module-accent);
        margin-bottom: 1rem;
    }
</style>