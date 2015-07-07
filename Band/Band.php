<?php

/**
 * Band Class
 *
 * Clase para representar las bandas del iReport.
 *
 * @category  ZappReport
 * @package   Band
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Band {

    /**
     * Los datos de jrxml de la banda.
     * @var \SimpleXMLElement
     */
    protected $data = NULL;

    /**
     * Listado de componentes.
     * @var array
     */
    public $component = array();

    /**
     * Listado de componentes que se renderean al final de la banda.
     * @var array
     */
    public $relativeToBottom = array();

    /**
     * Listado de componentes que su altura depende del stretchType.
     * @var array
     */
    public $stretchComponent = array();

    /**
     * Total de componentes en la banda.
     * @var int
     */
    public $total = 0;

    /**
     * Indica si esta rendereando un componente. Esta propiedad es usada para
     * determinar si fue un componente quien causo el salto de pagina.
     * @var int
     */
    public $render = FALSE;

    /**
     * Contiene los componentes de tipo Frame que se renderean en la banda. Esto
     * se utiliza en el salto de pagina, para dejar al inicio de cada pagina una
     * referencia a ellos.
     * @var int
     */
    public $activeFrames = array();

    /**
     * Contiene todas las expresiones del objeto parseadas.
     * @var array
     */
    public $parse = array();

    /**
     * Constructor de la clase.
     *
     * @param \SimpleXMLElement $band Datos del jrxml.
     * @return void
     */
    public function __construct($band)
    {
        $collect = ZappReport::collect($band);

        $this->data = $band;
        $this->height = $this->height();

        $this->component = $collect['component'];
        $this->relativeToBottom = $collect['relativeToBottom'];
        $this->stretchComponent = $collect['stretchComponent'];
        $this->total = count($this->component);

        $this->parse['printWhenExpression'] = ZappReport::parseExpression((string) $this->data->printWhenExpression);
    }

    /**
     * Devuelve el valor de la propiedad height.
     *
     * @return float Propiedad height.
     */
    public function height()
    {
        return (float) $this->data['height'];
    }

    /**
     * Devuelve el valor de la propiedad splitType.
     * @todo No implementado.
     * @return string Propiedad splitType.
     */
    public function split()
    {
        return (string) $this->data['splitType'];
    }

    /**
     * Evalua si la banda puede ser rendereada.
     *
     * @return boolean TRUE o FALSE.
     */
    public function printWhenExpression()
    {
        return isset($this->data->printWhenExpression) ? (boolean) ZappReport::analyse((string) $this->data->printWhenExpression, $this->parse['printWhenExpression']) : TRUE;
    }

    /**
     * Renderea los componentes de la banda.
     *
     * @return void
     */
    public function render()
    {
        // puede imprimirse la banda ?
        if ($this->printWhenExpression())
        {
            $report = ZappReport::get_instance();
            $name = $report->getState();

            $x = $report->property->leftMargin;
            $y = $name == 'pagefooter' ? ($report->property->pageHeight - $report->property->bottomMargin - $this->height()) : $report->GetY();

            //chequeamos que exista suficiente espacio para mostrar la banda
            if ($y + $this->height() > $report->PageBreakTrigger && !$report->InHeader && !$report->InFooter && $report->AcceptPageBreak())
            {
                $report->AddPage($report->CurOrientation, $report->CurPageSize);
                $y = $name == 'pagefooter' ? ($report->property->pageHeight - $report->property->bottomMargin - $this->height()) : $report->GetY();
            }

            if ($name == 'detail')
            {
                $report->evaluationVariable();

                if ($report->hasGroups())
                {
                    $report->$name->renderGroupHeader();
                    $y = $name == 'pagefooter' ? ($report->property->pageHeight - $report->property->bottomMargin - $this->height()) : $report->GetY();
                }
            }

            //$iPage: pagina donde inicio la banda
            //$mPage: pagina maxima al rederear los componentes
            $iPage = $mPage = $report->PageNo();

            //metadata de la banda
            $hp = &$report->hp;

            //metadata de la y en el frame
            $hy[$iPage] = array('yi' => $y, 'ym' => 0, 'gr' => $this->height(), 'to' => 0);

            // rendereamos los componentes sin crecimiento
            foreach ($this->component as $i => $c)
            {
                if ($c->printWhenExpression())
                {
                    $stretch = $c->stretchType();
                    if ($stretch == 'RelativeToBandHeight' || $stretch == 'RelativeToTallestObject')
                    {
                        $report->_out('#-' . $c->uuid() . '-#');
                        continue;
                    }

                    //se renderea el componente
                    $this->render = TRUE;
                    $report->rComponent = $c;
                    $c->render($x, $y);
                    $this->render = FALSE;

                    if ($iPage != $report->PageNo())
                    {
                        if ($mPage < $report->PageNo())
                        {
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
                        if ($i + 1 < $this->total)
                        {
                            $report->setPage($iPage);
                            $report->SetXY($x, $hp[$iPage]['yl']);
                        }
                        $my = ($hp[$iPage]['yl'] - ($c->y() < 0 ? $c->y() * -1 : $c->y())) - $y;
                    }
                    else
                    {
                        $my = ($report->GetY() - ($c->y() < 0 ? $c->y() * -1 : $c->y())) - $y;
                    }

                    /**
                     * refactorizando
                     */
                    $ip = &$hy[$report->PageNo()];
                    if ($ip['to'] < $my)
                    {
                        $ip['to'] = $my;
                    }
                    if ($ip['ym'] < $report->GetY())
                    {
                        $ip['ym'] = $report->GetY();
                    }
                    if ($ip['gr'] < $ip['ym'] - $ip['yi'])
                    {
                        $ip['gr'] = $ip['ym'] - $ip['yi'];
                    }
                }
            }

            //si existen paginas intermedias sin sus metadatas se actualizan
            for ($j = $iPage; $j <= $mPage; $j++)
            {
                if ($j > $iPage && $j < $mPage)
                {
                    $gr = $hp[$j]['yl'] - $hp[$j]['yi'];
                    $hy[$j] = array('yi' => $hp[$j]['yi'], 'ym' => $hp[$j]['ym'], 'gr' => $gr, 'to' => $gr);
                }
            }

            if ($mPage != $iPage)
            {
                $report->setPage($mPage);
                $report->SetXY($x, $hy[$mPage]['ym']);
                $report->resetVariables('Page');
            }
            else
            {
                $report->SetXY($x, ($hy[$iPage]['ym'] - $y > $hy[$iPage]['gr'] ? $hy[$iPage]['ym'] : $y + $hy[$iPage]['gr']));
            }

            //se renderean los componentes relativos al final de la banda
            foreach ($this->relativeToBottom as $crtb)
            {
                $crtb->render($x, $report->GetY() - $crtb->y());
            }

            if (!empty($this->stretchComponent))
            {
                $report->state = 777;
                $lasty = $report->GetY();

                //se renderean los componentes relativos al final de la banda
                foreach ($this->stretchComponent as $sc)
                {
                    for ($i = $iPage, $lPage = $mPage ? $mPage : $iPage; $i <= $lPage; $i++)
                    {
                        $stretch = $sc->stretchType();
                        if ($stretch == 'RelativeToBandHeight')
                        {
                            $sc->setHeight($hy[$i]['gr']);
                            $sc->render($x, $hy[$i]['yi'] - $sc->y());
                        }
                        elseif ($stretch == 'RelativeToTallestObject')
                        {
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
        }
    }

    /**
     * Renderea los grupos de cabecera (header groups).
     *
     * @return void
     */
    public function renderGroupHeader()
    {
        $report = ZappReport::get_instance();

        foreach ($report->groups as $group)
        {
            $result = $report->analyse($group->groupExpression(), $group->parse['groupExpression']);

            if ($result != $group->value('header'))
            {
                $group->render('header');

                $group->setValue('header', $result);

                if ($report->index() == 0)
                {
                    $group->setValue('footer', $result);
                }
            }
        }
    }

    /**
     * Renderea los grupos de pie de detail (footer groups).
     *
     * @return void.
     */
    public function renderGroupFooter()
    {
        $report = ZappReport::get_instance();

        $groups = $report->groups;

        for ($i = count($groups) - 1; $i >= 0; $i--)
        {
            $group = &$groups[$i];

            $result = $report->analyse($group->groupExpression(), $group->parse['groupExpression']);

            if ($result != $group->value('footer'))
            {
                $report->REPORT_COUNT --;
                $group->render('footer');
                $report->REPORT_COUNT ++;

                $group->setValue('footer', $result);

                //reseteamos todas las variables de tipo Group
                $report->resetVariables('Group', $group->name());
            }
        }
    }

}
