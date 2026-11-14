<?php

return [
    // Operating hours: used for "free now", the free/busy windows and the room schedule.
    'open'  => '07:00',
    'close' => '21:00',

    // Range drawn by the availability bars in search results (8 AM -> 8 PM in the design).
    'search_window' => ['start' => '08:00', 'end' => '20:00'],

    // Month+day as MDD, e.g. 401 = 1 April, 1115 = 15 November. A range whose end is
    // smaller than its start wraps over New Year.
    'terms' => [
        ['name' => 'April', 'terms' => [
            ['name' => 'Semester 1', 'start' => 401,  'end' => 731],
            ['name' => 'Semester 2', 'start' => 815,  'end' => 1215],
        ]],
        ['name' => 'July', 'terms' => [
            ['name' => 'Semester 1', 'start' => 701,  'end' => 1031],
            ['name' => 'Semester 2', 'start' => 1115, 'end' => 315],
        ]],
        ['name' => 'Evening', 'terms' => [
            ['name' => 'Trimester 1', 'start' => 501, 'end' => 831],
            ['name' => 'Trimester 2', 'start' => 901, 'end' => 1215],
            ['name' => 'Trimester 3', 'start' => 101, 'end' => 430],
        ]],
    ],
    'free_now_limit' => 12,        // cards shown on the Home screen

    // key => label / default illustration key / default capacity unit.
    // The keys must match what the Flutter app's RoomIllustration understands.
    'types' => [
        'lecture_theatre' => ['label' => 'Lecture Theatre', 'illustration' => 'lecture_hall', 'unit' => 'seats'],
        'computer_lab' => ['label' => 'Computer Lab', 'illustration' => 'computer_lab', 'unit' => 'workstations'],
        'room'      => ['label' => 'Room', 'illustration' => 'seminar',      'unit' => 'seats'],
    ],
];
