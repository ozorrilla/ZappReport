<?php

/**
 * Image Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Image extends Component {

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->parse['imageExpression'] = ZappReport::parseExpression($this->imageExpression());
    }

    /**
     * Devuelve el valor en la propiedad imageExpression.
     * 
     * @return string Propiedad imageExpression.
     */
    public function imageExpression()
    {
        return (string) $this->data->imageExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $height = $this->height();

        $report->Image($report->getPath() . $report->analyse($this->imageExpression(), $this->parse['imageExpression']), $this->x() + $x, $this->y() + $y, $this->width(), $height);
    }

}
