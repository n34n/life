<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '物语千寻';
?>

<div class="ownner">
    <p class="nickname">
        <img src="<?php echo Yii::$app->params['imgServer'].$owner->img->s_path; ?>" class="avatar">
        <label><?=$owner->nickname?></label>
    </p>
    <p class="headline">邀请您加入收纳项目</p>
</div>

<div class="triangle">
    <img src="images_2/triangle.png" width="53" height="30">
</div>

<div class="project">
    <h1><?=$proj->name?></h1>
</div>

