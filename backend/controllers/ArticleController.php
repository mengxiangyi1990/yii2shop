<?php

namespace backend\controllers;

use backend\models\Article;
use backend\models\ArticleCategory;
use backend\models\ArticleDetail;
use yii\data\Pagination;
use yii\helpers\Html;

class ArticleController extends \yii\web\Controller
{
    //文章列表页
    public function actionIndex(){
        /**
         * 准备分页数据
         */
        //总条数
        $totalCount = Article::find()->where(['>','status','-1'])->count();
        //实例化 分页组件对象
        $pageTool = new Pagination([
            'totalCount'=>$totalCount,
            'defaultPageSize'=>2
        ]);
        $model = Article::find()->where(['>','status','-1'])->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();

        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
    }
    //添加文章
    public function actionAdd(){
        $model = new Article();  //实例化article模型对象
        $category = ArticleCategory::find()->all();
        $article_content = new ArticleDetail();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());  //绑定数据到model组件上
            $article_content->load($request->post()); //绑定数据到article_content组件上
            if($model->validate() && $article_content->validate()){ // 如果验证成功
                $model->create_time = strtotime($model->create_time); //转为时间戳格式
                $model->save(false); //保存数据到article表中
                $article_content->article_id = $model->id; //将content表中article_id 和article表中数据关联起来
                $article_content->save();   //保存content表
               echo  \Yii::$app->session->setFlash('success','添加成功');
               return $this->redirect(['article/index']);
            }
        }
        $model->status = 1;
        return $this->render('add',['model'=>$model,'article_content'=>$article_content,'category'=>$category]);
    }

    //生成  Uedit编辑器操作
    public function actions()
    {
        return [
            'upload' => [
                'class' => 'kucha\ueditor\UEditorAction',
                'config' => [
                    "imageUrlPrefix"  => "http://www.baidu.com",//图片访问路径前缀
                    "imagePathFormat" => "/upload/image/{yyyy}{mm}{dd}/{time}{rand:6}", //上传保存路径
                    "imageRoot" => \Yii::getAlias("@webroot"),
                ],
            ]
        ];
    }

    //修改文章
    public function actionEdit($id){
        $model = Article::find()->where(['id'=>$id])->one();
        $category = ArticleCategory::find()->all();
        $article_content = ArticleDetail::find()->where(['article_id'=>$model->id])->one();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post());
            $article_content->load($request->post());
            if($model->validate() && $article_content->validate()){
                    $model->create_time = strtotime($model->create_time); //转为时间戳格式
                    $model->save();
                    $article_content->save();
                    echo \Yii::$app->session->setFlash('success','修改成功');
                    return $this->redirect(['article/index']);
            }
        }
        return $this->render('add',['model'=>$model,'category'=>$category,'article_content'=>$article_content]);
    }

    //删除文章
    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $model = Article::find()->where(['id'=>$id])->one();
        if($model){
            $model->status = -1;
            $model->save();
            return 'success';
        }
        //跳转到列表页
        return 'fail';
    }

}
