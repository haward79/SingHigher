<?php

    function mysqlQuery(string $query)
    {
        $dbCon = mysqli_connect('hostname', 'username', 'password', 'SingHigher');

        if($dbCon === false)
            return false;

        $dbResult = mysqli_query($dbCon, $query);

        if($dbResult === false)
            return false;
        else
        {
            mysqli_close($dbCon);
            return $dbResult;
        }
    }

