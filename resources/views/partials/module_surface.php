<?php if (!defined('ABO_WBO_MODULE_SURFACE')): ?>
<?php define('ABO_WBO_MODULE_SURFACE', true); ?>
<style>
    :root {
        --primary-red: var(--abo-primary, #8b1538);
        --primary-red-light: var(--abo-primary-light, #b91c1c);
        --primary-green: var(--abo-secondary, #2d5016);
        --primary-green-light: var(--abo-secondary-light, #3f8b2d);
    }

    .module-surface {
        --module-accent: var(--primary-green);
        --module-accent-soft: rgba(45, 80, 22, 0.12);
        --module-accent-strong: rgba(45, 80, 22, 0.22);
        --module-text-soft: var(--abo-gray-600, #475569);
    }

    .module-surface.theme-tasks {
        --module-accent: #2563eb;
        --module-accent-soft: rgba(37, 99, 235, 0.12);
        --module-accent-strong: rgba(37, 99, 235, 0.24);
    }

    .module-surface.theme-meetings {
        --module-accent: #f59e0b;
        --module-accent-soft: rgba(245, 158, 11, 0.14);
        --module-accent-strong: rgba(245, 158, 11, 0.24);
    }

    .module-surface.theme-events {
        --module-accent: #10b981;
        --module-accent-soft: rgba(16, 185, 129, 0.14);
        --module-accent-strong: rgba(16, 185, 129, 0.24);
    }

    .module-surface.theme-projects {
        --module-accent: #8b1538;
        --module-accent-soft: rgba(139, 21, 56, 0.12);
        --module-accent-strong: rgba(139, 21, 56, 0.24);
    }

    .module-surface.theme-reports {
        --module-accent: #7c3aed;
        --module-accent-soft: rgba(124, 58, 237, 0.12);
        --module-accent-strong: rgba(124, 58, 237, 0.22);
    }

    .module-surface.theme-donations {
        --module-accent: var(--primary-green);
        --module-accent-soft: rgba(45, 80, 22, 0.12);
        --module-accent-strong: rgba(45, 80, 22, 0.22);
    }

    .module-surface.theme-users {
        --module-accent: #0ea5e9;
        --module-accent-soft: rgba(14, 165, 233, 0.12);
        --module-accent-strong: rgba(14, 165, 233, 0.22);
    }

    .module-surface.theme-hierarchy {
        --module-accent: #9333ea;
        --module-accent-soft: rgba(147, 51, 234, 0.12);
        --module-accent-strong: rgba(147, 51, 234, 0.22);
    }

    .module-surface.theme-courses {
        --module-accent: #ea580c;
        --module-accent-soft: rgba(234, 88, 12, 0.12);
        --module-accent-strong: rgba(234, 88, 12, 0.22);
    }

    .module-surface.theme-executive {
        --module-accent: #0f766e;
        --module-accent-soft: rgba(15, 118, 110, 0.12);
        --module-accent-strong: rgba(15, 118, 110, 0.24);
    }

    .module-surface.theme-member {
        --module-accent: #2563eb;
        --module-accent-soft: rgba(37, 99, 235, 0.12);
        --module-accent-strong: rgba(37, 99, 235, 0.24);
    }

    .module-surface.theme-admin {
        --module-accent: #b45309;
        --module-accent-soft: rgba(180, 83, 9, 0.12);
        --module-accent-strong: rgba(180, 83, 9, 0.24);
    }

    .module-surface.theme-system-admin {
        --module-accent: #991b1b;
        --module-accent-soft: rgba(153, 27, 27, 0.12);
        --module-accent-strong: rgba(153, 27, 27, 0.24);
    }

    .module-hero {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        background:
            radial-gradient(circle at top right, var(--module-accent-soft), transparent 32%),
            linear-gradient(145deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        border: 1px solid rgba(148, 163, 184, 0.2);
        box-shadow: 0 22px 46px -34px rgba(15, 23, 42, 0.45);
    }

    .module-hero::before,
    .module-hero::after {
        content: '';
        position: absolute;
        border-radius: 999px;
        pointer-events: none;
    }

    .module-hero::before {
        inset: auto auto -90px -30px;
        width: 220px;
        height: 220px;
        background: linear-gradient(135deg, rgba(185, 28, 28, 0.08), rgba(255, 255, 255, 0));
    }

    .module-hero::after {
        inset: -70px -30px auto auto;
        width: 180px;
        height: 180px;
        background: linear-gradient(135deg, var(--module-accent-soft), rgba(255, 255, 255, 0));
    }

    .module-hero-content {
        position: relative;
        z-index: 1;
    }

    .module-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.9rem;
        margin-bottom: 1rem;
        border-radius: 999px;
        background: rgba(15, 23, 42, 0.04);
        color: var(--module-accent);
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .module-title {
        margin: 0;
        font-size: clamp(2rem, 2.8vw, 2.85rem);
        font-weight: 800;
        line-height: 1.05;
        letter-spacing: -0.03em;
        color: var(--abo-gray-900, #0f172a);
    }

    .module-title i {
        color: var(--module-accent);
    }

    .module-subtitle {
        max-width: 60rem;
        margin: 0.85rem 0 0;
        color: var(--module-text-soft);
        font-size: 1rem;
    }

    .module-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }

    .module-actions .btn {
        border-radius: 999px;
        padding-inline: 1rem;
        box-shadow: none;
    }

    .module-chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
    }

    .module-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.65rem 0.95rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(148, 163, 184, 0.2);
        color: var(--abo-gray-700, #334155);
        font-weight: 600;
        font-size: 0.92rem;
    }

    .module-chip i {
        color: var(--module-accent);
    }

    .module-callout {
        border: 1px solid rgba(148, 163, 184, 0.18);
        border-left: 4px solid var(--module-accent);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(248, 250, 252, 0.92));
        border-radius: 20px;
        padding: 1rem 1.1rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 16px 38px -34px rgba(15, 23, 42, 0.5);
    }

    .module-callout.warning {
        border-left-color: #f59e0b;
        background: linear-gradient(135deg, rgba(255, 251, 235, 0.95), rgba(255, 247, 237, 0.9));
    }

    .module-callout strong {
        color: var(--abo-gray-900, #0f172a);
    }

    .module-stat-card {
        height: 100%;
        border-radius: 22px;
        padding: 1.15rem;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 18px 36px -34px rgba(15, 23, 42, 0.55);
    }

    .module-stat-card .stat-topline {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
    }

    .module-stat-card .stat-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--module-accent-soft);
        color: var(--module-accent);
        font-size: 1.2rem;
    }

    .module-stat-card .stat-value {
        font-size: clamp(1.9rem, 2.4vw, 2.45rem);
        font-weight: 800;
        line-height: 1;
        color: var(--abo-gray-900, #0f172a);
    }

    .module-stat-card .stat-label {
        margin-top: 0.5rem;
        font-size: 0.88rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--abo-gray-500, #64748b);
    }

    .module-stat-card .stat-footnote {
        margin-top: 0.7rem;
        color: var(--module-text-soft);
        font-size: 0.92rem;
    }

    .module-panel {
        height: 100%;
        border-radius: 24px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 22px 42px -36px rgba(15, 23, 42, 0.52);
    }

    .module-panel-header {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.85rem;
        padding: 1.2rem 1.25rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.96), rgba(255, 255, 255, 0.98));
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
    }

    .module-panel-title {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
        color: var(--abo-gray-900, #0f172a);
    }

    .module-panel-title i {
        color: var(--module-accent);
    }

    .module-panel-body {
        padding: 1.2rem 1.25rem;
    }

    .module-soft-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: var(--module-accent-soft);
        color: var(--module-accent);
        font-size: 0.84rem;
        font-weight: 700;
    }

    .module-table {
        width: 100%;
        margin: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .module-table thead th {
        padding: 0.9rem 1rem;
        background: rgba(248, 250, 252, 0.96);
        color: var(--abo-gray-500, #64748b);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border-bottom: 1px solid rgba(148, 163, 184, 0.16);
        white-space: nowrap;
    }

    .module-table tbody td {
        padding: 1rem;
        vertical-align: top;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    }

    .module-table tbody tr:last-child td {
        border-bottom: none;
    }

    .module-table tbody tr:hover {
        background: rgba(248, 250, 252, 0.74);
    }

    .module-row-title {
        font-weight: 700;
        color: var(--abo-gray-900, #0f172a);
    }

    .module-row-meta {
        margin-top: 0.25rem;
        color: var(--module-text-soft);
        font-size: 0.92rem;
    }

    .module-status {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: 0.85rem;
        background: rgba(148, 163, 184, 0.18);
        color: var(--abo-gray-700, #334155);
    }

    .module-status.status-success {
        background: rgba(22, 163, 74, 0.14);
        color: #15803d;
    }

    .module-status.status-warning {
        background: rgba(245, 158, 11, 0.14);
        color: #b45309;
    }

    .module-status.status-danger {
        background: rgba(220, 38, 38, 0.14);
        color: #b91c1c;
    }

    .module-status.status-info {
        background: rgba(37, 99, 235, 0.14);
        color: #1d4ed8;
    }

    .module-status.status-neutral {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }

    .module-stack-list {
        display: grid;
        gap: 0.85rem;
    }

    .module-stack-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 18px;
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid rgba(148, 163, 184, 0.14);
    }

    .module-stack-value {
        font-weight: 800;
        color: var(--module-accent);
        white-space: nowrap;
    }

    .module-key-grid {
        display: grid;
        gap: 0.9rem;
    }

    .module-key-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px dashed rgba(148, 163, 184, 0.25);
    }

    .module-key-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .module-key-label {
        color: var(--module-text-soft);
        font-weight: 600;
    }

    .module-key-value {
        color: var(--abo-gray-900, #0f172a);
        font-weight: 700;
        text-align: right;
    }

    .module-metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 1rem;
    }

    .module-metric-card {
        border-radius: 18px;
        padding: 1rem;
        background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(255, 255, 255, 0.98));
        border: 1px solid rgba(148, 163, 184, 0.14);
    }

    .module-metric-label {
        color: var(--module-text-soft);
        font-size: 0.84rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .module-metric-value {
        margin-top: 0.55rem;
        font-size: 1.65rem;
        line-height: 1.1;
        font-weight: 800;
        color: var(--abo-gray-900, #0f172a);
    }

    .module-progress-track {
        height: 8px;
        border-radius: 999px;
        background: rgba(226, 232, 240, 0.85);
        overflow: hidden;
        margin-top: 0.55rem;
    }

    .module-progress-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, var(--module-accent), color-mix(in srgb, var(--module-accent) 62%, white));
    }

    .module-empty {
        padding: 2rem 1rem;
        text-align: center;
        color: var(--module-text-soft);
    }

    .module-empty i {
        font-size: 2.4rem;
        color: var(--module-accent);
        opacity: 0.75;
    }

    .module-muted-note {
        color: var(--module-text-soft);
        font-size: 0.92rem;
    }

    .module-form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .module-form-grid label {
        display: block;
        margin-bottom: 0.45rem;
        color: var(--abo-gray-700, #334155);
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .module-form-grid .form-select,
    .module-form-grid .form-control {
        border-radius: 14px;
    }

    .module-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 1rem;
    }

    .module-link-card {
        display: block;
        height: 100%;
        padding: 1.15rem;
        border-radius: 22px;
        text-decoration: none;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow: 0 20px 40px -34px rgba(15, 23, 42, 0.45);
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }

    .module-link-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 44px -34px rgba(15, 23, 42, 0.55);
        border-color: var(--module-accent-strong);
        text-decoration: none;
    }

    .module-link-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        background: var(--module-accent-soft);
        color: var(--module-accent);
        font-size: 1.25rem;
    }

    .module-caption {
        color: var(--module-text-soft);
        font-size: 0.9rem;
    }

    @media (max-width: 991px) {
        .module-hero {
            padding: 1.5rem;
            border-radius: 22px;
        }

        .module-title {
            font-size: 1.9rem;
        }
    }
</style>
<?php endif; ?>