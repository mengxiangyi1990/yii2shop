<?php
namespace frontend\controllers;

use backend\models\Article;
use backend\models\ArticleCategory;
use backend\models\Brand;
use backend\models\Goods;
use backend\models\GoodsCategory;
use frontend\models\Address;
use frontend\models\Cart;
use frontend\models\LoginForm;
use frontend\models\Member;
use frontend\models\Order;
use yii\data\Pagination;
use yii\db\Exception;
use yii\web\Controller;
use yii\web\Cookie;
use yii\web\Response;

class ApiController extends Controller{
    public $enableCsrfValidation = false;
    //public $token = 'My_objectV587';
    public function init()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        parent::init();
    }

    //用户注册
    public function actionUserRegister(){
        $result = [
            'error'=> true,
            'msg'=> '',
            'data'=> []
        ];
        if(\Yii::$app->request->isPost){
            $member = new Member();
            $member->scenario = Member::SCENARIO_ADD;
            $member->load(\Yii::$app->request->post(),'');
            if($member->validate()){
                $member->save(false);
                $result['data']['id'] = $member->id;
                $result['error'] = false;
            }else{
                $result['msg'] = $member->getErrors();
            }
        }else{
            $result['msg'] = '请求方式错误';
        }
        return $result;
    }

    //用户登录
    public function actionUserLogin(){
        $result = [
            'error'=> true,
            'msg'=> '',
            'data'=> []
        ];
        $member = new LoginForm();
        if(\Yii::$app->request->isPost){
            $member->load(\Yii::$app->request->post(),'');
            if($member->validate()){
                if($member->login()){
                    $result['msg'] = '登录成功';
                    $result['data']['token'] = \Yii::$app->user->identity['token']; // 将用户登录信息中的token查出来返回给用户

                }else{
                    $result['msg'] = $member->getErrors();
                }
            }else{
                $result['error'] = false;
                $result['msg'] = $member->getErrors();
            }
        }else{
            $result['msg'] = '请求方式错误';
        }
        return $result;
    }
    //修改密码
    public function actionModifyPassword(){
        $result = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $result['msg'] = '用户未登录';
            return $this->redirect(['member/login']);
        }else{
            if(\Yii::$app->request->isPost){
                $member = Member::findOne(['id'=>\Yii::$app->user->id]);
                $member->load(\Yii::$app->request->post(),'');
               if($member->validate()){
                       $member->save();
                       $result['error'] = false;
                       $result['data']['member_id'] = \Yii::$app->user->id;
                       $result['data']['username'] = $member->username;
                       $result['msg'] = '修改成功';
               }else{
                   $result['msg'] = $member->getErrors();
               }
            }else{
                $result['msg'] = '请求方式错误';
            }
        }
        return $result;
    }




    //获取当前登录的用户信息
    public function actionGetUserInfo(){
        $result = [
            'error' => true,
            'msg' => '',
            'data' =>[]
        ];

        if(\Yii::$app->user->isGuest){
            $result['msg'] = '未登录用户';
        }else{
            $member_id = \Yii::$app->user->id;
            $member = Member::find()->where(['id'=>$member_id])->one();
            $result['data']['username'] = $member->username;
            $result['error'] = false;
            $result['data']['email'] = $member->email;
            $result['data']['tel'] = $member->tel;
        }

        return $result;
    }

    //添加收货地址
    public function actionAddAddress(){
        $result  = [
            'error'=>true,
            'msg'=>'',
            'data' => []
        ];
        if(\Yii::$app->user->isGuest){
            $result['error'] = '未登录用户';
        }else{
            $address = new Address();
            $address->scenario = Address::SCENARIO_ADD;
            if(\Yii::$app->request->isPost){
                $address->load(\Yii::$app->request->post(),'');
                if($address->validate()){
                    $address->member_id = \Yii::$app->user->id;
                    $address->save();
                    $result['error'] = false;
                    $result['data']['name'] = $address->name;
                    $result['data']['address'] = $address->address;
                    $result['data']['tel'] = $address->tel;
                    $result['data']['member_id'] = $address->member_id;
                    $result['data']['id'] = $address->id;
                }else{
                    $result['msg'] = $address->getErrors();
                }
            }else{
                $result['msg'] = '请求方式错误';
            }
        }
        return $result;
    }

    //修改收货地址
    public function actionEditAddress(){
        $result = [
            'error'=> true,
            'msg'=> '',
            'data'=> []
        ];
        if(\Yii::$app->user->isGuest){
            $result['msg'] = '用户未登录';
        }else{
            if(\Yii::$app->request->isPost){
                $address_id = \Yii::$app->request->post('id');
                $member_id = \Yii::$app->user->id;
                $address = Address::findOne(['id'=>$address_id,'member_id'=>$member_id]);
                if(empty($address)){
                    echo '错误的请求id';exit;
                }
                $address->load(\Yii::$app->request->post());

                if($address->validate()){
                    $address->save();
                    $result['error'] = false;
                    $result['data'] = $address;
                }else{
                    $result['msg'] = $address->getErrors();
                }

            }else{
                $result['msg'] = '请求方式错误';
            }
        }
        return $result;
    }
    //获取收货地址
    public function actionGetAddress(){
        $result = [
            'error' => true,
            'msg' => '',
            'data' => []
        ];

        if(\Yii::$app->user->isGuest){
            $result['error'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isPost){
                $address = Address::findAll(['member_id'=>\Yii::$app->user->id]) ;
                $result['error'] = false;
                $key = 0;
                foreach ($address as $val){
                    $result['data'][++$key] = $val;
                }
            }else{
                $result['msg'] = '请求方式错误';
            }

        }

        return $result;

    }

    //删除地址
    public function actionDelAddress(){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
            return $this->redirect(['member/index']);
        }else{
            if(\Yii::$app->request->isGet){
                $address_id = \Yii::$app->request->get('id');
                $address = Address::findOne(['id'=>$address_id]);
                if($address){
                    $request['data'] = $address;
                    $address->delete();
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;
    }

    public function actionList($category_id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];

        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $category = GoodsCategory::findOne(['id'=>$category_id]);
                $query = Goods::find();
                //三种情况  1级分类 2级分类 3级分类
                if($category->depth == 2){//3级分类
                    $query->andWhere(['goods_category_id'=>$category_id]);
                }else{
                    $ids = $category->children()->select('id')->andWhere(['depth'=>2])->column();
                    $query->andWhere(['in','goods_category_id',$ids]);
                }

                if($category){
                    $request['error'] = false;
                    $request['data'] = $category;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;

    }

    public function actionBrand($brand_id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];

        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $category = Goods::findAll(['brand_id'=>$brand_id]);

                if($category){
                    $request['error'] = false;
                    $request['data'] = $category;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;

    }

    //获取文章分类
    public function actionArticleCategory($id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];

        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $article = ArticleCategory::findOne(['id'=>$id]);

                if($article){
                    $request['error'] = false;
                    $request['data'] = $article;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;

    }

    //获取某分类下面的所有文章
    public function actionGetArticle($id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $article = Article::findAll(['article_category_id'=>$id]);
                if($article){
                    $request['error'] = false;
                    $request['data'] = $article;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;
    }


    //获取某分类下面的所有文章
    public function actionGoodsCategory(){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $goodscategory = GoodsCategory::find()->all();
                if($goodscategory){
                    $request['error'] = false;
                    $request['data'] = $goodscategory;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;
    }

    //获取某分类的所有子分类
    public function actionChildrenCategory($id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];

        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $goodscategory = GoodsCategory::findAll(['parent_id'=>$id]);
                if($goodscategory){
                    $request['error'] = false;
                    $request['data'] = $goodscategory;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;
    }

    //-获取某分类的父分类
    public function actionParentCategory($parent_id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];

        if(\Yii::$app->user->isGuest){
            $request['msg'] = '未登录用户';
        }else{
            if(\Yii::$app->request->isGet){
                $goodscategory = GoodsCategory::findAll(['id'=>$parent_id]);
                if($goodscategory){
                    $request['error'] = false;
                    $request['data'] = $goodscategory;
                }else{
                    return $request['msg'] = '未找到的地址数据';
                }
            }else{
                $request['msg'] = '请求方式错误';
            }
        }
        return $request;
    }
    //添加商品到购物车
    public function actionAddtocart($goods_id,$amount){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            //读取cookie中的数据
            $cookies = \Yii::$app->request->cookies;
            $value = $cookies->getValue('carts');
            if($value){
                $carts = unserialize($value);
            }else{
                $carts = [];
            }
            //检查购物车中是否存在当前需要添加的商品
            if(array_key_exists($goods_id,$carts)){
                $carts[$goods_id] += $amount;
            }else{
                $carts[$goods_id] = intval($amount);
            }
            //添加数据到cookie中
            $cookies = \Yii::$app->response->cookies;
            $cookie = new Cookie();
            $cookie->name = 'carts';
            $cookie->value = serialize($carts);
            $cookie->expire = time()+7*24*3600;//过期时间戳
            $cookies->add($cookie);
        }else{
            //登录后查看cookie中购物车的信息
            $cookies = \Yii::$app->request->cookies;
            $value = $cookies->getValue('carts');
            if($value){
                $carts_cookie = unserialize($value);
                foreach ($carts_cookie as $cookie_goods =>$cooke_amount){
                    //已登录 购物车数据存数据库
                    $member_id = \Yii::$app->user->id;
                    //查找数据库中用户的购物信息
                    $carts = Cart::find()->where(['member_id'=>$member_id])->andWhere(['goods_id'=>$cookie_goods])->one();
                    if(empty($carts)){  //如果数据库中没有数据,就新添加一条
                        $carts_in_db = new Cart();
                        $carts_in_db->amount = $amount + $cooke_amount ;  //登录后将cookie中的商品数量保存到数据库
                        $carts_in_db->goods_id = $cookie_goods;
                        $carts_in_db->member_id = $member_id;
                        $carts_in_db->save();
                    }else{      //如果有数据,就将商品数量添加
                        $carts->amount += ($amount + $cooke_amount);
                        $carts->save();
                    }
                    if(\Yii::$app->request->cookies->get('carts')){
                        \Yii::$app->response->cookies->remove('carts'); //清除cookie中的信息
                    }
                }
            }
            //已登录 购物车数据存数据库
            $member_id = \Yii::$app->user->id;
            //查找数据库中用户的购物信息
            $carts = Cart::find()->where(['member_id'=>$member_id])->andWhere(['goods_id'=>$goods_id])->one();
            if(empty($carts)){  //如果数据库中没有数据,就新添加一条
                $carts_in_db = new Cart();
                $carts_in_db->amount = $amount ;  //登录后将cookie中的商品数量保存到数据库
                $carts_in_db->goods_id = $goods_id;
                $carts_in_db->member_id = $member_id;
                $carts_in_db->save();
            }else{      //如果有数据,就将商品数量添加
                $carts->amount += $amount;
                $carts->save();
            }
            if($carts){
                $request['error'] = false;
                $request['data'] = $carts;
            }else{
                return $request['msg'] = '未找到的地址数据';
            }

        }

        return $request;
    }

    //修改购物车某商品数量
    public function actionAjax($goods_id,$amount){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $cookies = \Yii::$app->request->cookies;
            $value = $cookies->getValue('carts');
            if($value){
                $carts = unserialize($value);
            }else{
                $carts = [];
            }
            //检查购物车中是否存在当前需要添加的商品
            if(array_key_exists($goods_id,$carts)){
                $carts[$goods_id] = $amount;
            }
            $cookies = \Yii::$app->response->cookies;
            $cookie = new Cookie();
            $cookie->name = 'carts';
            $cookie->value = serialize($carts);
            $cookie->expire = time()+7*24*3600;//过期时间戳
            $cookies->add($cookie);
        }else{
            $member_id = \Yii::$app->user->id;
            //查询数据库中的数据
            $cart = Cart::find()->where(['member_id'=>$member_id])->andWhere(['goods_id'=>$goods_id])->one();
            $cart->amount = $amount;
            $cart->save();
        }

        if($cart){
            $request['error'] = false;
            $request['data'] = $cart;
        }else{
            return $request['msg'] = '未找到的地址数据';
        }

        return $request;
    }
    //删除购物车某商品
    public function actionDel($goods_id){
        $member_id = \Yii::$app->user->id;
        if(\Yii::$app->user->isGuest){
            $cookies = \Yii::$app->request->cookies;
            $value = $cookies->getValue('carts');
            if($value){
                $carts = unserialize($value);
            }else{
                $carts = [];
            }
            unset($carts[$goods_id]);
            $cookies = \Yii::$app->response->cookies;
            $cookie = new Cookie();
            $cookie->name = 'carts';
            $cookie->value = serialize($carts);
            $cookie->expire = time()+7*24*3600;//过期时间戳
            $cookies->add($cookie);

        }else{
            //如果已经登录  从数据库中删除
            $cart = Cart::findOne(['member_id'=>$member_id,'goods_id'=>$goods_id]);
            if($cart){
                $cart->delete();
                return 'success';
            }
        }
        return "fail";
    }

    //获取支付方式
    public function actionGetPay($id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        $deliveries = [
            1=>['顺丰快递',25,'服务好,价格高,速度最快'],
            2=>['圆通快递',15,'服务一般,价格便宜,速度一般'],
            3=>['EMS',20,'服务一般,价格高,速度一般,全国任何地方都可以到'],
        ];
        if(\Yii::$app->user->isGuest) {
            $request['msg'] = '未登录用户';
        }else{
            $request['data'] = $deliveries[$id];
            $request['error'] = false;
        }
        return $request;
    }
    //获取支付方式
    public function actionGetConsignment($id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        $payment = [
            1=>['货到付款','送货上门后再收款 支持现金、POS机刷卡、支票支付'],
            2=>['在线支付','支持绝大数银行借记卡及部分银行信用卡'],
            3=>['上门自提','自提时付款，支持现金、POS刷卡、支票支付'],
            4=>['邮局汇款','通过快钱平台收款 汇款后1-3个工作日到账'],
        ];
        if(\Yii::$app->user->isGuest) {
            $request['msg'] = '未登录用户';
        }else{
            $request['data'] = $payment[$id];
            $request['error'] = false;
        }
        return $request;
    }
    //获取订单
    public function actionGetOrder(){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        //判断用户是否登录
        if(\Yii::$app->user->isGuest){
            $request['msg'] = "用户未登录";
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
                    $request['data'] = $model;
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
        }
        return $request;
    }
    //取消订单
    public function actionDelOrder($id){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $request['msg'] = "用户未登录";
        }else{
            $order = Order::findOne(['id'=>$id]);
            $request['data'] = $order;
        }
        return $request;
    }

    //登录api接口
    public function actionLogin(){
        $request = [
            'error'=>true,
            'msg'=>'',
            'data'=>[]
        ];
        if(\Yii::$app->user->isGuest){
            $request['msg'] = "用户未登录";
        }else{
            echo '已经登录';
        }
        return $request;
    }


}