
function isDigits(str)
{
    for(i=0, countI=str.length; i<countI; ++i)
    {
        if(str.charCodeAt(i) >= 48 && str.charCodeAt(i) <= 57)
        {}
        else
            return false;
    }

    return true;
}

const ControlForm = {
    data()
    {
        return {
            pin: ''
        };
    },
    methods: {
        generatePin: function ()
        {
            let request = new XMLHttpRequest();

            request.onreadystatechange = function (parent)
            {
                if(this.readyState == 4 && this.status == 200)
                {
                    response = JSON.parse(request.responseText);

                    if(response.pin != undefined)
                    {
                        parent.pin = '您的 PIN 是 ' + response.pin;
                        window.location.href = 'control.php';
                    }
                    else
                        parent.pin = '無法取得 PIN！';
                }
            }.bind(request, this);

            request.open('GET', 'api.php?req=genPin', true);
            request.send();
        }
    }
};

const CastForm = {
    data()
    {
        return {
            pin: ''
        };
    },
    methods: {
        connect: function()
        {
            if(this.pin.length != 5)
            {
                this.pin = '';
                alert('您輸入的 PIN 格式有誤。');
            }
            else if(!isDigits(this.pin))
            {
                this.pin = '';
                alert('您輸入的 PIN 格式有誤。');
            }
            else
            {
                let request = new XMLHttpRequest();

                request.onreadystatechange = function ()
                {
                    if(this.readyState == 4)
                    {
                        if(this.status == 200)
                            window.location.href = 'cast.php';
                        else
                            alert('您輸入的 PIN 未對應到任何接受對接的控制台。');
                    }
                }.bind(request, this);

                request.open('GET', 'api.php?req=castLogin&pin=' + this.pin, true);
                request.send();
            }
        }
    }
};

let ControlComp = null;
let CastComp = null;

document.addEventListener('DOMContentLoaded', function ()
{
    ControlComp = Vue.createApp(ControlForm).mount('#form_control');
    CastComp = Vue.createApp(CastForm).mount('#form_cast');
});

