<?php
namespace common\components;
use common\models\Log;
use frontend\models\Config;
use yii\base\Component;
use yii\base\Exception;

class OMS extends Component{
    //订单状态
    const BILL_STATUS_DELETED = 0; //作废
    const BILL_STATUS_SUBMIT = 1; //已提交
    const BILL_STATUS_NEW = 2; //新建
    const BILL_STATUS_FAILED = 3; //审核失败
    const BILL_STATUS_PREPARING = 4; //库房准备中
    const BILL_STATUS_TRANSPORTING = 6; //在途
    const BILL_STATUS_ABOARD = 10; //商城取消
    const BILL_STATUS_CLOSED = 12; //关闭
    const BILL_STATUS_COMPLETE = 15; //完成
    const BILL_STATUS_HOLDING = 16; //挂起等待

    //订单类型
    const BILL_TYPE_ZHENG = 1; //正常销售
    const BILL_TYPE_REJECT_IN = 2; //退货入的订单
    const BILL_TYPE_CHANGE_IN = 3; //换货入的订单
    const BILL_TYPE_CHANGE_OUT = 4; //换货出的订单

    //OMS接口连接信息
    private $_url = '';
    private $_contactCode = '';
    private $_systemKey ='';
    private $_version = '';
    private $_secretKey = '';

    private $_encryptMethod = 'AES-256-ECB';

    //查询到的数据格式
    private $_order = [
        'platFromCode' => '',//平台订单号
        'createTime' => '',
        'status' => '',
        'lists'=>[
            [
                'upc' => '',
                'skuCode' => '',
                'skuName' => '',
                'qty' => '',
                'finalUnitPrice' => '',
                'finalTotalActual' => '',
            ]
        ],
    ];
    /**
     * 返回的数据格式
     * @var array
     */
    private $_rtnOrder = [
        'billNo' => '',
        'billDate' => '2017-09-16',
        'totalAmount' => 0,
        'orderSource' => '官网',
        'Items' => [
            [
                'id'=>'0001',
                'name' => 'Twist强力伸缩型高领运动衣',
                'code' => '1010101030000000000',
                'lineType' => '0',
                'spec' => '',//非必须
                'unit' => '件',
                'taxRate' => 0.17,
                'quantity' => 3,
                'taxPrice' => 100.00,
                'totalAmount' => 300.00,
                'yhzcbs' => 0,
                'yhzcnr' => '',//非必须
                'lslbs' => '',//非必须
                'zxbm' => '',//非必须
                'kce' => '',//非必须
            ],
            [
                'id'=>'0002',//itemCode 对应 OMS 的skuCode 也就是商品库存ID
                'name' => 'Twist强力伸缩型高领运动衣', //skuName
                'code' => '1010101030000000000', //upc
                'lineType' => '0',
                'spec' => '',//非必须
                'unit' => '件',
                'taxRate' => 0.17,
                'quantity' => 2,
                'taxPrice' => 100.00,
                'totalAmount' => 200.00,
                'yhzcbs' => 0,
                'yhzcnr' => '',//非必须
                'lslbs' => '',//非必须
                'zxbm' => '',//非必须
                'kce' => '',//非必须
            ],
        ]
    ];

    private $_controller = 'component\OMS';
    private $_function = '';

    /**
     * 加载配置文件
     * @return bool
     */
    public function init()
    {
        parent::init();
        $attr = ['_url'=>'omsApiUrl','_secretKey'=>'omsApiSecretKey','_contactCode'=>'omsApiContactCode','_systemKey'=>'omsApiSystemKey','_version'=>'omsApiVersion'];
        $conf = Config::getByName($attr);

        foreach ($attr as $k=>$v){
            if (!isset($conf[$v]) || empty($conf[$v])){
                Log::add('OMS接口配置异常',Log::LOG_LEVEL_ERROR,$attr,$conf);
                $this->_handleErrorMsg('OMS接口配置异常');
            }
            $this->$k = $conf[$v];
        }
    }


