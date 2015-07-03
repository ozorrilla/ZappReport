<?php

/**
 * Line Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Line extends GraphicProperty {

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Devuelve TRUE o FALSE si la propiedad direction si ha sido cambiada.
     *  
     * @return boolean TRUE o FALSE
     */
    public function directionBottomUp()
    {
        return isset($this->data['direction']);
    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $height = $this->height();

        $x1 = $this->x() + $x;
        $y1 = $this->y() + $y;

        $x2 = $x1 + $this->width() + ($this->width() == 1 ? -1 : 0);
        $y2 = $height == 1 ? $y1 : $y1 + $height;

        if ($this->directionBottomUp())
        {
            $report->Line($x1, $y1, $x2, $y2, $this->getLineStyle());
            if ($this->lineStyle() == 'Double')
            {
                $report->Line($x1, $y1 + 2, $x2, $y2 + 2, $this->getLineStyle());
            }
        }
        else
        {
            $report->Line($x1, $y1, $x2, $y2, $this->getLineStyle());
            if ($this->lineStyle() == 'Double')
            {
                $report->Line($x1, $y1 + 2, $x2, $y2 + 2, $this->getLineStyle());
            }
        }
        if ($height == 1)
        {
            $report->y += 1;
        }
    }

}
