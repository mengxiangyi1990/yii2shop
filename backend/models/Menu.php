<?php

namespace backend\models;

use Codeception\Module\Yii2;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "menu".
 *
 * @property integer $id
 * @property string $name
 * @property integer $parent_id
 * @property string $url
 * @property integer $sort
 */
class Menu extends \yii\db\ActiveRecord
{
    const SCENARIO_ADD= 'add';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'menu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id', 'sort'], 'integer'],
            ['parent_id', 'required','message'=>'上级菜单不能为空'],
            [['name', 'url'], 'string', 'max' => 255],
            ['name','validateName','on'=>self::SCENARIO_ADD]
        ];
    }
    //自定义用户名验证规则
    public function validateName(){
        if(Menu::findOne(['name'=>$this->name])){
            $this->addError('name','已经存在的菜单名称');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'parent_id' => '上级菜单',
            'url' => '地址/路由',
            'sort' => '排序',
        ];
    }

    /**
     * @return \yii\rbac\Permission[]
     *
     * 链表查询出所有权限
     */

    public function getPermissions(){
         return $permissions = Yii::$app->authManager->getPermissions();
    }

    public function getMenus(){
        //查找数据库所有id=0的分类
         $menus = Menu::find()->where(['parent_id'=>0])->all();
         $menus = ArrayHelper::map($menus,'id','name');

         return ArrayHelper::merge(['0'=>'顶级分类'],$menus);

    }

}