    /**
     * 按订单号从OMS获取订单
     * @param $billNo
     * @param bool $rejected
     * @return null|array
     */
    private function _getFromOmsByOrderNo($billNo, $rejected=false){
        //处理查询的商品类型
        if($rejected){
            $method = 'queryRaInvoiceInfo';
        }else{
            $method = 'querySoInvoiceInfo';
        }

        $requestPram = ['code'=>$billNo];
        $result = $this->_callOMSApi($method,$requestPram);

        if (!$result){
            Log::add(Log::LOG_LEVEL_ERROR,'OMS接口调用异常',array_merge($requestPram,['method'=>$method]),$result);
            $this->_handleErrorMsg('OMS接口调用异常');
        }

        if($result['result']){//成功返回数据
            $bills = openssl_decrypt($result['body'],$this->_encryptMethod,md5($this->_secretKey));
            if(!$bills){
                Log::add(Log::LOG_LEVEL_ERROR,'OMS数据解密异常',$requestPram,$result);
                $this->_handleErrorMsg('OMS数据解密异常');
            }

            $bills = json_decode($bills, true);
            if(!$bills){
                Log::add(Log::LOG_LEVEL_ERROR,'OMS数据解密异常',$requestPram,$result);
                $this->_handleErrorMsg('OMS数据解密异常');
            }

            return $bills;

        }else{
            Log::add(Log::LOG_LEVEL_WARNING,'OMS接口调用异常',$requestPram,$result);
        }

        return null;
    }

    /**
     * 按时间段从OMS获取订单信息
     * @param $startTime
     * @param $endTime
     * @param int $page
     * @param int $pageSize
     * @param bool $isRejected
     * @return null|array
     */
    private function _getFromOmsByTime($startTime, $endTime, $page=1, $pageSize=10, $isRejected=true){
        if($isRejected){
            $method = 'queryRaInvoiceInfoPage';
        }else{
            $method = 'querySoInvoiceInfoPage ';
        }

        $requestPram = [
            'startTime' => date('Y-m-d H:i:s', $startTime),
            'endTime' => date('Y-m-d H:i:s', $endTime),
            'status'=>20,
            'startPage' => $page,
            'pageSize' => $pageSize,
        ];
        //print_r($requestPram);

        $result = $this->_callOMSApi($method,$requestPram);

        if (!$result) $this->_handleErrorMsg('OMS接口调用异常');

        if($result['result']){//成功返回数据
            $bills = openssl_decrypt($result['body'],$this->_encryptMethod,md5($this->_secretKey));
            if(!$bills){
                Log::add(Log::LOG_LEVEL_ERROR,'OMS数据解密异常',$requestPram,$result);
                $this->_handleErrorMsg('OMS数据解密异常');
            }

            $bills = json_decode($bills, true);
            if(!$bills){
                Log::add(Log::LOG_LEVEL_ERROR,'OMS数据解密异常',$requestPram,$result);
                $this->_handleErrorMsg('OMS数据解密异常');
            }

            return $bills;

        }else{
            Log::add(Log::LOG_LEVEL_WARNING,'OMS接口调用异常',$requestPram,$result);
        }

        return null;
    }

    /**
     * 按订单号查询订单正向订单，只为开票时查询使用
     * @param $billNo
     * @return array | null
     */
    public function getOrdersByBillNo($billNo){
        /*$order = '{
             "id": 879140,
             "shopCode": "UA京东旗舰店",
             "platformOrderCode": "1120171016009",
             "code": "E2487026",
             "createTime": 1509698055,
             "status": 6,
             "type": 3,
             "totalActual": 10,
             "soinvoiceLine": [
                 {
                     "id": 1042050,
                     "upc": "1010101030000000000",
                     "skuCode": "110885-090-XXL",
                     "skuName": "UA STAR WARS",
                     "qty": 6,
                     "finalUnitPrice": 1,
                     "finalTotalActual": 6
                 },
                 {
                     "id": 1042051,
                     "upc": "1010101030000000002",
                     "skuCode": "109127-002-S",
                     "skuName": "UA STAR WARS",
                     "qty": 4,
                     "finalUnitPrice": 1,
                     "finalTotalActual": 4
                 }
             ],
             "resultStatus": 1,
             "resultMsg": null
         }';

         $bill = json_decode($order,true);*/

        $this->_function = 'getOrdersByBillNo';
        $bills = $this->_getFromOmsByOrderNo($billNo);

        ///////////这里讲订单处理成一个，并对空的情况进行处理，保证只有一个订单往下传////////
        /// 主要upc，税收分类编码
        $bill  = $bills[0];
        /////////////////////////////////////

        //判断订单状态
        $status = [self::BILL_STATUS_COMPLETE, self::BILL_STATUS_TRANSPORTING];//在途和完成两种状态开票
        if(!in_array($bill['status'],$status)){//订单尚未出库，不对其进行开票
            return null;
        }

        //获取税率
        $taxRate = Config::getByName('taxRate');
        if(!$taxRate){
            Log::add(Log::LOG_LEVEL_ERROR,'开票税率配置异常','taxRate',$taxRate);
            return null;
        }

        //订单头信息
        $rtnOrder = [
            'billNo' => $billNo,
            'billDate' => date('Y-m-d H:i:s',$bill['createTime']),
            'totalAmount' => $bill['totalActual'],
            'orderSource' => \Yii::$app->params['defaultConfigTaxName'],
        ];
        //订单商品信息
        foreach ($bill['soinvoiceLine'] as $value){
            $rtnOrder['Items'][] = [
                'id'=>$value['skuCode'],//对应 OMS 的skuCode 也就是商品库存ID
                'code' => str_replace('-', '',$value['upc']), //upc,去掉中横线
                'name' => $value['skuName'], //skuName
                'lineType' => '0',
                'spec' => '',//非必须
                'unit' => '件',
                'taxRate' => $taxRate,
                'quantity' => $value['qty'],
                'taxPrice' => $value['finalUnitPrice'],
                'totalAmount' => $value['finalTotalActual'],
                'yhzcbs' => 0,
                'yhzcnr' => '',//非必须
                'lslbs' => '',//非必须
                'zxbm' => '',//非必须
                'kce' => '',//非必须
            ];
        }

        if(YII_ENV == 'dev'){
            $file_content = '获取订单信息：'."\r\n";
            $file_content .= '订单号：'.$billNo."\r\n";
            $file_content .= '获取结果:'.json_encode($bill)."\r\n";
            $file_content .= '输出结果:'.json_encode($rtnOrder)."\r\n\r\n";
            file_put_contents('./test.txt',$file_content,FILE_APPEND );
        }

        return $rtnOrder;
    }

