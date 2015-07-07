<?php

/**
 * CList Class
 *
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 *
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class CList extends Component
{

    /**
     * Listado de componentes.
     * @var array
     */
    public $component = array();

    /**
     * Total de datos.
     * @var int
     */
    public $dtotal = 0;

    /**
     * Total de componentes
     * @var int
     */
    public $total = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);

        $list = $data->children('jr', TRUE);

        // obtenemos el nombre del dataset
        $children = $list->list->children();
        $this->dataset = (string)$children[0]['subDataset'];

        // coleccionamos los componentes de la lista
        $collect = ZappReport::collect($list->list->children('jr', TRUE)->listContents);

        $this->component = $collect['component'];
        $this->relativeToBottom = $collect['relativeToBottom'];
        $this->stretchComponent = $collect['stretchComponent'];

        $this->total = count($this->component);
    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();
        // obtenemos los datos para el dataset utilizado
        // obtenemos los datos en la posicion actual del indice
        if (!isset($report->dataset[$this->dataset])) {
            die ('No se ha configurado ningun dataset.');
        }
        $data = $report->dataset[$this->dataset]->getDataInIndex($report->index());

        if ($data) {
            $y += $this->y();
            $x += $this->x();

            $report->initIterator($data, 0);

            $this->dtotal = count($report->values);

            while ($report->index() < $this->dtotal) {
                //chequeamos que exista suficiente espacio para mostrar la banda
                if ($y + $this->height() > $report->PageBreakTrigger && !$report->InHeader && !$report->InFooter && $report->AcceptPageBreak()) {
                    $report->AddPage($report->CurOrientation, $report->CurPageSize);
                    $y = $report->GetY();
                }

                //$iPage: pagina donde inicio el frame
                //$mPage: pagina maxima al rederear los componentes
                $iPage = $mPage = $report->PageNo();

                //metadata de la y en la banda
                $hp = & $report->hp;


                //metadata de la y en el frame
                $hy[$iPage] = array('yi' => $y, 'ym' => 0, 'gr' => 0, 'to' => 0);

                // rendereamos los componentes sin crecimiento
                foreach ($this->component as $i => $c) {
                    if ($c->printWhenExpression()) {
                        $stretch = $c->stretchType();
                        if ($stretch == 'RelativeToBandHeight' || $stretch == 'RelativeToTallestObject') {
                            $report->_out('#-' . $c->uuid() . '-#');
                            continue;
                        }

                        //se renderea el componente
                        $this->render = TRUE;
                        $report->rComponent = $c;
                        $c->render($x, $y);
                        $this->render = FALSE;

                        if ($iPage != $report->PageNo()) {
                            if ($mPage < $report->PageNo()) {
                                $mPage = $report->PageNo();
                                $gt = $report->GetY() - $hp[$mPage]['yi'];
                                $hy[$mPage] = array('yi' => $hp[$mPage]['yi'], 'ym' => $report->GetY(), 'gr' => $gt, 'to' => $gt);
                            }
                            elseif($mPage == $report->PageNo())
                            {
                                if($hy[$mPage]['ym'] < $report->GetY()){
                                    $gt = $report->GetY() - $hp[$mPage]['yi'];
                                    $hy[$mPage] = array('yi' => $hp[$mPage]['yi'], 'ym' => $report->GetY(), 'gr' => $gt, 'to' => $gt);
                                }
                            }
                            //si existen objetos sin mostrar
                            if ($i + 1 < $this->total) {
                                $report->setPage($iPage);
                                $report->SetXY($x, $hp[$iPage]['yl']);
                            }
                            $my = ($hp[$iPage]['yl'] - ($c->y() < 0 ? $c->y() * -1 : $c->y())) - $y;
                        } else {
                            $my = ($report->GetY() - ($c->y() < 0 ? $c->y() * -1 : $c->y())) - $y;
                        }

                        /**
                         * refactorizando
                         */
                        $ip = & $hy[$report->PageNo()];
                        if ($ip['to'] < $my) {
                            $ip['to'] = $my;
                        }
                        if ($ip['ym'] < $report->GetY()) {
                            $ip['ym'] = $report->GetY();
                        }
                        if ($ip['gr'] < $ip['ym'] - $ip['yi']) {
                            $ip['gr'] = $ip['ym'] - $ip['yi'];
                        }
                    }
                }

                //si existen paginas intermedias sin sus metadatas se actualizanF
                for ($j = $iPage; $j <= $mPage; $j++) {
                    if ($j > $iPage && $j < $mPage) {
                        $gr = $hp[$j]['yl'] - $hp[$j]['yi'];
                        $hy[$j] = array('yi' => $hp[$j]['yi'], 'ym' => $hp[$j]['ym'], 'gr' => $gr, 'to' => $gr);
                    }
                }

                if ($mPage != $iPage) {
                    $report->setPage($mPage);
                    $report->SetXY($x, $hy[$mPage]['ym']);
                    $report->resetVariables('Page');
                } else {
                    $report->SetXY($x, ($hy[$iPage]['ym'] - $y > $hy[$iPage]['gr'] ? $hy[$iPage]['ym'] : $y + $hy[$iPage]['gr']));
                }


                //se renderean los componentes relativos al final de la lista
                foreach ($this->relativeToBottom as $crtb) {
                    $crtb->render($x, $report->GetY() - $crtb->y());
                }

                if (!empty($this->stretchComponent)) {
                    $report->state = 777;
                    $lasty = $report->GetY();

                    //se renderean los componentes relativos al final de la lista
                    foreach ($this->stretchComponent as $sc) {
                        for ($i = $iPage, $lPage = $mPage ? $mPage : $iPage; $i <= $lPage; $i++) {
                            $stretch = $sc->stretchType();
                            if ($stretch == 'RelativeToBandHeight') {
                                $sc->setHeight($hy[$i]['gr']);
                                $sc->render($x, $hy[$i]['yi'] - $sc->y());
                            } elseif ($stretch == 'RelativeToTallestObject') {
                                $h = $hy[$i]['to'] == $hy[$i]['gr'] ? $sc->y() : 0;
                                $sc->setHeight($hy[$i]['to'] - $h);
                                $sc->render($x, $hy[$i]['yi']);
                            }

                            $report->pages[$i] = str_replace('#-' . $sc->uuid() . '-#', $report->buffer, $report->pages[$i]);
                            //limpiamos el buffer
                            $report->buffer = '';
                        }
                    }

                    //restauramos el estado de y
                    $report->SetXY($x, $lasty);
                    //desctivamos el buffer
                    $report->state = 2;
                }

                $y = $report->GetY();

                $report->next();
            }
            $report->resumeIterator();
        }

        return;
    }

}
