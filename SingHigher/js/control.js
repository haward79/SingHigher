
let gPin = '';

const InfoSection = {
    data()
    {
        return {
            isContentVisible: true,
            pin: gPin,
            isConnectionOn: false,
            isConnectionStateSynced: false,
            autoConnectionOffHandler: null,
            autoConnectionDelay: 5
        };
    },
    methods: {
        toggleContentVisibility: function ()
        {
            this.isContentVisible = !this.isContentVisible;
        },
        toggleConnection: function ()
        {
            if(this.isConnectionStateSynced)
            {
                // Clear auto off routine.
                if(this.autoConnectionOffHandler != null)
                {
                    clearTimeout(this.autoConnectionOffHandler);
                    this.autoConnectionOffHandler - null;
                }

                this.isConnectionStateSynced = false;
                let connectionState = (!this.isConnectionOn ? '1' : '0');
                let connectionStateStr = (!this.isConnectionOn ? '允許' : '拒絕');
                let parent = this;
                let request = new XMLHttpRequest();

                request.onreadystatechange = function ()
                {
                    if(this.readyState == 4)
                    {
                        if(this.status == 200)
                        {
                            parent.isConnectionOn = !parent.isConnectionOn;
                            parent.isConnectionStateSynced = true;

                            if(parent.isConnectionOn)
                                parent.autoConnectionOffHandler = setTimeout(parent.toggleConnection, parent.autoConnectionDelay * 1000);
                        }
                        else if(this.status == 401)
                            Error401();
                        else
                            alert('無法將控制台對接設定變更為' + connectionStateStr + '！請稍後重試！');
                    }
                };

                request.open('GET', 'api.php?req=setConState&state=' + connectionState, true);
                request.send();
            }
            else
                alert('請等待先前變更的設定生效後再執行其他操作！');
        },
        logout: function ()
        {
            let request = new XMLHttpRequest();

            request.onreadystatechange = function ()
            {
                if(request.readyState == 4)
                    window.location.href = 'index.php';
            };

            request.open('GET', 'api.php?req=logout');
            request.send();
        }
    },
    mounted()
    {
        let parent = this;
        let request = new XMLHttpRequest();

        request.onreadystatechange = function ()
        {
            if(this.readyState == 4)
            {
                if(this.status == 200)
                {
                    state = JSON.parse(request.responseText).state;

                    if(state == '1')
                        parent.isConnectionOn = true;
                    else
                        parent.isConnectionOn = false;

                    parent.isConnectionStateSynced = true;

                    // Switch off.
                    if(parent.isConnectionOn)
                        parent.toggleConnection();
                }
                else if(this.status == 401)
                    Error401();
                else
                {
                    alert('無法取得控制台對接設定！');
                    window.location.href = 'index.php';
                }
            }
        };

        request.open('GET', 'api.php?req=getConState', true);
        request.send();
    }
};

const CastSection = {
    data()
    {
        return {
            isContentVisible: true,
            video: {
                title: '',
                thumbUrl: '',
                duration: 0
            }
        };
    },
    mounted: function ()
    {
        this.retrievePlaying();
    },
    methods: {
        toggleContentVisibility: function ()
        {
            this.isContentVisible = !this.isContentVisible;
        },
        retrievePlaying: function ()
        {
            let parent = this;
            let request = new XMLHttpRequest();

            request.onreadystatechange = function ()
            {
                if(this.readyState == 4)
                {
                    if(this.status == 200)
                        parent.showPlaying(request.responseText);
                    else if(this.status == 401)
                        Error401();
                }
            };

            request.open('GET', 'api.php?req=getPlaying', true);
            request.send();
        },
        showPlaying: function (resp)
        {
            let result = JSON.parse(resp).playingVideo;

            if(result.title == undefined)
            {
                this.video.title = '( 無法取得資訊 )';
                this.video.thumbUrl = 'https://dummyimage.com/480X360/000000/fff&text=X';
                this.video.duration = 0;
            }
            else
            {
                this.video.title = result.title;
                this.video.thumbUrl = result.thumbUrl;
                this.video.duration = parseInt(result.duration);
            }

            setTimeout(this.retrievePlaying, 500);
        },
        timeToStr: function (second)
        {
            return timeToStr(second);
        },
        castCmd: function (op)
        {
            let opStr = ['playCast', 'pauseCast', 'muteCast', 'dropCast'][op];
            let request = new XMLHttpRequest();

            request.onreadystatechange = function ()
            {
                if(this.readyState == 4)
                {
                    if(this.status == 200)
                    {}
                    else if(this.status == 401)
                        Error401();
                    else
                        alert('播放控制指令操作失敗！');
                }
            };

            request.open('GET', 'api.php?req=castCtrl&op=' + opStr, true);
            request.send();
        }
    }
};

