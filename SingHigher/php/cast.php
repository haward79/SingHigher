<?php

    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/common.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/database.php');

    function loginCast(string $pin) : bool
    {
        $dbResult = mysqlQuery("SELECT * FROM user WHERE pin = '$pin' AND isConnectable = 1 AND isEnabled = 1;");

        if(mysqli_num_rows($dbResult) > 0)
        {
            $dbExtract = mysqli_fetch_assoc($dbResult);
            
            $_SESSION['mode'] = 1;
            $_SESSION['id'] = (int)$dbExtract['id'];
            $_SESSION['pin'] = (int)$dbExtract['pin'];
            $_SESSION['created'] = new DateTime($dbExtract['created']);

            return true;
        }
        else
            return false;
    }

    function countPlaylistVideos() : int
    {
        $userId = $_SESSION['id'];

        $dbResult = mysqlQuery("SELECT COUNT(id) FROM playlist WHERE userId = $userId;");

        if(mysqli_num_rows($dbResult) > 0)
            return mysqli_fetch_row($dbResult)[0];

        return -1;
    }

    function peekNextVideoId() : string
    {
        $nextVideoId = '';
        $userId = $_SESSION['id'];

        $dbResult = mysqlQuery("SELECT videoId FROM playlist WHERE userId = $userId ORDER BY orderNo DESC, id ASC LIMIT 1;");

        if(mysqli_num_rows($dbResult) > 0)
            $nextVideoId = mysqli_fetch_row($dbResult)[0];

        return $nextVideoId;
    }

    function popNextVideoId() : string
    {
        $nextVideoId = '';
        $userId = $_SESSION['id'];

        $dbResult = mysqlQuery("SELECT id, videoId FROM playlist WHERE userId = $userId ORDER BY orderNo DESC, id ASC LIMIT 1;");

        if(mysqli_num_rows($dbResult) > 0)
        {
            $dbExtract = mysqli_fetch_assoc($dbResult);
            
            $nextVideoAbsId = $dbExtract['id'];
            $nextVideoId = $dbExtract['videoId'];

            mysqlQuery("DELETE FROM playlist WHERE id = $nextVideoAbsId;");
        }

        return $nextVideoId;
    }

    function insertCastResponse(string $resp) : void
    {
        $userId = $_SESSION['id'];
        mysqlQuery("UPDATE user SET castResp = '$resp' WHERE id = $userId;");
    }

    function getPlaybackCmd() : array
    {
        $userId = $_SESSION['id'];
        $cmds = array();
        $ids = '';

        $dbResult = mysqlQuery("SELECT id, cmd, receivedTime FROM playbackCmd WHERE userId = $userId ORDER BY receivedTime ASC;");

        while($dbExtract = mysqli_fetch_assoc($dbResult))
        {
            $diffSeconds = (int)(new DateTime($dbExtract['receivedTime']))->diff(new DateTime())->format('%s');

            if($diffSeconds < 2)
                array_push($cmds, $dbExtract['cmd']);

            $ids .= $dbExtract['id'] . ',';
        }

        if($ids != '')
        {
            $ids = substr($ids, 0, strlen($ids)-1);
            $ids = str_replace(',', ' OR id = ', $ids);
            mysqlQuery("DELETE FROM playbackCmd WHERE id = $ids;");
        }

        return $cmds;
    }

