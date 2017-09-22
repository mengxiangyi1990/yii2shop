<?php

namespace frontend\controllers;

use backend\models\Goods;
use backend\models\GoodsCategory;
use backend\models\GoodsGallery;
use frontend\models\Cart;
use frontend\models\LoginForm;
use frontend\models\Member;
use frontend\models\SmsDemo;
use yii\data\Pagination;
use yii\web\Cookie;
use yii\web\NotFoundHttpException;

class MemberController extends \yii\web\Controller
{
    public $enableCsrfValidation = false;
    //用户注册
    public function actionRegister(){
        $member = new Member();
        $member->scenario = Member::SCENARIO_ADD;
        $request = \Yii::$app->request;
        if($request->isPost){
            $member->load($request->post(),'');
            if($member->validate()){
                 $member->save(false);
                 \Yii::$app->session->setFlash('success','添加成功');
                 return $this->redirect(['member/login']);
            }
        }
        return $this->renderPartial('register'); //局部渲染
    }

    //用户登录
    public function actionLogin(){
        //获取用户输入的用户名
        $model = new LoginForm();
        $request = \Yii::$app->request;
        if($request->isPost){
            $model->load($request->post(),'');
            if($model->validate()){
                if($model->login()){
                    \Yii::$app->session->setFlash('success','登录成功');
                    //登录后查看cookie中购物车的信息
                    $cookies = \Yii::$app->request->cookies;
                    $value = $cookies->getValue('carts');
                    if($value){
                        $carts_cookie = unserialize($value);
                        foreach ($carts_cookie as $cookie_goods =>$cooke_amount){
                            //已登录 购物车数据存数据库
                            $member_id = \Yii::$app->user->identity->id;
                            $request = \Yii::$app->request;
                            //查找数据库中用户的购物信息
                            $carts = Cart::find()->where(['member_id'=>$member_id])->andWhere(['goods_id'=>$cookie_goods])->one();
                            if(empty($carts)){  //如果数据库中没有数据,就新添加一条
                                $carts_in_db = new Cart();
                                $carts_in_db->amount = $cooke_amount ;  //登录后将cookie中的商品数量保存到数据库
                                $carts_in_db->goods_id = $cookie_goods;
                                $carts_in_db->member_id = $member_id;
                                $carts_in_db->save();
                            }else{      //如果有数据,就将商品数量添加
                                $carts->amount += $cooke_amount;
                                $carts->save();
                            }
                            if(\Yii::$app->request->cookies->get('carts')){
                                \Yii::$app->response->cookies->remove('carts'); //清除cookie中的信息
                            }
                            \Yii::$app->session->setFlash('success','商品已经成功添加到购物车!');
                        }
                    }

                    return $this->redirect(['member/index']);
                }
            }
        }

        return $this->renderPartial('login');
    }
    //用户注销
    public function actionLogout(){
        \Yii::$app->user->logout();

        return $this->redirect(['member/login']);
    }

    //验证用户名是否唯一
    public function actionValidateUser(){
        //获取用户输入的用户名
        $username = \Yii::$app->request->get('username');
        //判断数据表中是否存在用户名
        if(Member::find()->where(['username'=>$username])->one()){
            return 'false';
        };
        return 'true';
    }




//    //验证密码是否正确
//    public function actionValidatePassword(){
//        //获取用户输入的用户名
//        $model = new LoginForm();
//        $request = \Yii::$app->request;
//        if($request->isPost){
//            $model->load($request->post(),'');
//            if($model->validate()){
//                if($model->login()){
//                    //var_dump($model->password);exit;
//                    \Yii::$app->session->setFlash('success','登录成功');
//                    return $this->redirect(['member/index']);
//                }
//            }
//        }
//    }

    //前台首页
    public function actionIndex()
    {

        $categories1 = GoodsCategory::find()->where(['parent_id'=>0])->all();

        return $this->renderPartial('index',['categories1'=>$categories1]);
    }

