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
    <a href="com.n34n.jsonData://">yanglandphone</a>
    <a href="https://itunes.apple.com/cn/app/id477927812" id="openApp">贴吧客户端</a>
    <script type="text/javascript">
        document.getElementById('openApp').onclick = function(e){
            // 通过iframe的方式试图打开APP，如果能正常打开，会直接切换到APP，并自动阻止a标签的默认行为
            // 否则打开a标签的href链接
            var ifr = document.createElement('iframe');
            ifr.src = 'com.baidu.tieba://';
            ifr.style.display = 'none';
            document.body.appendChild(ifr);
            window.setTimeout(function(){
                document.body.removeChild(ifr);
            },3000)
        };
    </script>
</div>

<div class="android">
    <img src="../images_2/android.png" width="15" height="17">
    <label>安卓版本开发中，暂无下载资源</label>
</div>


