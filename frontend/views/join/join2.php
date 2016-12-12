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
    <a href="#" id="openApp">贴吧客户端</a>
    <script type="text/javascript">
        $('#openApp').click(function() {
            location.href = 'com.baidu.tieba://';
            setTimeout(function() {
                location.href = 'https://itunes.apple.com/cn/app/id477927812';
            }, 250);
            setTimeout(function() {
                location.reload();
            }, 1000);
        }
    </script>
</div>

<div class="android">
    <img src="../images_2/android.png" width="15" height="17">
    <label>安卓版本开发中，暂无下载资源</label>
</div>


