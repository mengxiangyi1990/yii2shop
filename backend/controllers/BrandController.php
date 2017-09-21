<?php

namespace backend\controllers;

use backend\filters\Rbacfilters;
use backend\models\Brand;
use yii\data\Pagination;
use yii\web\UploadedFile;
use flyok666\uploadifive\UploadAction;
use flyok666\qiniu\Qiniu;

class BrandController extends \yii\web\Controller
{
    public function actionAdd(){
        //实例化分类模型对象
        $model = new Brand();
        $request = \Yii::$app->request;
        if($request->isPost){  //如果是POST提交方式
            $model->load($request->post()); //将数据绑定到分类模型上
            if($model->validate()){ // 验证成功
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
            if($model->validate()){ // 验证成功
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
            'defaultPageSize'=>4
        ]); //实例化一个分页组件对象
        //查询分页后的总数据
        $model = Brand::find()->where(['>','status','-1'])->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();
        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool]);
    }

    //删除一条状态
    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $model = Brand::findOne(['id'=>$id]);

        if($model){
            $model->status = -1;
            $model->save();
            return 'success';
        }
        //跳转到列表页
        return 'fail';
    }


    public function actions() {
        return [
            's-upload' => [
                'class' => UploadAction::className(),
                'basePath' => '@webroot/upload',
                'baseUrl' => '@web/upload',
                'enableCsrf' => true, // default
                'postFieldName' => 'Filedata', // default
                //BEGIN METHOD
                //'format' => [$this, 'methodName'],
                //END METHOD
                //BEGIN CLOSURE BY-HASH
                'overwriteIfExist' => true,
                /*
                 'format' => function (UploadAction $action) {
                    $fileext = $action->uploadfile->getExtension();
                    $filename = sha1_file($action->uploadfile->tempName);
                    return "{$filename}.{$fileext}";
                },
                */
                //END CLOSURE BY-HASH
                //BEGIN CLOSURE BY TIME
                'format' => function (UploadAction $action) {
                    $fileext = $action->uploadfile->getExtension();
                    $filehash = sha1(uniqid() . time());
                    $p1 = substr($filehash, 0, 2);
                    $p2 = substr($filehash, 2, 2);
                    return "{$p1}/{$p2}/{$filehash}.{$fileext}";
                },
                //END CLOSURE BY TIME
                'validateOptions' => [
                    'extensions' => ['jpg', 'png','gif'],
                    'maxSize' => 1 * 1024 * 1024, //file size
                ],
                'beforeValidate' => function (UploadAction $action) {
                    //throw new Exception('test error');
                },
                'afterValidate' => function (UploadAction $action) {},
                'beforeSave' => function (UploadAction $action) {},
                'afterSave' => function (UploadAction $action) {
                    $qiniu = new Qiniu(\Yii::$app->params['qiniuyun']);
                    //上传文件到七牛云,并且指定一个 key(文件名称.包含路径)
                    $qiniu->uploadFile($action->getSavePath(),$action->getWebUrl());
                    //获取七牛云上的文件的url地址
                    $url = $qiniu->getLink($action->getWebUrl());
                    //输出图片路径
                    $action->output['fileUrl'] = $url;

                },
            ],
        ];
    }

    public function actionQiniu(){
        $config = [
            'accessKey'=>'q-7RZjpxAK0QKqej-QjDJg6eiJgPvcUSnCq_Vi7Q',
            'secretKey'=>'mIwUDxfEKDWGneviG3mBFbC6DcdYGrGy-glQBx1H',
            'domain'=>'http://ow0j7iilk.bkt.clouddn.com',
            'bucket'=>'yii2',
            'area'=>Qiniu::AREA_HUADONG
        ];

        $qiniu = new Qiniu($config);
        $key = '1.jpg';
        $file = \Yii::getAlias('@webroot/upload/1.jpg');
        $qiniu->uploadFile($file,$key);
        $url = $qiniu->getLink($key);
        var_dump($url);
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
