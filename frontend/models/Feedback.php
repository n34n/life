<?php
/**
 * Created by PhpStorm.
 * User: sam
 * Date: 2016/12/16
 * Time: 上午12:10
 */

namespace frontend\models;

use Yii;

class Feedback extends \yii\db\ActiveRecord
{
    public $username;
    public $email;
    public $des;

    public function rules()
    {
        return [
            //['des', 'required', 'message' => '意见反馈内容不能为空'],
            ['email', 'email', 'message' => '请输入有效的邮箱格式'],
        ];
    }
}