<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '意见反馈';
?>

<div class="info">
    <p>感谢你下载并使用了“物语千寻收纳助手”iOS版</p>
    <p>请将你使用App中的困惑或建议告诉我们</p>
</div>

<div class="feedbackForm">
    <?php $form = ActiveForm::begin(['action' => ['feedback/submit'],'method'=>'post', 'id'=>'submitForm']); ?>
    <div class="form_desc">
        <p>选择你反馈的意见类型</p>
        <input type="hidden" name="type" id="type" value="">
        <ul>
            <li><a href="#">功能建议</a></li>
            <li><a href="#">问题反馈</a></li>
            <li><a href="#">其他</a></li>
        </ul>
        <textarea rows="5" cols="40" placeholder="请描述你的建议"></textarea>
    </div>

    <div class="form_contact">
        <dl>
            <dt>姓名</dt>
            <dd><input type="text" name="username" value=""></dd>
        </dl>
        <dl>
            <dt>邮箱</dt>
            <dd><input type="text" name="email" value=""></dd>
        </dl>
    </div>

    <input type="submit" name="submit-button" class="button" value="提交">
    <?php ActiveForm::end(); ?>
</div>


<?=Html::jsFile('../js/jquery.min.js')?>
<script type="application/javascript">
//    function setType() {
//       alert($(this).html);
//        $(this).css("background","red");
////        $('.form_desc>ul>li').onclick(){
////            this.style.color = "##95C4FC";
////        }
//    }

    $(function() {
        // 点击 JAVA 变色
        $(".feedbackForm ul li").click(function(e) {
            $(".feedbackForm ul li").css("background", "white");
            $(".feedbackForm ul li a").css("color", "#ADADAD");

            $(this).css("background", "#95C4FC");
            $(this).children("a").css("color", "white");

            $("#type").val($(this).children("a").text());
            //alert();
        });
    });

</script>