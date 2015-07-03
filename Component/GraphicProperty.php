<?php

/**
 * GraphicProperty Class
 * 
 * Clase para representar las propiedades comunes de los componentes graficos
 * Ellipse, Line y Rectangle.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
abstract class GraphicProperty extends Component {

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Devuelve el valor de la propiedad lineStyle.
     * 
     * @return string Propiedad lineStyle.
     */
    public function lineStyle()
    {
        return isset($this->data->graphicElement->pen["lineStyle"]) ? (string) $this->data->graphicElement->pen["lineStyle"] : "Solid";
    }

    /**
     * Devuelve el valor de la propiedad lineWidth.
     * 
     * @return float Propiedad lineWidth.
     */
    public function lineWidth()
    {
        return isset($this->data->graphicElement->pen["lineWidth"]) ? (float) $this->data->graphicElement->pen["lineWidth"] : 1.0;
    }

    /**
     * Devuelve el formato de linea compatible con FPDF.
     * 
     * @return array El Formato.
     */
    public function getLineStyle()
    {

        $color = $this->lineColor();

        if (!$color)
        {
            $color = $this->fontColor();
        }

        return array(
            'width' => $this->lineWidth(),
            'cap' => 'butt', 'join' => 'miter',
            'dash' => $this->getDash(),
            'color' => $color
        );
    }

    /**
     * Devuelve el valor en la propiedad lineColor.
     * 
     * @return string Propiedad lineColor.
     */
    public function lineColor()
    {
        return isset($this->data->graphicElement->pen["lineColor"]) ? (string) $this->data->graphicElement->pen["lineColor"] : NULL;
    }

    /**
     * Devuelve el estrellado de la linea.
     * 
     * @return string|int.
     */
    public function getDash()
    {
        switch ($this->lineStyle())
        {
            case "Dashed":
                return "5,3";
                break;
            case "Dotted":
                return "1";
                break;
            default:// Solid, Double
                return 0;
                break;
        }
    }

}
