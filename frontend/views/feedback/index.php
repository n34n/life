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
        <input type="hidden" name="type" id="type" value="功能建议">
        <ul>
            <li><a href="#">功能建议</a></li>
            <li><a href="#">问题反馈</a></li>
            <li><a href="#">其他</a></li>
        </ul>
        <?= $form->field($model, 'des')->textArea(['rows' => '5', 'placeholder'=>'请描述你的建议'])->label(false) ?>
    </div>

    <div class="form_contact">
        <dl>
            <dt>姓名</dt>
            <dd><?= $form->field($model, 'username')->label(false) ?></dd>
        </dl>
        <dl>
            <dt>邮箱</dt>
            <dd><?= $form->field($model, 'email')->label(false) ?></dd>
        </dl>
    </div>

    <input type="submit" name="submit-button" class="button" value="提交" disabled="disabled">
    <?php ActiveForm::end(); ?>
</div>


<?=Html::jsFile('../js/jquery.min.js')?>
<script type="application/javascript">

    $(function() {
        $(".feedbackForm ul li:first").css("background", "#95C4FC");
        $(".feedbackForm ul li:first").children("a").css("color", "white");
        //$(".feedbackForm .button").css("background", "#ADADAD");
        
        // 点击 JAVA 变色
        $(".feedbackForm ul li").click(function(e) {
            $(".feedbackForm ul li").css("background", "white");
            $(".feedbackForm ul li a").css("color", "#ADADAD");

            $(this).css("background", "#95C4FC");
            $(this).children("a").css("color", "white");

            $("#type").val($(this).children("a").text());
            //alert();
        });

        $("textarea").change(function(e) {
            if($(this).val() == ""){
                $(".feedbackForm .button").css("background-position", "0 0");
                $(".feedbackForm .button").attr("disabled","disabled");
            }else{
                $(".feedbackForm .button").css("background-position", "0 -40px");
                $(".feedbackForm .button").removeAttr("disabled");
            }
        });


    });

</script>