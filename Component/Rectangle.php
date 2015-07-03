<?php

/**
 * Rectangle Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Rectangle extends GraphicProperty {

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Devuelve el valor en la propiedad radius.
     * 
     * @return float Propiedad radius.
     */
    public function getRadius()
    {
        return isset($this->data["radius"]) ? (float) $this->data["radius"] : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $borderStyle = $this->getLineStyle();
        $style = $this->opaque() == "Transparent" ? "D" : "DF";
        $radius = $this->getRadius();

        if ($this->height)
        {
            $height = $this->height;
            $this->height = 0;
        }
        else
        {
            $height = $this->height();
        }

        if ($radius > 0)
        {
            $report->RoundedRect($this->x() + $x, $this->y() + $y, $this->width(), $height, $radius, "1111", $style, $borderStyle, $this->backgroundColor());
        }
        else
        {
            $report->Rect($this->x() + $x, $this->y() + $y, $this->width(), $height, $style, array('all' => $borderStyle), $this->backgroundColor());
        }
    }

}
