<?php

/**
 * Component Class
 * 
 * Esta clase representa las propiedades comunes de los componentes.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
abstract class Component {

    /**
     * Datos del XML.
     * @var \SimpleXMLElement 
     */
    protected $data = NULL;

    /**
     * Cuando esta propiedad adquiere valor el height del xml no se toma en cuenta.
     * @var float 
     */
    protected $height = 0;

    /**
     * Contiene todas las expresiones del objeto parseadas.
     * @var array 
     */
    public $parse = array();

    /**
     * Constuctor.
     * 
     * @param type $xml
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->parse['printWhenExpression'] = ZappReport::parseExpression((string) $this->data->reportElement->printWhenExpression);
    }

    /**
     * Devuelve el valor en la propiedad x.
     * 
     * @return float Propiedad x.
     */
    public function x()
    {
        return (float) $this->data->reportElement['x'];
    }

    /**
     * Devuelve el valor en la propiedad y.
     * 
     * @return float Propiedad y.
     */
    public function y()
    {
        return (float) $this->data->reportElement['y'];
    }

    /**
     * Devuelve el valor en la propiedad width.
     * 
     * @return float Propiedad width.
     */
    public function width()
    {
        return (float) $this->data->reportElement['width'];
    }

    /**
     * Devuelve el valor en la propiedad height.
     * 
     * @return float Propiedad height.
     */
    public function height()
    {
        return (float) $this->data->reportElement['height'];
    }

    /**
     * Devuelve el valor en la propiedad forecolor, convertido a RGB.
     * 
     * @return array El color RGB de la letra.
     */
    public function fontColor()
    {
        return isset($this->data->reportElement["forecolor"]) ? $this->toColor((string) $this->data->reportElement["forecolor"]) : array(0, 0, 0);
    }

    /**
     * Devuelve el valor en la propiedad backcolor, convertido a RGB.
     * 
     * @return array  El color RGB del fondo.
     */
    public function backgroundColor()
    {
        return isset($this->data->reportElement["backcolor"]) ? $this->toColor((string) $this->data->reportElement["backcolor"]) : array(255, 255, 255);
    }

    /**
     * Devuelve el valor en la propiedad mode, indica si usara relleno de fondo.
     * 
     * @return string Propiedad mode.
     */
    public function opaque()
    {
        return $this->data->reportElement["mode"];
    }

    /**
     * Devuelve el valor de la propiedad positionType. Los tres valores posibles 
     * son: FixRelativeToBottom, FixRelativeToTop, Float.
     * 
     * @return string Propiedad positionType.
     */
    public function positionType()
    {
        return isset($this->data->reportElement["positionType"]) ? $this->data->reportElement["positionType"] : 'Float';
    }

    /**
     * Devuelve el valor en la propiedad stretchType.
     * 
     * @return string Propiedad stretchType.
     */
    public function stretchType()
    {
        return (string) $this->data->reportElement["stretchType"];
    }

    /**
     * Evalua la expresion que determina si el componente puede renderearse.
     * 
     * @return boolean TRUE o FALSE.
     */
    public function printWhenExpression()
    {
        return isset($this->data->reportElement->printWhenExpression) ? (boolean) ZappReport::get_instance()->analyse((string) $this->data->reportElement->printWhenExpression, $this->parse['printWhenExpression']) : TRUE;
    }

    /**
     * Permite clonar el objeto, eliminando las referencias.
     * @return void 
     */
    function __clone()
    {
        foreach ($this as $name => $value)
        {
            if (gettype($value) == 'object')
            {
                $this->$name = clone($this->$name);
            }
        }
    }

    /**
     * Obtiene la propiedad uuid del componente.
     * 
     * @return string Propiedad uuid.
     */
    public function uuid()
    {
        return (string) $this->data->reportElement['uuid'];
    }

    /**
     * Convierte un color hexadecimal a RGB.
     * 
     * @param string $color El color en hexadecimal.
     * @return array El color en RGB.
     */
    public function toColor($color)
    {
        return array(hexdec(substr($color, 1, 2)), hexdec(substr($color, 3, 2)), hexdec(substr($color, 5, 2)));
    }

    /**
     * Cambia el valor del atributo $height.
     * 
     * @param float $height El nuevo valor.
     * @return void
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * Este metodo es implementado por cada componente que hereda esta clase.
     * 
     * @param int $x La posicion en el eje x.
     * @param int $y La posicion en el eje y.
     */
    abstract public function render($x, $y);
}
