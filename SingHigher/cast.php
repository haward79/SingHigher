<?php
    require_once 'php/common.php';

    if(!checkCastCompatLogin())
        header('Location: index.php');
?>

<!doctype html>
<html>

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>投放 - Sing Higher</title>
        <link rel="shortcut icon" href="images/icon.png" />
        <link rel="stylesheet" href="css/common.css" />
        <link rel="stylesheet" href="css/cast.css" />
        <script src="https://www.youtube.com/iframe_api"></script>
        <script src="js/vue.js"></script>
        <script src="js/cast.js"></script>
    </head>

    <body>

        <div id="click_cover">
            請用滑鼠左鍵點一下畫面 ......<br />
            聽說按下鍵盤的 F11 有助於增加使用者體驗。
        </div>

        <div id="player"></div>
        <div id="player_cover"></div>

        <div id="marquee_container">
            <div id="marquee">
                <marquee-item v-for="item in items" :content="item"></marquee-item>
            </div>
        </div>
        
        <div id="msg"></div>
    
    </body>

</html>