    /**
     * 按时间获取退货单，并返回所有状态订单
     * @param $beginTime
     * @param int $interval
     * @param int $page
     * @param int $pageSize
     * @return array|null
     */
    public function getOrdersByTime($beginTime, $interval=24*3600, $page=1, $pageSize=10){
        //$rejectedOrders = $this->_getFromOmsByTime($beginTime, $beginTime+$interval, $page, $pageSize, true);

        /////判断并处理退货单，没有查到的情况就直接退出/////
        // if(empty($rejectedOrders)) return null;
        //$orders = $rejectedOrders;
        /////////////////////////////////
        /** @var 处理完的订单形式 $orders */
        $orders = [
            "count"=> 1,
            "currentPage"=>1,
            "totalPages"=> 1,
            "start"=>0,
            "size"=>50,
            "sortStr"=>"id asc",
            "firstPage"=> true,
            "lastPage"=> true,
            'items' => [
                [
                    "id"=> 879140,
                    "shopCode"=>"CN",
                    "platformOrderCode"=>"1120171016009",
                    "code"=>"E2487026",
                    "createTime"=>1509698055,
                    "status"=> 20,
                    "type"=> 3,
                    "totalActual"=>4,
                    'soinvoiceLine' => [
                        [
                            "id"=> 1042050,
                            "upc"=> "1282244-090-XXL",
                            "skuCode"=> "110885-090-XXL",
                            "skuName"=> "UA STAR WARS",
                            "qty"=> 1,
                            "finalUnitPrice"=> 1,
                            "finalTotalActual"=> 1,
                        ],
                        [
                            "id"=> 1042051,
                            "upc"=> "2016926-002-S",
                            "skuCode"=> "109127-002-",
                            "skuName"=> null,
                            "qty"=> 1,
                            "finalUnitPrice"=> 0.01,
                            "finalTotalActual"=> 0.01,
                        ]
                    ],
                ]
            ]
        ];

        /** @var 返回给控制台的数据格式 $rtn */
        /**$rtn = [
        'currentPage' => 1,
        'totalPage' => 1,
        'pageSize' => 10,
        'orders' => [
        [
        'orderSource'=>'CN',
        'billNo' => '1120171016005',//平台订单号，platformOrderCode
        'billDate' => '1509698055',
        'status'=>'1',
        'totalAmount' => '4',
        'items' => [
        [
        'id'=>'110885-090-XXL',//skuCode，也是skuId
        'code'=>'1010101030000000000',//upc
        'name'=> 'UA STAR WARS',//skuName
        'quantity'=>'3',
        'taxPrice'=>'1',//finalUnitPrice
        'totalAmount' => '3',//finalTotalActual
        ],
        [
        'id'=>'109127-002-S',//skuCode，也是skuId
        'code'=>'1010101030000000000',//upc
        'name'=> 'UA STAR WARS',//skuName
        'quantity'=>'1',
        'taxPrice'=>'1',//finalUnitPrice
        'totalAmount' => '1',//finalTotalActual
        ]
        ],
        ]
        ]
        ];*/

        $rtnOrders = [
            'currentPage' => $orders['currentPage'],
            'totalPage' => $orders['totalPages'],
            'pageSize' => $orders['size'],
        ];

        foreach ($orders['items'] as $order){
            //一条订单
            $tmpOrder = [
                'orderSource'=> $order['shopCode'],
                'billNo' => $order['platformOrderCode'],//平台订单号，platformOrderCode
                'billDate' => $order['createTime'],//createTime
                'status' => $order['status'],
                'totalAmount' => $order['totalActual'],
            ];
            //订单中的商品
            foreach ($order['soinvoiceLine'] as $goods){
                $tmpOrder['items'][] = [
                    'id' => $goods['skuCode'],//skuCode，也是skuId
                    'code' => $goods['upc'],//upc
                    'name' => $goods['skuName'],//skuName
                    'quantity' => $goods['qty'],
                    'taxPrice' => $goods['finalUnitPrice'],//finalUnitPrice
                    'totalAmount' => $goods['finalTotalActual'],//finalTotalActual
                ];
            }

            $rtnOrders['orders'][] = $tmpOrder;
        }

        return $rtnOrders;
    }

