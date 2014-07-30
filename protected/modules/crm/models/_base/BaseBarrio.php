<?php

/**
 * This is the model base class for the table "barrio".
 * DO NOT MODIFY THIS FILE! It is automatically generated by AweCrud.
 * If any changes are necessary, you must set or override the required
 * property or method in class "Barrio".
 *
 * Columns in table "barrio" available as properties of the model,
 * and there are no model relations.
 *
 * @property integer $id
 * @property string $nombre
 * @property integer $parroquia_id
 *
 */
abstract class BaseBarrio extends AweActiveRecord {

    public static function model($className=__CLASS__) {
        return parent::model($className);
    }

    public function tableName() {
        return 'barrio';
    }

    public static function representingColumn() {
        return 'nombre';
    }

    public function rules() {
        return array(
            array('nombre, parroquia_id', 'required'),
            array('parroquia_id', 'numerical', 'integerOnly'=>true),
            array('nombre', 'length', 'max'=>45),
            array('id, nombre, parroquia_id', 'safe', 'on'=>'search'),
        );
    }

    public function relations() {
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
                'id' => Yii::t('app', 'ID'),
                'nombre' => Yii::t('app', 'Nombre'),
                'parroquia_id' => Yii::t('app', 'Parroquia'),
        );
    }

    public function search() {
        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('nombre', $this->nombre, true);
        $criteria->compare('parroquia_id', $this->parroquia_id);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function behaviors() {
        return array_merge(array(
        ), parent::behaviors());
    }
}