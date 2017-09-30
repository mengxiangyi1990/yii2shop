<?php
namespace frontend\controllers;

use frontend\models\Address;
use frontend\models\LoginForm;
use frontend\models\Member;
use yii\web\Controller;
use yii\web\Response;

class ApiController extends Controller{
    public $enableCsrfValidation = false;

    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //用户注册
    public function actionUserRegister(){
        $result = [
            'error'=> true,
            'msg'=> '',
            'data'=> []
        ];
        if(\Yii::$app->request->isPost){
            $member = new Member();
            $member->scenario = Member::SCENARIO_ADD;
            $member->load(\Yii::$app->request->post(),'');
            if($member->validate()){
                $member->save(false);
                $result['data']['id'] = $member->id;
                $result['error'] = false;
            }else{
                $result['msg'] = $member->getErrors();
            }
        }else{
            $result['msg'] = '请求方式错误';
        }
        return $result;
    }

    //用户登录
    public function actionUserLogin(){
        $result = [
            'error'=> true,
            'msg'=> '',
            'data'=> []
        ];
        $member = new LoginForm();
        if(\Yii::$app->request->isPost){
            $member->load(\Yii::$app->request->post(),'');
            if($member->validate()){
                if($member->login()){
                    $result['msg'] = '登录成功';
                }
            }else{
                $result['msg'] = $member->getErrors();
            }
        }else{
            $result['msg'] = '请求方式错误';
        }
        return $result;
    }
    //修改密码
    public function actionModifyPassword(){
        $result = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $result['msg'] = '用户未登录';
            return $this->redirect(['member/login']);
        }else{
            if(\Yii::$app->request->isPost){
                $member = Member::findOne(['id'=>\Yii::$app->user->id]);
                $member->load(\Yii::$app->request->post(),'');
               if($member->validate()){
                       $member->save();
                       $result['error'] = false;
                       $result['data']['member_id'] = \Yii::$app->user->id;
                       $result['data']['username'] = $member->username;
                       $result['msg'] = '修改成功';
               }else{
                   $result['msg'] = $member->getErrors();
               }
            }else{
                $result['msg'] = '请求方式错误';
            }
        }
        return $result;
    }


    //获取当前登录的用户信息
    public function actionGetUserInfo(){
        $result = [
            'error' => true,
            'msg' => '',
            'data' =>[]
        ];

        if(\Yii::$app->user->isGuest){
            $result['msg'] = '未登录用户';
        }else{
            $member_id = \Yii::$app->user->id;
            $member = Member::find()->where(['id'=>$member_id])->one();
            $result['data']['username'] = $member->username;
            $result['error'] = false;
            $result['data']['email'] = $member->email;
            $result['data']['tel'] = $member->tel;
        }

        return $result;
    }

    //添加收货地址
    public function actionAddAddress(){
        $result  = [
            'error'=>true,
            'msg'=>'',
            'data' => []
        ];
        if(\Yii::$app->user->isGuest){
            $result['error'] = '未登录用户';
        }else{
            $address = new Address();
            $address->scenario = Address::SCENARIO_ADD;
            if(\Yii::$app->request->isPost){
                $address->load(\Yii::$app->request->post(),'');
                if($address->validate()){
                    $address->member_id = \Yii::$app->user->id;
                    $address->save();
                    $result['error'] = false;
                    $result['data']['name'] = $address->name;
                    $result['data']['address'] = $address->address;
                    $result['data']['tel'] = $address->tel;
                    $result['data']['member_id'] = $address->member_id;
                    $result['data']['id'] = $address->id;
                }else{
                    $result['msg'] = $address->getErrors();
                }
            }else{
                $result['msg'] = '请求方式错误';
            }
        }
        return $result;
    }

    //修改收货地址
    public function actionEditAddress(){
        $result = [
            'error'=> true,
            'msg'=> '',
            'data'=> []
        ];
        if(\Yii::$app->user->isGuest){
            $result['msg'] = '用户未登录';
        }else{
            if(\Yii::$app->request->isPost){
                $address_id = \Yii::$app->request->post('id');
                $member_id = \Yii::$app->user->id;
                $address = Address::findOne(['id'=>$address_id,'member_id'=>$member_id]);
                if(empty($address)){
                    echo '错误的请求id';exit;
                }
                $address->load(\Yii::$app->request->post());

                if($address->validate()){

                    $address->save();
                        var_dump($address);exit;
                    $result['error'] = false;
                    $result['data'] = $address;
                }else{
                    $result['msg'] = $address->getErrors();
                }

            }else{
                $result['msg'] = '请求方式错误';
            }

        }

        return $result;
    }




    //获取收货地址
    public function actionGetAddress(){
        $result = [
            'error' => true,
            'msg' => '',
            'data' => []
        ];

        if(\Yii::$app->user->isGuest){
            $result['error'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isPost){
                $address = Address::findAll(['member_id'=>\Yii::$app->user->id]) ;
                $result['error'] = false;
                $key = 0;
                foreach ($address as $val){
                    $result['data'][++$key] = $val;
                }
            }else{
                $result['msg'] = '请求方式错误';
            }

        }

        return $result;

    }

    //删除地址
    public function actionDelAddress(){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
            return $this->redirect(['member/index']);
        }else{
            if(\Yii::$app->request->isGet){
                $address_id = \Yii::$app->request->get('id');
                $address = Address::findOne(['id'=>$address_id]);
                if($address){
                    $request['data'] = $address;
                    $address->delete();
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;
    }




}