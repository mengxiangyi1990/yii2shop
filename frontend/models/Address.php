<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "address".
 *
 * @property integer $id
 * @property string $name
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $address
 * @property integer $tel
 * @property integer $status
 */
class Address extends \yii\db\ActiveRecord
{
    //public $id = '';
    const SCENARIO_ADD= 'add';


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tel'], 'integer'],
            [['status'], 'string'],
            [['member_id'], 'integer'],
            //['sms','validateSms','message'=>'手机验证码错误'],
            [['name', 'address'], 'string', 'max' => 255],
            [['name', 'address'], 'string', 'max' => 255],
            [['province', 'city', 'area'], 'string', 'max' => 20],
            ['name', 'required', 'on'=>self::SCENARIO_ADD,'message'=>'名字 必须添写'],
            ['address', 'required', 'on'=>self::SCENARIO_ADD,'message'=>'详细地址 必须添写'],
            ['tel', 'required', 'on'=>self::SCENARIO_ADD,'message'=>'联系电话 必须添写'],
            ['province', 'required', 'on'=>self::SCENARIO_ADD,'message'=>'省份 必须添写'],
            [ 'city',  'required', 'on'=>self::SCENARIO_ADD,'message'=>'市级 必须添写'],
            ['area',  'required', 'on'=>self::SCENARIO_ADD,'message'=>'县/乡 必须添写'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '收货人',
            'province' => '省',
            'city' => '市',
            'area' => '县',
            'address' => '详细地址',
            'tel' => '电话号码',
            'status' => '(0默认地址,1不是)',
        ];
    }
}
