<?php

require_once 'Variable.php';

/**
 * Average Class
 * 
 * Clase que representa las variables en el iReport.
 * 
 * @category  ZappReport
 * @package   Variable
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Average extends Variable {

    /**
     * Contador.
     * @var int 
     */
    private $count = 0;

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
        $this->count++;

        $this->value += (float) ZappReport::get_instance()->analyse($this->variableExpression(), $this->parse['variableExpression']);
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->value = 0;
        $this->count = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function value()
    {
        return $this->value == 0 || $this->count == 0 ? 0 : $this->value / $this->count;
    }

}
