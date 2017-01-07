<?php

require_once 'Scrobbler.php';

class example_scrobbler 
{
    var $help = false;
    var $appname, $debug;
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
                return;
            }

            $scrobbler = new md_Scrobbler($this->username, $this->password, 
                $this->api_key, $this->api_secret, $this->api_sk, 
                $this->clientId, $this->clientVer);

            $scrobbler->debug = $this->debug;

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
        echo "    --api_key:            You can use api_key, api_secret and api_sk instead of password.\n";
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
                return;
            }

            if($arg == '--debug')
            {
                $this->debug = true;
                continue;
            }

            $this->parseOption($arg, $argv);
        }
    }

    function parseOption($arg, &$argv)
    {
        $options = array( 'username', 'password', 'api_key', 'api_secret', 'api_sk',
                          'clientId', 'clientVer',
                          'artist', 'track', 'scrobbleTime', 'trackDuration', 'album', 
                          'trackNumber', 'source', 'rating', 'mbTrackId' );

        foreach($options as $option_name)
        {
            if($arg == '--' . $option_name)
            {
                $this->$option_name = array_shift($argv);
                if($this->debug)
                {
                    echo "Saw --" . $option_name . " as " . $this->$option_name . "\n";
                }
                return;
            }
        }
    }
}

$example_scrobbler = new example_scrobbler();
$example_scrobbler->main($argc, $argv);
