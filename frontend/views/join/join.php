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
    <button class="button" href="#">进入物语千寻</button>
</div>

<div class="android">
    <img src="../images_2/android.png" width="15" height="17">
    <label>安卓版本开发中，暂无下载资源</label>
</div>

