<?php
namespace backend\models;


use yii\base\Model;

class RoleForm extends Model{
    public $name;
    public $description;
    public $permissions;
    const SCENARIO_ADD = 'add';

    public function rules()
    {
        return [
            ['name','required','message'=>'角色名称不能为空'],
            ['name','validateName','on'=>self::SCENARIO_ADD],
            ['permissions','safe'],
            ['description','required','message'=>'描述不能为空'],
        ];
    }

    public function validateName(){
        if(\Yii::$app->authManager->getRole($this->name)){
            $this->addError('name','角色名称已经存在!');
        }
    }

    public function attributeLabels()
    {
        return [
            'name'=>'角色名称',
            'description'=>'描述',
            'permissions'=>'权限名称',
        ];
    }

    /**
     * 静态方法返回权限表的所有数据
     */
    public static function getPermissionItems(){
        $permissions = \Yii::$app->authManager->getPermissions() ;
        $items = [];
        foreach ($permissions as $permission){
            $items[$permission->name] = $permission->description;
        }
        return $items;
    }

}