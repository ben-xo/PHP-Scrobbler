<?php

require_once 'Scrobbler.php';

class example_scrobbler 
{
    var $help = false;
    var $appname;
    var $password, $api_key, $api_secret, $api_sk, $clientId, $clientVer, $source = 'P', $rating, $mbTrackId;

    function main($argc, $argv) {
        date_default_timezone_set('UTC');
        mb_internal_encoding('UTF-8');

        try
        {
            $this->parseOptions($argv);
            if($this->help)
            {
                $this->usage($this->appname, $argv);
            }

            $scrobbler = new md_Scrobbler($this->user, $this->password, 
                $this->api_key, $this->api_secret, $this->api_sk, 
                $this->clientId, $this->clientVer);

            $scrobbler->add($this->artist, $this->track, $this->album, $this->trackDuration, 
                $this->scrobbleTime, $this->trackNumber, $this->source, $this->rating, 
                $this->mbTrackId);

            $scrobbler->submit();
            echo "Done\n";
        }
        catch(Exception $e)
        {   
            echo $e->getMessage() . "\n";
            echo "Try $appname --help\n";
        }

    }

    function usage($appname, array $argv)
    {
        echo "Usage: {$appname} [OPTIONS]\n";
        echo "    --help:               This message.\n\n";
        echo "    --username:           last.fm username\n";
        echo "    --password:           last.fm password (optional)\n";
        echo "    --api_key:            You cna use api_key, api_secret and api_sk instead of password.\n";
        echo "    --api_secret\n";
        echo "    --api_sk\n";
        echo "\n";
        echo "    --clientId            Something like TST\n";
        echo "    --clientVer           Something like 1.0\n";
        echo "\n";
        echo "Data for the actual scrobble: --artist --track --scrobbleTime --trackDuration\n";
        echo "                              --album --trackNumber --source --rating --mbTrackId\n";
        echo "\n";
    }

    function parseOptions(array $argv)
    {
        $this->appname = array_shift($argv);
        
        while($arg = array_shift($argv))
        {
            if($arg == '--help' || $arg == '-h')
            {
                $this->help = true;
                continue;
            }

            if($arg == '--username')
            {
                $this->username = array_shift($argv);
                continue;
            }
     
            if($arg == '--password')
            {
                $this->password = array_shift($argv);
                continue;
            }
     
            if($arg == '--api_key')
            {
                $this->api_key = array_shift($argv);
                continue;
            }
     
            if($arg == '--api_secret')
            {
                $this->api_secret = array_shift($argv);
                continue;
            }
     
            if($arg == '--api_sk')
            {
                $this->api_sk = array_shift($argv);
                continue;
            }
     
            if($arg == '--clientId')
            {
                $this->clientId = array_shift($argv);
                continue;
            }

            if($arg == '--clientVer')
            {
                $this->clientVer = array_shift($argv);
                continue;
            }
     
            if($arg == '--artist')
            {
                $this->artist = array_shift($argv);
                continue;
            }

            if($arg == '--track')
            {
                $this->track = array_shift($argv);
                continue;
            }

            if($arg == '--scrobbleTime')
            {
                $this->scrobbleTime = array_shift($argv);
                continue;
            }

            if($arg == '--trackDuration')
            {
                $this->trackDuration = array_shift($argv);
                continue;
            }

            if($arg == '--album')
            {
                $this->album = array_shift($argv);
                continue;
            }

            if($arg == '--trackNumber')
            {
                $this->trackNumber = array_shift($argv);
                continue;
            }

            if($arg == '--source')
            {
                $this->source = array_shift($argv);
                continue;
            }

            if($arg == '--rating')
            {
                $this->rating = array_shift($argv);
                continue;
            }

            if($arg == '--mbTrackId')
            {
                $this->mbTrackId = array_shift($argv);
                continue;
            }
       }
    }
}

$example_scrobbler = new example_scrobbler();
$example_scrobbler->main($argc, $argv);