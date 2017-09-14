<?php

namespace backend\controllers;

use backend\models\Brand;
use backend\models\Goods;
use backend\models\GoodsCategory;
use backend\models\GoodsDayCount;
use backend\models\GoodsGallery;
use backend\models\GoodsInfo;
use flyok666\uploadifive\UploadAction;
use flyok666\qiniu\Qiniu;
use yii\data\Pagination;
use yii\db\Query;
use yii\web\NotFoundHttpException;


class GoodsController extends \yii\web\Controller
{
    //商品列表
    public function actionIndex(){
        /**
         * 准备分页数据
         */
        $name = \Yii::$app->request->get('name');
        $sn = \Yii::$app->request->get('sn');
        $minPrice = \Yii::$app->request->get('minPrice');
        $maxPrice = \Yii::$app->request->get('maxPrice');
        $query = Goods::find();
        if(!empty($name)){
            $query = $query->where(['like','name',$name]);
        }
        if(!empty($sn)){
            $query = $query->andwhere(['like','sn',$sn]);
        }
        if(!empty($minPrice)){
            $query = $query->andWhere(['>=','shop_price',$minPrice]);
        }
        if(!empty($maxPrice)){
            $query = $query->andWhere(['<=','shop_price',$maxPrice]);
        }
        $totalCount = $query->count();  //查询brand表中数据总条数
        $pageTool = new Pagination([
            'totalCount'=>$totalCount,
            'defaultPageSize'=>4
        ]); //实例化一个分页组件对象

        $model = $query->limit($pageTool->limit)->offset($pageTool->offset)->orderBy('id desc')->all();

        return $this->render('index',['models'=>$model,'pageTool'=>$pageTool,'name'=>$name,'sn'=>$sn,'minPrice'=>$minPrice,'maxPrice'=>$maxPrice]);
    }
    //商品添加
    public function actionAdd(){
        $model = new Goods();
        $brand = Brand::find()->all();  //查询出brand表中所有数据
        $request = \Yii::$app->request;
        if($request->isPost){ //如果是post提交方式
            //通过post中goods_category_id查找goods_category表中的对应的id, 再查找所有parent_id = id 的数据, 如果有,就不能绑定数据,并且parent_id != 0
            $goodsCategoryId = $request->post('Goods')['goods_category_id'];
            $goods_category = GoodsCategory::find()->where(['parent_id'=>$goodsCategoryId])->one();
            if(empty($goods_category) && $goodsCategoryId != 0){$model->load($request->post());//将提交数据绑定到$model上
                $model->load($request->post());//将提交数据绑定到$model上
            }else{
                throw new NotFoundHttpException('只能在相应的分类下添加商品!');
            }

            if($model->validate()){ //验证通过后执行
                $model->create_time = time();  //goods表中create_time 为当前时间戳格式
                $goods_info = new GoodsInfo();  //实例化GoodsInfo模型对象
                $goods_info->content = $model->content; //将内容绑定

                $goods_day_count = GoodsDayCount::find()->where(['day'=>date('Y-m-d',$model->create_time)])->one(); //实例化GoodsDayCount模型对象
                if(empty($goods_day_count)){  //如果商品数量表中没有对应的日期 就新添加一条记录 数量为1
                    $goods_day_count = new GoodsDayCount();
                    $goods_day_count->day = date('Y-m-d',$model->create_time);
                    $goods_day_count->count = 1;
                    $model->sn = $goods_day_count->day.'00001';
                }else{
                    $goods_day_count->count += 1;
                    $model->sn = date('Ymd',time()).sprintf('%05d',$goods_day_count->count);
                }
                $goods_day_count->save();
                $goods_info->save();  //将数据保存到goods_info表中
                $model->status = 1;
                $model->view_times = 1;
                $model->save();
                return $this->redirect(['goods/index']);
            }
        }
        $model->is_on_sale = 1;
        return $this->render('add',['model'=>$model,'brand'=>$brand]);
    }