    private function _callOMSApi($method, array $requestPram){
        //构造请求参数
        $prams = [
            'messageId' => $this->_getMessageId(),
            'contactCode' => $this->_contactCode,
            'method' => $method,
            'systemKey' => $this->_systemKey,
            'version' => $this->_version,
            'body' => $this->_getRequestBody($requestPram),
        ];
        $data = json_encode($prams);

        $sign = $this->_getSign($prams);
        $url = $this->_url.'?sign='.$sign;

        if(YII_ENV == 'dev'){
            $file_content = '请求OMS接口'."\r\n";
            $file_content .= '请求时间：'.date('Y-m-d H:i:s')."\r\n";
            $file_content .= '请求url:'.$url."\r\n";
            $file_content .= '请求参数'.$data."\r\n";
            $file_content .= "\r\n\r\n";
            file_put_contents('./test.txt',$file_content,FILE_APPEND );
        }


        //CURL请求OMS接口获取数据
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);//设置获取内容但不输出
        curl_setopt($ch,CURLOPT_HTTPHEADER,[
            'Content-Type: application/text; charset=utf-8',//大坑
            //'Content-Length: ' . strlen($data),
        ]);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//取消ssl验证

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);//设置curl的请求时间
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置curl的执行时间
        $output = curl_exec($ch); //执行，获取url内容并不是输出到浏览器

        if(YII_ENV == 'dev'){
            $file_content = '请求结果：'.$output;
            $file_content .= "\r\n\r\n\r\n\r\n\r\n";
            file_put_contents('./test.txt',$file_content,FILE_APPEND );
        }

        //增加以下代码来检测错误！！！
        if ($output === false) {
            Log::add(Log::LOG_LEVEL_ERROR,'OMS接口调用失败',$prams,curl_error($ch));
            return false;
        }

        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);//获取请求信息
        if ($code != 200) {
            Log::add(Log::LOG_LEVEL_ERROR,'OMS接口调用异常',$prams,$output);
            return false;
        }

        curl_close($ch);//释放资源
        if (YII_ENV == 'dev') Log::add(Log::LOG_LEVEL_NOTICE,'OMS接口调用',$prams,$output);
        return json_decode($output, true);
    }

    private function _getMessageId(){
        $id = md5(microtime(true));
        return substr($id,0,8).'-'.substr($id,8,4).'-'.substr($id,12,4).'-'.substr($id,16,4).'-'.substr($id,20,12);
    }

    /**
     * 获取请求body的加密字符串
     * @param array $requestData
     * @return string
     */
    private function _getRequestBody(array $requestData){
            $data = json_encode($requestData);
            $key = md5($this->_secretKey);
            $method = $this->_encryptMethod;
            $body = openssl_encrypt($data, $method, $key);

            if(YII_ENV == 'dev'){
            $file_content = '加密请求参数：'."\r\n";
            $file_content .= '请求时间：'.date('Y-m-d H:i:s')."\r\n";
            $file_content .= '请求参数:'.$data."\r\n";
            $file_content .= '加密请求参数:'.$body."\r\n";
            $file_content .= "\r\n\r\n";
            file_put_contents('./test.txt',$file_content,FILE_APPEND );
            Log::add(Log::LOG_LEVEL_NOTICE,'加密请求参数',$requestData,$body);
        }

        return $body;
    }

    /**
     * 获取请求签名
     * @param array $data
     * @return string
     */
    private function _getSign(array $data){
        //获取签名
        ksort($data);
        $source = '';
        foreach ($data as $k => $v) $source .= $k.'='.$v.'&';
        $source = mb_substr($source, 0, (mb_strlen($source)-1));
        $source = $source.$this->_secretKey;
        return hash('sha256',$source);
    }

    /**
     * 处理异常消息
     * @param $msg
     * @throws Exception
     */
    private function _handleErrorMsg($msg){
        $msg = YII_ENV == 'dev' ? $msg : '未知异常';
        throw new Exception($msg);
    }

    public function test(){
//        $body = 'hD2dB89HQJeHbb7zYyYSm4qEI0olMIc04r3BGqLUt/rc9KdhSeAXTNmzQjoIeTWWM3e5T0NtJdeZCPLsxRxhgVtJxjrOWsfk/pwwdt3rc60dlJ67t2pTocJieb/O8MxV8C3cB9zwNZMbhmaEVkbG9n7mEPFJJKALW2TSY/vwiyL5rKtmjBLyGCTmDR/fiMsr8bwsiv5g2PeC78GQac5ZohRwFyppnsox+lB5TmZlpb8KmbEnnnLmORYIqgND86XiNFLBu5+Ybx+PPETg4r2wdsT+v2JOp9io0MG+/sl2EUuVv6BTyKmIHOHQbCJ9WNjNoC6cfB8BEHFjyL4A3FCRYBREOw2jKvyfvsMTjvv1lzkiWppJRTF7KXDDg+ggfoFcJHWNhOO9eljYHQp6Rmr5S8q/ia5ZqbHiQDruYG+ol4CspanwH1HoWfbimoh47a//aUL9O3mZwRCaZjSIJgx+smDfIhEw1BWMtmudN2rcP/+qfnStbr55g3FaYa7qfmDSE8hN5JimyqkGm1WyBaD8BLJaHXfGmEDCzqqp5HdfH/JJdttO/pwZIvbjmAB2bNQuDZJJaWOEwQoewahoS+sTPPDu+Z+zzSCNZ1j+9IKXjTAVW27e5XRjtZaLgRELUXVhURKieFnoE1v4TtUT8Qbufcv6qVF17ZiowLoyZpkKkFD0Lt5gEZ3ri9clEqQYaGLRvMi+ErZElNlnkus4WkrN/V0wae7acFgfCl+xM463Dj4HHv3oYjwvajCUfCZvVnWhcGN9wQXGEGJaI0ZXbTkDVyjk34iBifjXchJ4bgEehqyxIifpmXr/lZLSkfLeJe+pkjgPNRvAVxH16QclJqtoAVDUIlutRLXzjIwIMPKYkB0xOwaFR3/i/66VPRNdyblBXTf0jGvoXeWNS64p7AOgRndf26DKXTE5sL8bDxbSBMkDYUoy/zfH0OBt+fiZAtSgZ1cH/f2K0IJvTWTuMCGboCfvcvFRYq+2BzdRLKkaSPWLGx+Vvk05UNqna0vMpNyy7AoQ+J448+GqmRS9HCuaVM6mkEH1IxC3Dg0KGwBg6NjAOlSUk004Kqro67MCVRZMwtfEVHQ5pUo4a6W0A5Pj/uU9Lv+4hIO5WpLEGVPXVMeUmPyvd+VCijxgoFDioXhoG7XVya8nFNU/ngpJ9ImjG8kVMrSTKvoNVILZm6SzCA1Ug/jDDW4WM28KzXC6P59IwqbA/901NE3aIORH3MZpljfD8B7jf2y6akxkdtJ+jSKDIjG3q5kzCXM3Q3JNkzzCRahSGJGVP5w5s2jq2HrZLfNu7o7va5xVQ2f5Nr6u5jSdOr3ubUsNSrN/gUGNsDZw82JRan3d33UrIXl5vj7Yj+ARAUSrf8A048JKhV9o3bBifuBvN8YnuyJ0GkbcE4uuZ8leazbll3fim4KJiV+Nqg==';
//        $a = openssl_decrypt($body,$this->_encryptMethod,md5($this->_secretKey));
//        print_r(json_decode($a,true));die;

        //$this->getOrdersByTime(strtotime('-1 day'),24*3600);die;
        $this->getOrdersByBillNo('1120171016002');die;

        $a = $this->_getFromOmsByTime(strtotime('2017-09-10'),strtotime('2017-11-17'));
        print_r($a);

        die;

        $str = '{"startPage":1,"pageSize":50, "startTime":"2017-09-22 10:00:00","endTime":"2017-09-22 10:05:00"}';
        $request = json_decode($str,true);
        $this->_callOMSApi('querySoinvoiceInfo',$request);
    }

}