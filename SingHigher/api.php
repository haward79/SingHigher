<?php

    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/control.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/cast.php');
    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/youtube.php');

    // Deal with client request.
    if(isset($_GET['req']))
    {
        $request = strtolower($_GET['req']);

        if($request == strtolower('genPin'))
        {
            $pin = generatePin();

            if(loginControl($pin))
            {
                http_response_code(200);
                header('Content-Type: text/json');

                echo json_encode(['pin' => $pin]);
            }
            else
                http_response_code(500);
        }
        else if($request == strtolower('castLogin'))
        {
            if(isset($_GET['pin']))
            {
                if(checkControlLogin() && checkLoginDue() && checkLoginEnable())
                {
                    if($_SESSION['pin'] == $_GET['pin'])
                        http_response_code(200);
                    else
                        http_response_code(400);
                }
                else if(loginCast($_GET['pin']))
                    http_response_code(200);
                else
                    http_response_code(401);
            }
            else
                http_response_code(400);
        }
        else if($request == strtolower('getConState'))
        {
            if(checkControlLogin() && checkLoginDue() && checkLoginEnable())
            {
                http_response_code(200);
                header('Content-Type: text/json');

                echo json_encode(['state' => (getConnectionAvail() ? '1' : '0')]);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('setConState'))
        {
            if(checkControlLogin() && checkLoginDue() && checkLoginEnable())
            {
                if(isset($_GET['state']))
                {
                    if($_GET['state'] == '1')
                        setConnectionAvail(true);
                    else
                        setConnectionAvail(false);

                    http_response_code(200);
                }
                else
                    http_response_code(400);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('enqueueVideo'))
        {
            if(checkControlLogin() && checkLoginDue() && checkLoginEnable())
            {
                if(isset($_GET['videoId']))
                {
                    if($_GET['videoId'] != '')
                        enqueueVideo($_GET['videoId']);

                    http_response_code(200);
                }
                else
                    http_response_code(400);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('getPlaylist'))
        {
            if(checkControlLogin() && checkLoginDue() && checkLoginEnable())
            {
                http_response_code(200);
                header('Content-Type: text/json');
                
                echo json_encode(getPlaylistVideosInfo());
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('castCtrl'))
        {
            if(checkControlLogin() && checkLoginDue() && checkLoginEnable())
            {
                if(isset($_GET['op']) && $_GET['op'] != '')
                    castPlaybackCmd($_GET['op']);

                http_response_code(200);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('getPin'))
        {
            if(isset($_SESSION['pin']))
            {
                http_response_code(200);
                header('Content-Type: text/json');

                echo json_encode(['pin' => $_SESSION['pin']]);
            }
            else
                http_response_code(400);
        }
        else if($request == strtolower('castRoutine'))
        {
            if(checkCastCompatLogin() && checkLoginDue() && checkLoginEnable())
            {
                if(isset($_GET['videoId']) && $_GET['videoId'] != '')
                    insertCastResponse($_GET['videoId']);
                else
                    insertCastResponse('');
                
                http_response_code(200);
                header('Content-Type: text/json');

                echo json_encode(['countPlaylist' => countPlaylistVideos(), 'nextVideo' => getVideoInfo(peekNextVideoId()), 'cmd' => getPlaybackCmd()]);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('getNextVideo'))
        {
            if(checkCastCompatLogin() && checkLoginDue() && checkLoginEnable())
            {
                http_response_code(200);
                header('Content-Type: text/json');

                echo json_encode(['nextVideoId' => popNextVideoId()]);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('getPlaying'))
        {
            if(checkCastCompatLogin() && checkLoginDue() && checkLoginEnable())
            {
                http_response_code(200);
                header('Content-Type: text/json');

                echo json_encode(['playingVideo' => getPlayingVideo()]);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('search'))
        {
            if(checkCastCompatLogin() && checkLoginDue() && checkLoginEnable())
            {
                if(isset($_GET['keyword']))
                {
                    http_response_code(200);
                    header('Content-Type: text/json');

                    echo json_encode(searchVideosByKeyword($_GET['keyword']));
                }
                else
                    http_response_code(400);
            }
            else
                http_response_code(401);
        }
        else if($request == strtolower('logout'))
        {
            logout();

            http_response_code(200);
        }
        else
            http_response_code(404);
    }