const SearchSection = {
    data()
    {
        return {
            isContentVisible: true,
            searchKeyword: '',
            searchMsg: '',
            videos: []
        };
    },
    methods: {
        toggleContentVisibility: function ()
        {
            this.isContentVisible = !this.isContentVisible;
        },
        search: function ()
        {
            if(this.searchKeyword == '')
            {
                this.searchMsg = '請先輸入關鍵字再按搜尋！';
                this.videos = [];
            }
            else
            {
                this.searchMsg = '正在 YouTube 搜尋「' + this.searchKeyword + '」 ......';
                let parent = this;
                let request = new XMLHttpRequest();

                request.onreadystatechange = function ()
                {
                    if(this.readyState == 4)
                    {
                        if(this.status == 200)
                            parent.showSearchResult(JSON.parse(request.responseText));
                        else if(this.status == 401)
                            Error401();
                        else
                            this.searchMsg = '搜尋失敗！請稍後重試。';
                    }
                };

                request.open('GET', 'api.php?req=search&keyword=' + this.searchKeyword, true);
                request.send();
            }
        },
        showSearchResult: function (result)
        {
            let parent = this;

            this.searchMsg = '以下是在 YouTube 搜尋「' + this.searchKeyword + '」的結果。';
            this.videos = [];
            
            result.forEach(function (value, index)
            {
                parent.videos.push({
                    id: index,
                    videoId: value['id'],
                    title: value['title'],
                    thumbUrl: value['thumbUrl'],
                    duration: timeToStr(value['duration'])
                });
            });
        },
        addVideoToPlaylist: function (videoId)
        {
            let request = new XMLHttpRequest();

            request.onreadystatechange = function ()
            {
                if(this.readyState == 4)
                {
                    if(this.status != 200)
                        alert('加入待播清單時發生錯誤！');
                }
            };

            request.open('GET', 'api.php?req=enqueueVideo&videoId=' + videoId);
            request.send();
        }
    }
};

const PlaylistSection = {
    data()
    {
        return {
            isContentVisible: true,
            countPlaylist: 0,
            videos: []
        };
    },
    mounted()
    {
        this.retrievePlaylist();
    },
    methods: {
        toggleContentVisibility: function ()
        {
            this.isContentVisible = !this.isContentVisible;
        },
        retrievePlaylist: function ()
        {
            let parent = this;
            let request = new XMLHttpRequest();

            request.onreadystatechange = function ()
            {
                if(this.readyState == 4)
                {
                    if(this.status == 200)
                        parent.showPlaylist(JSON.parse(request.responseText));
                    else if(this.status == 401)
                        Error401();
                }
            };

            request.open('GET', 'api.php?req=getPlaylist', true);
            request.send();
        },
        showPlaylist: function (result)
        {
            let parent = this;
            this.countPlaylist = result.length;
            this.videos = [];
            
            result.forEach(function (value, index)
            {
                parent.videos.push({
                    id: index,
                    videoId: value['id'],
                    title: value['title'],
                    thumbUrl: value['thumbUrl'],
                    duration: timeToStr(value['duration'])
                });
            });

            setTimeout(this.retrievePlaylist, 500);
        }
    }
};

document.addEventListener('DOMContentLoaded', function ()
{
    // Get pin.
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

    request.open('GET', 'api.php?req=getPin', true);
    request.send();
});

function init()
{
    Vue.createApp(InfoSection).mount('#info_section');
    Vue.createApp(CastSection).mount('#cast_section');

    Vue.createApp(SearchSection).component('video_present-component', {
        template: `
            <div class="videoPresent_block">
                <div class="videoPresent_thumb_block">
                    <img class="videoPresent_thumb_img" :src="thumbUrl" />
                    <span class="videoPresent_duration_text">{{ duration }}</span>
                </div>
                
                <div class="videoPresent_description_block">
                    <h3 class="videoPresent_videoTitle_text">{{ videoTitle }}</h3>
                    <button @click.prevent="$emit('addVideoToPlaylist', videoId)">待播</button>
                </div>
            </div>
        `,
        props: ['videoId', 'videoTitle', 'thumbUrl', 'duration']
    }).mount('#search_section');

    Vue.createApp(PlaylistSection).component('video_present-component', {
        template: `
            <div class="videoPresent_block">
                <div class="videoPresent_thumb_block">
                    <img class="videoPresent_thumb_img" :src="thumbUrl" />
                    <span class="videoPresent_duration_text">{{ duration }}</span>
                </div>
                
                <div class="videoPresent_description_block">
                    <h3 class="videoPresent_videoTitle_text">{{ videoTitle }}</h3>
                </div>
            </div>
        `,
        props: ['videoId', 'videoTitle', 'thumbUrl', 'duration']
    }).mount('#playlist_section');
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

function replaceAll(str, needle, replacement)
{
    while(str.indexOf(needle) != -1)
        str = str.replace(needle, replacement);

    return str;
}

function Error401()
{
    alert('您已遺失本控制台的存取權！');
    window.location.href = 'index.php';
}

