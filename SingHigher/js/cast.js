
let kDelaySecond = 2;
let kDefaultVideoId = '3wmYrJ1cnUw';
let isInit = false;
let gPin = '';
let player = null;
let Marquee = null;
let marqueeLeft = 0;
let Msg = null;
let MsgHandler = null;

const MarqueeApp = {
    data()
    {
        return {
            items: [],

            countPlaylist: -1,
            
            nextVideo: {
                title: '',
                duration: 0
            },
            
            playback: {
                id: '',
                title: '',
                duration: 0,
                elapsed: 0
            },
            isWithServer: false
        };
    },
    mounted()
    {
        this.withServer();
    },
    methods: {
        refreshMarquee: function ()
        {
            this.getPlayback();
            this.items = [];

            this.items = this.items.concat(['您已連接至 PIN 為 ' + gPin + ' 的控制台。']);

            if(!this.isWithServer)
                this.items = this.items.concat(['<warn>無法連接至伺服器，請檢查網路連線與防火牆設定。</warn>']);

            if(this.playback.id != '')
                this.items = this.items.concat(['當前播放的是「' + this.playback.title + '」( ' + timeToStr(this.playback.elapsed) + ' / ' + timeToStr(this.playback.duration) + ' )。']);

            if(this.countPlaylist > 0)
                this.items = this.items.concat(['下一個播放的是「' + this.nextVideo.title + '」( ' + timeToStr(this.nextVideo.duration) + ' )。']);

            if(this.countPlaylist >= 0)
                this.items = this.items.concat(['目前共有 ' + this.countPlaylist + ' 個項目在待播清單中。']);
            else
                this.items = this.items.concat(['<warn>無法取得待播清單。</warn>']);

            this.items = this.items.concat(['所有影片皆由 Youtube 取得，影片版權歸其法定歸屬人所有，並不在 Sing Higher 的授權範圍中。']);
        },
        getPlayback: function ()
        {
            if([1, 2, 5].indexOf(player.getPlayerState()) >= 0)
            {
                this.playback.id = player.getVideoData().video_id;
                this.playback.title = player.getVideoData().title;
                this.playback.duration = Math.ceil(player.getDuration());
                this.playback.elapsed = Math.round(player.getCurrentTime());
            }
            else
            {
                this.playback.id = '';
                this.playback.title = '未知媒體';
                this.playback.duration = 0;
                this.playback.elapsed = 0;
            }
        },
        withServer: function ()
        {
            let parent = this;
            let request = new XMLHttpRequest();

            request.onreadystatechange = function()
            {
                if(this.readyState == 4)
                {
                    if(this.status == 200)
                        parent.isWithServer = true;
                    else if(this.status == 401)
                        Error401();
                    else
                        parent.isWithServer = false;

                    if(parent.isWithServer)
                    {
                        let resp = JSON.parse(request.responseText);

                        parent.countPlaylist = resp.countPlaylist;
                        parent.nextVideo.title = resp.nextVideo.title;
                        parent.nextVideo.duration = resp.nextVideo.duration;

                        parent.runCmds(resp.cmd);
                    }

                    parent.refreshMarquee();

                    setTimeout(parent.withServer, 500);
                }
            }

            if(this.playback.id == undefined || this.playback.id == '')
                request.open('GET', 'api.php?req=castRoutine', true);
            else
                request.open('GET', 'api.php?req=castRoutine&videoId=' + this.playback.id, true);

            request.send();
        },
        runCmds: function (cmds)
        {
            cmds.forEach(function (cmd)
            {
                switch(cmd)
                {
                    case 'playCast':
                        player.playVideo();
                        showMsg('控制台播放控制：播放');
                        break;

                    case 'pauseCast':
                        player.pauseVideo();
                        showMsg('控制台播放控制：暫停');
                        break;

                    case 'muteCast':
                        if(player.isMuted())
                        {
                            player.unMute();
                            showMsg('控制台播放控制：取消靜音');
                        }
                        else
                        {
                            player.mute();
                            showMsg('控制台播放控制：靜音');
                        }
                        break;

                    case 'dropCast':
                        playNextVideo();
                }
            });
        }
    }
};

