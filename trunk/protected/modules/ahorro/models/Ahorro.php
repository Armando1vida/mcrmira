<?php

Yii::import('ahorro.models._base.BaseAhorro');

class Ahorro extends BaseAhorro {

    //    estado:DEUDA,PAGADO
    const ESTADO_DEUDA = 'DEUDA';
    const ESTADO_PAGADO = 'PAGADO';
//    tipo:OBLIGATORIO, VOLUNTARIO, PRIMER_PAGO
    const TIPO_OBLIGATORIO = 'OBLIGATORIO';
    const TIPO_VOLUNTARIO = 'VOLUNTARIO';
    const TIPO_PRIMER_PAGO = 'PRIMER_PAGO';
    //Valor a pagar por registro en la mancomunidad
    const VALOR_REGISTRO = 70;
    //anulacion
    const ANULADO_SI = 'SI';
    const ANULADO_NO = 'NO';
    //descripciones
    const DESCRIPCION_CANTIDAD_EXTRA = 'Ahorro Voluntario creado por cantidad sobrante en un depósito';
    const DESCRIPCION_CANTIDAD_EXTRA_CREDITO = 'Ahorro Voluntario creado por cantidad sobrante en un depósito de crédito';

    public $cantidad_extra;

    /**
     * @return Ahorro
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public static function label($n = 1) {
        return Yii::t('app', 'Ahorro|Ahorros', $n);
    }

    public function relations() {
        return array_merge(parent::relations(), array(
//            'ahorroDepositoVoluntario' => array(self::HAS_ONE, 'AhorroDeposito', 'ahorro_id'),
            'socio' => array(self::BELONGS_TO, 'Persona', 'socio_id'),
        ));
    }

    public function rules() {
        return array_merge(parent::rules(), array(
            array('cantidad', 'numerical', 'min' => 1, 'tooSmall' => 'La cantidad debe ser mayor a 0'),
            array('cantidad', 'existPagoObligatorio', 'on' => 'create'),
            array('cantidad', 'cantidadMayor10PagoObligatorio'),
//            array('tipo', 'unique', 'criteria' => array(
//                    'condition' => 'socio_id=:socio_id',
//                    'params' => array(
//                        ':socio_id' => $this->socio_id
//                    )
//                ), 'on' => 'insert'),
                )
        );
    }

    public function de_tipo($tipo) {
        $this->getDbCriteria()->mergeWith(
                array(
                    'condition' => 't.tipo = :tipo',
                    'params' => array(
                        ':tipo' => $tipo
                    ),
                )
        );
        return $this;
    }

    public function de_socio($socio_id) {
        $this->getDbCriteria()->mergeWith(
                array(
                    'condition' => 't.socio_id = :socio_id',
                    'params' => array(
                        ':socio_id' => $socio_id
                    ),
                )
        );
        return $this;
    }

    public function de_cliente_obligatorio($id_socio) {
        $this->getDbCriteria()->mergeWith(
                array(
                    'condition' => 'socio_id = :socio_id AND tipo=:tipo',
                    'params' => array(
                        ':socio_id' => $id_socio,
                        ':tipo' => self::TIPO_OBLIGATORIO
                    ),
                )
        );
        return $this;
    }

    public function de_cliente_voluntario($id_socio) {
        $this->getDbCriteria()->mergeWith(
                array(
                    'condition' => 'socio_id = :socio_id AND tipo=:tipo',
                    'params' => array(
                        ':socio_id' => $id_socio,
                        ':tipo' => self::TIPO_VOLUNTARIO
                    ),
                )
        );
        return $this->findAll();
    }

    public function socioAhorroVoluntarioTotal($id_socio) {
//        select sum(cantidad) from ahorro where socio_id=2 and tipo='VOLUNTARIO' and anulado='NO'
        $command = Yii::app()->db->createCommand()
                ->select('sum(cantidad)as total')
                ->from('ahorro ')
                ->where(array('and', 'socio_id=:id_socio', 'tipo=:tipo', 'anulado=:anulado'));
        $command->params = array('id_socio' => $id_socio, 'tipo' => self::TIPO_VOLUNTARIO, 'anulado' => self::ANULADO_NO);

        $return = $command->queryAll();

        return $return[0]['total'];
    }

    public function socioAhorroObligatorioTotal($id_socio) {
//        select sum(saldo_favor) from ahorro where socio_id=2 and tipo='OBLIGATORIO' and anulado='SI'
        $command = Yii::app()->db->createCommand()
                ->select('sum(saldo_favor)as total')
                ->from('ahorro ')
                ->where(array('and', 'socio_id=:id_socio', 'tipo=:tipo', 'anulado=:anulado'));
        $command->params = array('id_socio' => $id_socio, 'tipo' => self::TIPO_OBLIGATORIO, 'anulado' => self::ANULADO_NO);

        $return = $command->queryAll();

        return $return[0]['total'];
    }

    /*
     * devuelve los ahorros voluntarios con su respectovo saldo a favor de un cleinte 
     */

