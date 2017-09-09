<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "article".
 *
 * @property integer $id
 * @property string $name
 * @property string $intro
 * @property integer $article_category_id
 * @property integer $sort
 * @property integer $status
 * @property integer $create_time
 */
class Article extends \yii\db\ActiveRecord
{
    public $content;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['intro'], 'string'],
            [['article_category_id', 'sort', 'status'], 'integer'],
            [['create_time','sort','article_category_id','intro','name'],'required','message'=>'必须填写'],
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
            'name' => '名称',
            'intro' => '简介',
            'article_category_id' => '文章分类id',
            'sort' => '排序',
            'status' => '状态(0隐藏 1正常)',
            'create_time' => '创建时间',
            'content' => '主要内容',
        ];
    }
    /**
     * 链表查询 返回article_category 表中数据
     */
    public function getCategory(){
        return $this->hasOne(ArticleCategory::className(),['id'=>'article_category_id']);
    }
}
