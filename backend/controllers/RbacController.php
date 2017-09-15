<?php

namespace backend\controllers;

use backend\models\PermissionForm;
use yii\web\NotFoundHttpException;

class RbacController extends \yii\web\Controller
{
    /**
     * Rbac的所有功能都是通过调用组件的方法来实现的
     */

    //添加权限
    public function actionPermissionAdd(){
        //实例化表单模型
        $model = new PermissionForm();
        $model->scenario = PermissionForm::SCENARIO_ADD;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                //实例化权限组件
                $auth = \Yii::$app->authManager;
                //创建权限
                $permission = $auth->createPermission($model->name);
                $permission->description = $model->description;
                //保存到数据表
                $auth->add($permission);
                //提示信息
                \Yii::$app->session->setFlash('success','添加成功');

                return $this->redirect(['permission-index']);
            }
        }
        return $this->render('permission',['model'=>$model]);
    }

    //权限列表
    public function actionPermissionIndex(){
        //实例化权限组件
        $auth = \Yii::$app->authManager;
        //获取所有的权限
        $permissions = $auth->getPermissions();
        //调用视图
       // return $this->render('permission-index',['permissions'=>$permissions]);
        return $this->render('permission-index',['permissions'=>$permissions]);
    }

    //修改权限
    public function actionPermissionEdit($name){
        //实例化表单模型
        $model = new PermissionForm();
        $model->scenario = PermissionForm::SCENARIO_Edit;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $old_permission = \Yii::$app->authManager->getPermission($name);
                //判断权限名是否存在
                if(empty($old_permission)){
                    throw new NotFoundHttpException('不存在该权限');
                }
                //判断权限名是否存在并且是否和更改前相同
                if($model->name == $name){
                    //实例化权限组件
                    $auth = \Yii::$app->authManager;
                    //创建权限
                    $permission = \Yii::$app->authManager->getPermission($model->name);
                    $permission->description = $model->description;
                    //保存到数据表
                    $auth->update($name,$permission);
                    //提示信息
                    \Yii::$app->session->setFlash('success','无任何修改');
                    return $this->redirect(['permission-index']);
                }else{
                    if(\Yii::$app->authManager->getPermission($model->name)){
                        $model->addError('name','已经存在的权限');
                    }else{
                        //实例化权限组件
                        $auth = \Yii::$app->authManager;
                        //创建权限
                        $permission = $auth->createPermission($model->name);
                        $permission->description = $model->description;
                        //保存到数据表
                        $auth->update($name,$permission);
                        //提示信息
                        \Yii::$app->session->setFlash('success','修改成功');
                        return $this->redirect(['permission-index']);
                    }
                }
            }
        }

        $model->name = $name;
        $model->description = \Yii::$app->request->get('description');
        return $this->render('permission',['model'=>$model]);
    }

    //删除权限
    public function actionPermissionDel(){

        $name = \Yii::$app->request->post('name');

        $permission = \Yii::$app->authManager->getPermission($name);
        if($permission){
            //实例化authManager组件
            \Yii::$app->authManager->remove($permission);
            return 'success';
        }else{
            return 'fail';
        }
    }

}
