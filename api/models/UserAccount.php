<?php
namespace api\models;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class UserAccount extends ActiveRecord implements IdentityInterface {
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'user_account';
    }

    /**
     * Finds an identity by the given ID.
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        // TODO: Implement findIdentity() method.
        return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findUser()
    {
        $account    = $_POST['account'];
        $device     = $_POST['device'];
        $type       = $_POST['type'];
        return static::findOne([
            'account' => $account,
            'device' => $device,
            'type' => $type,
        ]);
    }

    /**
     * Finds an identity by the given token.
     * @param mixed $token the token to be looked for
     * @param mixed $type the type of the token. The value of this parameter depends on the implementation.
     * For example, [[\yii\filters\auth\HttpBearerAuth]] will set this parameter to be `yii\filters\auth\HttpBearerAuth`.
     * @return IdentityInterface the identity object that matches the given token.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    /**
     * Returns an ID that can uniquely identify a user identity.
     * @return string|integer an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /**
     * Returns a key that can be used to check the validity of a given identity ID.
     *
     * The key should be unique for each individual user, and should be persistent
     * so that it can be used to check the validity of the user identity.
     *
     * The space of such keys should be big enough to defeat potential identity attacks.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @return string a key that is used to check the validity of a given identity ID.
     * @see validateAuthKey()
     */
    public function getAuthKey()
    {
        // TODO: Implement getAuthKey() method.
    }


    /**
     * Validates the given auth key.
     *
     * This is required if [[User::enableAutoLogin]] is enabled.
     * @param string $authKey the given auth key
     * @return boolean whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    //获取key
    public static function getSecret()
    {
        return Yii::$app->params['secret'];
    }


    //检查用户是否登录
    public static function checkAccess()
    {
        //$user_id    = $_POST['user_id'];
        //$account    = $_POST['account'];
        //$device     = $_POST['device'];
        //$type       = $_POST['type'];

        //判断是否接收到用户登录类型
        if(isset($_POST['type']) && !empty($_POST['type']))
        {
            //$model  = new UserAccount();
            $user   = self::find()->where($_POST)->one();

            //判断是否查到用户
            if(empty($user))
            {
                //用户不存在,返回登录界面
                $data['code']  = '600';
                $data['data']['isLogin']  = 'N';
            }else{
                //判断用户登录状态
                if($user->status == 10)
                {
                    //设置token令牌,用于后续页面用户验证使用
                    $user->access_token = self::setToken();
                    $user->save();

                    //状态为10,进入主界面
                    $data['code']  = '200';
                    $data['data']['isLogin']  = 'Y';
                    $data['data']['access_token']=$user->access_token;

                }else{
                    //状态为0,返回登录界面
                    $data['code']  = '600';
                    $data['data']['isLogin']  = 'N';
                }
            }
        }else{
            //无登录类型,返回登录界面
            $data['code']  = '600';
            $data['data']  = '';
        }

        return $data;
    }

    //生成Token
    public static function setToken()
    {
        $secret = self::getSecret();
        $str = '';
        ksort($_POST);
        foreach ($_POST as $value){
            $str .= $value;
        }
        $str .= time().$secret;
        return md5($str);
    }


    //检查待创建用户数据是否有效
    public static function checkUserData()
    {
        //$user_id    = $_POST['user_id'];
        //$account    = $_POST['account'];
        //$device     = $_POST['device'];
        //$type       = $_POST['type'];
        if(isset($_POST['account']) && isset($_POST['device']) && isset($_POST['type'])
            && isset($_POST['timestamp']) && isset($_POST['sign']))
        {
            $sign = self::checkSign();
            if($sign == 1)
            {
                $data['code']  = 200;
                $data['data']  = '';
                return $data;
            }else{
                $data['code']  = 501;
                $data['data']  = '';
                return $data;
            }

        }else{
            $data['code']  = 401;
            $data['data']  = '';
            return $data;
        }
    }

    //检查签名
    protected static function checkSign()
    {
        $secret = self::getSecret();
        $str  = '';
        $sign = $_POST['sign'];
        unset($_POST['sign']);
        ksort($_POST);
        foreach ($_POST as $value){
            $str .= $value;
        }

        $_sign = md5($str.$secret);
        if($sign == $_sign){
            return 1;
        }else{
            return 0;
        }
    }

}
