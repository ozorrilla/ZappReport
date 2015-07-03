<?php

require_once 'Variable.php';

/**
 * Count Class
 * 
 * Clase que representa las variables en el iReport.
 * 
 * @category  ZappReport
 * @package   Variable
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Count extends Variable {

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
        $this->value++;
    }

}
