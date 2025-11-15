<?php

namespace App\Validators;

/**
 * EventValidator - Validates event-related input data
 * 
 * Handles validation for event creation, updates, registrations,
 * and event management operations.
 * 
 * @package App\Validators
 * @version 1.0.0
 */
class EventValidator extends BaseValidator
{
    /**
     * Define validation rules for event operations
     */
    protected function defineRules(): void
    {
        // Default rules - can be overridden by specific validation methods
        $this->rules = [];
    }

    /**
     * Validate event creation data
     */
    public function validateEventCreation(array $data): bool
    {
        $this->rules = [
            'title' => 'required|min:3|max:255',
            'description' => 'required|min:20|max:5000',
            'event_type' => 'required|in:cultural,educational,fundraising,social,political,religious,sports,conference',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'scope_id' => 'required|integer|min:1',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'timezone' => 'required|max:50',
            'venue_name' => 'required_if:is_virtual,false|max:255',
            'venue_address' => 'required_if:is_virtual,false|max:500',
            'is_virtual' => 'boolean',
            'virtual_platform' => 'required_if:is_virtual,true|max:100',
            'virtual_link' => 'required_if:is_virtual,true|url|max:500',
            'is_paid_event' => 'boolean',
            'ticket_price' => 'required_if:is_paid_event,true|numeric|min:0',
            'currency' => 'required_if:is_paid_event,true|in:USD,EUR,ETB',
            'max_attendees' => 'integer|min:1|max:100000',
            'registration_required' => 'boolean',
            'registration_deadline' => 'required_if:registration_required,true|date|before:start_datetime',
            'agenda' => 'array',
            'requirements' => 'max:1000',
            'featured_image' => 'file|image|max:5120' // 5MB max
        ];

        return $this->validate($data);
    }

