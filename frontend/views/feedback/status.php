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
    <input type="button" class="close" value="关闭" onclick="WeixinJSBridge.call('closeWindow');" />
</div>


