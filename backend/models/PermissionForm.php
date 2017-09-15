<?php
namespace backend\models;

use yii\base\Model;

class PermissionForm extends Model{
    public $name;
    public $description;
    const SCENARIO_ADD = 'add';
    const SCENARIO_Edit = 'edit';

    public function rules()
    {
        return [
            ['name','required','message'=>'权限名称不能为空'],
            ['description','required','message'=>'描述不能为空'],
            ['name','validateName','on'=>self::SCENARIO_ADD],
            ['name','required','on'=>self::SCENARIO_Edit],
        ];
    }
    //添加时验证权限名称
    public function validateName(){
        if(\Yii::$app->authManager->getPermission($this->name)){
            $this->addError('name','权限已经存在');
        }
    }

    public function attributeLabels()
    {
        return [
            'name'=>'权限名称',
            'description' => '描述'
        ];
    }

}