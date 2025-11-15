<?php

/**
 * Afaan Oromoo Task Management Translations
 * 
 * Jechootaa fi ergaalee bulchiinsa hojiiwwanii sirna ABO-WBO
 * keessatti fayyadaman.
 * 
 * @package Lang\OM
 * @version 1.0.0
 */

return [
    // Task Management
    'title' => 'Bulchiinsa Hojiiwwanii',
    'task' => 'Hojii',
    'tasks' => 'Hojiiwwan',
    'create_task' => 'Hojii Uumi',
    'edit_task' => 'Hojii Gulali',
    'view_task' => 'Hojii Ilaali',
    'delete_task' => 'Hojii Balleessi',
    'assign_task' => 'Hojii Ramadi',
    'complete_task' => 'Hojii Xumuri',

    // Task Properties
    'properties' => [
        'name' => 'Maqaa Hojii',
        'description' => 'Ibsa Hojii',
        'priority' => 'Dursa Kennuu',
        'status' => 'Haala',
        'category' => 'Ramaddii',
        'scope' => 'Bal\'ina',
        'due_date' => 'Guyyaa Dhumaa',
        'start_date' => 'Guyyaa Jalqabaa',
        'assigned_to' => 'Kan Ramaadame',
        'assigned_by' => 'Kan Ramaade',
        'created_by' => 'Kan Uume',
        'estimated_hours' => 'Sa\'aatiiwwan Tilmaamamanii',
        'actual_hours' => 'Sa\'aatiiwwan Dhugaa',
        'progress' => 'Adeemsa',
        'attachments' => 'Itti Makamuu',
        'tags' => 'Mallattoowwan',
        'dependencies' => 'Wal Hirkattoota'
    ],

    // Priority Levels  
    'priority' => [
        'low' => 'Gadi Aanaa',
        'normal' => 'Baratamaa',
        'high' => 'Ol Aanaa', 
        'urgent' => 'Ariifachiisaa',
        'critical' => 'Murteessaa',
        'emergency' => 'Hatattamaa'
    ],

    // Task Status
    'status' => [
        'not_started' => 'Hin Jalqabne',
        'in_progress' => 'Adeemsa Keessa',
        'on_hold' => 'Tursiifame',
        'completed' => 'Xumurame',
        'cancelled' => 'Dhaabaame',
        'archived' => 'Kuufame',
        'pending_review' => 'Sakatta\'amaa Eegaa Jiru',
        'approved' => 'Fudhatame',
        'rejected' => 'Diidame'
    ],

    // Scope Levels
    'scope' => [
        'global' => 'Addunyaa',
        'godina' => 'Godina', 
        'gamta' => 'Gamta',
        'gurmu' => 'Gurmu',
        'individual' => 'Dhuunfaa'
    ],

    // Categories
    'categories' => [
        'administrative' => 'Bulchiinsa',
        'financial' => 'Maallaqaa',
        'educational' => 'Barnoota',
        'cultural' => 'Aadaa',
        'community' => 'Hawaasaa',
        'technical' => 'Teeknikaa',
        'marketing' => 'Gabaa',
        'fundraising' => 'Maallaqaa Walitti Qabuu',
        'event_planning' => 'Sagantaa Taateewwanii',
        'member_management' => 'Bulchiinsa Miseensotaa',
        'communications' => 'Qunnamtii',
        'research' => 'Qorannoo'
    ],

    // Assignment & Actions
    'assignment' => [
        'assign_to' => 'Ramaaduu',
        'assigned_by' => 'Kan Ramaade',
        'assignment_date' => 'Guyyaa Ramaddii',
        'reassign' => 'Lamuu Ramaduu',
        'unassign' => 'Ramadduu Dhiisuu',
        'bulk_assign' => 'Hedduu Waliin Ramaduu',
        'auto_assign' => 'Ofumaan Ramaduu',
        'assignment_history' => 'Seenaa Ramaddii'
    ],

    // Time Tracking
    'time_tracking' => [
        'log_time' => 'Yeroo Galmeessi',
        'time_spent' => 'Yeroo Dabarfame',
        'time_remaining' => 'Yeroo Hafee',
        'start_timer' => 'Yeroo Sassaabi',
        'stop_timer' => 'Yeroo Dhaabi',
        'pause_timer' => 'Yeroo Addaan Kaa\'i',
        'time_entries' => 'Galmee Yeroo',
        'total_time' => 'Yeroo Waliigalaa',
        'billable_hours' => 'Sa\'aatiiwwan Kaffaltii',
        'overtime' => 'Yeroo Dabalataa'
    ],

    // Comments & Communication
    'comments' => [
        'add_comment' => 'Yaada Dabali',
        'edit_comment' => 'Yaada Gulali', 
        'delete_comment' => 'Yaada Balleessi',
        'reply_comment' => 'Yaadaaf Deebii Kenni',
        'comment_history' => 'Seenaa Yaadaa',
        'internal_notes' => 'Yaadachiisa Keessaa',
        'public_comments' => 'Yaadaalee Uummataa',
        'private_notes' => 'Yaadachiisa Dhuunfaa'
    ],

    // Notifications
    'notifications' => [
        'task_assigned' => 'Hojii Siif Ramaadameera',
        'task_completed' => 'Hojii Xumurame',
        'task_overdue' => 'Hojii Yeroo Darbee',
        'task_updated' => 'Hojii Haaromfameera',
        'deadline_approaching' => 'Guyyaan Dhumaa Dhihaachaa Jira',
        'new_comment' => 'Yaada Haaraa',
        'status_changed' => 'Haalli Jijjiirame',
        'assignment_changed' => 'Ramaddiin Jijjiirame'
    ],

    // Reports & Analytics
    'reports' => [
        'task_reports' => 'Gabaasaawwan Hojiiwwanii',
        'productivity_report' => 'Gabaasa Omishaa',
        'time_tracking_report' => 'Gabaasa Hordoffii Yeroo',
        'completion_rate' => 'Sadarkaa Xumurii',
        'overdue_tasks' => 'Hojiiwwan Yeroo Darban',
        'pending_approvals' => 'Fudhachiisaa Eegaa Jiran',
        'workload_distribution' => 'Raabsa Baaddii Hojii',
        'performance_metrics' => 'Safartuuwwan Raawwii'
    ]
];