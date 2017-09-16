<?php

namespace backend\controllers;

use backend\models\PermissionForm;
use backend\models\RoleForm;
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
                    $permission = $old_permission;
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
        $model->description = $request->get('description');
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

    /**
     * 添加角色的功能
     */
    public function actionAddRole(){
        $model = new RoleForm();
        $model->scenario = RoleForm::SCENARIO_ADD;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){ //验证通过
                $auth = \Yii::$app->authManager;
                $role = $auth->createRole($model->name);//创建角色
                $role->description = $model->description;
                $auth->add($role); //保存角色
                //如果选择了权限,给用户分配权限
                $permissions = $model->permissions;
                if($permissions){
                    foreach ($permissions as $permissionName){  //checkbox传递的数据是数组需要遍历
                        $permission = $auth->getPermission($permissionName); //需要获取到权限对象而不是字符串
                        $auth->addChild($role,$permission); //给用户分配权限
                    }
                }
                //跳转到列表页
                \Yii::$app->session->setFlash('success','角色添加成功');
                return $this->redirect(['role-index']);
            }
        }
        return $this->render('role-add',['model'=>$model]);
    }

    //角色列表页
    public function actionRoleIndex(){
        //实例化权限组件
        $auth = \Yii::$app->authManager;
        //获取所有角色
        $role = $auth->getRoles();
        //分配数据到视图页
        return $this->render('roles-index',['roles'=>$role]);
    }
    //修改角色
    public function actionRoleEdit($name){
        $model = new RoleForm();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                //获取原角色
                $o_role = \Yii::$app->authManager->getRole($name);
                if(empty($o_role)){
                    throw new NotFoundHttpException('未找到该角色名称!');
                }else{
                    if($model->name == $name){ //如果角色名称未修改的情况下
                        $auth = \Yii::$app->authManager;
                        $role = $o_role;
                        $role->description = $model->description;
                        $auth->update($name,$role);
                        //如果选择了权限,给用户分配权限
                        $permissions = $model->permissions;
                        if(empty($permissions)){ //如果为空就清除角色的所有权限
                            foreach ($auth->getPermissionsByRole($name) as $permissionName){
                                $auth->removeChild($auth->getRole($name),$permissionName);
                            }
                        }else{ //如果不为空,先清除所有权限,再给角色添加权限
                            foreach ($auth->getPermissionsByRole($name) as $permissionName){
                                $auth->removeChild($auth->getRole($name),$permissionName);
                            }
                            foreach ($permissions as $permissionName){  //checkbox传递的数据是数组需要遍历
                                $permission = $auth->getPermission($permissionName); //需要获取到权限对象而不是字符串
                                $auth->addChild($role,$permission); //给用户分配权限
                            }
                        }
                        \Yii::$app->session->setFlash('success','修改成功');
                        return $this->redirect('role-index');
                    }
                }
            }
        }

        //数据回显
        $model->name = $name;
        $model->description = $request->get('description');
        $permissions = \Yii::$app->authManager->getPermissionsByRole($name);
        $model->permissions = array_keys($permissions);
        return $this->render('role-add',['model'=>$model]);
    }

    //删除角色
    public function actionRoleDel(){

        $name = \Yii::$app->request->post('name');
        $role = \Yii::$app->authManager->getRole($name);  //获取当前角色数据
        $permissions = \Yii::$app->authManager->getPermissions($name); //获取角色权限数据
        if($role){
            \Yii::$app->authManager->remove($role);
            if($permissions){  //判断是否存在权限
                foreach ($permissions as $permissionName){
                    \Yii::$app->authManager->removeChild($role,$permissionName);
                }
            }
            return 'success';
        }else{
            return 'fail';
        }
    }
}
