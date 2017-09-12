<?php
namespace backend\models;


use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use creocoder\nestedsets\NestedSetsQueryBehavior;
class CategoryQuery extends ActiveQuery {
    public function behaviors() {
        return [
            NestedSetsQueryBehavior::className(),
        ];
    }
}