<?php

namespace backend\controllers;

use backend\models\Brand;
use yii\data\Pagination;
use yii\web\UploadedFile;

class BrandController extends \yii\web\Controller
{
    public function actionAdd(){
        //实例化分类模型对象
        $model = new Brand();
        $request = \Yii::$app->request;
        if($request->isPost){  //如果是POST提交方式
            $model->load($request->post()); //将数据绑定到分类模型上
            $model->file = UploadedFile::getInstance($model,'file'); //将上传文件绑定到模型上
            if($model->validate()){ // 验证成功
                if(!empty($model->file)){
                    //生成新的包含文件路径的文件名
                    $file = '/upload/'.uniqid().'.'.$model->file->getExtension();
                    //将文件保存到根目录 upload路径下
                    $model->file->saveAs(\Yii::getAlias('@webroot').$file,false);
                }else{
                    $file = '/upload/59b0ff87934f4.jpg';
                }
                //将文件相对路径保存到logo属性上
                $model->logo = $file;
                //保存数据到brand表中
                $model->save();
                //显示提示信息
                \Yii::$app->session->setFlash('success','添加成功');
                //跳转到列表页
                return $this->redirect(['brand/index']);
            }else{
                var_dump($model->getErrors());exit; //提示错误信息
            }
        }
        //默认状态为正常
        $model->status = 1;
        return $this->render('add',['model'=>$model]);
    }
    /**
     *  修改状态表
     */

    public function actionEdit($id){
        $model = Brand::findOne(['id'=>$id]);
        $request = \Yii::$app->request;
        if($request->isPost){  //如果是POST提交方式
            $model->load($request->post()); //将数据绑定到分类模型上
            $model->file = UploadedFile::getInstance($model,'file'); //将上传文件绑定到模型上
            if($model->validate()){ // 验证成功
                if(!empty($model->file)){
                    //生成新的包含文件路径的文件名
                    $file = '/upload/'.uniqid().'.'.$model->file->getExtension();
                    //将文件保存到根目录 upload路径下
                    $model->file->saveAs(\Yii::getAlias('@webroot').$file,false);
                }else{
                    $file = '/upload/59b0ff87934f4.jpg';
                }
                //将文件相对路径保存到logo属性上
                $model->logo = $file;
                //保存数据到brand表中
                $model->save();
                //显示提示信息
                \Yii::$app->session->setFlash('success','修改成功');
                //跳转到列表页
                return $this->redirect(['brand/index']);
            }else{
                var_dump($model->getErrors());exit; //提示错误信息
            }
        }
        return $this->render('add',['model'=>$model]);
    }



    //状态列表
    public function actionIndex(){
        /**
         * 准备分页数据
         */
        $totalCount = Brand::find()->where(['>','status','-1'])->count();  //查询brand表中数据总条数
        $pageTool = new Pagination([
            'totalCount'=>$totalCount,
            'defaultPageSize'=>2
        ]); //实例化一个分页组件对象
        //查询分页后的总数据
        $model = Brand::find()->where(['>','status','-1'])->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();
        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
    }

    //删除一条状态
    public function actionDel($id){
        $brand = Brand::find()->where(['id'=>$id])->one();
        /**
         * 将查询出来的数据状态改为-1 保存到数据表中
         */
        $brand->status = -1;
        $brand->save();
        //跳转到列表页
        return $this->redirect(['brand/index']);
    }




}
