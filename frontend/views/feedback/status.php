<?php
/* @var $this yii\web\View */

$this->title = '意见反馈';
?>


<div class="feedback_status">
    <?=$img?>
</div>

<div class="feedback_message">
    <h2><?=$h2?></h2>
    <p><?=$p?></p>
    <a href="WeixinJSBridge.call('closeWindow');">关闭</a>
</div>


