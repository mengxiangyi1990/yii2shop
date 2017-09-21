<?php

namespace frontend\models;

use backend\models\Brand;
use backend\models\Goods;
use backend\models\GoodsGallery;
use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "member".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $email
 * @property string $tel
 * @property integer $last_login_time
 * @property integer $last_login_ip
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Member extends \yii\db\ActiveRecord implements IdentityInterface
{
    public $password;
    public $confirm_password;
    const SCENARIO_ADD = "add";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'member';
    }

    /**
     * @inheritdoc
     */

    public function rules()
    {
        return [
            ['username','required','message'=>'用户名不能为空'],
            ['password','required','on'=>self::SCENARIO_ADD,'message'=>'密码不能为空'], //指定规则在添加用户的场景下才起作用
            ['password','string'],
            ['confirm_password','required','on'=>self::SCENARIO_ADD,'message'=>'确认密码不能为空'],
            ['confirm_password','required','message'=>'确认密码不能为空'],
            ['rolesName','safe'],
            ['tel','match','pattern'=>'/^[0-9]{11,11}$/','message'=>'请正确输入11位电话号码'],
            ['username','unique','on'=>self::SCENARIO_ADD,'message'=>'该用户名已经存在'],
            //['email','unique','on'=>self::SCENARIO_ADD,'message'=>'该邮箱已经存在'],
            //['email','required','on'=>self::SCENARIO_ADD,'message'=>'邮箱不能为空'],
            [['email'],'match','pattern'=>'/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/','message'=>'只允许英文字母、数字、下划线、英文句号、以及中划线组成']
        ];
    }

    //保存之前处理数据的方法
    public function beforeSave($insert)
    {
        if($insert){
            //添加
            //密码加密为hash形式密文
            $this->password_hash = \Yii::$app->security->generatePasswordHash($this->password);
            $this->created_at = time();
            $this->auth_key = Yii::$app->security->generateRandomString(); //生成随机的 auth_key
        }else{
            //修改
            $this->updated_at = time();
            //密码加密为hash形式密文
            if($this->password){
                $this->password_hash = \Yii::$app->security->generatePasswordHash($this->password);
                $this->auth_key = Yii::$app->security->generateRandomString(); //生成随机的 auth_key
            }
        }


        return parent::beforeSave($insert); // 必须返回父类方法
    }

    //自定义手机验证码规则
    public function validateSms(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        $code = $redis->get('code_'.$this->phone);
        if($code == null || $code != $this->sms){
            $this->addError('sms','手机验证码错误');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'auth_key' => 'Auth Key',
            'password_hash' => '密码(密文)',
            'email' => '邮箱',
            'tel' => '电话',
            'last_login_time' => '最后登录时间',
            'last_login_ip' => '最后登录ip',
            'status' => '状态(1正常，0删除)',
            'created_at' => '添加时间',
            'updated_at' => '修改时间',
        ];
    }

    /**
     * Finds an identity by the given ID.
     * @param string|int $id the ID to be looked for
     * @return IdentityInterface the identity object that matches the given ID.
     * Null should be returned if such an identity cannot be found
     * or the identity is not in an active state (disabled, deleted, etc.)
     */
    public static function findIdentity($id)
    {
        return self::findOne(['id'=>$id]);
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
     * @return string|int an ID that uniquely identifies a user identity.
     */
    public function getId()
    {
        return $this->id;
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
     * @return bool whether the given auth key is valid.
     * @see getAuthKey()
     */
    public function validateAuthKey($authKey)
    {
        return $authKey == $this->auth_key;
    }



    //查看商品详情
    //public function get


}
