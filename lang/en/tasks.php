<?php

/**
 * English Task Management Translations
 * 
 * Task management interface, workflows, status updates,
 * and project management translations.
 * 
 * @package Lang\EN
 * @version 1.0.0
 */

return [
    // Task Management
    'title' => 'Task Management',
    'create_task' => 'Create Task',
    'edit_task' => 'Edit Task',
    'view_task' => 'View Task',
    'delete_task' => 'Delete Task',
    'assign_task' => 'Assign Task',
    'my_tasks' => 'My Tasks',
    'all_tasks' => 'All Tasks',
    'team_tasks' => 'Team Tasks',
    'task_details' => 'Task Details',
    'task_list' => 'Task List',
    'task_board' => 'Task Board',
    'task_calendar' => 'Task Calendar',

    // Task Properties
    'properties' => [
        'title' => 'Task Title',
        'description' => 'Description',
        'priority' => 'Priority',
        'status' => 'Status',
        'category' => 'Category',
        'level_scope' => 'Scope Level',
        'scope_id' => 'Scope Organization',
        'parent_task' => 'Parent Task',
        'sub_tasks' => 'Sub Tasks',
        'dependencies' => 'Dependencies',
        'tags' => 'Tags',
        'attachments' => 'Attachments',
        'comments' => 'Comments',
        'activity_log' => 'Activity Log',
        'assigned_to' => 'Assigned To',
        'assigned_by' => 'Assigned By',
        'created_by' => 'Created By',
        'start_date' => 'Start Date',
        'due_date' => 'Due Date',
        'completed_date' => 'Completed Date',
        'estimated_hours' => 'Estimated Hours',
        'actual_hours' => 'Actual Hours',
        'completion_percentage' => 'Completion Percentage',
        'created_at' => 'Created At',
        'updated_at' => 'Last Updated'
    ],

    // Priority Levels
    'priority' => [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'urgent' => 'Urgent',
        'critical' => 'Critical',
        'priority_colors' => [
            'low' => 'Low Priority (Green)',
            'medium' => 'Medium Priority (Yellow)',
            'high' => 'High Priority (Orange)',
            'urgent' => 'Urgent Priority (Red)',
            'critical' => 'Critical Priority (Dark Red)'
        ]
    ],

    // Task Status
    'status' => [
        'pending' => 'Pending',
        'in_progress' => 'In Progress',
        'under_review' => 'Under Review',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'on_hold' => 'On Hold',
        'blocked' => 'Blocked',
        'archived' => 'Archived',
        'status_descriptions' => [
            'pending' => 'Task is waiting to be started',
            'in_progress' => 'Task is currently being worked on',
            'under_review' => 'Task is being reviewed',
            'completed' => 'Task has been finished successfully',
            'cancelled' => 'Task has been cancelled',
            'on_hold' => 'Task is temporarily paused',
            'blocked' => 'Task is blocked by dependencies',
            'archived' => 'Task has been archived'
        ]
    ],

    // Task Categories
    'categories' => [
        'administrative' => 'Administrative',
        'financial' => 'Financial',
        'educational' => 'Educational',
        'social' => 'Social',
        'technical' => 'Technical',
        'community' => 'Community',
        'cultural' => 'Cultural',
        'development' => 'Development',
        'maintenance' => 'Maintenance',
        'research' => 'Research',
        'planning' => 'Planning',
        'coordination' => 'Coordination'
    ],

    // Scope Levels
    'scope_levels' => [
        'global' => 'Global Level',
        'godina' => 'Godina Level',
        'gamta' => 'Gamta Level',
        'gurmu' => 'Gurmu Level',
        'cross_level' => 'Cross-Level',
        'scope_descriptions' => [
            'global' => 'Tasks affecting the entire global organization',
            'godina' => 'Tasks specific to a Godina region',
            'gamta' => 'Tasks specific to a Gamta district',
            'gurmu' => 'Tasks specific to a local Gurmu',
            'cross_level' => 'Tasks spanning multiple organizational levels'
        ]
    ],

    // Task Assignment
    'assignment' => [
        'assign_to' => 'Assign To',
        'assign_multiple' => 'Assign to Multiple Users',
        'reassign' => 'Reassign Task',
        'unassign' => 'Unassign Task',
        'assigned_users' => 'Assigned Users',
        'assignment_date' => 'Assignment Date',
        'assignment_notes' => 'Assignment Notes',
        'notify_assignees' => 'Notify Assignees',
        'assignment_email' => 'Send Assignment Email',
        'assignment_sms' => 'Send Assignment SMS',
        'bulk_assign' => 'Bulk Assignment',
        'auto_assign' => 'Auto Assignment Rules',
        'workload_balance' => 'Workload Balance',
        'assignment_history' => 'Assignment History'
    ],

    // Task Actions
    'actions' => [
        'start_task' => 'Start Task',
        'pause_task' => 'Pause Task',
        'resume_task' => 'Resume Task',
        'complete_task' => 'Complete Task',
        'reopen_task' => 'Reopen Task',
        'cancel_task' => 'Cancel Task',
        'archive_task' => 'Archive Task',
        'duplicate_task' => 'Duplicate Task',
        'move_task' => 'Move Task',
        'merge_tasks' => 'Merge Tasks',
        'split_task' => 'Split Task',
        'convert_to_project' => 'Convert to Project',
        'add_subtask' => 'Add Subtask',
        'add_dependency' => 'Add Dependency',
        'set_reminder' => 'Set Reminder',
        'track_time' => 'Track Time',
        'log_work' => 'Log Work',
        'update_progress' => 'Update Progress'
    ],

    // Task Views
    'views' => [
        'list_view' => 'List View',
        'board_view' => 'Board View',
        'calendar_view' => 'Calendar View',
        'gantt_view' => 'Gantt View',
        'timeline_view' => 'Timeline View',
        'workload_view' => 'Workload View',
        'report_view' => 'Report View',
        'my_view' => 'My Tasks View',
        'team_view' => 'Team View',
        'hierarchy_view' => 'Hierarchy View'
    ],

    // Filters and Sorting
    'filters' => [
        'filter_by' => 'Filter By',
        'all_tasks' => 'All Tasks',
        'my_tasks' => 'My Tasks',
        'assigned_by_me' => 'Assigned by Me',
        'overdue_tasks' => 'Overdue Tasks',
        'due_today' => 'Due Today',
        'due_this_week' => 'Due This Week',
        'completed_tasks' => 'Completed Tasks',
        'high_priority' => 'High Priority',
        'by_category' => 'By Category',
        'by_status' => 'By Status',
        'by_priority' => 'By Priority',
        'by_assignee' => 'By Assignee',
        'by_due_date' => 'By Due Date',
        'by_creation_date' => 'By Creation Date',
        'custom_filter' => 'Custom Filter',
        'saved_filters' => 'Saved Filters',
        'clear_filters' => 'Clear Filters'
    ],

    // Task Comments
    'comments' => [
        'add_comment' => 'Add Comment',
        'edit_comment' => 'Edit Comment',
        'delete_comment' => 'Delete Comment',
        'comment_placeholder' => 'Write your comment here...',
        'no_comments' => 'No comments yet',
        'comment_added' => 'Comment added successfully',
        'comment_updated' => 'Comment updated successfully',
        'comment_deleted' => 'Comment deleted successfully',
        'reply_to_comment' => 'Reply to Comment',
        'comment_history' => 'Comment History',
        'mention_user' => 'Mention User (@username)',
        'attach_file' => 'Attach File to Comment'
    ],

    // Task Attachments
    'attachments' => [
        'add_attachment' => 'Add Attachment',
        'upload_file' => 'Upload File',
        'attach_from_library' => 'Attach from Library',
        'no_attachments' => 'No attachments',
        'download_attachment' => 'Download',
        'view_attachment' => 'View',
        'delete_attachment' => 'Delete',
        'attachment_uploaded' => 'Attachment uploaded successfully',
        'attachment_deleted' => 'Attachment deleted successfully',
        'max_file_size' => 'Maximum file size: :size',
        'allowed_file_types' => 'Allowed file types: :types'
    ],

    // Task Templates
    'templates' => [
        'task_templates' => 'Task Templates',
        'create_template' => 'Create Template',
        'use_template' => 'Use Template',
        'template_name' => 'Template Name',
        'template_description' => 'Template Description',
        'save_as_template' => 'Save as Template',
        'manage_templates' => 'Manage Templates',
        'template_categories' => 'Template Categories',
        'common_templates' => 'Common Templates',
        'my_templates' => 'My Templates',
        'shared_templates' => 'Shared Templates'
    ],

    // Task Dependencies
    'dependencies' => [
        'task_dependencies' => 'Task Dependencies',
        'add_dependency' => 'Add Dependency',
        'remove_dependency' => 'Remove Dependency',
        'depends_on' => 'Depends On',
        'blocking' => 'Blocking',
        'dependency_type' => 'Dependency Type',
        'finish_to_start' => 'Finish to Start',
        'start_to_start' => 'Start to Start',
        'finish_to_finish' => 'Finish to Finish',
        'start_to_finish' => 'Start to Finish',
        'dependency_lag' => 'Dependency Lag',
        'circular_dependency' => 'Circular dependency detected',
        'dependency_chain' => 'Dependency Chain'
    ],

    // Time Tracking
    'time_tracking' => [
        'time_tracking' => 'Time Tracking',
        'start_timer' => 'Start Timer',
        'stop_timer' => 'Stop Timer',
        'pause_timer' => 'Pause Timer',
        'resume_timer' => 'Resume Timer',
        'log_time' => 'Log Time',
        'time_spent' => 'Time Spent',
        'estimated_time' => 'Estimated Time',
        'remaining_time' => 'Remaining Time',
        'time_entries' => 'Time Entries',
        'daily_timesheet' => 'Daily Timesheet',
        'weekly_timesheet' => 'Weekly Timesheet',
        'time_report' => 'Time Report',
        'billable_hours' => 'Billable Hours',
        'non_billable_hours' => 'Non-billable Hours'
    ],

    // Task Notifications
    'notifications' => [
        'task_assigned' => 'Task assigned to you',
        'task_updated' => 'Task has been updated',
        'task_completed' => 'Task has been completed',
        'task_overdue' => 'Task is overdue',
        'task_reminder' => 'Task reminder',
        'task_comment' => 'New comment on task',
        'task_mentioned' => 'You were mentioned in a task',
        'task_due_soon' => 'Task is due soon',
        'dependency_completed' => 'Task dependency completed',
        'subtask_completed' => 'Subtask completed',
        'notification_settings' => 'Task Notification Settings',
        'email_notifications' => 'Email Notifications',
        'sms_notifications' => 'SMS Notifications',
        'in_app_notifications' => 'In-app Notifications'
    ],

    // Task Reports
    'reports' => [
        'task_reports' => 'Task Reports',
        'completion_report' => 'Completion Report',
        'productivity_report' => 'Productivity Report',
        'workload_report' => 'Workload Report',
        'overdue_report' => 'Overdue Tasks Report',
        'time_tracking_report' => 'Time Tracking Report',
        'assignment_report' => 'Assignment Report',
        'category_report' => 'Category Report',
        'priority_report' => 'Priority Report',
        'custom_report' => 'Custom Report',
        'export_report' => 'Export Report',
        'schedule_report' => 'Schedule Report',
        'report_period' => 'Report Period',
        'generate_report' => 'Generate Report'
    ],

    // Task Validation Messages
    'validation' => [
        'title_required' => 'Task title is required',
        'title_max_length' => 'Task title cannot exceed 255 characters',
        'description_max_length' => 'Description cannot exceed 5000 characters',
        'due_date_future' => 'Due date must be in the future',
        'start_date_before_due' => 'Start date must be before due date',
        'assignee_required' => 'At least one assignee is required',
        'priority_valid' => 'Please select a valid priority level',
        'category_valid' => 'Please select a valid category',
        'scope_level_required' => 'Scope level is required',
        'estimated_hours_positive' => 'Estimated hours must be a positive number',
        'completion_percentage_range' => 'Completion percentage must be between 0 and 100',
        'file_upload_size' => 'File size cannot exceed :size',
        'file_upload_type' => 'File type not allowed'
    ],

    // Success Messages
    'success' => [
        'task_created' => 'Task created successfully',
        'task_updated' => 'Task updated successfully',
        'task_deleted' => 'Task deleted successfully',
        'task_assigned' => 'Task assigned successfully',
        'task_completed' => 'Task marked as completed',
        'task_archived' => 'Task archived successfully',
        'status_updated' => 'Task status updated successfully',
        'priority_updated' => 'Task priority updated successfully',
        'comment_added' => 'Comment added successfully',
        'file_uploaded' => 'File uploaded successfully',
        'time_logged' => 'Time logged successfully',
        'reminder_set' => 'Reminder set successfully',
        'template_saved' => 'Task template saved successfully',
        'bulk_update' => 'Tasks updated successfully'
    ],

    // Error Messages
    'errors' => [
        'task_not_found' => 'Task not found',
        'access_denied' => 'Access denied to this task',
        'cannot_delete' => 'Cannot delete task with dependencies',
        'cannot_complete' => 'Cannot complete task with incomplete dependencies',
        'assignment_failed' => 'Task assignment failed',
        'update_failed' => 'Task update failed',
        'file_upload_failed' => 'File upload failed',
        'invalid_status_change' => 'Invalid status change',
        'circular_dependency' => 'Circular dependency detected',
        'user_not_found' => 'Assigned user not found',
        'invalid_date_range' => 'Invalid date range',
        'template_save_failed' => 'Failed to save template'
    ],

    // Help and Tips
    'help' => [
        'task_help' => 'Task Management Help',
        'keyboard_shortcuts' => 'Keyboard Shortcuts',
        'tips_title' => 'Task Management Tips',
        'tips' => [
            'Use clear and descriptive task titles',
            'Set realistic due dates and deadlines',
            'Break large tasks into smaller subtasks',
            'Use tags to organize related tasks',
            'Regularly update task progress',
            'Communicate with team members through comments',
            'Use templates for recurring tasks',
            'Set up notifications for important deadlines'
        ],
        'best_practices' => 'Best Practices',
        'shortcuts' => [
            'n' => 'Create new task',
            'e' => 'Edit selected task',
            'd' => 'Delete selected task',
            'c' => 'Complete selected task',
            'a' => 'Assign selected task',
            'f' => 'Filter tasks',
            's' => 'Search tasks',
            'r' => 'Refresh task list'
        ]
    ]
];