<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Log Channel Configuration
    |--------------------------------------------------------------------------
    |
    | Defines the log channel used by the error manager. This allows you to 
    | specify which logging channel should be used to record errors. The value
    | can be set in the .env file using the variable ERROR_MANAGER_LOG_CHANNEL.
    | By default, it will use the "error_manager" channel.
    |
    | Example: ERROR_MANAGER_LOG_CHANNEL=error_manager
    |
    */
    'log_channel' => env('ERROR_MANAGER_LOG_CHANNEL', 'error_manager'),

    /*
    |--------------------------------------------------------------------------
    | Supported Languages
    |--------------------------------------------------------------------------
    |
    | A list of supported languages for error messages and other localization
    | needs. This value can be set in the .env file using the variable
    | ERROR_MANAGER_SUPPORTED_LANGUAGES, and it should be a comma-separated string
    | of language codes (e.g., "it,en,fr,es,pt,de"). By default, it supports
    | Italian, English, French, Spanish, Portuguese, and German.
    |
    | Example: ERROR_MANAGER_SUPPORTED_LANGUAGES=it,en,fr,es,pt,de
    |
    */
    'supported_languages' => explode(',', env('ERROR_MANAGER_SUPPORTED_LANGUAGES', 'it,en,fr,es,pt,de')),

    /*
    |--------------------------------------------------------------------------
    | DevTeam Email Address
    |--------------------------------------------------------------------------
    |
    | The email address used for notifying the development team in case of 
    | critical errors. The value can be set in the .env file using the variable
    | ERROR_MANAGER_DEVTEAM_EMAIL. By default, it will use "devteam@gmail.com".
    |
    | Example: ERROR_MANAGER_DEVTEAM_EMAIL=devteam@example.com
    |
    */
    'devteam_email' => env('ERROR_MANAGER_DEVTEAM_EMAIL', 'devteam@gmail.com'),

    /*
    |--------------------------------------------------------------------------
    | Send Email to DevTeam in Case of Error
    |--------------------------------------------------------------------------
    |
    | Determines whether the system should send an email to the development 
    | team when a critical error occurs. This setting helps ensure the DevTeam 
    | is aware of serious issues immediately. This can be overridden in the 
    | .env file if needed.
    |
    | Example: ERROR_MANAGER_EMAIL_NOTIFICATION=true
    |
    */
    'email_notifications' => env('ERROR_MANAGER_EMAIL_NOTIFICATION', false),

];