    //商品修改
    //商品添加
    public function actionEdit($id){
        $model = Goods::find()->where(['id'=>$id])->one();
        $goodsInfo = GoodsInfo::find()->where(['goods_id'=>$id])->one();
        $model->content = $goodsInfo->content;
        $brand = Brand::find()->all();  //查询出brand表中所有数据
        $request = \Yii::$app->request;
        if($request->isPost){ //如果是post提交方式
            //通过post中goods_category_id查找goods_category表中的对应的id, 再查找所有parent_id = id 的数据, 如果有,就不能绑定数据,并且parent_id != 0
            $goodsCategoryId = $request->post('Goods')['goods_category_id'];
            $goods_category = GoodsCategory::find()->where(['parent_id'=>$goodsCategoryId])->one();
            if(empty($goods_category) && $goodsCategoryId != 0){$model->load($request->post());//将提交数据绑定到$model上
                $model->load($request->post());//将提交数据绑定到$model上
            }else{
                throw new NotFoundHttpException('只能在相应的分类下添加商品!');
            }

            if($model->validate()){ //验证通过后执行
                $model->create_time = time();  //goods表中create_time 为当前时间戳格式
                $goodsInfo->content = $model->content; //将内容绑定

                $goods_day_count = GoodsDayCount::find()->where(['day'=>date('Y-m-d',$model->create_time)])->one(); //实例化GoodsDayCount模型对象
                if(empty($goods_day_count)){  //如果商品数量表中没有对应的日期 就新添加一条记录 数量为1
                    $goods_day_count = new GoodsDayCount();
                    $goods_day_count->day = date('Y-m-d',$model->create_time);
                    $goods_day_count->count = 1;
                    $model->sn = $goods_day_count->day.'00001';
                }
                $goods_day_count->save();
                $goodsInfo->save();  //将数据保存到goods_info表中
                $model->status = 1;
                $model->view_times = 1;
                $model->save();
                return $this->redirect(['goods/index']);
            }
        }
        $model->is_on_sale = 1;
        return $this->render('add',['model'=>$model,'brand'=>$brand]);
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
                    ]
            ],
             's-upload' => [
                 'class' => UploadAction::className(),
                 'basePath' => '@webroot/upload',
                 'baseUrl' => '@web/upload',
                 'enableCsrf' => true, // default
                 'postFieldName' => 'Filedata', // default
                 'overwriteIfExist' => true,
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
                     //将图片路径和goods_id 保存到goods_gallery表中
                     $goods_id = \Yii::$app->request->post('goods_id');
                     $goods_gallery = new GoodsGallery();
                     $goods_gallery->goods_id = $goods_id;
                     $goods_gallery->path = $url;
                     $goods_gallery->save();
                     $action->output['id'] = $goods_gallery->id;

                 },
            ]
        ];
    }

    //删除商品
    public function actionDel(){
        $id = \Yii::$app->request->post('id');
        $good = Goods::findOne(['id'=>\Yii::$app->request->post('id')]);
        $goodContent = GoodsInfo::findOne(['goods_id'=>$id]);
        $good_gallery = GoodsGallery::findOne(['goods_id'=>$id]);
        if($good){
            $good->delete();
            $goodContent->delete();
            $good_gallery->delete();
            return 'success';
        }
        //跳转到列表页
        return 'fail';
    }
    //预览商品
    public function actionShow($id){
        $model = GoodsInfo::find()->where(['goods_id'=>$id])->one();
        return $this->render('show',['model'=>$model]);
    }
    //商品相册
    public function actionGallery($id){
        $goodsGallery = new GoodsGallery();
        $model = GoodsGallery::find()->where(['goods_id'=>$id])->all();
        return $this->render('gallery',['models'=>$model,'goodsGallery'=>$goodsGallery]);
    }

    public function actionDelgallery(){
        $id = \Yii::$app->request->post('id');
        $model = GoodsGallery::findOne(['id'=>$id]);
        if($model){
           $model->delete();
            return 'success';
        }
        //跳转到列表页
        return 'fail';
    }
}
