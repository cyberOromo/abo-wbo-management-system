<?php

namespace App\Validators;

/**
 * TaskValidator - Validates task-related input data
 * 
 * Handles validation for task creation, updates, assignments,
 * and task management operations.
 * 
 * @package App\Validators
 * @version 1.0.0
 */
class TaskValidator extends BaseValidator
{
    /**
     * Define validation rules for task operations
     */
    protected function defineRules(): void
    {
        // Default rules - can be overridden by specific validation methods
        $this->rules = [];
    }

    /**
     * Validate task creation data
     */
    public function validateTaskCreation(array $data): bool
    {
        $this->rules = [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:10|max:2000',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|in:administrative,cultural,educational,fundraising,social,political,religious',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'scope_id' => 'required|integer|min:1',
            'assigned_to' => 'integer|min:1',
            'due_date' => 'required|date',
            'estimated_hours' => 'numeric|min:0.5|max:1000',
            'dependencies' => 'array',
            'attachments' => 'array',
            'tags' => 'array',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
            'recurrence_end_date' => 'required_if:is_recurring,true|date|after:due_date'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task update data
     */
    public function validateTaskUpdate(array $data): bool
    {
        $this->rules = [
            'title' => 'min:3|max:255',
            'description' => 'min:10|max:2000',
            'priority' => 'in:low,medium,high,urgent',
            'category' => 'in:administrative,cultural,educational,fundraising,social,political,religious',
            'assigned_to' => 'integer|min:1',
            'due_date' => 'date',
            'estimated_hours' => 'numeric|min:0.5|max:1000',
            'dependencies' => 'array',
            'tags' => 'array',
            'is_recurring' => 'boolean',
            'recurrence_pattern' => 'required_if:is_recurring,true|in:daily,weekly,monthly,yearly',
            'recurrence_end_date' => 'required_if:is_recurring,true|date|after:due_date'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task status update
     */
    public function validateStatusUpdate(array $data): bool
    {
        $this->rules = [
            'status' => 'required|in:pending,in_progress,completed,cancelled,on_hold',
            'completion_percentage' => 'integer|min:0|max:100',
            'notes' => 'max:1000',
            'attachments' => 'array'
        ];

        // Additional validation based on status
        if (isset($data['status'])) {
            switch ($data['status']) {
                case 'completed':
                    $this->rules['completion_percentage'] = 'required|integer|min:100|max:100';
                    break;
                case 'in_progress':
                    $this->rules['completion_percentage'] = 'required|integer|min:1|max:99';
                    break;
                case 'cancelled':
                case 'on_hold':
                    $this->rules['notes'] = 'required|min:10|max:1000';
                    break;
            }
        }

        return $this->validate($data);
    }

    /**
     * Validate task assignment data
     */
    public function validateTaskAssignment(array $data): bool
    {
        $this->rules = [
            'assigned_to' => 'required|integer|min:1',
            'assignment_notes' => 'max:500',
            'priority_override' => 'in:low,medium,high,urgent',
            'due_date_override' => 'date|after:today'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task comment data
     */
    public function validateTaskComment(array $data): bool
    {
        $this->rules = [
            'comment' => 'required|min:3|max:1000',
            'is_private' => 'boolean',
            'attachments' => 'array'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task search/filter data
     */
    public function validateTaskSearch(array $data): bool
    {
        $this->rules = [
            'search' => 'max:255',
            'status' => 'in:pending,in_progress,completed,cancelled,on_hold',
            'priority' => 'in:low,medium,high,urgent',
            'category' => 'in:administrative,cultural,educational,fundraising,social,political,religious',
            'level_scope' => 'in:global,godina,gamta,gurmu',
            'scope_id' => 'integer|min:1',
            'assigned_to' => 'integer|min:1',
            'created_by' => 'integer|min:1',
            'due_date_from' => 'date',
            'due_date_to' => 'date',
            'created_from' => 'date',
            'created_to' => 'date',
            'completion_percentage_min' => 'integer|min:0|max:100',
            'completion_percentage_max' => 'integer|min:0|max:100',
            'overdue_only' => 'boolean',
            'tags' => 'array',
            'sort_by' => 'in:title,priority,due_date,created_at,updated_at,completion_percentage',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:10|max:100'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task time tracking data
     */
    public function validateTimeTracking(array $data): bool
    {
        $this->rules = [
            'hours_worked' => 'required|numeric|min:0.1|max:24',
            'work_date' => 'required|date',
            'description' => 'required|min:10|max:500',
            'billable' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task dependency data
     */
    public function validateTaskDependency(array $data): bool
    {
        $this->rules = [
            'dependent_task_id' => 'required|integer|min:1',
            'dependency_type' => 'required|in:finish_to_start,start_to_start,finish_to_finish,start_to_finish',
            'lag_days' => 'integer|min:0|max:365'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task attachment upload
     */
    public function validateAttachmentUpload(array $data): bool
    {
        $this->rules = [
            'attachment' => 'required|file|max:10240', // 10MB max
            'description' => 'max:255'
        ];

        return $this->validate($data);
    }

    /**
     * Validate bulk task operations
     */
    public function validateBulkTaskOperation(array $data): bool
    {
        $this->rules = [
            'task_ids' => 'required|array|min:1',
            'task_ids.*' => 'integer|min:1',
            'operation' => 'required|in:assign,update_status,delete,update_priority,update_due_date',
            'assigned_to' => 'required_if:operation,assign|integer|min:1',
            'status' => 'required_if:operation,update_status|in:pending,in_progress,completed,cancelled,on_hold',
            'priority' => 'required_if:operation,update_priority|in:low,medium,high,urgent',
            'due_date' => 'required_if:operation,update_due_date|date',
            'notes' => 'max:500'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task template creation
     */
    public function validateTaskTemplate(array $data): bool
    {
        $this->rules = [
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:10|max:1000',
            'category' => 'required|in:administrative,cultural,educational,fundraising,social,political,religious',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_hours' => 'numeric|min:0.5|max:1000',
            'tasks' => 'required|array|min:1',
            'tasks.*.title' => 'required|min:3|max:255',
            'tasks.*.description' => 'required|min:10|max:2000',
            'tasks.*.estimated_hours' => 'numeric|min:0.5|max:1000',
            'tasks.*.dependencies' => 'array',
            'is_public' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate task report generation
     */
    public function validateTaskReport(array $data): bool
    {
        $this->rules = [
            'report_type' => 'required|in:summary,detailed,time_tracking,productivity,overdue',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after:date_from',
            'level_scope' => 'in:global,godina,gamta,gurmu',
            'scope_id' => 'integer|min:1',
            'assigned_to' => 'integer|min:1',
            'created_by' => 'integer|min:1',
            'status' => 'array',
            'priority' => 'array',
            'category' => 'array',
            'format' => 'required|in:pdf,excel,csv'
        ];

        return $this->validate($data);
    }

    /**
     * Define custom error messages for task validation
     */
    protected function defineMessages(): void
    {
        parent::defineMessages();
        
        $this->messages = array_merge($this->messages, [
            'title.required' => 'Task title is required.',
            'title.min' => 'Task title must be at least 3 characters.',
            'title.max' => 'Task title may not exceed 255 characters.',
            
            'description.required' => 'Task description is required.',
            'description.min' => 'Task description must be at least 10 characters.',
            'description.max' => 'Task description may not exceed 2000 characters.',
            
            'priority.required' => 'Task priority is required.',
            'priority.in' => 'Please select a valid priority level.',
            
            'category.required' => 'Task category is required.',
            'category.in' => 'Please select a valid task category.',
            
            'level_scope.required' => 'Organization level is required.',
            'level_scope.in' => 'Please select a valid organization level.',
            
            'scope_id.required' => 'Organization unit is required.',
            'scope_id.integer' => 'Please select a valid organization unit.',
            
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Please provide a valid due date.',
            'due_date.after' => 'Due date must be in the future.',
            
            'assigned_to.integer' => 'Please select a valid assignee.',
            'assigned_to.min' => 'Please select a valid assignee.',
            
            'estimated_hours.numeric' => 'Estimated hours must be a number.',
            'estimated_hours.min' => 'Estimated hours must be at least 0.5.',
            'estimated_hours.max' => 'Estimated hours may not exceed 1000.',
            
            'status.required' => 'Task status is required.',
            'status.in' => 'Please select a valid task status.',
            
            'completion_percentage.integer' => 'Completion percentage must be a whole number.',
            'completion_percentage.min' => 'Completion percentage cannot be negative.',
            'completion_percentage.max' => 'Completion percentage cannot exceed 100.',
            
            'recurrence_pattern.required_if' => 'Recurrence pattern is required for recurring tasks.',
            'recurrence_end_date.required_if' => 'End date is required for recurring tasks.',
            'recurrence_end_date.after' => 'Recurrence end date must be after the due date.',
            
            'comment.required' => 'Comment text is required.',
            'comment.min' => 'Comment must be at least 3 characters.',
            'comment.max' => 'Comment may not exceed 1000 characters.',
            
            'hours_worked.required' => 'Hours worked is required.',
            'hours_worked.numeric' => 'Hours worked must be a number.',
            'hours_worked.min' => 'Hours worked must be at least 0.1.',
            'hours_worked.max' => 'Hours worked cannot exceed 24 hours per day.',
            
            'work_date.required' => 'Work date is required.',
            'work_date.date' => 'Please provide a valid work date.',
            
            'task_ids.required' => 'Please select at least one task.',
            'task_ids.array' => 'Task selection is invalid.',
            'task_ids.min' => 'Please select at least one task.',
            
            'operation.required' => 'Please select an operation to perform.',
            'operation.in' => 'Please select a valid operation.',
            
            'assigned_to.required_if' => 'Please select an assignee for the assignment operation.',
            'status.required_if' => 'Please select a status for the status update operation.',
            'priority.required_if' => 'Please select a priority for the priority update operation.',
            'due_date.required_if' => 'Please provide a due date for the due date update operation.',
            
            'attachment.required' => 'Please select a file to upload.',
            'attachment.file' => 'The uploaded item must be a file.',
            'attachment.max' => 'File size may not exceed 10MB.',
            
            'name.required' => 'Template name is required.',
            'name.min' => 'Template name must be at least 3 characters.',
            
            'tasks.required' => 'Template must contain at least one task.',
            'tasks.array' => 'Tasks must be provided as an array.',
            'tasks.min' => 'Template must contain at least one task.',
            
            'report_type.required' => 'Report type is required.',
            'report_type.in' => 'Please select a valid report type.',
            
            'date_from.required' => 'Start date is required.',
            'date_to.required' => 'End date is required.',
            'date_to.after' => 'End date must be after start date.',
            
            'format.required' => 'Report format is required.',
            'format.in' => 'Please select a valid report format.'
        ]);
    }

    /**
     * Validate task due date is not in the past
     */
    protected function validateFutureDate(string $field, $value): bool
    {
        if (strtotime($value) < strtotime('today')) {
            $this->addError($field, 'The ' . $field . ' must be today or in the future.');
            return false;
        }
        return true;
    }

    /**
     * Validate task assignment permissions
     */
    public function validateAssignmentPermissions(array $data, int $currentUserId, string $userLevel): bool
    {
        // This would contain business logic to check if the current user
        // can assign tasks to the specified user based on hierarchy
        // Implementation would depend on your specific business rules
        
        return true; // Placeholder - implement actual permission logic
    }

    /**
     * Validate task dependency to prevent circular dependencies
     */
    protected function validateNonCircularDependency(string $field, $value, int $taskId): bool
    {
        // This would contain logic to check for circular dependencies
        // Implementation would require database queries to trace dependency chains
        
        return true; // Placeholder - implement actual circular dependency check
    }
}