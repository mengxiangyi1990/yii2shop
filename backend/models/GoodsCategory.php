<?php

namespace backend\models;
use creocoder\nestedsets\NestedSetsBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "goods_category".
 *
 * @property integer $id
 * @property integer $tree
 * @property integer $lft
 * @property integer $rgt
 * @property integer $depth
 * @property string $name
 * @property integer $parent_id
 * @property string $intro
 */
class GoodsCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'goods_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name','parent_id'],'required'],
            [['tree', 'lft', 'rgt', 'depth', 'parent_id'], 'integer'],
            [['intro'], 'string'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tree' => '树id',
            'lft' => '左值',
            'rgt' => '右值',
            'depth' => '层级',
            'name' => '名称',
            'parent_id' => '上级分类id',
            'intro' => '简介',
        ];
    }
    //获取商品分类ztree数据
    public static function getZNodes(){
        $top = ['id'=>0,'name'=>'顶级分类','parent_id'=>0];
        $goodsCategories =  GoodsCategory::find()->select(['id','name','parent_id'])->asArray()->all();
        return ArrayHelper::merge([$top],$goodsCategories);  //合并为二维数组
    }


    public function behaviors() {
        return [
            'tree' => [
                'class' => NestedSetsBehavior::className(),
                'treeAttribute' => 'tree',  // 这里需要打开, 支持多颗树,有多个一级分类
                // 'leftAttribute' => 'lft',
                // 'rightAttribute' => 'rgt',
                // 'depthAttribute' => 'depth',
            ],
        ];
    }

    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }


    //获取首页商品分类
    public static function getGoodsCategories(){
        $redis = new \Redis();
        $redis->connect('127.0.0.1');
        $html = $redis->get('goods_categories');
        if($html === false){
            $html = '';
            $categories1 = self::find()->where(['parent_id'=>0])->all();
            foreach ($categories1 as $i=>$category1){
                $html .= '<div class="cat '.($i?'':'item1').'">';
                $html .= '<h3><a href="'.\yii\helpers\Url::to(['member/list','category_id'=>$category1->id]).'">'.$category1->name.'</a><b></b></h3>';
                $html .= '<div class="cat_detail">';
                foreach ($category1->children(1)->all() as $k=>$category2){
                    $html .= '<dl '.($k?'':'class="dl_1st"').'>';
                    $html .= '<dt><a href="'.\yii\helpers\Url::to(['member/list','category_id'=>$category2->id]).'">'.$category2->name.'</a></dt>';
                    $html .= '<dd>';
                    foreach ($category2->children()->all() as $category3){
                        $html .= '<a href="'.\yii\helpers\Url::to(['member/list','category_id'=>$category3->id]).'">'.$category3->name.'</a>';
                    }
                    $html .= '</dd>';
                    $html .= '</dl>';
                }
                $html .= '</div>';
                $html .= '</div>';
            }
            //缓存到redis中
            $redis->set('goods_categories',$html,24*3600);
        }
        return $html;
    }

}
