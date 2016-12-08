<?php

namespace frontend\models;

use Yii;
use frontend\models\UserAccount;
use frontend\models\RelUserProject;
use frontend\models\Image;

/**
 * This is the model class for table "user".
 *
 * @property integer $user_id
 * @property string $nickname
 * @property integer $nickname_updated
 * @property integer $avatar_updated
 * @property string $tag_data
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nickname_updated', 'avatar_updated', 'created_at', 'updated_at'], 'integer'],
            [['tag_data'], 'string'],
            [['nickname'], 'string', 'max' => 45],
        ];
    }

    public function fields()
    {
        $fields['user_id']  = 'user_id';// = parent::fields(); // TODO: Change the autogenerated stub
        $fields['nickname'] = 'nickname';
        $fields['img']      = 'img';

        return $fields;
    }

    public static function join()
    {
        //检查参数
        if(!isset($_POST['manager_id'],$_POST['project_id'],$_POST['openid'],$_POST['nickname'])){
            return 20000;
        }

        //整理参数
        $manager_id = $_POST['manager_id'];
        $project_id = $_POST['project_id'];
        $openid     = $_POST['openid'];
        $device     = 'browser';
        $type       = 'weixin';
        $nickname   = $_POST['nickname'];
        $timestamp  = time();
        $domain     = Yii::$app->params['apiServer'];


        //判断项目是否存在
        $projinfo = Project::findOne($project_id);
        if(empty($projinfo)){
            return 50001;
        }

        //判断是否是新用户
        $ua = UserAccount::findOne(['account'=>$openid]);
        if(empty($ua)){
            //制作签名
            $sign = self::makeSign($openid,$device,$type,$nickname,$timestamp);

            //创建用户
            $user = self::curlCreateUser($domain,$openid,$device,$type,$nickname,$timestamp,$sign);

            if(!isset($user->data->user->access_token)){
                return 400;
            }else{
                $access_token = $user->data->user->access_token;
            }
        }else{
            $access_token = $ua->access_token;
        }

        //关联项目
        $projinfo = self::curlRelProject($domain,$access_token,$project_id,$manager_id);

        return $projinfo;
    }


    //制作签名
    private static function makeSign($openid,$device,$type,$nickname,$timestamp)
    {
        $data['account']    = $openid;
        $data['device']     = $device;
        $data['type']       = $type;
        $data['created_by'] = $nickname;
        $data['timestamp']  = $timestamp;

        //签名制作
        $secret = Yii::$app->params['secret'];
        $str  = '';
        ksort($data);
        foreach ($data as $value){$str .= $value;}
        $sign = md5($str.$secret);
        return $sign;
    }

    //通过接口创建用户
    private static function curlCreateUser($domain,$account,$device,$type,$created_by,$timestamp,$sign)
    {
        //创建用户
        $curl = "curl -X POST --header 'Content-Type: application/x-www-form-urlencoded' --header 'Accept: application/json' ";
        $curl.= "-d 'account=".$account."&device=".$device."&type=".$type."&created_by=".$created_by."&timestamp=".$timestamp."&sign=".$sign."' ";
        $curl.= "'".$domain."v1/token/get-token'";
        $json = @exec($curl);

        //返回创建成功的用户信息
        $data = json_decode($json);

        return $data;
    }

    //通过接口关联项目
    private static function curlRelProject($domain,$access_token,$project_id,$manager_id)
    {
        //关联项目
        $curl = "curl -X POST --header 'Content-Type: application/x-www-form-urlencoded' --header 'Accept: application/json' ";
        $curl.= "-d 'project_id=".$project_id."&manager_id=".$manager_id."' ";
        $curl.= "'".$domain."v1/project/member-join?access-token=".$access_token."'";
        $json = @exec($curl);

        //返回关联成功的项目信息
        $data = json_decode($json);

        return $data;
    }

    //获取头像
    public function getImg()
    {
        return $this->hasOne(Image::className(), ['rel_id' => 'user_id'])->where(['model'=>'avatar'])->select('s_path');
    }
}
