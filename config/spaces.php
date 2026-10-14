<?php

return [
    // Operating hours: used for "free now", the free/busy windows and the room schedule.
    'open'  => '07:00',
    'close' => '21:00',

    // Range drawn by the availability bars in search results (8 AM -> 8 PM in the design).
    'search_window' => ['start' => '08:00', 'end' => '20:00'],

    'semester' => 'Fall Semester', // or compute from a semesters table
    'free_now_limit' => 12,        // cards shown on the Home screen

    // key => label / default illustration key / default capacity unit.
    // The keys must match what the Flutter app's RoomIllustration understands.
    'types' => [
        'lecture_hall' => ['label' => 'Lecture Hall', 'illustration' => 'lecture_hall', 'unit' => 'seats'],
        'computer_lab' => ['label' => 'Computer Lab', 'illustration' => 'computer_lab', 'unit' => 'workstations'],
        'seminar'      => ['label' => 'Seminar Room', 'illustration' => 'seminar',      'unit' => 'seats'],
        'study_pod'    => ['label' => 'Study Pod',    'illustration' => 'study_pod',    'unit' => 'seats'],
        'science_lab'  => ['label' => 'Science Lab',  'illustration' => 'science_lab',  'unit' => 'seats'],
    ],
];