    public function socioAhorrosVoluntarios($id_socio) {
//        select id,saldo_favor from ahorro where socio_id=2 and tipo='VOLUNTARIO' and anulado=0 
        $command = Yii::app()->db->createCommand()
                ->select('id,cantidad')
                ->from('ahorro ')
                ->where(array('and', 'socio_id=:id_socio', 'tipo=:tipo', 'anulado=:anulado'));
        $command->params = array('id_socio' => $id_socio, 'tipo' => self::TIPO_VOLUNTARIO, 'anulado' => self::ANULADO_NO);

        $return = $command->queryAll();

        return $return;
    }

    public function socioAhorrosObligatorios($id_socio) {
//        select id,saldo_favor from ahorro where socio_id=2 and tipo='VOLUNTARIO' and anulado=0 
        $command = Yii::app()->db->createCommand()
                ->select('id,saldo_favor')
                ->from('ahorro ')
                ->where(array('and', 'socio_id=:id_socio', 'tipo=:tipo', 'anulado=:anulado'));
        $command->params = array('id_socio' => $id_socio, 'tipo' => self::TIPO_OBLIGATORIO, 'anulado' => self::ANULADO_NO);

        $return = $command->queryAll();

        return $return;
    }

    public function setAnuladoVoluntario($id, $cantidad = NULL) {
        $toUpdate = array();



//        $toUpdate = array('cantidad' => $cantidad, 'saldo_favor' => $cantidad);
        $toUpdate = array('saldo_contra' => $cant1, 'saldo_favor' => $cantidad);
//
        $command = Yii::app()->db->createCommand()
                ->update('ahorro', $toUpdate, "id=:id", array(':id' => $id));


        return $command == 1 ? true : false;
    }

    public function setAnuladoObligatorio($id, $cantidad = NULL) {
        $toUpdate = array();
        $cant = Yii::app()->db->createCommand()->select('cantidad,id')->from('ahorro')->where('id=:id', array('id' => $id));
        $cant1 = $cant->queryAll();
        $cant1 = floatval($cant1['0']['cantidad']) - $cantidad;
//        if ($cantidad) {
        $toUpdate = array('saldo_contra' => $cant1, 'saldo_favor' => $cantidad);
//        } else {
//            $toUpdate = array('anulado' => self::ANULADO_SI);
//        }


        $command = Yii::app()->db->createCommand()
                ->update('ahorro', $toUpdate, "id=:id", array(':id' => $id));

        return $command == 1 ? true : false;
    }

    public static function fechaMes($id_cliente) {
        $mes = date("m") + 0;
        $meses = Util::obtenerMeses();
        $año = date("Y");
        return "C_" . $id_cliente . "_" . $meses[$mes - 1] . "_" . $año;
    }

