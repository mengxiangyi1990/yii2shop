<?php

namespace backend\models;

use yii\base\Model;

class LoginForm extends Model{
    public $username;
    public $password;
    public $code;
    public $remember;
    public function rules()
    {
        return [
          ['username','required','message'=>'用户名不能为空'],
          ['password','required','message'=>'密码不能为空'],
          ['remember','integer'],
          ['code','captcha','captchaAction' => 'admin/captcha','message'=>'验证码错误'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'username'=>'用户名',
            'password'=>'密码',
            'code'=>'验证码',
            'rename'=>'Remember me'
        ];
    }
    public function login(){
        $user = Admin::findOne(['username'=>$this->username]);
        if($user){

            //\Yii::$app->security->validatePassword($this->password,$user->password_hash); 安全组件验证密码

            //账号存在继续验证密码
            if(\Yii::$app->security->validatePassword($this->password,$user->password_hash)){
                //验证通过 可以通过
                $user->last_login_time = time();
                $user->last_login_ip = \Yii::$app->request->getUserIP();
                $user->save();
                //判断用户是否选择自动登录功能
                if($this->remember == 1){
                    return \Yii::$app->user->login($user,3600*24);
                }

                return \Yii::$app->user->login($user);
            }else{
                //密码不正确
                $this->addError('password','密码错误');
            }
        }else{
            //账号不存在
            $this->addError('username','用户名不存在');
        }
        return false;
    }




}



