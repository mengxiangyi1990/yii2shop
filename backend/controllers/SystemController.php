<?php
namespace backend\controllers;

use frontend\models\Order;
use yii\web\Controller;

class SystemController extends Controller{
    //生成首页静态页面
    public function actionIndexStatic(){
     $data = $this->renderPartial('@frontend/views/member/index.php');
     //保存静态页
     file_put_contents(\Yii::getAlias('@frontend/web/html/index.php'),$data);
    }

}