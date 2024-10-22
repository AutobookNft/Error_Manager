<?php

namespace Fabio\UltraSecureUpload;

class ConfigManager
{
    // Define configuration variables
    protected $devTeamEmail;
    protected $emailNotification;
    protected $routechannel;

    public function __construct()
    {
        // Retrieve configuration values
        $this->devTeamEmail = config('error_menage.devteam_email');
        $this->emailNotification = config('error_menage.email_notifications', false); 
        $this->routechannel = config('error_menage.log_channel', 'upload');

    }

    // Define methods to retrieve the configuration values    
    public function getDevTeamEmail()
    {
        // Return the value of the devTeamEmail variable
        return $this->devTeamEmail;
    }

    public function getEmailNotification ()
    {
        // Return the value of the emailNotification variable
        return $this->emailNotification;
    }

    Public function getRouteChannel()
    {
        // Return the value of the routechannel variable
        return $this->routechannel;
    }

}
