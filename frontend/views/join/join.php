<?php
/* @var $this yii\web\View */
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = '物语千寻';
?>

<div class="member">
    <div>
        <?php
            if($avatar == ''){
               echo '<img class="avatar">';
            }else{
                echo '<img src="'.$avatar.'" class="avatar">';
            }
        ?>
    </div>
    <p class="nickname"><?=$nickname?></p>
    <p class="headline">
        <?php
            if(isset($_GET['succ']) && $_GET['succ']==1){
                if(isset($_GET['is_manager']) && $_GET['is_manager']==1){
                    echo '您是该项目的创建人';
                }else{
                    echo '已成功加入';
                }
            }else{
                echo '成功加入';
            }
        ?>
    </p>
    <p class="project"><?=$project_name?></p>
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

