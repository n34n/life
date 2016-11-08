<?php
namespace api\models;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use api\models\UserAccount;

class User extends ActiveRecord implements IdentityInterface {
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    public static function getUserInfo($token)
    {
        $query = self::find()->from(['u'=>'user'])
            ->leftJoin(['a'=>'user_account'],'u.user_id = a.user_id')
            ->where(['a.access_token'=>$token])
            ->one();
        return $query;
    }

    public function savedata()
    {
        $this->created_at = time();
        $this->tag_data   = trim(Yii::$app->params['defaultTags']);
        $this->save();
        $user_id = $this->getId();
        return $user_id;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $userinfo = $_POST['userinfo'];
        return $userinfo;
        //return static::findOne(['user_id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return UserAccount::findOne(['access_token' => $token]);
    }

    //这个就是我们进行yii\filters\auth\QueryParamAuth调用认证的函数，下面会说到。
    public function loginByAccessToken($token, $type) {
        //查询数据库中有没有存在这个token
        return static::findIdentityByAccessToken($token, $type);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
}
