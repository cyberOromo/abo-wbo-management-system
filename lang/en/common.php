<?php

/**
 * English Common Translations
 * 
 * Common phrases, buttons, labels, and messages used throughout
 * the ABO-WBO Management System application.
 * 
 * @package Lang\EN
 * @version 1.0.0
 */

return [
    // Application Details
    'app_name' => 'ABO-WBO Management System',
    'app_description' => 'Global Oromo Organization Management Platform',
    'app_version' => 'Version 1.0.0',
    'copyright' => '© 2025 ABO-WBO Global Organization. All rights reserved.',

    // Navigation & Menu
    'navigation' => [
        'home' => 'Home',
        'dashboard' => 'Dashboard',
        'profile' => 'Profile',
        'settings' => 'Settings',
        'logout' => 'Logout',
        'login' => 'Login',
        'register' => 'Register',
        'about' => 'About',
        'contact' => 'Contact',
        'help' => 'Help',
        'documentation' => 'Documentation',
        'support' => 'Support'
    ],

    // Common Actions
    'actions' => [
        'create' => 'Create',
        'edit' => 'Edit',
        'update' => 'Update',
        'save' => 'Save',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'submit' => 'Submit',
        'search' => 'Search',
        'filter' => 'Filter',
        'sort' => 'Sort',
        'export' => 'Export',
        'import' => 'Import',
        'download' => 'Download',
        'upload' => 'Upload',
        'view' => 'View',
        'details' => 'Details',
        'close' => 'Close',
        'confirm' => 'Confirm',
        'approve' => 'Approve',
        'reject' => 'Reject',
        'assign' => 'Assign',
        'unassign' => 'Unassign',
        'activate' => 'Activate',
        'deactivate' => 'Deactivate',
        'suspend' => 'Suspend',
        'restore' => 'Restore',
        'duplicate' => 'Duplicate',
        'copy' => 'Copy',
        'move' => 'Move',
        'print' => 'Print',
        'share' => 'Share',
        'send' => 'Send',
        'receive' => 'Receive',
        'refresh' => 'Refresh',
        'reload' => 'Reload',
        'reset' => 'Reset',
        'clear' => 'Clear',
        'select_all' => 'Select All',
        'deselect_all' => 'Deselect All',
        'back' => 'Back',
        'next' => 'Next',
        'previous' => 'Previous',
        'continue' => 'Continue',
        'finish' => 'Finish',
        'skip' => 'Skip'
    ],

    // Common Labels
    'labels' => [
        'id' => 'ID',
        'name' => 'Name',
        'title' => 'Title',
        'description' => 'Description',
        'type' => 'Type',
        'category' => 'Category',
        'status' => 'Status',
        'priority' => 'Priority',
        'level' => 'Level',
        'date' => 'Date',
        'time' => 'Time',
        'datetime' => 'Date & Time',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
        'created_by' => 'Created By',
        'updated_by' => 'Updated By',
        'assigned_to' => 'Assigned To',
        'assigned_by' => 'Assigned By',
        'due_date' => 'Due Date',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'duration' => 'Duration',
        'location' => 'Location',
        'address' => 'Address',
        'email' => 'Email',
        'phone' => 'Phone',
        'website' => 'Website',
        'notes' => 'Notes',
        'comments' => 'Comments',
        'tags' => 'Tags',
        'keywords' => 'Keywords',
        'language' => 'Language',
        'country' => 'Country',
        'city' => 'City',
        'organization' => 'Organization',
        'department' => 'Department',
        'position' => 'Position',
        'role' => 'Role',
        'permissions' => 'Permissions',
        'access_level' => 'Access Level',
        'visibility' => 'Visibility',
        'public' => 'Public',
        'private' => 'Private',
        'internal' => 'Internal',
        'external' => 'External',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'pending' => 'Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'completed' => 'Completed',
        'in_progress' => 'In Progress',
        'on_hold' => 'On Hold',
        'cancelled' => 'Cancelled',
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived'
    ],

    // Organizational Hierarchy
    'hierarchy' => [
        'global' => 'Global',
        'godina' => 'Godina',
        'gamta' => 'Gamta',
        'gurmu' => 'Gurmu',
        'levels' => [
            'global' => 'Global Level',
            'godina' => 'Godina Level',
            'gamta' => 'Gamta Level',
            'gurmu' => 'Gurmu Level'
        ],
        'select_global' => 'Select Global Organization',
        'select_godina' => 'Select Godina',
        'select_gamta' => 'Select Gamta',
        'select_gurmu' => 'Select Gurmu',
        'hierarchy_structure' => 'Organizational Hierarchy',
        'manage_hierarchy' => 'Manage Hierarchy'
    ],

    // Status Messages
    'status' => [
        'success' => 'Success',
        'error' => 'Error',
        'warning' => 'Warning',
        'info' => 'Information',
        'loading' => 'Loading...',
        'processing' => 'Processing...',
        'saving' => 'Saving...',
        'deleting' => 'Deleting...',
        'uploading' => 'Uploading...',
        'downloading' => 'Downloading...',
        'connecting' => 'Connecting...',
        'disconnected' => 'Disconnected',
        'offline' => 'Offline',
        'online' => 'Online',
        'available' => 'Available',
        'unavailable' => 'Unavailable',
        'maintenance' => 'Under Maintenance',
        'coming_soon' => 'Coming Soon'
    ],

    // Error Messages
    'errors' => [
        'general_error' => 'An error occurred. Please try again.',
        'network_error' => 'Network connection error. Please check your internet connection.',
        'server_error' => 'Server error. Please try again later.',
        'permission_denied' => 'Permission denied. You do not have access to this resource.',
        'not_found' => 'The requested resource was not found.',
        'validation_error' => 'Please fix the validation errors and try again.',
        'file_upload_error' => 'File upload failed. Please try again.',
        'database_error' => 'Database connection error. Please try again later.',
        'session_expired' => 'Your session has expired. Please login again.',
        'csrf_token_mismatch' => 'Security token mismatch. Please refresh the page.',
        'rate_limit_exceeded' => 'Too many requests. Please wait before trying again.',
        'maintenance_mode' => 'The system is currently under maintenance. Please try again later.'
    ],

    // Success Messages
    'success' => [
        'created' => 'Successfully created.',
        'updated' => 'Successfully updated.',
        'deleted' => 'Successfully deleted.',
        'saved' => 'Successfully saved.',
        'uploaded' => 'File uploaded successfully.',
        'downloaded' => 'File downloaded successfully.',
        'sent' => 'Successfully sent.',
        'approved' => 'Successfully approved.',
        'rejected' => 'Successfully rejected.',
        'assigned' => 'Successfully assigned.',
        'activated' => 'Successfully activated.',
        'deactivated' => 'Successfully deactivated.',
        'restored' => 'Successfully restored.',
        'archived' => 'Successfully archived.',
        'published' => 'Successfully published.',
        'copied' => 'Successfully copied.',
        'moved' => 'Successfully moved.',
        'imported' => 'Data imported successfully.',
        'exported' => 'Data exported successfully.'
    ],

    // Confirmation Messages
    'confirmations' => [
        'delete' => 'Are you sure you want to delete this item?',
        'delete_multiple' => 'Are you sure you want to delete the selected items?',
        'delete_permanent' => 'This action cannot be undone. Are you sure?',
        'approve' => 'Are you sure you want to approve this item?',
        'reject' => 'Are you sure you want to reject this item?',
        'activate' => 'Are you sure you want to activate this item?',
        'deactivate' => 'Are you sure you want to deactivate this item?',
        'suspend' => 'Are you sure you want to suspend this user?',
        'restore' => 'Are you sure you want to restore this item?',
        'archive' => 'Are you sure you want to archive this item?',
        'publish' => 'Are you sure you want to publish this item?',
        'cancel_operation' => 'Are you sure you want to cancel this operation?',
        'leave_page' => 'You have unsaved changes. Are you sure you want to leave?',
        'logout' => 'Are you sure you want to logout?'
    ],

    // Time & Dates
    'time' => [
        'now' => 'Now',
        'today' => 'Today',
        'yesterday' => 'Yesterday',
        'tomorrow' => 'Tomorrow',
        'this_week' => 'This Week',
        'last_week' => 'Last Week',
        'next_week' => 'Next Week',
        'this_month' => 'This Month',
        'last_month' => 'Last Month',
        'next_month' => 'Next Month',
        'this_year' => 'This Year',
        'last_year' => 'Last Year',
        'next_year' => 'Next Year',
        'minutes_ago' => ':count minute ago|:count minutes ago',
        'hours_ago' => ':count hour ago|:count hours ago',
        'days_ago' => ':count day ago|:count days ago',
        'weeks_ago' => ':count week ago|:count weeks ago',
        'months_ago' => ':count month ago|:count months ago',
        'years_ago' => ':count year ago|:count years ago',
        'in_minutes' => 'in :count minute|in :count minutes',
        'in_hours' => 'in :count hour|in :count hours',
        'in_days' => 'in :count day|in :count days',
        'in_weeks' => 'in :count week|in :count weeks',
        'in_months' => 'in :count month|in :count months',
        'in_years' => 'in :count year|in :count years'
    ],

    // Pagination
    'pagination' => [
        'previous' => '&laquo; Previous',
        'next' => 'Next &raquo;',
        'showing' => 'Showing',
        'to' => 'to',
        'of' => 'of',
        'results' => 'results',
        'page' => 'Page',
        'per_page' => 'Per Page',
        'first' => 'First',
        'last' => 'Last',
        'no_results' => 'No results found',
        'empty_state' => 'No data available'
    ],

    // File Operations
    'files' => [
        'file' => 'File',
        'files' => 'Files',
        'document' => 'Document',
        'documents' => 'Documents',
        'image' => 'Image',
        'images' => 'Images',
        'video' => 'Video',
        'videos' => 'Videos',
        'audio' => 'Audio',
        'size' => 'Size',
        'type' => 'Type',
        'format' => 'Format',
        'extension' => 'Extension',
        'upload_file' => 'Upload File',
        'choose_file' => 'Choose File',
        'drag_drop' => 'Drag and drop files here or click to select',
        'max_size' => 'Maximum file size: :size',
        'allowed_types' => 'Allowed file types: :types',
        'file_too_large' => 'File is too large',
        'invalid_file_type' => 'Invalid file type',
        'upload_progress' => 'Upload Progress',
        'upload_complete' => 'Upload Complete'
    ],

    // Search & Filter
    'search' => [
        'search' => 'Search',
        'search_placeholder' => 'Search...',
        'search_results' => 'Search Results',
        'no_results' => 'No results found',
        'filter' => 'Filter',
        'filters' => 'Filters',
        'clear_filters' => 'Clear Filters',
        'apply_filters' => 'Apply Filters',
        'sort_by' => 'Sort by',
        'sort_asc' => 'Ascending',
        'sort_desc' => 'Descending',
        'show_all' => 'Show All',
        'advanced_search' => 'Advanced Search'
    ],

    // Notifications
    'notifications' => [
        'notification' => 'Notification',
        'notifications' => 'Notifications',
        'no_notifications' => 'No notifications',
        'mark_read' => 'Mark as Read',
        'mark_unread' => 'Mark as Unread',
        'mark_all_read' => 'Mark All as Read',
        'delete_notification' => 'Delete Notification',
        'view_all' => 'View All Notifications',
        'new_notification' => 'New Notification',
        'unread_count' => ':count unread notification|:count unread notifications'
    ],

    // Accessibility
    'accessibility' => [
        'skip_to_content' => 'Skip to content',
        'close_dialog' => 'Close dialog',
        'toggle_menu' => 'Toggle menu',
        'loading_content' => 'Loading content',
        'error_content' => 'Error loading content',
        'required_field' => 'Required field',
        'optional_field' => 'Optional field',
        'help_text' => 'Help text',
        'screen_reader_only' => 'Screen reader only content'
    ],

    // Placeholders
    'placeholders' => [
        'enter_name' => 'Enter name',
        'enter_email' => 'Enter email address',
        'enter_phone' => 'Enter phone number',
        'enter_description' => 'Enter description',
        'enter_notes' => 'Enter notes',
        'select_option' => 'Select an option',
        'select_date' => 'Select date',
        'select_time' => 'Select time',
        'type_to_search' => 'Type to search',
        'no_selection' => 'No selection made'
    ]
];