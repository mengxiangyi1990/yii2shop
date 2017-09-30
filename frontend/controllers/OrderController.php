<?php

namespace frontend\controllers;

use backend\models\Goods;
use frontend\models\Address;
use frontend\models\Cart;
use frontend\models\Order;
use frontend\models\OrderGoods;
use yii\db\Exception;
use yii\db\Query;

class OrderController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    //订单信息表单
    public function actionIndex(){
        //判断用户是否登录
        if(\Yii::$app->user->isGuest){
            \Yii::$app->session->setFlash('success','请登录后结算');
            return $this->redirect(['member/login']);
        }else{
            $id = \Yii::$app->user->id;
            $Carts = Cart::find()->where(['member_id'=>$id])->all();
            //实例化order模型
            $model = new Order();
            $request = \Yii::$app->request;
            $address = Address::find()->all();
            if($request->isPost){
               $delivery_id = $request->post('delivery_id');
                if($delivery_id == ""){
                    $delivery_id = 1;
                }
                $model->delivery_id = $delivery_id;
                $model->delivery_name = Order::$deliveries[$model->delivery_id][0];
                $model->delivery_price = Order::$deliveries[$model->delivery_id][1];
                //通过address_id 查找地址表中数据
                $address_id = $request->post('address_id');
                if($address_id == ""){
                    $address_id = 8;
                }

                $address_in_db = Address::findOne(['id'=>$address_id,'member_id'=>\Yii::$app->user->id]);
                $model->member_id = \Yii::$app->user->id;
                $model->name = $address_in_db->name;
                $model->province = $address_in_db->province;
                $model->city = $address_in_db->city;
                $model->area = $address_in_db->area;
                $model->address = $address_in_db->address;
                $model->tel = $address_in_db->tel;
                $model->status = 1; //+++++++还没做
                $model->create_time = time();
                $model->total = 0;//遍历购物车表里面的商品,累加计算,加上运费
                $goods_id = $request->post('goods_ids');
                foreach ($goods_id as $value){  //遍历查询所有goods_id
                   $goods = Goods::findOne(['id'=>$value]); //查询goods表中对应的数据
                   $cats = Cart::findOne(['goods_id'=>$value]); // 查询cart表中对应的数据
                    $model->total += ($goods->shop_price)*($cats->amount);
                }
                $model->total += $model->delivery_price; //保存order表中total数据
                // 操作mysql之前开启事物
                $transaction = \Yii::$app->db->beginTransaction();//开始事务
                try{
                    //保存order表数据
                    $model->save();
                    //订单商品详情表
                    $carts = Cart::find()->where(['member_id'=>\Yii::$app->user->id])->all();
                    foreach ($carts as $cart){
                        //检查库存
                        if($cart->amount > $cart->goods->stock){
                            //库存不足,不能下单(抛出异常)
                            throw new Exception($cart->goods->name.'商品库存不足,不能下单');
                        }
                        //实例化order_goods模型
                        $order_goods = new OrderGoods();
                        $order_goods->order_id = $model->id;
                        $order_goods->goods_id = $cart->goods_id;
                        $order_goods->goods_name = $cart->goods->name;
                        $order_goods->logo = $cart->goods->logo;
                        $order_goods->price = $cart->goods->shop_price;
                        $order_goods->amount = $cart->amount;
                        $order_goods->total = ($cart->amount)*($cart->goods->shop_price);
                        if($order_goods->save()){
                           $cart->delete();
                        }
                        //实例化goods模型
                        $goods = Goods::findOne(['id'=>$cart->goods_id]);
                        $goods->stock  -= $cart->amount;
                        $goods->save();
                    }
                   //不需要跳转, 直接视图跳转,成功都显示一个页面
                }catch (Exception $e){
                    // 不能下单,回滚
                    $transaction->rollBack();
                }
            }
            return $this->renderPartial('index',['models'=>$model,'address'=>$address,'carts'=>$Carts]);
        }
    }

    //订单提交成功页面
    public function actionSuccess(){



        return $this->renderPartial('success');
    }

    //订单列表页
    public function actionMy_order(){
        $models = Order::findAll(['member_id'=>\Yii::$app->user->id]);

        return $this->renderPartial('order',['models'=>$models]);
    }


}
