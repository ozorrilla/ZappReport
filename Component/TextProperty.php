<?php

/**
 * TextProperty Class
 * 
 * Clase para representar las propiedades comunes de los componentes
 * Static Text y Text Field.
 * 
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
abstract class TextProperty extends Component {

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Devuelve el valor de la propiedad fontName.
     * 
     * @return string Propiedad fontName.
     */
    public function fontName()
    {
        $font = (string) $this->data->textElement->font['fontName'];
        return $font != "" ? $font : 'times';
    }

    /**
     * Devuelve el valor de la propiedad size.
     * 
     * @return float Propiedad size.
     */
    public function size()
    {
        return (float) $this->data->textElement->font['size'] ? (float) $this->data->textElement->font['size'] : 10;
    }

    /**
     * Devuelve el estilo de la letra (bold,italic,underline,strike through).
     * 
     * @return string El estilo.
     */
    public function fontStyle()
    {

        $style = '';

        $font = $this->data->textElement->font;

        if ((string) $font['isBold'] == 'true')
        {
            $style = 'B';
        }
        if ((string) $font['isItalic'] == 'true')
        {
            $style .= 'I';
        }
        if ((string) $font['isUnderline'] == 'true')
        {
            $style .= 'U';
        }
        if ((string) $font['isStrikeThrough'] == 'true')
        {
            $style .= 'D';
        }
        return $style;
    }

    /**
     * Devuelve la alineacion horizontal (L=>left,C=>center,R=>right).
     * 
     * @return string La alinieacion.
     */
    public function taling()
    {
        return isset($this->data->textElement['textAlignment']) ? substr((string) $this->data->textElement['textAlignment'], 0, 1) : 'L';
    }

    /**
     * Devuelve la alineacion vertical (T=>top,M=>middle,B=>bottom).
     * @todo No implementado.
     * @return string La aleneacion.
     */
    public function valing()
    {
        return isset($this->data->textElement['verticalAlignment']) ? substr((string) $this->data->textElement['verticalAlignment'], 0, 1) : 'T';
    }

    /**
     * Devuelve el valor en la propiedad rotation.
     * @todo No implementado.
     * @return string Propiedad rotation.
     */
    public function rotate()
    {
        return (string) $this->data->textElement['rotation'];
    }    

    /**
     * Devuelve el angulo de rotacion.
     * 
     * @return int El angulo.
     */
    public function angle()
    {
        $angle = 0;
        //@TODO rotacion basica
        switch ($this->rotate())
        {
            case "Left":
                $angle = 90;
                break;
            case "Right":
                $angle = 270;
                break;
            case "UpsideDown":
                $angle = 180;
                break;
        }
        return $angle;
    }

    /**
     * Devuelve el valor en la propiedad evaluationTime.
     * 
     * @return string Propiedad evaluationTime.
     */
    public function evaluationTime()
    {
        return (string) $this->data['evaluationTime'];
    }

    /**
     * Cambia en el reporte el estilo asociado a la letra.
     * 
     * @return boolean Si debe ser rellenado.
     */
    public function style()
    {
        $report = ZappReport::get_instance();
        //estilo 
        $report->SetFont($this->fontName(), $this->fontStyle(), $this->size());

        //color 
        $color = $this->fontColor();
        $report->SetTextColor($color[0], $color[1], $color[2]);

        //rotacion
        if ($this->rotate())
        {
//            $report->StartTransform();
//            $report->Rotate($this->angle(), $this->x() + $x, $this->y() + $y);
//            $report->StopTransform();
        }

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

        return $fill;
    }

}
