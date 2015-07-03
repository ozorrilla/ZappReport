<?php

/**
 * Property Class
 * 
 * Clase que representa las propiedades del reporte.
 * 
 * @category  ZappReport
 * @package   Core
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Property {

    /**
     * Nombre del reporte.
     * @var string 
     */
    public $name;

    /**
     * Ancho de la pagina.
     * @var int 
     */
    public $pageWidth;

    /**
     * Alto de la pagina.
     * @var int 
     */
    public $pageHeight;

    /**
     * Orientacion de la pagina.
     * @var string 
     */
    public $orientation;

    /**
     * Margen izquierdo de la pagina.
     * @var int 
     */
    public $leftMargin;

    /**
     * Margen derecho de la pagina.
     * @var int 
     */
    public $rightMargin;

    /**
     * Margen en la parte superios de la pagina. 
     * @var int
     */
    public $topMargin;

    /**
     * Margen en la parte inferior de la pagina.
     * @var int
     */
    public $bottomMargin;

    /**
     * Unidad de medida del reporte.
     * @var string 
     */
    public $unit = "pt";

    /**
     * Formato de la pagina.
     * @var string 
     */
    public $format = "A4";

    /**
     * Formato de los caracteres.
     * @var boolean 
     */
    public $unicode = TRUE;

    /**
     * Codificacion.
     * @var string 
     */
    public $encoding = "UTF-8";

    /**
     * Constructor.
     * 
     * @param \SimpleXmlElment $xml XML.
     * @return void
     */
    public function __construct($xml)
    {
        $this->name = (string) $xml["name"];
        $this->orientation = isset($xml["orientation"]) ? substr((string) $xml["orientation"], 0, 1) : 'P';
        $this->pageWidth = (string) $xml["pageWidth"];
        $this->pageHeight = (string) $xml["pageHeight"];
        $this->leftMargin = (string) $xml["leftMargin"];
        $this->rightMargin = (string) $xml["rightMargin"];
        $this->topMargin = (string) $xml["topMargin"];
        $this->bottomMargin = (string) $xml["bottomMargin"];
        $this->columnWidth = (string) $xml["columnWidth"];
    }

}
