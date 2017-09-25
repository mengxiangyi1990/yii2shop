<?php

namespace backend\models;

use backend\filters\Rbacfilters;
use Yii;
use yii\rbac\Permission;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "admin".
 *
 * @property integer $id
 * @property string $username
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $last_login_time
 * @property string $last_login_ip
 */
class Admin extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * @inheritdoc
     */
    public $password;
    public $rolesName;
    //常量定义场景
    const SCENARIO_ADD = 'add';


    public static function tableName()
    {
        return 'admin';
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



    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username','required','message'=>'用户名不能为空'],
            ['password','required','on'=>self::SCENARIO_ADD,'message'=>'密码不能为空'], //指定规则在添加用户的场景下才起作用
            ['password','string'],
            ['rolesName','safe'],
            ['status','required'],
            ['username','unique','on'=>self::SCENARIO_ADD,'message'=>'该用户名已经存在'],
            ['email','unique','on'=>self::SCENARIO_ADD,'message'=>'该邮箱已经存在'],
            ['email','required','message'=>'邮箱不能为空'],
            [['email'],'match','pattern'=>'/^[a-zA-Z0-9_-]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/','message'=>'只允许英文字母、数字、下划线、英文句号、以及中划线组成']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'password' => '密码',
            'email' => 'Email',
            'status' => '状态',
            'rolesName' => '角色',
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
        return $this->auth_key;
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

    /**
     * 静态方法返回权限表的所有数据
     */
    public static function getRoles(){
        $roles = \Yii::$app->authManager->getRoles() ;
        $items = [];
        foreach ($roles as $role){
            $items[$role->description] = $role->description;
        }
        return $items;
    }
    //获取用户的菜单
    public function getMenus(){
        //获取所有一级菜单
        $menuItems = [];
        $menus = Menu::find()->where(['parent_id'=>0])->orderBy('id desc')->all();
        foreach ($menus as $menu) {

                //获取所有一级菜单的子菜单
                $children = Menu::find()->where(['parent_id' => $menu->id])->all();
                $items = [];
                foreach ($children as $child) {
                    //判断当前用户是否有该路由的权限,只显示有权限的菜单
                    if (Yii::$app->user->can($child->url)) {
                        $items[] = ['label' => $child->name, 'url' => [$child->url]];
                    }
                }
                if($items != null){
                    $menuItems[] = ['label' => $menu->name, 'items' => $items];
                }

            }
            return $menuItems;
    }



}
