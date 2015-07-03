<?php

/**
 * Group Class
 * 
 * Clase que representa los grupos en el reporte.
 * 
 * @category  ZappReport
 * @package   Band
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Group {

    /**
     * Nombre del grupo.
     * @var string 
     */
    private $name;

    /**
     * La expresion del grupo.
     * @var string 
     */
    private $groupExpression;

    /**
     * Si debe comenzar en una nueva pagina.
     * @todo No implementada.
     * @var boolean
     */
    private $isStartNewPage;

    /**
     * Si resetea el numero de pagina.
     * @todo No implementado.
     * @var boolean
     */
    private $isResetPageNumber;

    /**
     * Si se debe dibujar el header en cada pagina.
     * @todo No implementado.
     * @var boolean
     */
    private $isReprintHeaderOnEachPage;

    /**
     * Banda que representa los componentes del header.
     * @var Band 
     */
    private $groupHeader = NULL;

    /**
     * Banda que representa los componentes del footer.
     * @var Band 
     */
    private $groupFooter = NULL;

    /**
     * Guarda los valores del header y del footer despues de ser evaluado.
     * @var array 
     */
    private $value = array('header' => NULL, 'footer' => NULL);

    /**
     * Contiene todas las expresiones del objeto parseadas.
     * @var array 
     */
    public $parse = array();

    /**
     * Construtor.
     * 
     * @param \SimpleXmlElment $data.
     * @return void
     */
    public function __construct($data)
    {
        //attribute - simple
        $this->name = (string) $data['name'];
        $this->isStartNewPage = (boolean) $data['isStartNewPage'];
        $this->isResetPageNumber = (boolean) $data['isResetPageNumber'];
        $this->isReprintHeaderOnEachPage = (boolean) $data['isReprintHeaderOnEachPage'];
        $this->groupExpression = isset($data->groupExpression) ? (string) $data->groupExpression : NULL;

        $this->parse['groupExpression'] = ZappReport::parseExpression($this->groupExpression);

        //attribute - object
        if ($data->groupHeader && count($data->groupHeader->band->children()) > 0)
        {
            $this->groupHeader = new Band($data->groupHeader->band);
        }
        if ($data->groupFooter && count($data->groupFooter->band->children()) > 0)
        {
            $this->groupFooter = new Band($data->groupFooter->band);
        }
    }

    /**
     * Devuelve el nombre del grupo.
     * 
     * @return string El nombre.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Devuelve la expresion del grupo.
     * 
     * @return string La expresion.
     */
    public function groupExpression()
    {
        return $this->groupExpression;
    }

    /**
     * Devuelve el valor en la propiedad StartNewPage.
     * 
     * @return boolean Propiedad StartNewPage.
     */
    public function isStartNewPage()
    {
        return $this->isStartNewPage;
    }

    /**
     * Devuelve el valor en la propiedad ResetPageNumber.
     * 
     * @return boolean Propiedad ResetPageNumber.
     */
    public function isResetPageNumber()
    {
        return $this->isResetPageNumber;
    }

    /**
     * Devuelve el valor en la propiedad ReprintHeaderOnEachPage.
     * 
     * @return boolean Propiedad ReprintHeaderOnEachPage.
     */
    public function isReprintHeaderOnEachPage()
    {
        return $this->isReprintHeaderOnEachPage;
    }

    /**
     * Devuelve la banda asociada al groupHeader.
     * 
     * @return Band La banda.
     */
    public function header()
    {
        return $this->groupHeader;
    }

    /**
     * Devuelve la banda asociada al groupFooter.
     * 
     * @return Band La banda.
     */
    public function footer()
    {
        return $this->groupFooter;
    }

    /**
     * Devuelve el valor del grupo pasado por parametro.
     * 
     * @param string $name El nombre del grupo (header o footer).
     * @return mixed El valor del grupo.
     */
    public function value($name)
    {
        return $this->value[$name];
    }

    /**
     * Cambia el valor en grupo pasado por parametro.
     * 
     * @param string $name El nombre del grupo (header o footer).
     * @param mixed $value El nuevo valor.
     * @return void
     */
    public function setValue($name, $value)
    {
        $this->value[$name] = $value;
    }

    /**
     * Renderea el grupo pasado por parametro.
     * 
     * @param string $name El nombre del grupo (header o footer).
     * @return void
     */
    public function render($name)
    {
        $group = $this->$name();

        if ($group)
        {
            $report = ZappReport::get_instance();
            $report->activeGroup = $group;
            $report->pstate = 'group';
            $group->render();
            $report->pstate = 'detail';
        }
    }

}
