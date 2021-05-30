<?php

    define('kYoutubeKey', ['api_key1', 'api_key2']);

    require_once ($_SERVER['DOCUMENT_ROOT'] . '/php/database.php');

    function youtubeQueryBy(string $url, int $keyIndex) : string
    {
        $response = '';

        if($keyIndex >= 0 && $keyIndex < count(kYoutubeKey))
        {
            $urlKey = $url . '&key=' . kYoutubeKey[$keyIndex];

            // Open session.
            $curlHandler = curl_init($urlKey);

            // Set options.
            curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandler, CURLOPT_HEADER, false);

            // Send request and get response.
            $response = curl_exec($curlHandler);

            // Close session.
            curl_close($curlHandler);

            // Check quota exceed in response.
            if(strpos($response, 'quotaExceeded') !== false)
                $response = youtubeQueryBy($url, $keyIndex + 1);
        }

        return $response;
    }

    function youtubeQuery(string $url) : string
    {
        return youtubeQueryBy($url, 0);
    }

    function searchVideosByKeyword(string $keyword) : array
    {
        $videoIds = array();
        $videos = array();

        if($keyword != '')
        {
            $response = youtubeQuery('https://www.googleapis.com/youtube/v3/search?part=snippet&maxResults=10&type=video&q=' . urlencode($keyword));
            $response = json_decode($response, true)['items'];

            foreach($response as $video)
                array_push($videoIds, $video['id']['videoId']);
        }

        $videos = getVideosInfo($videoIds);

        return $videos;
    }

    function getVideoInfo(string $videoId) : array
    {
        if($videoId == '')
            return [];
        else
        {
            $videos = getVideosInfo([$videoId]);

            if(count($videos) > 0)
                return $videos[0];
            else
                return [];
        }
    }

    function getVideosInfo(array $videoIds) : array
    {
        $videos = array();  // Store all videos.
        $isInCache = array();  // Store "is in cache" state.
        $cacheIds = array();  // Store ids for cache videos.
        $queryIds = array();  // Store ids for query videos.
        $cacheVideos = array();  // Store videos from cache.
        $queryVideos = array();  // Store videos from query.

        // Remove blank video id.
        $checkedVideoIds = [];
        foreach($videoIds as $videoId)
        {
            if($videoId != '')
                array_push($checkedVideoIds, $videoId);
        }
        $videoIds = $checkedVideoIds;

        if(count($videoIds) > 0)
        {
            // Query cache.
            foreach($videoIds as $videoId)
            {
                $tmp = checkCache($videoId);

                // Exists in cache.
                if(count($tmp) > 0)
                {
                    array_push($isInCache, true);
                    array_push($cacheIds, $videoId);
                    array_push($cacheVideos, $tmp);
                }
                
                // Not exists in cache.
                else
                {
                    array_push($isInCache, false);
                    array_push($queryIds, $videoId);
                }
            }

            // Query online.
            if(count($queryIds) > 0)
            {
                $response = youtubeQuery('https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id=' . videoIdsToString($queryIds));
                $response = json_decode($response, true)['items'];

                $counter = 0;
                foreach($response as $video)
                {
                    array_push($queryVideos, [
                        'id' => $queryIds[$counter],
                        'title' => html_entity_decode($video['snippet']['title']),
                        'thumbUrl' => $video['snippet']['thumbnails']['high']['url'],
                        'duration' => isoDurationToSecond($video['contentDetails']['duration'])
                    ]);

                    ++$counter;
                }

                writeCaches($queryVideos);
            }

            // Merge.
            foreach($isInCache as $state)
            {
                // Exists in cache.
                if($state)
                {
                    array_push($videos, $cacheVideos[0]);
                    $cacheVideos = array_slice($cacheVideos, 1);
                }
                
                // Not exists in cache.
                else
                {
                    array_push($videos, $queryVideos[0]);
                    $queryVideos = array_slice($queryVideos, 1);
                }
            }
        }

        return $videos;
    }

    function checkCache(string $videoId) : array
    {
        $video = [];
        
        $dbResult = mysqlQuery("SELECT * FROM youtube WHERE videoId = '$videoId';");
        
        if(mysqli_num_rows($dbResult) > 0)
        {
            $dbExtract = mysqli_fetch_assoc($dbResult);
            
            $video = [
                'id' => $dbExtract['videoId'],
                'title' => $dbExtract['title'],
                'thumbUrl' => $dbExtract['thumbUrl'],
                'duration' => $dbExtract['duration']
            ];
        }

        return $video;
    }

    function writeCache(array $videos) : void
    {
        $id = $videos['id'];
        $title = $videos['title'];
        $thumbUrl = $videos['thumbUrl'];
        $duration = $videos['duration'];

        mysqlQuery("INSERT INTO youtube (videoId, title, thumbUrl, duration, lastUpdate) VALUES ('$id', '$title', '$thumbUrl', $duration, NOW());");
    }

    function writeCaches(array $videos) : void
    {
        foreach($videos as $video)
            writeCache($video);
    }

    function videoIdsToString(array $videoIds)
    {
        $str = '';

        foreach($videoIds as $videoId)
            $str .= $videoId . ',';

        if(substr($str, strlen($str)-1, 1) == ',')
            $str = substr($str, 0, strlen($str)-1);

        return $str;
    }

    function isoDurationToSecond(string $str) : int
    {
        $second = 0;

        $str = substr($str, 2);
        $str = substr($str, 0, strlen($str)-1);

        for($i=0; $i<26; ++$i)
            $str = str_replace(chr(65 + $i), ' ', $str);

        $slice = explode(' ', $str);

        $mul = 1;
        for($i=count($slice)-1; $i>=0; --$i)
        {
            $second += (int)$slice[$i] * $mul;
            $mul *= 60;
        }

        return $second;
    }

