<?php

require_once 'Variable.php';

/**
 * Highest Class
 * 
 * Clase que representa las variables en el iReport.
 * 
 * @category  ZappReport
 * @package   Variable
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Highest extends Variable {

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
        $value = (float) ZappReport::get_instance()->analyse($this->variableExpression(), $this->parse['variableExpression']);
        if ($value > $this->value)
        {
            $this->value = $value;
        }
    }

}
