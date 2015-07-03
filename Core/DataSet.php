<?php

/**
 * DataSet Class
 * 
 * Clase que representa los dataset en el iReport.
 * 
 * @category  ZappReport
 * @package   Core
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class DataSet {

    /**
     * Nombre del dataSet.
     * @var string 
     */
    private $name = '';

    /**
     * Datos del dataSet.
     * @var array 
     */
    private $data = array();

    /**
     * Constructor.
     * 
     * @param string $name Nombre del dataSet.
     * @param array $data Datos del dataSet.
     */
    public function __construct($name, $data)
    {
        $this->name = $name;
        $this->data = $data;
    }

    /**
     * Devuelve el nombre del dataSet.
     * 
     * @return string El nombre.
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Devuelve los datos del dataSet.
     * 
     * @return array Los datos.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Devuelve los datos del dataSet en esa posicion.
     * 
     * @param int $index El indice.
     * @return mixed Los datos en index.
     */
    public function getDataInIndex($index)
    {
        return isset($this->data[$index]) ? $this->data[$index] : NULL;
    }

}