    //商品列表页
    public function actionList($category_id){
        $category = GoodsCategory::findOne(['id'=>$category_id]);
        $query = Goods::find();
        //三种情况  1级分类 2级分类 3级分类
        if($category->depth == 2){//3级分类
            //sql: select * from goods where goods_category_id = $category_id
            $query->andWhere(['goods_category_id'=>$category_id]);
        }else{
            //1级分类 2级分类
            //$category id = 5
            //3级分类ID  7 8
            //SQL select *  from goods where goods_category_id  in (7,8)
            /* $ids = [];//  [7,8]
             foreach ($category->children()->andWhere(['depth'=>2])->all() as $category3){
                 $ids[]=$category3->id;
             }*/
            $ids = $category->children()->select('id')->andWhere(['depth'=>2])->column();
            //var_dump($ids);exit;
            $query->andWhere(['in','goods_category_id',$ids]);
        }
        $pager = new Pagination();
        $pager->totalCount = $query->count();
        $pager->defaultPageSize = 20;
        $models = $query->limit($pager->limit)->offset($pager->offset)->all();
        return $this->renderPartial('list',['models'=>$models,'pageTool'=>$pager]);
    }
    //商品详情页
    public function actionGoods($id){
        $model = Goods::findOne(['id'=>$id]);
        return $this->renderPartial('goods',['model'=>$model]);
    }

    //添加到购物车页面  完成添加到购物车的操作
    public function actionAddtocart($goods_id,$amount){
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
                    $member_id = \Yii::$app->user->identity->id;
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
                $member_id = \Yii::$app->user->identity->id;
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
        }
        \Yii::$app->session->setFlash('success','商品已经成功添加到购物车!');
        //直接跳转到购物车
        return $this->redirect(['cart','amount'=>$amount,'goods_id'=>$goods_id]);
    }

    //购物车页面
    public function actionCart(){
        //获取购物车数据
        if(\Yii::$app->user->isGuest){
            //从cookie中获取
            $cookies = \Yii::$app->request->cookies;
            $value = $cookies->getValue('carts');
            if($value){
                $carts = unserialize($value);//$carts = [1=>2,2=>10]
            }else{
                $carts = [];
            }
            $models = Goods::find()->where(['in','id',array_keys($carts)])->all();
        }else{

            $member_id = \Yii::$app->user->identity->id;
            $member = Cart::find()->where(['member_id'=>$member_id])->all(); //查找数据中的数据
            $carts = [];
            foreach ($member as $value){
               $carts[$value->goods_id] = $value->amount;
            }
            //从数据库中获取
            $models = Goods::find()->where(['in','id',array_keys($carts)])->all();

        }
        return $this->renderPartial('cart',['models'=>$models,'carts'=>$carts]);
    }
    //AJAX修改购物车商品数量
    public function actionAjax(){
        // goods_id  amount  2=>1
        $goods_id = \Yii::$app->request->post('goods_id');
        $amount = \Yii::$app->request->post('amount');
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
    }

    //删除商品分类
    public function actionDel(){
        $goods_id = \Yii::$app->request->post('id');
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



    //自定义验证码
    public function actions()
    {
        return [
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'foreColor' => 0xFF00FF,
                'minLength' => '4',
                'maxLength' => '4',
                'height' => '40',
                'width' => '220',
                'padding' => -1,
            ],
        ];
    }

    //测试发送短信

    public function actionSms(){

//        // 调用示例：
//        $demo = new SmsDemo(
//            "LTAI1cbOxUZtiWaQ",
//            "Ro305Z0CEtmmdqfVw1U3HXo7hFSiDo"
//        );
//
//        echo "SmsDemo::sendSms\n";
//        $response = $demo->sendSms(
//            "怪蜀黍的书屋", // 短信签名
//            "SMS_97855013", // 短信模板编号
//            "18582494674", // 短信接收者
//            Array(  // 短信模板中字段的值
//                "code"=>rand(1000,9999),
//                //"product"=>"dsd"
//            )
//        );
//        if($response->message == 'OK'){
//            echo '成功';
//        }else{
//            echo '失败';
//        }
        $phone = \Yii::$app->request->post('phone');
        $code = rand(1000,9999);
       // \Yii::$app->session->set('code_'.$phone,$code);
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        $redis->set('code_'.$phone,$code);
        echo 'OK';
        echo $code;

    }

    //验证短信验证码
    public function actionValidateSms($phone,$sms){
    $redis = new \Redis();
    $redis->connect('127.0.0.1');
    $code = $redis->get('code_'.$phone);
    //
    if($code==null || $code != $sms){
        return 'false';
    }
    //
    return 'true';
    }


}
