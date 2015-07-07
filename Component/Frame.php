<?php

/**
 * Frame Class
 * 
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class Frame extends Component {

    /**
     * Listado de componentes. 
     * @var array 
     */
    public $component = array();

    /**
     * Total de componentes.
     * @var int 
     */
    public $total = 0;

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);

        // coleccionamos los componentes de la lista
        $collect = ZappReport::get_instance()->collect($data);

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
        $x+=$this->x();
        $y+=$this->y();

        //estilo de la linea que dibuja los bordes
        $borderStyle = array(
            'width' => 1.0,
            'cap' => 'butt', 'join' => 'miter',
            'dash' => 0,
            'color' => $this->fontColor()
        );

        //si es transparente o se debe rellenar
        $style = $this->opaque() == "Transparent" ? "D" : "DF";

        $report = ZappReport::get_instance();
        //imprimimos el frame usando el id
        $report->_out('#-' . $this->uuid() . '-#');

        //banda activa
        $name = $report->getState();

        //enviamos el frame a la lista
        $report->$name->activeFrames [] = $this;

        //$iPage: pagina donde inicio el frame
        //$mPage: pagina maxima al rederear los componentes
        $iPage = $mPage = $report->PageNo();

        //metadata de la y en la banda
        $hp = &$report->hp;

        //metadata de la y en el frame
        $hy[$iPage] = array('yi' => $y, 'ym' => 0, 'gr' => 0, 'to' => 0);

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

        $report->state = 777;
        $lasty = $report->GetY();
        for ($i = $iPage, $lPage = $mPage ? $mPage : $iPage; $i <= $lPage; $i++)
        {
            $h = $hy[$i]['gr'] > $hy[$i]['to'] ? $hy[$i]['gr'] : $hy[$i]['to'];
            $report->Rect($x, $hy[$i]['yi'], $this->width(), $h, $style, array('all' => $borderStyle), $this->backgroundColor());

            $report->pages[$i] = str_replace('#-' . $this->uuid() . '-#', $report->buffer, $report->pages[$i]);
            //limpiamos el buffer
            $report->buffer = '';
        }
        //restauramos el estado de y
        $report->SetXY($x, $lasty);
        //desctivamos el buffer
        $report->state = 2;

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

        //extraemos el frame
        array_pop($report->$name->activeFrames);
    }

}
