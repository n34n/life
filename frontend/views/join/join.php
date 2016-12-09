<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '物语千寻';
?>

<div class="member">
    <div>
        <img src="<?=$member->headimgurl?>" class="avatar">
    </div>
    <p class="nickname"><?=$member->nickname?></p>
    <p class="headline">成功加入</p>
    <p class="project"><?=$proj->name?></p>
</div>

<div class="shape">
    <img src="../images_2/shape.png">
</div>

<div class="dataform">
    <button class="button" onClick="location.href='http://mp.weixin.qq.com/mp/redirect?url=http://https%3A%2F%2Fitunes.apple.com%2Fcn%2Fapp%2Fbear-hua-li-shu-xie-bi-ji%2Fid1091189122%3Fmt%3D12'">进入物语千寻</button>
</div>

<div class="android">
    <img src="../images_2/android.png" width="15" height="17">
    <label>安卓版本开发中，暂无下载资源</label>
</div>

