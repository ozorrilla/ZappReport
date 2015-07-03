<?php

require_once 'Variable.php';

/**
 * Sum Class
 * 
 * Clase que representa las variables en el iReport.
 * 
 * @category  ZappReport
 * @package   Variable
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Sum extends Variable {

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
        $this->value += (float) ZappReport::get_instance()->analyse($this->variableExpression(), $this->parse['variableExpression']);
    }

}
