<?php
    require_once 'php/common.php';

    if(!checkControlLogin())
        header('Location: index.php');
?>

<!doctype html>
<html>

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>控制台 - Sing Higher</title>
        <link rel="shortcut icon" href="images/icon.png" />
        <link rel="stylesheet" href="css/common.css" />
        <link rel="stylesheet" href="css/control.css" />
        <link rel="stylesheet" href="css/switch.css" />
        <script src="js/vue.js"></script>
        <script src="js/control.js"></script>
    </head>

    <body>

        <section id="header_section">

            <div id="headerContent_block">
                <img src="images/icon_with_name.png" />
            
                <h1>
                    Sing Higher 
                    控制台
                </h1>
            </div>
        
        </section>

        <section id="info_section" class="general_section">
            
            <h2 @click="toggleContentVisibility">控制台管理</h2>

            <div class="general_section_content" v-if="isContentVisible">
            
                <p>
                    本控制台的 PIN 是 <span>{{ pin }}</span> 。<br />
                    請您妥善保管您的 PIN 以免您的隱私外洩。

                    <p id="tips_text">
                        您可以在要投放畫面的設備上以瀏覽器打開 <a href="https://singhigher.haward79.tw/" target="_blank">Sing Higher 首頁</a>。<br />
                        在投放功能中輸入上方的 PIN，並點擊「連接」。<br />
                        若接成功，投放端會顯示主畫面，您可以使用本控制台與投放端互動。<br />
                        備註：您可能需要啟用下方的選項才能成功對接。<br />
                        警告：一個控制台最多只允許一個投放端與其連接，若您嘗試讓多個投放端同時連接至一個控制台，本系統將不對此行為提供任何保證。
                    </p>
                </p>
                
                <br />

                <p>
                    是否允許其他裝置連接本控制台，並投放主畫面？

                    <div class="switch_element" :class="{ switch_element_off: !isConnectionOn, switch_element_on: isConnectionOn }">
                        <div class="switch_indicator" :class="{ switch_indicator_off: !isConnectionOn, switch_indicator_on: isConnectionOn }" @click="toggleConnection"></div>
                    </div>

                    <p id="tips_text">
                        若投放端與主控台位於相同設備、相同瀏覽器，即使不啟用此選項也可以成功連接。<br />
                        在其他狀況下，您必須啟用此選項，其他投放端才能與本主控台連接、並投放主畫面。<br />
                        為了保護您的隱私，本選項為常關設定。當您啟用此選項後，本項將於 {{ autoConnectionDelay }} 秒後恢復關閉狀態。
                    </p>
                </p>

                <br />

                <p>
                    您盡興了嗎？
                    <button id="exit_button" @click="logout">關閉本控制台</button>

                    <p id="tips_text">
                        感謝您使用 Sing Higher，希望您擁有一個美好的使用體驗。<br />
                        請點擊上方的「關閉本控制台」按鈕即可關閉本主控台。<br />
                        為了維護其他使用者的權益。一個控制台創建後最長可使用 1 天。<br />
                        當控制台使用超時後，系統將會自動關閉該控制台，此操作不再另行通知使用者。
                    </p>
                </p>

            </div>

        </section>

        <section id="cast_section" class="general_section">
            
            <h2 @click="toggleContentVisibility">投放控制</h2>

            <div class="general_section_content" v-if="isContentVisible">

                <p><b>投放中的主畫面正在播放：</b></p>

                <div id="videoPresent_container">

                    <div class="videoPresent_block">
                        <div class="videoPresent_thumb_block">
                            <img class="videoPresent_thumb_img" :src="video.thumbUrl" />
                            <span class="videoPresent_duration_text">{{ timeToStr(video.duration) }}</span>
                        </div>
                        
                        <div class="videoPresent_description_block">
                            <h3 class="videoPresent_videoTitle_text">{{ video.title }}</h3>
                        </div>
                    </div>

                </div>

                <br />

                <p>
                    <span id="playbackControl_text">★ 播放控制 ★</span><br />
                    <button @click="castCmd(0)">播放</button>
                    <button @click="castCmd(1)">暫停</button>
                    <button @click="castCmd(2)">靜音</button>
                    <button @click="castCmd(3)">棄歌</button>
                </p>

            </div>

        </section>

        <section id="search_section" class="general_section">
        
            <h2 @click="toggleContentVisibility">搜尋影片</h2>

            <div class="general_section_content" v-if="isContentVisible">

                <input type="text" v-model="searchKeyword" @keypress.enter="search" placeholder="本搜尋由 Youtube 提供，請輸入關鍵字？" />
                <button v-on:click="search">搜尋</button>

                <p id="searchMsg_text">{{ searchMsg }}</p>

                <div id="videoPresent_container">

                    <video_present-component
                        v-for="video in videos"
                        :key="video.id"
                        :video-id="video.videoId"
                        :video-title="video.title"
                        :thumb-url="video.thumbUrl"
                        :duration="video.duration"
                        @add-video-to-playlist="addVideoToPlaylist"
                    >
                    </video_present-component>

                </div>

            </div>
        
        </section>

        <section id="playlist_section" class="general_section">
        
            <h2 @click="toggleContentVisibility">待播清單</h2>

            <div class="general_section_content" v-if="isContentVisible">

                <p id="playlistMsg_text">總共有 {{ countPlaylist }} 個項目在待播清單中。</p>

                <div id="videoPresent_container">

                    <video_present-component
                        v-for="video in videos"
                        :key="video.id"
                        :video-id="video.videoId"
                        :video-title="video.title"
                        :thumb-url="video.thumbUrl"
                        :duration="video.duration"
                        @add-video-to-playlist="addVideoToPlaylist"
                    >
                    </video_present-component>

                </div>

            </div>
        
        </section>

        <footer>
            All rights reserved by Sing Higher @ <a href="https://www.haward79.tw/" target="_blank">haward79.tw</a> &copy Version 1.0 - 2021
        </footer>
    
    </body>

</html>