    public function existPagoObligatorio($attribute, $params) {
        //    SELECT count(*)as pago FROM  `ahorro` 
//    WHERE  `socio_id` =1 AND  `tipo` =  'OBLIGATORIO' AND  YEAR(`fecha`) = YEAR('2014-09-13') and MONTH (`fecha`) =MONTH ('2014-09-13')
        $command = Yii::app()->db->createCommand()->select('count(*) as pago')
                ->from('ahorro')
                ->where('socio_id =:socio_id AND  tipo = :tipo AND  YEAR(fecha) = YEAR(:fecha) and MONTH (fecha) = MONTH (:fecha)', array(':socio_id' => $this->socio_id, ':tipo' => self::TIPO_OBLIGATORIO, ':fecha' => $this->fecha)
        );
        $command = $command->queryAll();

        $validator = $command['0']['pago'] > 0;

        if ($validator && $this->tipo == self::TIPO_OBLIGATORIO) {
            $this->addError($attribute, 'Ya existe un pago obligatorio para este mes.');
        }
    }

    public function cantidadMayor10PagoObligatorio($attribute, $params) {
        if ($this->cantidad > 10 && $this->tipo == self::TIPO_OBLIGATORIO) {
            $this->addError($attribute, 'La cantidad sobrepasa lo establecido para un ahorro obligatorio');
        }
    }

    /*     * *Consultas para dashboard* */

    public function getTotalAhorros_Obligatorios_y_Primer_Pago() {
//        select sum(ad.cantidad) from ahorro a
//        inner join ahorro_deposito ad on a.id = ad.ahorro_id
//        where a.tipo = 'OBLIGATORIO' or a.tipo = 'PRIMER_PAGO';

        $command = Yii::app()->db->createCommand()
                ->select('sum(ad.cantidad)as total')
                ->from('ahorro a')
                ->join('ahorro_deposito ad', 'a.id = ad.ahorro_id')
                ->where('a.tipo =:tipo_obligatorio or a.tipo =:tipo_preimer_pago'
                , array(':tipo_obligatorio' => self::TIPO_OBLIGATORIO, ':tipo_preimer_pago' => self::TIPO_PRIMER_PAGO));
        $result = $command->queryAll();
        return $result[0]['total'] ? $result[0]['total'] : 0;
    }

    public function getTotalAhorros_Voluntarios() {
//        select sum(saldo_favor) as total from ahorro
//        where tipo = 'VOLUNTARIO'


        $command = Yii::app()->db->createCommand()
                ->select('sum(saldo_favor) as total')
                ->from('ahorro')
                ->where('tipo =:tipo_voluntario'
                , array(':tipo_voluntario' => self::TIPO_VOLUNTARIO));
        $result = $command->queryAll();
        return $result[0]['total'] ? $result[0]['total'] : 0;
    }

    public function getTotalAhorros_extras() {
//        select sum(ae.cantidad) from ahorro a
//        inner join ahorro_extra ae on a.id = ae.ahorro_id
//        where ae.anulado = 'NO'


        $command = Yii::app()->db->createCommand()
                ->select('sum(ae.cantidad) as total')
                ->from('ahorro a')
                ->join('ahorro_extra ae', 'a.id = ae.ahorro_id')
                ->where('ae.anulado =:anulado_no'
                , array(':anulado_no' => self::ANULADO_NO));
        $result = $command->queryAll();
        return $result[0]['total'] ? $result[0]['total'] : 0;
    }

    public function getTotalAhorros_Deuda() {
//        select sum(a.saldo_contra) from ahorro a
//        where a.tipo = 'OBLIGATORIO' or a.tipo = 'PRIMER_PAGO'
//        and estado = 'DEUDA' and a.anulado='NO';


        $command = Yii::app()->db->createCommand()
                ->select('sum(a.saldo_contra) as total')
                ->from('ahorro a')
                ->where('a.anulado =:anulado_no and a.tipo =:tipo_obligatorio or a.tipo=:tipo_preimer_pago'
                , array(':anulado_no' => self::ANULADO_NO, ':tipo_obligatorio' => self::TIPO_OBLIGATORIO, ':tipo_preimer_pago' => self::TIPO_PRIMER_PAGO));
        $result = $command->queryAll();
        return $result[0]['total'] ? $result[0]['total'] : 0;
    }

}
