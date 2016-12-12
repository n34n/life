<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '物语千寻';
?>

<div class="member">
    <div>
        <img src="../images_2/avatar.jpg" class="avatar">
    </div>
    <p class="nickname">被邀请人昵称</p>
    <p class="headline">成功加入</p>
    <p class="project">江桥万达城市公寓3号601室</p>
</div>

<div class="shape">
    <img src="../images_2/shape.png">
</div>

<div class="dataform">
    <button class="button" onClick="location.href='jsonData://'">进入物语千寻</button>
    <a href="jump">download</a>
    <a href="com.baidu.tieba://">com.baidu.tieba</a>
    <a href="https://itunes.apple.com/cn/app/id477927812" id="openApp">贴吧客户端</a>
    <script type="text/javascript">

        if(navigator.userAgent.match(/(iPhone|iPod|iPad);?/i))  {

            //Animation://com.yz.animation

            var isInstalled;

            //var gz = '{"comName":"${com.short_name}","comID":"${com.id}","comPhoneNum":"${com.phone_num}","type":"0"}';

            //var jsongz =JSON.parse(gz);



            //下面是IOS调用的地址，自己根据情况去修改

            var ifrSrc = 'com.baidu.tieba://';

            var ifr = document.createElement('iframe');

            ifr.src = ifrSrc;

            ifr.style.display = 'none';

            ifr.onload = function() {

                // alert('Is installed.');

                isInstalled = true;

                alert(isInstalled);

                document.getElementById('openApp').click();};

            ifr.onerror = function() {

                // alert('May be not installed.');

                isInstalled = false;

                alert(isInstalled);

            }

            document.body.appendChild(ifr);

            setTimeout(function() {

                document.body.removeChild(ifr);

            },1000);

        }

        }
    </script>
</div>

<div class="android">
    <img src="../images_2/android.png" width="15" height="17">
    <label>安卓版本开发中，暂无下载资源</label>
</div>


