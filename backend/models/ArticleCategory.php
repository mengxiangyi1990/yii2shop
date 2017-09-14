<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "article_category".
 *
 * @property integer $id
 * @property string $name
 * @property string $intro
 * @property integer $sort
 * @property integer $status
 */
class ArticleCategory extends \yii\db\ActiveRecord{


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'article_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required','message'=>'名称不能为空'],
            [['sort'], 'required','message'=>'排序必须填写'],
            [['status'], 'required'],
            [['intro'], 'required','message'=>'简介内容必须填写!'],
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
            'sort' => '排序',
            'status' => '状态',
        ];
    }
}
