<?php

require_once 'HFPDF.php';

require_once 'Variable/Average.php';
require_once 'Variable/Count.php';
require_once 'Variable/Sum.php';
require_once 'Variable/Lowest.php';
require_once 'Variable/Highest.php';
require_once 'Variable/DistinctCount.php';

require_once 'Core/Property.php';
require_once 'Core/DataSet.php';

require_once 'Band/Band.php';
require_once 'Band/Group.php';

require_once 'Component/Component.php';
require_once 'Component/GraphicProperty.php';
require_once 'Component/TextProperty.php';
require_once 'Component/CList.php';
require_once 'Component/Ellipse.php';
require_once 'Component/Frame.php';
require_once 'Component/Html.php';
require_once 'Component/Image.php';
require_once 'Component/Line.php';
require_once 'Component/Rectangle.php';
require_once 'Component/StaticText.php';
require_once 'Component/TextField.php';

/**
 * ZappReport Class
 *
 * Clase dedicada a manejar reportes y la forma que se muestran sus datos.
 *
 * @category  ZappReport
 * @package   ZappReport
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class ZappReport extends HFPDF
{

    /**
     * Ruta del fichero jrxml.
     * @var string
     */
    private $path = "";

    /**
     * Contine el objeto jrxml del reporte.
     * @var SimpleXMLElement
     */
    public $xml;

    /**
     * Datos a mostrar en el repote.
     * @var mixed
     */
    public $values;

    /**
     * El total de elementos que hay en data.
     * @var int
     */
    public $total;

    /**
     * Parametros usados en el reporte.
     * @var array
     */
    public $parameter = array();

    /**
     * Contiene las variables definidas para el reporte.
     * @var array
     */
    public $variables = array();

    /**
     * Banda Background.
     * @var Band
     */
    public $background = NULL;

    /**
     * Banda Title.
     * @var Band
     */
    public $btittle = NULL;

    /**
     * Banda PageHeader.
     * @var Band
     */
    public $pageheader = NULL;

    /**
     * Banda ColumnHeader.
     * @var Band
     */
    public $columnheader = NULL;

    /**
     * Banda Detail.
     * @var Band
     */
    public $detail = NULL;

    /**
     * Banda ColumnFooter.
     * @var Band
     */
    public $columnfooter = NULL;

    /**
     * Banda PageFooter.
     * @var Band
     */
    public $pagefooter = NULL;

    /**
     * Banda Summary.
     * @var Band
     */
    public $summary = NULL;

    /**
     * Lista de Grupos.
     * @var Group
     */
    public $groups = array();

    /**
     * Banda en la que esta el reporte.
     * @var type string
     */
    public $pstate = '';

    /**
     * Indica si se puede evaluar los compontentes donde time = page.
     * @var boolean
     */
    public $readyPage = FALSE;

    /**
     * Contiene todas las variables que seran evaluadas al final de la pagina.
     * @var array
     */
    public $evaluatePage = array();

    /**
     * Regular expressions for variables iReport map.
     * @var array
     */
    public $varexpr = array('/\$V\{PAGE_NUMBER\}/', '/\$V\{REPORT_COUNT\}/', '/\$V\{PAGE_COUNT\}/');

    /**
     * Posicion del puntero que recorre los datos.
     * @var type int
     */
    public $REPORT_COUNT = 0;

    /**
     * Guarda el crecimiento de la banda que se ejecuta.
     * @var int
     */
    public $bandGrow = 0;

    /**
     * Indica si debe ser mostrado la banda columnheader, por defecto esta en TRUE.
     * @var boolean
     */
    public $showColumnHeader = TRUE;

    /**
     * Lista de dataset del reporte.
     * @var DataSet
     */
    public $dataset = array();

    /**
     * Permite guardar datos cuando se necesita usar $this->value y
     * $this->REPORT_COUNT. Esto pasa sobre todo cuando se renderea el componente
     * CList.
     * @var array
     */
    public $iterator = array();

    /**
     * El componente que se esta rendereando.
     * @var Component
     */
    public $rComponent = NULL;

    /**
     * Idioma a utilizar en el reporte.
     * @var array
     */
    public $language = array();

    /**
     *
     * @var type
     */
    private static $instance;

    /**
     * Contiene el nombre del grupo rendereado en ese momento.
     * @var null
     */
    public $activeGroup = NULL;

    public function __construct()
    {
        self::$instance = &$this;
    }

    /**
     * Permite la carga de un fichero con el idioma a usar en el reporte.
     *
     * @param type $path La direccion del fichero json que contiene el idioma.
     * @return ZappReport
     */
    public function language($path)
    {
        //carga de idiomas
        if (file_exists($path)) {
            $lang = json_decode(file_get_contents($path));
            $this->language = array_merge($this->language, (array)$lang);
        }

        return $this;
    }

    /**
     * Devuelve la traduccion del texto pasado en $line.
     *
     * @param string $line La linea a traducir.
     * @param array $data Los argumentos a aplicarle.
     * @return    string
     */
    function lang($line = '', $data = array())
    {
        $value = ($line == '' OR !isset($this->language[$line])) ? FALSE : $this->language[$line];

        // Because killer robots like unicorns!
        if ($value === FALSE) {
            $value = $line;
        }

        if (!empty($data)) {
            $value = vsprintf($value, $data);
        }

        return $value;
    }

    /**
     * Devuelve una instancia de la clase.
     *
     * @return ZappReport
     */
    public static function &get_instance()
    {
        return self::$instance;
    }

    /**
     * Cambia el valor de pstate.
     *
     * @param string $state El nuevo valor.
     */
    public function setState($state)
    {
        $this->pstate = $state;
    }

    /**
     * Devuelve el valor de pstate.
     *
     * @return string El valor de pstate
     */
    public function getState()
    {
        return $this->pstate;
    }

    /**
     * Pone en blanco el valor de pstate.
     *
     * @return void
     */
    public function resetState()
    {
        $this->pstate = '';
    }

    /**
     * Adicionar un dataset.
     *
     * @param string $name Nombre del dataset.
     * @param array $data Datos del dataset.
     * @return void
     */
    public function addDataset($name, $data)
    {
        $this->dataset [$name] = new DataSet($name, $data);
    }

    /**
     * Cambia la direccion del fichero jrxml.
     *
     * @param string $path
     * @return ZappReport
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Devuelve la direccion del fichero jrxml.
     *
     * @return string El valor de path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Load the configuration of the report.
     *
     * @param string $name Filename
     * @param array $data Data of report.
     * @param array $parameters [optional] Parameters of report.
     * @return ZappReport
     */
    public function load($name, $data, $parameters = NULL)
    {
        if(file_exists($this->path."$name.jrxml"))
        {
            //se carga el xml
            $xml_string = file_get_contents($this->path."$name.jrxml");
            $this->xml = simplexml_load_string($xml_string);

            //se actualizan los datos a mostrar
            $this->values = $data;

            //cantidad de elementos
            $this->total = count($data);

            //recolectamos los parametros
            if ($parameters) {
                $this->collectParameters($parameters);
            }

            //recolectamos las variables
            $this->variables = $this->collectVariables($this->xml);

            //parseamos el xml para llevalo a clases php
            $this->parser();

            return $this;
        }
        else
        {
            echo 'No se encontr&oacute; el reporte en la siguiente direcci&oacute;n: '.$this->path."$name.jrxml";
        }

    }

    /**
     * Inicializamos los parametros del reporte.
     *
     * @param type $parameters Parametros a recolectar.
     * @return void
     */
    public function collectParameters($parameters)
    {
        foreach ($this->xml->parameter as $value) {
            $name = (string)$value['name'];
            if (isset($parameters[$name])) {
                $this->parameter[$name] = $parameters[$name];
            }
        }
    }

    /**
     * Recolectamos las variables.
     *
     * @param SimpleXMLElement $map El xml extraido del jrxml.
     * @return array Variables recolectadas.
     */
    public function collectVariables($map)
    {
        $variables = array();
        foreach ($map->variable as $variable) {
            switch ($variable['calculation']) {
                case 'Sum':
                    $var = new Sum($variable);
                    break;
                case 'Average':
                    $var = new Average($variable);
                    break;
                case 'Count':
                    $var = new Count($variable);
                    break;
                case 'DistinctCount':
                    $var = new DistinctCount($variable);
                    break;
                case 'Lowest':
                    $var = new Lowest($variable);
                    break;
                case 'Highest':
                    $var = new Highest($variable);
                    break;
                default : //Nothing
                    $var = new Variable($variable);
                    break;
            }
            $variables[(string)$variable['name']] = $var;
        }
        return $variables;
    }

    /**
     * Parsea el xml y obtiene las diferentes partes del reporte.
     *
     * @return void
     */
    public function parser()
    {
        //property
        $this->property = new Property($this->xml);

        //Background
        if ($this->exist('background')) {
            $this->background = new Band($this->xml->background->band);
        }

        //Title
        if ($this->exist('title')) {
            $this->btittle = new Band($this->xml->title->band);
        }

        //Page Header
        if ($this->exist('pageHeader')) {
            $this->pageheader = new Band($this->xml->pageHeader->band);
        }

        //Column Header
        if ($this->exist('columnHeader')) {
            $this->columnheader = new Band($this->xml->columnHeader->band);
        }

        //Detail
        if ($this->exist('detail')) {
            $this->detail = new Band($this->xml->detail->band);
        }

        //Column Footer
        if ($this->exist('columnFooter')) {
            $this->columnfooter = new Band($this->xml->columnFooter->band);
        }

        //Page Footer
        if ($this->exist('pageFooter')) {
            $this->pagefooter = new Band($this->xml->pageFooter->band);
        }

        //Summary
        if ($this->exist('summary')) {
            $this->summary = new Band($this->xml->summary->band);
        }

        //Groups
        foreach ($this->xml->group as $group) {
            $this->groups [] = new Group($group);
        }
    }

    /**
     * Determina si existe una banda.
     *
     * @param string $band Nombre de la Banda.
     * @return boolean TRUE si existe la banda sino FALSE.
     */
    public function exist($band)
    {
        return isset($this->xml->$band) && count($this->xml->$band->band->children()) > 0;
    }

    /**
     * Permite coleccionar componentes dentro de una banda o un objeto.
     *
     * @param mixed $object
     * @return array La lista de componentes recolectados.
     */
    public static function collect($object)
    {
        $c = NULL;
        $index = 0;
        $component = array();
        $relativeToBottom = array();
        $growthComponent = array();
        $stretchComponent = array();

        foreach ($object->children() as $name => $elem) {
            switch ($name) {
                case 'reportElement':
                    break;
                case 'rectangle':
                    $c = new Rectangle($elem);
                    break;
                case 'staticText':
                    $c = new StaticText($elem);
                    break;
                case 'textField':
                    $c = new TextField($elem);
                    $growthComponent [] = $index;
                    break;
                case 'line':
                    $c = new Line($elem);
                    break;
                case 'ellipse':
                    $c = new Ellipse($elem);
                    break;
                case 'image':
                    $c = new Image($elem);
                    break;
                case 'pie3DChart':
                    $c = new Pie3DChart($elem->pie3DPlot, $elem->pieDataset, $elem->chart);
                    break;
                case 'pieChart':
                    $c = new PieChart($elem->piePlot, $elem->pieDataset, $elem->chart);
                    break;
                case 'lineChart':
                    $c = new LineChart($elem->linePlot, $elem->categoryDataset, $elem->chart);
                    break;
                case 'barChart':
                    $c = new BarChart($elem->barPlot, $elem->categoryDataset, $elem->chart);
                    break;
                case 'stackedBarChart':
                    $c = new StakedBarChart($elem->barPlot, $elem->categoryDataset, $elem->chart);
                    break;
                case 'areaChart':
                    $c = new AreaChart($elem->areaPlot, $elem->categoryDataset, $elem->chart);
                    break;
                case 'frame':
                    $c = new Frame($elem);
                    $growthComponent [] = $index;
                    break;
                case 'componentElement':
                    if ($elem->children('jr', TRUE)->list) {
                        $c = new CList($elem);
                        $growthComponent [] = $index;
                    } elseif ($elem->children('hc', TRUE)->html) {
                        $c = new Html($elem);
                    } elseif ($elem->children('jr', TRUE)->Codabar) {

                    }
                    break;
                default :
                    $c = NULL;
                    break;
            }

            if ($c) {
                $index++;
                if ($c->positionType() == 'FixRelativeToBottom') {
                    $relativeToBottom [] = clone($c);
                } else {
                    $component [] = $c;
                    $stretch = $c->stretchType();
                    if ($stretch == 'RelativeToBandHeight' || $stretch == 'RelativeToTallestObject') {
                        $stretchComponent[] = clone($c);
                    }
                }
            }
        }

        return array('relativeToBottom' => $relativeToBottom, 'component' => $component, 'stretchComponent' => $stretchComponent);
    }

    /**
     * Parsea una expresion en busca de variables, campos o parametros.
     *
     * @param string $exp La expresion que se desea parsear.
     * @return array Un arreglo con los nombres y el tipo de elementos encontrados.F
     */
    public static function parseExpression($exp)
    {
        if ($exp == "") {
            return array();
        }
        $lenght = strlen($exp);

        // r=reconocimiento, n=recolectando nombre
        $state = 'r';
        // v=variable, f=campo, p=parametro
        $type = '';
        $name = "";

        $parse = array();

        for ($i = 0; $i < $lenght; $i++) {
            if ($state == 'r') {
                if ($exp[$i] == '$' && isset($exp[$i + 1]) && isset($exp[$i + 2])) {
                    $t = $exp[$i + 1];
                    if (($t == 'F' || $t == 'P' || $t == 'V') && $exp[$i + 2] == '{') {
                        $state = 'n';
                        $type = $t;
                        $i += 2;
                    }
                }
            } else {
                if ($exp[$i] != '}') {
                    $name .= $exp[$i];
                } else {
                    $parse [] = array('type' => $type, 'name' => $name);
                    $type = '';
                    $name = '';
                    $state = 'r';
                }
            }
        }
        return $parse;
    }

    /**
     * Evalua una expresion.
     *
     * @param type $expression La expresion.
     * @param type $parse El parse de la expresion.
     * @return type
     */
    public function analyse($expression, $parse)
    {
        //@TODO arreglar la expresion regular en un texto con comillas 
        //se las come entonces se necesita ponerlas doble para que funcione.
        $plain = preg_replace(array('/^"|"$/', '/\}+\s*+\++(\s*+"|")/', '/"+\s*+\++\s*+\$/'), array('', '}', '$'), $expression);

        foreach ($parse as $p) {
            $type = $p['type'];
            $name = $p['name'];
            $v = '';
            if ($type == 'V') {
                if ($name == 'PAGE_NUMBER') {
                    $v = $this->PageNo();
                } elseif ($name == 'REPORT_COUNT') {
                    $v = $this->REPORT_COUNT + 1;
                } elseif ($name == 'PAGE_COUNT') {
                    $v = '{nb}';
                } else {
                    $v = $this->variables[$name]->value();
                }
            } elseif ($type == 'P') {
                if (isset($this->parameter[$name])) {
                    $v = $this->parameter[$name];
                }
            } else {
                if (isset($this->values[$this->REPORT_COUNT])) {
                    if (isset($this->values[$this->REPORT_COUNT][$name])) {
                        $v = $this->values[$this->REPORT_COUNT][$name];
                    }
                }
            }

            if (is_array($v)) {
                return $v;//este puede ser el caso en que $v contenga un objeto
            } else {
                $plain = preg_replace('/\$' . $type . '\{' . $name . '\}/', $v == NULL ? 'NULL' : $v, $plain);
            }
        }

        //evaluamos la expresion en php si existe
        if (preg_match('/\$evaluate/', $plain)) {

            //disponemos algunas variables en contexto
            $index = $this->REPORT_COUNT;
            $values = $this->values;
            $params = $this->parameter;

            error_reporting(0);
            eval($plain);
            error_reporting(E_ALL);
        }
        return isset($evaluate) ? $evaluate : $plain;
    }

    /**
     * Inicia la ejecucion del reporte.
     *
     * @param string $name Nombre del dichero
     * @param string $dest El tipo de salida.
     *                   - I Abrirlo en el navegador.
     *                   - D Fichero descargable.
     *                   - F Salvar a una direccion.
     *                   - S Como string.
     */
    public function show($name = '', $dest = '')
    {
        $p = $this->property;
        parent::FPDF($p->orientation, $p->unit, $p->format);
        $this->SetMargins($p->leftMargin, $p->topMargin, $p->rightMargin);

        $bottomMargin = $p->bottomMargin;
        if ($this->pagefooter) {
            $bottomMargin += $this->pagefooter->height();
        }

        $this->AliasNbPages();
        $this->SetAutoPageBreak(TRUE, $bottomMargin);
        $this->AddPage();
        $this->build();
        $this->Output($name, $dest);
    }

    /**
     * Maneja la banda detail y la forma en que se muestra.
     *
     * @return void
     */
    public function build()
    {
        if ($this->detail && $this->total() > 0) {
            $this->setState('detail');
            while (($this->index() + 1) <= $this->total()) {
                $this->detail->render();
                $this->next();
                if ($this->hasGroups()) {
                    $this->detail->renderGroupFooter();
                }
            }
        }

        // render columnfooter
        if ($this->columnfooter) {
            $this->setState('columnfooter');
            $this->columnfooter->render();
        }

        // render summary
        if ($this->summary) {
            $this->showColumnHeader = FALSE;
            $this->setState('summary');
            $this->summary->render();
        }
    }

    /**
     * Maneja la banda background, title, header, columnheader y la forma en que
     * se muestran.
     *
     * @return void
     */
    public function Header()
    {
        $state = $this->getState();

        if ($this->background) {
            $this->setState('background');
            $this->background->render();
            $this->SetXY($this->property->leftMargin, $this->property->topMargin);
        }

        if ($this->PageNo() == 1) {
            if ($this->btittle) {
                $this->setState('btittle');
                $this->btittle->render();
            }
        }

        // render pageheader
        if ($this->pageheader) {
            $this->setState('pageheader');
            $this->pageheader->render();
        }

        if ($this->showColumnHeader) {
            // render columnheader
            if ($this->columnheader) {
                $this->setState('columnheader');
                $this->columnheader->render();
            }
        }

        $this->setState($state);
    }

    /**
     * Maneja la banda footer y la forma en que se muestra.
     *
     * @return void
     */
    public function Footer()
    {
        $state = $this->getState();
        if ($this->pagefooter) {
            $this->setState('pagefooter');
            $this->pagefooter->render();
        }
        $this->setState($state);
    }

    /**
     * Retorna la posicion actual del puntero que recorre los datos.
     *
     * @return int La posicion.
     */
    public function index()
    {
        return $this->REPORT_COUNT;
    }

    /**
     * Incrementa el puntero en uno.
     *
     * @return void
     */
    public function next()
    {
        $this->REPORT_COUNT++;
    }

    /**
     * Devuelve el total de elementos en data.
     *
     * @return int El total.
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * Permite conocer si existe algun grupo en el reporte.
     *
     * @return boolean TRUE si existe sino FALSE.
     */
    public function hasGroups()
    {
        return count($this->groups) > 0;
    }

    /**
     * Resetea el valor de las variables.
     *
     * @param string $type El tipo.
     * @param string $name [optional] Si el tipo es Group, aqui se indica el nombre
     * del grupo.
     *
     * @return void
     */
    public function resetVariables($type, $name = NULL)
    {
        foreach ($this->variables as $var) {
            if ($var->resetType() == $type) {
                if (!$name || $var->resetGroup() == $name) {
                    $var->reset();
                }
            }
        }
    }

    /**
     * Evalua todas las variables.
     *
     * @return void
     */
    public function evaluationVariable()
    {
        foreach ($this->variables as $var) {
            $var->evaluate();
        }
    }

    /**
     * Rendereamos los componentes, que son evaluados al final de la pagina.
     * @todo No implementado.
     * @return void
     */
    public function evaluationTime()
    {
        //rendereamos los componenetes que se evaluan al final de la pagina
        if (count($this->evaluatePage) > 0) {
            //obtenemos la posicion actual del puntero y la guardamos
            $pointer = $this->REPORT_COUNT;

            //indicamos que pueden ser evaluadas las variables
            $this->readyPage = TRUE;

            foreach ($this->evaluatePage as $var) {
                $this->REPORT_COUNT = $var['index'];
                $var['object']->render($var['x'], $var['y']);
            }

            //restauramos los valores iniciales del reporte
            $this->REPORT_COUNT = $pointer;
            $this->readyPage = FALSE;
            $this->evaluatePage = array();
        }
    }

    /**
     * Permite cambiar temporalmente los valores de $this->value y $this->REPORT_COUNT
     *
     * @param array $data Los nuevos datos.
     * @param int $index La posicion del puntero.
     */
    public function initIterator($data, $index)
    {
        array_push($this->iterator, $this->values);
        array_push($this->iterator, $this->REPORT_COUNT);
        $this->values = $data;
        $this->REPORT_COUNT = $index;
    }

    /**
     * Restaura los valores de $this->value y $this->REPORT_COUNT
     */
    public function resumeIterator()
    {
        $this->REPORT_COUNT = array_pop($this->iterator);
        $this->values = array_pop($this->iterator);
    }

}