    /**
     * Validate event update data
     */
    public function validateEventUpdate(array $data): bool
    {
        $this->rules = [
            'title' => 'min:3|max:255',
            'description' => 'min:20|max:5000',
            'event_type' => 'in:cultural,educational,fundraising,social,political,religious,sports,conference',
            'start_datetime' => 'date',
            'end_datetime' => 'date|after:start_datetime',
            'timezone' => 'max:50',
            'venue_name' => 'max:255',
            'venue_address' => 'max:500',
            'is_virtual' => 'boolean',
            'virtual_platform' => 'max:100',
            'virtual_link' => 'url|max:500',
            'is_paid_event' => 'boolean',
            'ticket_price' => 'numeric|min:0',
            'currency' => 'in:USD,EUR,ETB',
            'max_attendees' => 'integer|min:1|max:100000',
            'registration_required' => 'boolean',
            'registration_deadline' => 'date|before:start_datetime',
            'agenda' => 'array',
            'requirements' => 'max:1000',
            'featured_image' => 'file|image|max:5120'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event registration data
     */
    public function validateEventRegistration(array $data): bool
    {
        $this->rules = [
            'registration_type' => 'required|in:participant,volunteer,speaker,organizer',
            'special_requirements' => 'max:500',
            'dietary_preferences' => 'array',
            'emergency_contact' => 'array',
            'emergency_contact.name' => 'required_with:emergency_contact|max:100',
            'emergency_contact.phone' => 'required_with:emergency_contact|regex:/^[+]?[0-9]{10,15}$/',
            'emergency_contact.relationship' => 'required_with:emergency_contact|max:50'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event search/filter data
     */
    public function validateEventSearch(array $data): bool
    {
        $this->rules = [
            'search' => 'max:255',
            'event_type' => 'in:cultural,educational,fundraising,social,political,religious,sports,conference',
            'level_scope' => 'in:global,godina,gamta,gurmu',
            'scope_id' => 'integer|min:1',
            'status' => 'in:draft,published,cancelled,completed',
            'is_virtual' => 'boolean',
            'is_paid_event' => 'boolean',
            'date_from' => 'date',
            'date_to' => 'date',
            'created_by' => 'integer|min:1',
            'registration_status' => 'in:open,closed,full',
            'sort_by' => 'in:title,start_datetime,created_at,registration_count',
            'sort_order' => 'in:asc,desc',
            'per_page' => 'integer|min:10|max:100'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event status update
     */
    public function validateStatusUpdate(array $data): bool
    {
        $this->rules = [
            'status' => 'required|in:draft,published,cancelled,completed',
            'reason' => 'required_if:status,cancelled|max:500',
            'notify_attendees' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate attendance marking
     */
    public function validateAttendanceMarking(array $data): bool
    {
        $this->rules = [
            'attendee_ids' => 'required|array|min:1',
            'attendee_ids.*' => 'integer|min:1',
            'attendance_status' => 'required|in:attended,no_show',
            'notes' => 'max:500',
            'check_in_time' => 'date'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event agenda item
     */
    public function validateAgendaItem(array $data): bool
    {
        $this->rules = [
            'title' => 'required|min:3|max:255',
            'description' => 'max:1000',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'speaker' => 'max:100',
            'location' => 'max:255',
            'type' => 'required|in:presentation,workshop,break,networking,ceremony'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event feedback/rating
     */
    public function validateEventFeedback(array $data): bool
    {
        $this->rules = [
            'overall_rating' => 'required|integer|min:1|max:5',
            'content_rating' => 'integer|min:1|max:5',
            'organization_rating' => 'integer|min:1|max:5',
            'venue_rating' => 'integer|min:1|max:5',
            'speaker_rating' => 'integer|min:1|max:5',
            'comments' => 'max:2000',
            'suggestions' => 'max:1000',
            'would_recommend' => 'boolean',
            'would_attend_again' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate bulk attendee operations
     */
    public function validateBulkAttendeeOperation(array $data): bool
    {
        $this->rules = [
            'attendee_ids' => 'required|array|min:1',
            'attendee_ids.*' => 'integer|min:1',
            'operation' => 'required|in:confirm,cancel,move_to_waitlist,send_reminder',
            'message' => 'required_if:operation,send_reminder|max:1000'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event report generation
     */
    public function validateEventReport(array $data): bool
    {
        $this->rules = [
            'report_type' => 'required|in:attendance,feedback,financial,summary',
            'event_ids' => 'array',
            'event_ids.*' => 'integer|min:1',
            'date_from' => 'date',
            'date_to' => 'date|after:date_from',
            'level_scope' => 'in:global,godina,gamta,gurmu',
            'scope_id' => 'integer|min:1',
            'event_type' => 'array',
            'format' => 'required|in:pdf,excel,csv'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event import data
     */
    public function validateEventImport(array $data): bool
    {
        $this->rules = [
            'import_file' => 'required|file|mimes:text/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'level_scope' => 'required|in:global,godina,gamta,gurmu',
            'scope_id' => 'required|integer|min:1',
            'default_event_type' => 'required|in:cultural,educational,fundraising,social,political,religious,sports,conference',
            'publish_immediately' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event template creation
     */
    public function validateEventTemplate(array $data): bool
    {
        $this->rules = [
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:20|max:1000',
            'event_type' => 'required|in:cultural,educational,fundraising,social,political,religious,sports,conference',
            'default_duration_hours' => 'required|numeric|min:0.5|max:168',
            'default_agenda' => 'array',
            'default_requirements' => 'max:1000',
            'is_public' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate virtual event settings
     */
    public function validateVirtualEventSettings(array $data): bool
    {
        $this->rules = [
            'platform' => 'required|in:zoom,teams,webex,custom',
            'meeting_id' => 'required_if:platform,zoom|max:100',
            'meeting_password' => 'max:100',
            'join_url' => 'required|url|max:500',
            'dial_in_numbers' => 'array',
            'webinar_id' => 'max:100',
            'recording_enabled' => 'boolean',
            'auto_record' => 'boolean',
            'waiting_room_enabled' => 'boolean',
            'authentication_required' => 'boolean'
        ];

        return $this->validate($data);
    }

    /**
     * Validate event notification settings
     */
    public function validateNotificationSettings(array $data): bool
    {
        $this->rules = [
            'send_registration_confirmation' => 'boolean',
            'send_reminder_24h' => 'boolean',
            'send_reminder_1h' => 'boolean',
            'send_follow_up' => 'boolean',
            'custom_reminder_times' => 'array',
            'email_template' => 'max:5000',
            'sms_enabled' => 'boolean',
            'sms_template' => 'max:160'
        ];

        return $this->validate($data);
    }

    /**
     * Define custom error messages for event validation
     */
    protected function defineMessages(): void
    {
        parent::defineMessages();
        
        $this->messages = array_merge($this->messages, [
            'title.required' => 'Event title is required.',
            'title.min' => 'Event title must be at least 3 characters.',
            'title.max' => 'Event title may not exceed 255 characters.',
            
            'description.required' => 'Event description is required.',
            'description.min' => 'Event description must be at least 20 characters.',
            'description.max' => 'Event description may not exceed 5000 characters.',
            
            'event_type.required' => 'Event type is required.',
            'event_type.in' => 'Please select a valid event type.',
            
            'level_scope.required' => 'Organization level is required.',
            'level_scope.in' => 'Please select a valid organization level.',
            
            'scope_id.required' => 'Organization unit is required.',
            'scope_id.integer' => 'Please select a valid organization unit.',
            
            'start_datetime.required' => 'Event start date and time is required.',
            'start_datetime.date' => 'Please provide a valid start date and time.',
            
            'end_datetime.required' => 'Event end date and time is required.',
            'end_datetime.date' => 'Please provide a valid end date and time.',
            'end_datetime.after' => 'End date and time must be after start date and time.',
            
            'timezone.required' => 'Timezone is required.',
            
            'venue_name.required_if' => 'Venue name is required for physical events.',
            'venue_address.required_if' => 'Venue address is required for physical events.',
            
            'virtual_platform.required_if' => 'Virtual platform is required for online events.',
            'virtual_link.required_if' => 'Virtual event link is required for online events.',
            'virtual_link.url' => 'Please provide a valid virtual event link.',
            
            'ticket_price.required_if' => 'Ticket price is required for paid events.',
            'ticket_price.numeric' => 'Ticket price must be a number.',
            'ticket_price.min' => 'Ticket price cannot be negative.',
            
            'currency.required_if' => 'Currency is required for paid events.',
            'currency.in' => 'Please select a valid currency.',
            
            'max_attendees.integer' => 'Maximum attendees must be a number.',
            'max_attendees.min' => 'Maximum attendees must be at least 1.',
            'max_attendees.max' => 'Maximum attendees cannot exceed 100,000.',
            
            'registration_deadline.required_if' => 'Registration deadline is required when registration is mandatory.',
            'registration_deadline.before' => 'Registration deadline must be before event start time.',
            
            'featured_image.image' => 'Featured image must be an image file.',
            'featured_image.max' => 'Featured image size may not exceed 5MB.',
            
            'registration_type.required' => 'Registration type is required.',
            'registration_type.in' => 'Please select a valid registration type.',
            
            'emergency_contact.name.required_with' => 'Emergency contact name is required.',
            'emergency_contact.phone.required_with' => 'Emergency contact phone is required.',
            'emergency_contact.phone.regex' => 'Please provide a valid emergency contact phone number.',
            'emergency_contact.relationship.required_with' => 'Emergency contact relationship is required.',
            
            'overall_rating.required' => 'Overall rating is required.',
            'overall_rating.integer' => 'Rating must be a whole number.',
            'overall_rating.min' => 'Rating must be at least 1 out of 5.',
            'overall_rating.max' => 'Rating cannot exceed 5 out of 5.',
            
            'attendee_ids.required' => 'Please select at least one attendee.',
            'attendee_ids.array' => 'Attendee selection is invalid.',
            'attendee_ids.min' => 'Please select at least one attendee.',
            
            'attendance_status.required' => 'Attendance status is required.',
            'attendance_status.in' => 'Please select a valid attendance status.',
            
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Please provide start time in HH:MM format.',
            
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'Please provide end time in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            
            'operation.required' => 'Please select an operation to perform.',
            'operation.in' => 'Please select a valid operation.',
            
            'message.required_if' => 'Message is required for reminder operations.',
            
            'report_type.required' => 'Report type is required.',
            'report_type.in' => 'Please select a valid report type.',
            
            'format.required' => 'Report format is required.',
            'format.in' => 'Please select a valid report format.',
            
            'import_file.required' => 'Please select a file to import.',
            'import_file.mimes' => 'Import file must be CSV or Excel format.',
            
            'default_event_type.required' => 'Default event type is required for import.',
            
            'platform.required' => 'Virtual platform is required.',
            'platform.in' => 'Please select a valid virtual platform.',
            
            'meeting_id.required_if' => 'Meeting ID is required for Zoom events.',
            'join_url.required' => 'Join URL is required for virtual events.',
            'join_url.url' => 'Please provide a valid join URL.',
            
            'default_duration_hours.required' => 'Default duration is required.',
            'default_duration_hours.numeric' => 'Duration must be a number.',
            'default_duration_hours.min' => 'Duration must be at least 0.5 hours.',
            'default_duration_hours.max' => 'Duration cannot exceed 168 hours (1 week).'
        ]);
    }

    /**
     * Validate event date is in the future
     */
    protected function validateFutureDateTime(string $field, $value): bool
    {
        if (strtotime($value) < strtotime('+1 hour')) {
            $this->addError($field, 'The ' . $field . ' must be at least 1 hour in the future.');
            return false;
        }
        return true;
    }

    /**
     * Validate event capacity doesn't exceed venue limits
     */
    protected function validateVenueCapacity(string $field, $value, array $venueData): bool
    {
        // This would contain logic to check venue capacity limits
        // Implementation would depend on your venue management system
        
        return true; // Placeholder - implement actual capacity validation
    }

    /**
     * Validate event scheduling conflicts
     */
    protected function validateSchedulingConflicts(array $eventData, ?int $eventId = null): bool
    {
        // This would contain logic to check for scheduling conflicts
        // with other events at the same venue or for the same organizer
        
        return true; // Placeholder - implement actual conflict checking
    }
}