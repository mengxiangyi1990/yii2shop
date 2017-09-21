<?php

namespace backend\controllers;

use backend\filters\Rbacfilters;
use backend\models\ArticleCategory;
use yii\data\Pagination;

class ArticleCategoryController extends \yii\web\Controller
{
    public function actionIndex(){
     /**
     * 准备分页数据
     */
        $totalCount = ArticleCategory::find()->where(['>','status','-1'])->count();  //查询brand表中数据总条数
        $pageTool = new Pagination([
            'totalCount'=>$totalCount,
            'defaultPageSize'=>4
        ]); //实例化一个分页组件对象
        //查询分页后的总数据
        $model = ArticleCategory::find()->where(['>','status','-1'])->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();
        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
    }

    //添加分类
    public function actionAdd(){
        //实例化 ArticleCategory 模型对象
        $model = new ArticleCategory();
        $request = \Yii::$app->request;
        if($request->isPost){ //如果是POST提交方式
            $model->load($request->post()); //绑定数据
            if($model->validate()){  // 如果验证成功
                $model->save(); //保存数据到数据表
                //显示提示信息
                \Yii::$app->session->setFlash('success','添加成功');
                //跳转到列表页
                return $this->redirect(['article-category/index']);
            }
        }
        $model->status = 1; //默认状态为显示
        return $this->render('add',['model'=>$model]);
    }
    public function actionEdit($id){
        $model = ArticleCategory::find()->where(['id'=>$id])->one();
        $request = \Yii::$app->request; // 实例化request组件
        if($request->isPost){ // 如果是POST提交方式
            $model->load($request->post());
            if($model->rules()){  //如果验证成功
                $model->save(); // 更新数据表中数据
                return $this->redirect(['article-category/index']);
            }
        }


        return $this->render('add',['model'=>$model]);
    }



    //删除一个分类
    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $model = ArticleCategory::find()->where(['id'=>$id])->one();
        if($model){
            $model->status = -1;
            $model->save();
            return 'success';
        }
        //跳转到列表页
        return 'fail';
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
