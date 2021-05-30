<?php

    ini_set('display_errors', true);

    if(session_status() == PHP_SESSION_NONE)
        session_start();

    
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/database.php');


    function checkLogin() : bool
    {
        return (isset($_SESSION['id']) && $_SESSION['id'] >= 0);
    }

    function checkControlLogin() : bool
    {
        $resp = checkLogin() && $_SESSION['mode'] == 0;
        
        if($resp)
            controlUpdateLastLogin();

        return $resp;
    }

    function controlUpdateLastLogin() : void
    {
        $userId = $_SESSION['id'];
        mysqlQuery("UPDATE user SET lastShown = NOW() WHERE id = $userId;");
    }

    function checkCastCompatLogin() : bool
    {
        return (checkCastLogin() || checkControlLogin());
    }

    function checkCastLogin() : bool
    {
        return (checkLogin() && $_SESSION['mode'] == 1);
    }

    function checkLoginDue() : bool
    {
        if(checkLogin())
        {
            $d = $_SESSION['created']->diff(new DateTime());
            $days = (int)$d->format('%a');
            $sign = $d->format('%R');

            if($sign == '+' && $days == 0)
                return true;
            else
            {
                logout();
                return false;
            }
        }
        else
            return false;
    }

    function checkLoginEnable() : bool
    {
        if(checkLogin())
        {
            $userId = $_SESSION['id'];
            $dbResult = mysqlQuery("SELECT id FROM user WHERE id = $userId AND isEnabled = 1;");

            if(mysqli_num_rows($dbResult) > 0)
                return true;
            else
            {
                logout();
                return false;
            }
        }
        else
            return false;
    }

    function logout()
    {
        if(checkLogin())
        {
            $userId = $_SESSION['id'];
            mysqlQuery("UPDATE user SET isEnabled = 0 WHERE id = $userId;");

            unset($_SESSION['id']);
            unset($_SESSION['pin']);
            unset($_SESSION['created']);
            unset($_SESSION['mode']);
        }
    }

