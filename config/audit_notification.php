<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Notification Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for configuring the messages for the audit notification email.
    | You can customize the messages and titles to fit your needs.
    |
    */

    'title' => 'Monitoring Ticket Raised',
    'live_calls_title' => 'Live Call Monitoring Alert',
    'autofails_title' => 'AutoFails Notification',
    'exam_results_title' => 'Exam Results Notification',

    'from_name' => 'Monitoring System',
    'no_reply_message' => 'This is an automated message, please do not reply to this email.',

    'results_message' => 'Hi :user_name,

    A monitoring ticket has been raised for your recent calls by your quality Analysts, :quality_name. Your supervisor, :supervisor_name, will be reviewing the call and providing feedback shortly.

    Thanks for your hard work!',

    'live_calls_message' => 'Hi :user_name,

    A live call monitoring alert has been triggered for one of your recent calls by your quality Analysts, :quality_name. Your supervisor, :supervisor_name, will be reviewing the call and providing feedback shortly.

    Thanks for your hard work!',

    'autofails_message' => 'Hi :user_name,

    Your recent call has failed automatic quality checks. Your supervisor, :supervisor_name, will be reviewing the call and providing feedback shortly.

    Thanks for your hard work!',

    'exam_results_message' => 'Hi :user_name,

    Your recent exam results have been published. You can view your results by logging into the system.

    Thanks for your hard work!',

];
