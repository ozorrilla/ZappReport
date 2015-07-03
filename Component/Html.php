<?php

/**
 * Html Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Html extends Component {

    /**
     * Datos del XML.
     * @var \SimpleXMLElement
     */
    private $html;

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);

        $this->html = $data->children('hc', TRUE)->html;
        $this->parse['htmlContentExpression'] = ZappReport::parseExpression((string) $this->html->children('hc', TRUE)->htmlContentExpression);
    }

    /**
     * Evalua y devuelve el valor de la propiedad htmlContentExpression.
     * 
     * @return string Propiedad htmlContentExpression.
     */
    public function text()
    {
        $result = ZappReport::get_instance()->analyse((string) $this->html->children('hc', TRUE)->htmlContentExpression, $this->parse['htmlContentExpression']);

        return $result;
    }

    /**
     * Devuelve el valor en la propiedad horizontalAlign.
     * 
     * @return string Propiedad horizontalAlign.
     */
    public function taling()
    {
        return isset($this->html['horizontalAlign']) ? substr((string) $this->html['horizontalAlign'], 0, 1) : 'L';
    }

    /**
     * Devuelve el valor de la propiedad verticalAlignment.
     * @todo No implementado.
     * @return string Propiedad verticalAlignment.
     */
    public function valing()
    {
        return isset($this->html['verticalAlignment']) ? substr((string) $this->html['verticalAlignment'], 0, 1) : 'L';
    }

    /**
     * Devuelve el valor de la propiedad scaleType.
     * @todo No implementado.
     * @return string Propiedad scaleType.
     */
    public function scaleType()
    {
        return (string) $this->html['scaleType'];
    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $height = $this->height();

        //background
        if ($this->opaque() == "Opaque")
        {
            $bgcolor = $this->backgroundColor();
            $report->SetFillColor($bgcolor[0], $bgcolor[1], $bgcolor[2]);
            $fill = TRUE;
        }
        else
        {
            $fill = FALSE;
        }

        $report->MultiCell($this->width(), $this->height(), $report->WriteHTML($this->text()), 0, $this->taling(), $fill);
    }

}
