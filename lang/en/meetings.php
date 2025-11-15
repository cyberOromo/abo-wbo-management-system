<?php

/**
 * English Meeting System Translations
 * 
 * Meeting management, scheduling, Zoom integration,
 * and conference management translations.
 * 
 * @package Lang\EN
 * @version 1.0.0
 */

return [
    // Meeting Management
    'title' => 'Meeting Management',
    'create_meeting' => 'Create Meeting',
    'schedule_meeting' => 'Schedule Meeting',
    'edit_meeting' => 'Edit Meeting',
    'view_meeting' => 'View Meeting',
    'delete_meeting' => 'Delete Meeting',
    'cancel_meeting' => 'Cancel Meeting',
    'join_meeting' => 'Join Meeting',
    'start_meeting' => 'Start Meeting',
    'end_meeting' => 'End Meeting',
    'my_meetings' => 'My Meetings',
    'all_meetings' => 'All Meetings',
    'upcoming_meetings' => 'Upcoming Meetings',
    'past_meetings' => 'Past Meetings',
    'meeting_details' => 'Meeting Details',
    'meeting_list' => 'Meeting List',
    'meeting_calendar' => 'Meeting Calendar',

    // Meeting Properties
    'properties' => [
        'title' => 'Meeting Title',
        'description' => 'Description',
        'agenda' => 'Meeting Agenda',
        'objectives' => 'Meeting Objectives',
        'type' => 'Meeting Type',
        'level_scope' => 'Scope Level',
        'scope_id' => 'Organization',
        'date' => 'Meeting Date',
        'start_time' => 'Start Time',
        'end_time' => 'End Time',
        'duration' => 'Duration',
        'timezone' => 'Timezone',
        'location' => 'Location',
        'venue' => 'Venue',
        'address' => 'Address',
        'meeting_link' => 'Meeting Link',
        'meeting_id' => 'Meeting ID',
        'passcode' => 'Passcode',
        'password' => 'Meeting Password',
        'host' => 'Meeting Host',
        'organizer' => 'Organizer',
        'attendees' => 'Attendees',
        'invited_users' => 'Invited Users',
        'required_attendees' => 'Required Attendees',
        'optional_attendees' => 'Optional Attendees',
        'status' => 'Status',
        'priority' => 'Priority',
        'category' => 'Category',
        'tags' => 'Tags',
        'attachments' => 'Attachments',
        'notes' => 'Notes',
        'minutes' => 'Meeting Minutes',
        'recording' => 'Recording',
        'created_by' => 'Created By',
        'created_at' => 'Created At',
        'updated_at' => 'Last Updated'
    ],

    // Meeting Types
    'types' => [
        'regular' => 'Regular Meeting',
        'emergency' => 'Emergency Meeting',
        'general_assembly' => 'General Assembly',
        'board_meeting' => 'Board Meeting',
        'committee_meeting' => 'Committee Meeting',
        'working_group' => 'Working Group',
        'training' => 'Training Session',
        'workshop' => 'Workshop',
        'seminar' => 'Seminar',
        'conference' => 'Conference',
        'webinar' => 'Webinar',
        'town_hall' => 'Town Hall',
        'one_on_one' => 'One-on-One',
        'team_meeting' => 'Team Meeting',
        'project_meeting' => 'Project Meeting',
        'planning_session' => 'Planning Session',
        'review_meeting' => 'Review Meeting',
        'brainstorming' => 'Brainstorming Session',
        'presentation' => 'Presentation',
        'interview' => 'Interview'
    ],

    // Meeting Status
    'status' => [
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'postponed' => 'Postponed',
        'rescheduled' => 'Rescheduled',
        'pending' => 'Pending Confirmation',
        'confirmed' => 'Confirmed',
        'draft' => 'Draft',
        'status_descriptions' => [
            'scheduled' => 'Meeting is scheduled and confirmed',
            'in_progress' => 'Meeting is currently taking place',
            'completed' => 'Meeting has finished',
            'cancelled' => 'Meeting has been cancelled',
            'postponed' => 'Meeting has been postponed',
            'rescheduled' => 'Meeting has been rescheduled',
            'pending' => 'Meeting is pending confirmation',
            'confirmed' => 'Meeting is confirmed by all parties',
            'draft' => 'Meeting is in draft status'
        ]
    ],

    // Meeting Priority
    'priority' => [
        'low' => 'Low Priority',
        'medium' => 'Medium Priority',
        'high' => 'High Priority',
        'urgent' => 'Urgent',
        'critical' => 'Critical'
    ],

    // Meeting Categories
    'categories' => [
        'administrative' => 'Administrative',
        'financial' => 'Financial',
        'educational' => 'Educational',
        'social' => 'Social',
        'cultural' => 'Cultural',
        'planning' => 'Planning',
        'coordination' => 'Coordination',
        'decision_making' => 'Decision Making',
        'information_sharing' => 'Information Sharing',
        'problem_solving' => 'Problem Solving',
        'training' => 'Training',
        'evaluation' => 'Evaluation'
    ],

    // Scope Levels
    'scope_levels' => [
        'global' => 'Global Level',
        'godina' => 'Godina Level',
        'gamta' => 'Gamta Level',
        'gurmu' => 'Gurmu Level',
        'cross_level' => 'Cross-Level',
        'scope_descriptions' => [
            'global' => 'Meetings for the entire global organization',
            'godina' => 'Meetings specific to a Godina region',
            'gamta' => 'Meetings specific to a Gamta district',
            'gurmu' => 'Meetings specific to a local Gurmu',
            'cross_level' => 'Meetings spanning multiple organizational levels'
        ]
    ],

    // Zoom Integration
    'zoom' => [
        'zoom_meeting' => 'Zoom Meeting',
        'create_zoom_meeting' => 'Create Zoom Meeting',
        'join_zoom_meeting' => 'Join Zoom Meeting',
        'zoom_link' => 'Zoom Link',
        'zoom_id' => 'Zoom Meeting ID',
        'zoom_passcode' => 'Zoom Passcode',
        'zoom_password' => 'Zoom Password',
        'zoom_settings' => 'Zoom Settings',
        'enable_waiting_room' => 'Enable Waiting Room',
        'mute_on_entry' => 'Mute Participants on Entry',
        'enable_recording' => 'Enable Recording',
        'auto_recording' => 'Auto Recording',
        'cloud_recording' => 'Cloud Recording',
        'local_recording' => 'Local Recording',
        'enable_breakout_rooms' => 'Enable Breakout Rooms',
        'enable_chat' => 'Enable Chat',
        'enable_screen_share' => 'Enable Screen Share',
        'host_video' => 'Host Video',
        'participant_video' => 'Participant Video',
        'join_before_host' => 'Join Before Host',
        'meeting_authentication' => 'Meeting Authentication',
        'zoom_error' => 'Zoom integration error',
        'zoom_success' => 'Zoom meeting created successfully'
    ],

    // Meeting Invitations
    'invitations' => [
        'send_invitations' => 'Send Invitations',
        'invite_participants' => 'Invite Participants',
        'invitation_sent' => 'Invitation Sent',
        'invitation_pending' => 'Invitation Pending',
        'invitation_accepted' => 'Invitation Accepted',
        'invitation_declined' => 'Invitation Declined',
        'invitation_tentative' => 'Tentative',
        'resend_invitation' => 'Resend Invitation',
        'cancel_invitation' => 'Cancel Invitation',
        'invitation_subject' => 'Meeting Invitation: :title',
        'invitation_message' => 'You are invited to attend the following meeting:',
        'rsvp_required' => 'RSVP Required',
        'rsvp_deadline' => 'RSVP Deadline',
        'invitation_reminders' => 'Invitation Reminders',
        'send_reminder' => 'Send Reminder',
        'reminder_sent' => 'Reminder Sent',
        'bulk_invite' => 'Bulk Invite',
        'invite_external' => 'Invite External Participants'
    ],

    // Meeting Attendance
    'attendance' => [
        'attendance' => 'Attendance',
        'mark_attendance' => 'Mark Attendance',
        'attendance_list' => 'Attendance List',
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'excused' => 'Excused',
        'left_early' => 'Left Early',
        'attendance_rate' => 'Attendance Rate',
        'total_attendees' => 'Total Attendees',
        'expected_attendees' => 'Expected Attendees',
        'actual_attendees' => 'Actual Attendees',
        'attendance_report' => 'Attendance Report',
        'check_in_time' => 'Check-in Time',
        'check_out_time' => 'Check-out Time',
        'duration_attended' => 'Duration Attended',
        'attendance_tracking' => 'Attendance Tracking',
        'auto_attendance' => 'Auto Attendance (from Zoom)',
        'manual_attendance' => 'Manual Attendance'
    ],

    // Meeting Minutes
    'minutes' => [
        'meeting_minutes' => 'Meeting Minutes',
        'take_minutes' => 'Take Minutes',
        'edit_minutes' => 'Edit Minutes',
        'approve_minutes' => 'Approve Minutes',
        'publish_minutes' => 'Publish Minutes',
        'minutes_template' => 'Minutes Template',
        'agenda_items' => 'Agenda Items',
        'discussion_points' => 'Discussion Points',
        'decisions_made' => 'Decisions Made',
        'action_items' => 'Action Items',
        'next_steps' => 'Next Steps',
        'follow_up_items' => 'Follow-up Items',
        'attendee_list' => 'Attendee List',
        'apologies' => 'Apologies',
        'meeting_summary' => 'Meeting Summary',
        'key_highlights' => 'Key Highlights',
        'issues_raised' => 'Issues Raised',
        'resolutions' => 'Resolutions',
        'minutes_status' => 'Minutes Status',
        'draft_minutes' => 'Draft Minutes',
        'final_minutes' => 'Final Minutes',
        'distribute_minutes' => 'Distribute Minutes',
        'minutes_approval' => 'Minutes Approval',
        'sign_off' => 'Sign Off'
    ],

    // Meeting Actions
    'actions' => [
        'schedule' => 'Schedule Meeting',
        'reschedule' => 'Reschedule Meeting',
        'cancel' => 'Cancel Meeting',
        'postpone' => 'Postpone Meeting',
        'duplicate' => 'Duplicate Meeting',
        'recurring_setup' => 'Setup Recurring Meeting',
        'add_to_calendar' => 'Add to Calendar',
        'share_meeting' => 'Share Meeting Details',
        'export_calendar' => 'Export to Calendar',
        'print_agenda' => 'Print Agenda',
        'download_recording' => 'Download Recording',
        'share_recording' => 'Share Recording',
        'generate_report' => 'Generate Report',
        'archive_meeting' => 'Archive Meeting',
        'copy_meeting_link' => 'Copy Meeting Link',
        'test_audio_video' => 'Test Audio/Video'
    ],

    // Recurring Meetings
    'recurring' => [
        'recurring_meeting' => 'Recurring Meeting',
        'setup_recurring' => 'Setup Recurring',
        'recurrence_pattern' => 'Recurrence Pattern',
        'repeat_every' => 'Repeat Every',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'custom' => 'Custom',
        'repeat_on' => 'Repeat On',
        'end_recurrence' => 'End Recurrence',
        'never' => 'Never',
        'after_occurrences' => 'After :count occurrences',
        'by_date' => 'By date',
        'weekdays' => 'Weekdays',
        'weekends' => 'Weekends',
        'specific_days' => 'Specific Days',
        'edit_series' => 'Edit Series',
        'edit_occurrence' => 'Edit This Occurrence',
        'delete_series' => 'Delete Series',
        'delete_occurrence' => 'Delete This Occurrence',
        'series_info' => 'This is part of a recurring meeting series'
    ],

    // Meeting Rooms
    'rooms' => [
        'meeting_rooms' => 'Meeting Rooms',
        'book_room' => 'Book Room',
        'room_availability' => 'Room Availability',
        'room_name' => 'Room Name',
        'room_capacity' => 'Room Capacity',
        'room_location' => 'Room Location',
        'room_facilities' => 'Room Facilities',
        'room_equipment' => 'Room Equipment',
        'room_booking' => 'Room Booking',
        'booking_conflict' => 'Booking Conflict',
        'room_reserved' => 'Room Reserved',
        'room_available' => 'Room Available',
        'setup_requirements' => 'Setup Requirements',
        'catering_requirements' => 'Catering Requirements',
        'technical_requirements' => 'Technical Requirements',
        'room_layout' => 'Room Layout',
        'virtual_room' => 'Virtual Room'
    ],

    // Meeting Reports
    'reports' => [
        'meeting_reports' => 'Meeting Reports',
        'attendance_report' => 'Attendance Report',
        'productivity_report' => 'Productivity Report',
        'meeting_statistics' => 'Meeting Statistics',
        'utilization_report' => 'Room Utilization Report',
        'cost_analysis' => 'Meeting Cost Analysis',
        'effectiveness_report' => 'Meeting Effectiveness Report',
        'action_items_report' => 'Action Items Report',
        'decision_tracking' => 'Decision Tracking Report',
        'meeting_trends' => 'Meeting Trends',
        'participant_engagement' => 'Participant Engagement',
        'time_analysis' => 'Time Analysis',
        'resource_usage' => 'Resource Usage',
        'roi_analysis' => 'ROI Analysis',
        'export_report' => 'Export Report',
        'schedule_report' => 'Schedule Report'
    ],

    // Meeting Notifications
    'notifications' => [
        'meeting_scheduled' => 'Meeting scheduled',
        'meeting_updated' => 'Meeting updated',
        'meeting_cancelled' => 'Meeting cancelled',
        'meeting_rescheduled' => 'Meeting rescheduled',
        'meeting_reminder' => 'Meeting reminder',
        'meeting_starting' => 'Meeting starting soon',
        'meeting_started' => 'Meeting has started',
        'meeting_ended' => 'Meeting has ended',
        'invitation_received' => 'Meeting invitation received',
        'rsvp_reminder' => 'RSVP reminder',
        'agenda_updated' => 'Meeting agenda updated',
        'minutes_available' => 'Meeting minutes available',
        'recording_available' => 'Meeting recording available',
        'action_item_assigned' => 'Action item assigned',
        'follow_up_required' => 'Follow-up required',
        'attendance_required' => 'Your attendance is required',
        'notification_settings' => 'Meeting Notification Settings'
    ],

    // Meeting Validation
    'validation' => [
        'title_required' => 'Meeting title is required',
        'title_max_length' => 'Meeting title cannot exceed 255 characters',
        'description_max_length' => 'Description cannot exceed 5000 characters',
        'date_required' => 'Meeting date is required',
        'date_future' => 'Meeting date must be in the future',
        'start_time_required' => 'Start time is required',
        'end_time_required' => 'End time is required',
        'end_after_start' => 'End time must be after start time',
        'duration_positive' => 'Duration must be positive',
        'host_required' => 'Meeting host is required',
        'attendees_required' => 'At least one attendee is required',
        'room_conflict' => 'Room is already booked for this time',
        'host_conflict' => 'Host has another meeting at this time',
        'invalid_zoom_settings' => 'Invalid Zoom settings',
        'max_attendees_exceeded' => 'Maximum number of attendees exceeded',
        'recurring_pattern_required' => 'Recurrence pattern is required for recurring meetings'
    ],

    // Success Messages
    'success' => [
        'meeting_created' => 'Meeting created successfully',
        'meeting_updated' => 'Meeting updated successfully',
        'meeting_deleted' => 'Meeting deleted successfully',
        'meeting_cancelled' => 'Meeting cancelled successfully',
        'meeting_rescheduled' => 'Meeting rescheduled successfully',
        'invitations_sent' => 'Invitations sent successfully',
        'reminder_sent' => 'Reminder sent successfully',
        'attendance_marked' => 'Attendance marked successfully',
        'minutes_saved' => 'Meeting minutes saved successfully',
        'minutes_published' => 'Meeting minutes published successfully',
        'room_booked' => 'Room booked successfully',
        'zoom_meeting_created' => 'Zoom meeting created successfully',
        'recording_started' => 'Recording started successfully',
        'recording_stopped' => 'Recording stopped successfully',
        'settings_updated' => 'Meeting settings updated successfully'
    ],

    // Error Messages
    'errors' => [
        'meeting_not_found' => 'Meeting not found',
        'access_denied' => 'Access denied to this meeting',
        'cannot_join' => 'Cannot join meeting at this time',
        'meeting_full' => 'Meeting has reached maximum capacity',
        'invalid_meeting_link' => 'Invalid meeting link',
        'zoom_error' => 'Zoom integration error',
        'room_unavailable' => 'Selected room is not available',
        'scheduling_conflict' => 'Scheduling conflict detected',
        'past_meeting' => 'Cannot edit past meetings',
        'insufficient_permissions' => 'Insufficient permissions to perform this action',
        'network_error' => 'Network error, please try again',
        'recording_failed' => 'Recording failed to start',
        'upload_failed' => 'File upload failed',
        'calendar_sync_failed' => 'Calendar synchronization failed'
    ],

    // Help and Tips
    'help' => [
        'meeting_help' => 'Meeting Management Help',
        'scheduling_tips' => 'Scheduling Tips',
        'zoom_setup' => 'Zoom Setup Guide',
        'best_practices' => 'Meeting Best Practices',
        'troubleshooting' => 'Troubleshooting',
        'keyboard_shortcuts' => 'Keyboard Shortcuts',
        'tips' => [
            'Schedule meetings with clear objectives',
            'Send agenda items in advance',
            'Start and end meetings on time',
            'Encourage active participation',
            'Take detailed meeting minutes',
            'Follow up on action items',
            'Use breakout rooms for larger meetings',
            'Test audio and video before important meetings'
        ],
        'shortcuts' => [
            'n' => 'Create new meeting',
            'e' => 'Edit selected meeting',
            'd' => 'Delete selected meeting',
            'j' => 'Join meeting',
            'c' => 'Copy meeting link',
            'r' => 'Refresh meeting list',
            'f' => 'Filter meetings',
            's' => 'Search meetings'
        ]
    ]
];