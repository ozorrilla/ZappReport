<?php

/**
 * StaticText Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class StaticText extends TextProperty {

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Devuelve el valor en la propiedad text
     * 
     * @return string Propiedad text
     */
    public function text()
    {
        return utf8_decode(ZappReport::get_instance()->lang((string) $this->data->text));
    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $fill = $this->style();

        $report->SetXY($x + $this->x(), $y + $this->y());
        $report->MultiCell($this->width(), $this->height(), $this->text(), 0, $this->taling(), $fill);
    }

}
