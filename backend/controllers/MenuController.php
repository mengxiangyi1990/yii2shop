<?php

namespace backend\controllers;

use backend\filters\Rbacfilters;
use backend\models\Menu;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

class MenuController extends \yii\web\Controller
{
    //添加菜单
    public function actionAddMenu(){
        $model = new Menu();
        $model->scenario = Menu::SCENARIO_ADD;
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            if($model->validate()){
                $model->save();
                \Yii::$app->session->setFlash('success','添加成功');
                return $this->redirect(['menu/index']);
            }
        }
        return $this->render('add',['model'=>$model]);
    }
    //菜单列表
    public function actionIndex()
    {
        /**
         * 准备分页数据
         */
        $totalCount = Menu::find()->count();

        $pageTool = new Pagination([
            'totalCount'=>$totalCount,
            'defaultPageSize'=>5
        ]); //实例化一个分页组件对象

        $model = Menu::find()->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();
        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
    }
    //删除菜单
    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $menu = Menu::findOne(['id'=>$id]);
        if($menu){
            $menu->delete();
            return 'success';
        }else{
            return 'fail';
        }
    }

    //修改菜单
    public function actionEdit($id){
        $model = Menu::findOne(['id'=>$id]);
        //如果未找到id对应的菜单就抛出异常
        if(empty(Menu::findOne(['id'=>$id]))){
            throw new NotFoundHttpException('未找到的菜单');
        }
        $request = \Yii::$app->request;
        if ($request->isPost){

            $model->load($request->post());
            if($model->validate()){ //如果通过验证,修改菜单信息
                if(empty(Menu::findOne(['name'=>$model->name]))){
                        $model->save();
                        \Yii::$app->session->setFlash('success','修改成功');
                        return $this->redirect(['menu/index']);
                }else{
                    $o_menu = Menu::findOne(['id'=>$id]);
                    if($model->name == $o_menu->name){
                            $model->save();
                            \Yii::$app->session->setFlash('success','修改成功');
                            return $this->redirect(['menu/index']);
                    }else{
                        $model->addError('name','已经存在的菜单名称');
                    }
                }
            }
        }
        return $this->render('add',['model'=>$model]);
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
