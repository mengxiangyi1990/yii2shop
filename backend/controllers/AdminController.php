<?php

namespace backend\controllers;

use backend\filters\Rbacfilters;
use backend\models\Admin;
use backend\models\PasswordForm;
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
    public function actionAdd()
    {
        if (\Yii::$app->user->isGuest) {
            \Yii::$app->session->setFlash('success', '未登录账号,请重新登录');
            return $this->redirect(['admin/login']);
        } else {
            $model = new Admin();
            $model->scenario = Admin::SCENARIO_ADD;   //方法和场景没有直接关系, 需要自己指定场景

            $request = \Yii::$app->request;
            if ($request->isPost) {
                $model->load($request->post());
                if ($model->validate()) {
                    $model->save();
                    //给用户分配角色
                    $auth = \Yii::$app->authManager;
                    if($model->rolesName != null){
                        foreach ($model->rolesName as $role_name){
                            $role_name = $auth->getRole($role_name);
                            $auth->assign($role_name,$model->getId());
                        }
                    }
                    \Yii::$app->session->setFlash('success', '添加成功!');
                    return $this->redirect(['admin/index']);
                }
            }
            $model->status = 1;
            return $this->render('add', ['model' => $model]);
        }
    }

    //修改用户
    //添加用户
    public function actionEdit($id)
    {
        if (\Yii::$app->user->isGuest) {
            \Yii::$app->session->setFlash('success', '未登录账号,请重新登录');
            return $this->redirect(['admin/login']);
        } else {
            $model = Admin::findOne(['id' => $id]);
            //拦截错误
            if (empty($model)) {
                throw new NotFoundHttpException('用户不存在!');  //抛出异常
            }
            $request = \Yii::$app->request;
            if ($request->isPost) {
                if ($request->post('Admin')['username'] != $model->username) {
                    $model->addError('username', '用户名不允许更改');
                } else {
                    $model->load($request->post());
                    if ($id != \Yii::$app->user->identity->id) {
                        throw new NotFoundHttpException('当前用户只允许更改自己的密码!');  //抛出异常
                    } else {
                        if ($model->validate()) {
                            $model->save();
                            $auth = \Yii::$app->authManager;
                            //首先判断用户是否选择了角色
                            if(empty($model->rolesName)){  //如果没有选择角色的话  就清空所有角色
                                $auth->revokeAll($id);
                            }else{
                                $auth->revokeAll($id);
                                foreach ($model->rolesName as $rolename){
                                    $role_name = $auth->getRole($rolename);
                                    $auth->assign($role_name,$model->getId());
                                }
                            }
                            \Yii::$app->session->setFlash('success', '修改成功!');
                            return $this->redirect(['admin/index']);
                        }
                    }
                }
            }
            //将用户拥有的角色查询出来回显到修改页面
            $auth = \Yii::$app->authManager;
            $roles = $auth->getRolesByUser($id); //通过getRolesByUser()方法查找出用户的所有角色
            if($roles != null){
                $model->rolesName = [];
                foreach ($roles as $role){
                    $model->rolesName[] = $role->name;
                }
            }
            return $this->render('add', ['model' => $model]);
        }
    }
    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $user = Admin::findOne(['id'=>$id]);
        if($user){
            //$user->delete();
            $auth = \Yii::$app->authManager; //实例化authManager组件
            if($auth->getRolesByUser($id)){
                    $auth->revokeAll($id);
            }

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

        \Yii::$app->session->setFlash('success','退出成功!');
        return $this->redirect(['admin/login']);
    }

    //重置密码
    public function actionResetpassword()
    {
        if (\Yii::$app->user->isGuest) {
            \Yii::$app->session->setFlash('success', '未登录账号,请重新登录');
            return $this->redirect(['admin/login']);
        } else {
            $model = new PasswordForm();
            $request = \Yii::$app->request;
            if($request->isPost){
                $model->load($request->post());
                if($model->validate()){
                    $admin = \Yii::$app->user->identity;
                    $admin->password = $model->n_password;
                    $admin->save();
                    \Yii::$app->session->setFlash('success','密码被修改,请重新登录!如非本人更改,请联系管理员!');
                    return $this->redirect(['admin/logout']);
                }
            }
            return $this->render('reset', ['model' => $model]);
        }
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

    //过滤的行为
    public function behaviors()
    {
        return [
            'rbac'=>[
                'class'=>Rbacfilters::className(),
                'except'=>['logout','login','captcha','error'],
            ]
        ];
    }

}
