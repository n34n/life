<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
$this->title = '物语千寻';
?>



<!--这里是邀请人及其共享的项目信息-->
<div>
    <h1><?=$proj->name?></h1>
    <p><?php print_r($member);?></p>
    <p>成功加入项目,请点击下方App入口,下载或直接进入App进行协同操作</p>
    <p><?php print_r($info);?></p>
</div>

