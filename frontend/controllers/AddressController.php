<?php

namespace frontend\controllers;

use frontend\models\Address;

class AddressController extends \yii\web\Controller
{
    //地址首页
    public function actionIndex()
    {
        $model = Address::find()->all();
        return $this->render('index',['models'=>$model]);
    }

    //添加地址
    public function actionAdd(){
        $new_address = new Address();
        $request = \Yii::$app->request;
        if($request->isPost){
           $new_address->load($request->post(),'');
            if($new_address->validate()){
                $new_address->save();
                \Yii::$app->session->setFlash('success','添加成功');
                return $this->redirect(['address/index']);
            }
        }
    }
    //删除地址
    public function actionDel(){
       $id = \Yii::$app->request->post('id');
       $address = Address::find()->where(['id'=>$id])->one();
       if($address){
           $address->delete();
           return "success";
       }
        return "fail";
    }




    //修改地址
    public function actionEdit($id){
        $model = Address::find()->all();
        $new_address = Address::findOne(['id'=>$id]);
        $defaultProvince = $new_address->province;
        $defaultCity = $new_address->city;
        $defaultArea = $new_address->area;
        //var_dump($new_address->name);exit;
        $request = \Yii::$app->request;
        if($request->isPost){
            $new_address->load($request->post(),'');
            if($new_address->validate()){
                $new_address->save(); //有问题  字符串站了字符
                \Yii::$app->session->setFlash('success','修改成功');
                return $this->redirect(['address/index']);
            }
        }
        return $this->render('edit',['models'=>$model,'new_address'=>$new_address,'defaultProvince'=>$defaultProvince,'defaultCity'=>$defaultCity,'defaultArea'=>$defaultArea]);
    }

}
