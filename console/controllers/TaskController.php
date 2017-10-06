<?php
namespace console\controllers;

use frontend\models\Order;
use yii\console\Controller;

class TaskController extends Controller{
    //手动清理未支付的订单
    public function actionClean(){
        //设置脚本执行时间(0为不终止)
        set_time_limit(0);
        while(true){
            Order::updateAll(['status'=>0],'status = 1 and create_time <'.(time()-24*3600));
            //每个一秒执行一次
            sleep(1);

            echo iconv('utf-8','gbk','清理完成'.date('Y-m-d H:i:s')."\n");
        }
    }
}