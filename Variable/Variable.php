<?php

/**
 * Variable Class
 * 
 * Clase que representa las variables en el iReport.
 * 
 * @category  ZappReport
 * @package   Variable
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Variable {

    /**
     * Datos del XML.
     * 
     * @var \SimpleXMLElement. 
     */
    protected $data = NULL;

    /**
     * Contiene el valor que va adquiriendo la variable.
     * 
     * @var mixed 
     */
    protected $value;

    /**
     * Contiene todas las expresiones del objeto parseadas.
     * @var array 
     */
    public $parse = array();

    /**
     * Constructor.
     * 
     * @param type $data Los datos del xml.
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->parse['variableExpression'] = ZappReport::parseExpression($this->variableExpression());
        $this->parse['initialValue'] = ZappReport::parseExpression($this->initialValue());
    }

    /**
     * Devuelve el valor en la property resetGroup.
     * 
     * @return string Property resetGroup
     */
    public function resetGroup()
    {
        return (string) $this->data['resetGroup'];
    }

    /**
     * Devuelve el valor en la property resetType.
     * 
     * @return string Property resetType sino Report.
     */
    public function resetType()
    {
        return isset($this->data['resetType']) ? (string) $this->data['resetType'] : 'Report';
    }

    /**
     * Devuelve el valor en la property name.
     * 
     * @return string Property name.
     */
    public function name()
    {
        return (string) $this->data['name'];
    }

    /**
     * Devuelve el valor en la property initialValueExpression.
     * 
     * @return string Property initialValueExpression.
     */
    public function initialValue()
    {
        return (string) $this->data->initialValueExpression;
    }

    /**
     * Devuelve el valor de la property calculation. Tipos(Nothing, Count, 
     * Distint Count, Sum, Lowest, Highest, Average).
     * 
     * @return string Property calculation.
     */
    public function calculation()
    {
        return (string) $this->data['calculation'];
    }

    /**
     * Devuelve el valor de la property variableExpression.
     * 
     * @return string Property variableExpression.
     */
    public function variableExpression()
    {
        return (string) $this->data->variableExpression;
    }

    /**
     * Devuelve el valor de la variable.
     * 
     * @return mixed Resultado del calculo.
     */
    public function value()
    {
        return $this->value;
    }

    /**
     * Cambia el valor de value.
     * 
     * @param mixed $value El nuevo valor.
     * @return void
     */
    public function setVaue($value)
    {
        $this->value = $value;
    }

    /**
     * Reinicia el valor de value.
     * @return void 
     */
    public function reset()
    {
        $this->value = 0;
    }

    /**
     * Evalua la variable segun su expresion y actualiza value.
     * 
     * @return void
     */
    public function evaluate()
    {
        $this->value = ZappReport::get_instance()->analyse($this->variableExpression(), $this->parse['variableExpression']);
    }

    /**
     * Evalua la variable utilizando la property initialValueExpression.
     * 
     * @return void
     */
    public function initialEvaluation()
    {
        $this->value = ZappReport::get_instance()->analyse($this->initialValue(), $this->parse['initialValue']);
    }

}
