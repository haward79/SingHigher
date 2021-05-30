<!doctype html>
<html>

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>歡迎 - Sing Higher</title>
        <link rel="shortcut icon" href="images/icon.png" />
        <link rel="stylesheet" href="css/common.css" />
        <link rel="stylesheet" href="css/index.css" />
        <script src="js/vue.js"></script>
        <script src="js/index.js"></script>
    </head>

    <body>

        <img id="iconWithName_img" src="images/icon_with_name.png" />

        <p id="description_text">
            Sing Higher 是奠基於網頁開發的線上 KTV 系統。
            您可以在電腦、筆電、平板、手機等設備上打開瀏覽器，
            並盡情享受 Sing Higher 的樂趣！
            更重要的是 Sing  Higher 完全免費、也沒有任何廣告，
            期待熱愛 K 歌的您邀請朋友一同參與！
            <br /><br />
            ※ 遇到問題了嗎？您可以聯繫<a target="_blank" href="mailto:haward79@mail.haward79.tw">系統管理員</a>尋求協助！
        </p>
    
        <div id="form_container">

            <section class="form_block" id="form_control">
                <h2><span>首先 , </span>控制台</h2>
                
                <p id="test">
                    您必須先取得一個 PIN 才能建立控制台，
                    並透過控制台來管理 KTV 系統。
                </p>

                <input type="text" v-bind:value="pin" placeholder="點擊下方按鈕來取得 PIN" @keypress.enter="generatePin" readonly />
                <button v-on:click="generatePin">取得</button>
            </section>

            <section class="form_block" id="form_cast">
                <h2><span>接著 , </span>投放</h2>
                
                <p>
                    輸入控制台提供的 PIN 來執行對接。
                    讓您在相同裝置上或不同裝置上投放 KTV 的主畫面。
                </p>

                <input type="text" v-model="pin" placeholder="請輸入您的控制台的 PIN？" @keypress.enter="connect" />
                <button v-on:click="connect">連接</button>
            </section>

        </div>
    
    </body>

</html>

