<?php

namespace backend\models;
use Yii;
use creocoder\nestedsets\NestedSetsBehavior;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "goods".
 *
 * @property integer $id
 * @property string $name
 * @property string $sn
 * @property string $logo
 * @property integer $goods_category_id
 * @property integer $brand_id
 * @property string $market_price
 * @property string $shop_price
 * @property integer $stock
 * @property integer $is_on_sale
 * @property integer $status
 * @property integer $sort
 * @property integer $create_time
 * @property integer $view_times
 */
class Goods extends \yii\db\ActiveRecord
{
    public $content;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['goods_category_id', 'brand_id', 'goods_category_id','stock', 'is_on_sale', 'status', 'sort', 'create_time', 'view_times'], 'integer'],
            [['market_price', 'shop_price'], 'number'],
            [['name', 'sn'], 'string', 'max' => 20],
            [['logo'], 'string', 'max' => 255],
            ['content','string'],
            [['name'],'required','message'=>'商品名称必须填写'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '商品名称',
            'sn' => '货号',
            'logo' => 'LOGO图片',
            'goods_category_id' => '商品分类id',
            'brand_id' => '品牌分类',
            'market_price' => '市场价格',
            'shop_price' => '商品价格',
            'stock' => '库存',
            'is_on_sale' => '是否在售(1在售,0下架)',
            'status' => '状态(1正常 0回收站)',
            'sort' => '排序',
            'create_time' => '添加时间',
            'view_times' => '浏览次数',
        ];
    }
}