function onYouTubeIframeAPIReady()
{
    player = new YT.Player('player', {
        height: '100%',
        width: '100%',
        videoId: '',
        playerVars: {
            enablejsapi: 1,
            playsinline: 1,
            controls: 0,
            autoplay: 0,
            disablekb: 1,
            iv_load_policy: 3,
            loop: 0,
            modestbranding: 1,
            rel: 0,
            showinfo: 0,
            ecver: 0
        },
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange
        }
    });
}

function onPlayerReady()
{
    player.setVolume(100);

    let clickCover = document.getElementById('click_cover');
    clickCover.addEventListener('click', function ()
    {
        clickCover.style.display = 'none';
        player.playVideo();
    });

    getPin();
}

function getPin()
{
    let request = new XMLHttpRequest();

    request.onreadystatechange = function()
    {
        if(this.readyState == 4)
        {
            if(this.status == 200)
            {
                gPin = JSON.parse(request.responseText).pin;
                init();
            }
            else if(this.status == 401)
                Error401();
            else
                window.location.href = 'index.php';
        }
    };

    request.open('GET', 'api.php?req=getPin');
    request.send();
}

function onPlayerStateChange(e)
{
    if(e.data == YT.PlayerState.ENDED)
        playNextVideo();
}

function init()
{
    Vue.createApp(MarqueeApp).component('marquee-item', {
        template: `<span v-html="content"></span>`,
        props: ['content']
    }).mount('#marquee_container');

    Marquee = document.getElementById('marquee');
    Msg = document.getElementById('msg');

    clearMsg();
    runMarquee();
    playNextVideo();
}

function playNextVideo()
{
    let request = new XMLHttpRequest();

    request.onreadystatechange = function ()
    {
        if(this.readyState == 4)
        {
            if(this.status == 200)
                playVideo(JSON.parse(request.responseText).nextVideoId);
            else if(this.status == 200)
                Error401();
            else
            {
                showMsg('錯誤：無法取得下個項目');
                setTimeout(playNextVideo, (kDelaySecond + 1) * 1000);
            }
        }
    };

    request.open('GET', 'api.php?req=getNextVideo');
    request.send();
}

function playVideo(videoId)
{
    let isDefault = false;

    if(videoId == '')
    {
        videoId = kDefaultVideoId;
        isDefault = true;
    }

    let videoLoading = setInterval(function ()
    {
        if([1, 2, 5].indexOf(player.getPlayerState()) >= 0)
        {
            clearInterval(videoLoading);

            if(isDefault)
                showMsg('暖場播放：' + player.getVideoData().title);
            else
                showMsg('開始播放：' + player.getVideoData().title);

            player.playVideo();
        }
        else
            showMsg('讀取下個項目中...');
    }, 500);

    player.loadVideoById(videoId);
}

function getBodyWidth()
{
    return document.getElementsByTagName('body')[0].clientWidth;
}

function runMarquee()
{
    marqueeLeft = getBodyWidth();
    Marquee.style.left = marqueeLeft + 'px';

    shiftMarquee();
}

function shiftMarquee()
{
    if(marqueeLeft < -Marquee.clientWidth)
        runMarquee();
    else
    {
        marqueeLeft -= 1;
        Marquee.style.left = marqueeLeft + 'px';

        setTimeout(shiftMarquee, 10);
    }
}

function showMsg(msg)
{
    clearMsg();

    Msg.innerHTML = msg;
    Msg.style.display = 'block';
    
    MsgHandler = setTimeout(clearMsg, kDelaySecond * 1000);
}

function clearMsg()
{
    if(MsgHandler != null)
    {
        clearTimeout(MsgHandler);
        MsgHandler = null;
    }

    Msg.innerHTML = '';
    Msg.style.display = 'none';
}

function timeToStr(seconds)
{
    let minutes = 0;
    
    minutes = Math.floor(seconds / 60);
    seconds %= 60;

    if(seconds < 10)
        seconds = '0' + seconds;

    return (minutes + ':' + seconds);
}

function Error401()
{
    alert('您已遺失本投放端的存取權！');
    window.location.href = 'index.php';
}

