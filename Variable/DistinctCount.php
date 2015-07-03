<?php

require_once 'Variable.php';

/**
 * DistinctCount Class
 * 
 * Clase que representa las variables en el iReport.
 * 
 * @category  ZappReport
 * @package   Variable
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class DistinctCount extends Variable {

    public $rawData = array();

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate()
    {
        $value = ZappReport::get_instance()->analyse($this->variableExpression(), $this->parse['variableExpression']);
        if (!isset($this->rawData[$value]))
        {
            $this->rawData[(string) $value] = TRUE;
            $this->value++;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->rawData = array();
        $this->value = 0;
    }

}
