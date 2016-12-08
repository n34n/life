<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '物语千寻';
?>



<!--这里是邀请人及其共享的项目信息-->
<div class="ownner">
    <h1>项目名称</h1>
    <p>您的好友<?=$owner->nickname?>, 邀请您加入项目</p>
    <img src="<?=Yii::$app->params['imgServer'].$owner->img->s_path?>" width="100" height="100">
</div>


<div>
    <?php $form = ActiveForm::begin(['action' => ['join/join'],'method'=>'post', 'id'=>'joinForm']); ?>
        <input type="hidden" name="manager_id" value="<?=$owner->user_id?>">
        <input type="hidden" name="project_id" value="<?=$proj->project_id?>">
        <input type="hidden" name="openid" value="<?=$member->openid?>">
        <input type="hidden" name="nickname" value="<?=$member->nickname?>">
        <input type="submit" name="submit-button" value="加入项目">
    <?php ActiveForm::end(); ?>
</div>