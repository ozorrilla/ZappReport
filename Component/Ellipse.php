<?php

/**
 * Ellipse Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Ellipse extends GraphicProperty {

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
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $height = $this->height();

        $report->Ellipse($this->x() + $x + ($this->width() / 2), $this->y() + $y + ( $height / 2), $this->width() / 2, $height / 2, 0, 0, 360, $this->opaque() == "Transparent" ? "D" : "DF", $this->getLineStyle(), $this->backgroundColor());
    }

}
