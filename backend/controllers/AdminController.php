<?php

namespace backend\controllers;

use backend\models\Admin;
use common\models\LoginForm;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use yii\web\User;

class AdminController extends \yii\web\Controller
{
    public function actionIndex(){
        if(\Yii::$app->user->isGuest){
            \Yii::$app->session->setFlash('success','未登录账号,请重新登录');
            return $this->redirect(['admin/login']);
        }else{
            //准备分页数据
            $totalCount = Admin::find()->count();
            //实例化分页组件
            $pageTool = new Pagination([
                'totalCount'=>$totalCount,
                'defaultPageSize'=>5
            ]);
            $model = Admin::find()->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();
            return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
        }
    }

    //添加用户
    public function actionAdd(){
        $model = new Admin();
        $model->scenario = Admin::SCENARIO_ADD;   //方法和场景没有直接关系, 需要自己指定场景

        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                    $model->save();
                    \Yii::$app->session->setFlash('success','添加成功!');
                    return $this->redirect(['admin/index']);
            }
        }
        $model->status = 1;
        return $this->render('add',['model'=>$model]);
    }

    //修改用户
    //添加用户
    public function actionEdit($id){
        $model = Admin::findOne(['id'=>$id]);
        //拦截错误
        if(empty($model)){
            throw new NotFoundHttpException('用户不存在!');  //抛出异常
        }
        $request = \Yii::$app->request;
        if($request->isPost){
            if($request->post('Admin')['username'] != $model->username){
                $model->addError('username','用户名不允许更改');
            }else{
                $model->load($request->post());
                if($id != \Yii::$app->user->identity->id){
                    throw new NotFoundHttpException('当前用户只允许更改自己的密码!');  //抛出异常
                }else{
                    if($model->validate()){
                        $model->save();
                        \Yii::$app->session->setFlash('success','修改成功!');
                        return $this->redirect(['admin/index']);
                    }
                }
            }
        }
        return $this->render('add',['model'=>$model]);
    }

    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $user = Admin::findOne(['id'=>$id]);
        if($user){
            $user->delete();
            return 'success';
        }else{
            return 'fail';
        }
    }

    //登录页面
    public function actionLogin(){
        //实例化表单模型
        $model = new \backend\models\LoginForm();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){ //如果验证功通过
                if($model->login()){
                    \Yii::$app->session->setFlash('success','登录成功');
                    return $this->redirect(['admin/index']);
                }
            }
        }
        return $this->render('login',['model'=>$model]);
    }

    //用户注销
    public function actionLogout(){
        \Yii::$app->user->logout(); //调用user组件的logout方法
        return $this->redirect(['admin/login']);
    }

    //重置密码
    public function actionResetpassword($id){
        $model = Admin::findOne(['id'=>$id]);
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->password = $model->n_password;
                $model->save();
                \Yii::$app->session->setFlash('success','密码修改成功!');
                return $this->redirect(['admin/index']);
            }
        }


        return $this->render('reset',['model'=>$model]);
    }


    //自定义验证码
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'foreColor' => 0xFF00FF,
                'minLength' => '4',
                'maxLength' => '4',
                'height' => '40',
                'width' => '220',
                'padding' => -1,
            ],
        ];
    }


}
