<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '物语千寻';
?>

<!--这里是邀请人及其共享的项目信息-->
<div class="ownner">
    <p class="nickname"><?=$owner->nickname?></p>
    <p class="headline">邀请您加入收纳项目</p>
</div>

<div class="triangle">
    <img src="images_2/triangle.png">
</div>

<div class="project">
    <h1><?=$proj->name?></h1>
</div>

<div class="dataform">
    <?php $form = ActiveForm::begin(['action' => ['join/join'],'method'=>'post', 'id'=>'joinForm']); ?>
        <input type="hidden" name="manager_id" value="<?=$owner->user_id?>">
        <input type="hidden" name="project_id" value="<?=$proj->project_id?>">
        <input type="hidden" name="openid" value="<?=$member->openid?>">
        <input type="hidden" name="nickname" value="<?=$member->nickname?>">
        <input type="submit" name="submit-button" class="button" value="确定加入">
    <?php ActiveForm::end(); ?>
</div>