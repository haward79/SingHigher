<?php

    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/common.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/database.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/youtube.php');

    function refreshPins() : void
    {
        mysqlQuery("UPDATE user SET isEnabled = 0 WHERE TIMESTAMPDIFF(DAY, created, NOW()) >= 1;");
    }

    function getEnabledPins() : array
    {
        $enabledPins = array();

        // Refresh outdated pins.
        refreshPins();

        $dbResult = mysqlQuery("SELECT pin FROM user WHERE isEnabled = 1;");

        if($dbResult !== false)
        {
            while($dbExtract = mysqli_fetch_row($dbResult))
                array_push($enabledPins, $dbExtract[0]);
        }

        return $enabledPins;
    }

    function generatePin() : string
    {
        $existPins = getEnabledPins();

        while(true)
        {
            $pin = random_int(10000, 100000);

            foreach($existPins as $i)
            {
                if($pin == (int)$i)
                    continue;
            }

            break;
        }

        $pin = sprintf('%05d', $pin);

        // Write pin to database.
        mysqlQuery("INSERT INTO user (pin, created, countCast, isConnectable, isEnabled) VALUES ('$pin', NOW(), 0, 0, 1);");

        return $pin;
    }

    function loginControl(string $pin) : bool
    {
        $dbResult = mysqlQuery("SELECT * FROM user WHERE pin = '$pin' AND isEnabled = 1;");

        if(mysqli_num_rows($dbResult) > 0)
        {
            $dbExtract = mysqli_fetch_assoc($dbResult);
            
            $_SESSION['mode'] = 0;
            $_SESSION['id'] = (int)$dbExtract['id'];
            $_SESSION['pin'] = (int)$dbExtract['pin'];
            $_SESSION['created'] = new DateTime($dbExtract['created']);

            return true;
        }
        else
            return false;
    }

    function getConnectionAvail() : bool
    {
        $userId = $_SESSION['id'];
        
        $dbResult = mysqlQuery("SELECT isConnectable FROM user WHERE id = $userId;");

        if(mysqli_num_rows($dbResult) > 0)
            return (mysqli_fetch_row($dbResult)[0] == '1');

        return false;
    }

    function setConnectionAvail(bool $state) : void
    {
        $userId = $_SESSION['id'];
        $state = $state ? 1 : 0;

        mysqlQuery("UPDATE user SET isConnectable = $state WHERE id = $userId;");
    }

    function enqueueVideo(string $videoId) : void
    {
        $userId = $_SESSION['id'];

        if($videoId != '')
            mysqlQuery("INSERT INTO playlist (userId, videoId, orderNo) VALUES ($userId, '$videoId', 0);");
    }

    function getPlaylistVideoIds() : array
    {
        $userId = $_SESSION['id'];
        $videoIds = [];

        $dbResult = mysqlQuery("SELECT videoId FROM playlist WHERE userId = $userId ORDER BY orderNo DESC, id ASC;");

        while($dbExtract = mysqli_fetch_row($dbResult))
            array_push($videoIds, $dbExtract[0]);

        return $videoIds;
    }

    function getPlaylistVideosInfo() : array
    {
        return getVideosInfo(getPlaylistVideoIds(), true);
    }

    function getPlayingVideo() : array
    {
        $userId = $_SESSION['id'];
        $video = array();

        $dbResult = mysqlQuery("SELECT castResp FROM user WHERE id = $userId;");

        if(mysqli_num_rows($dbResult) > 0)
        {
            $videoId = mysqli_fetch_row($dbResult)[0];

            if($videoId != null)
                $video = getVideoInfo($videoId);
        }

        return $video;
    }

    function castPlaybackCmd(string $cmd)
    {
        $userId = $_SESSION['id'];

        if($cmd != '')
            mysqlQuery("INSERT INTO playbackCmd (userId, cmd, receivedTime) VALUES ($userId, '$cmd', NOW());");
    }

