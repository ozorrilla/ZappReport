<?php

/**
 * TextField Class
 *
 * Clase que representa el componente que lleva el mismo nombre en el iReport.
 *
 * @category  ZappReport
 * @package   Component
 * @version   1.0
 * @author    Osley Zorrilla Rivera <ozorrilla87@gmail.com>
 * @copyright Zoapp, Todos los derechos reservados.
 */
class TextField extends TextProperty
{

    /**
     * Expresiones regulares para los formatos de fecha en java y su
     * equivalente en php.
     * @var array
     */
    private $java_format_date = array(
        '/GG/' => '',
        '/yyyy/' => 'Y',
        '/yy/' => 'y',
        '/MMMMM|MMMM/' => 'F',
        '/MMM/' => 'M',
        '/MM/' => 'm',
        '/M/' => 'n',
        '/dd/' => 'd',
        '/d/' => 'j',
        '/hh|kk/' => 'h',
        '/h|k/' => 'g',
        '/HH|KK/' => 'H',
        '/H|K/' => 'G',
        '/mm|m/' => 'i',
        '/ss|s/' => 's',
        '/SSS/' => 'u',
        '/EEEEE|EEEE/' => 'l',
        '/EE/' => 'D',
        '/DDD|D/' => 'z',
        '/F/' => 'N',
        '/w|W/' => 'W',
        '/aa/' => 'A',
        '/a/' => 'a',
        '/zzzz|zzz|z/' => 'T'
    );

    /**
     * Simbolo para el separador de grupo.
     * @var string
     */
    public static $GROUPING_SEPARATOR_SYMBOL = NULL;

    /**
     * Simbolo para el separador decimal.
     * @var string
     */
    public static $DECIMAL_SEPARATOR_SYMBOL = NULL;

    /**
     * Simbolo de porciento.
     * @var string
     */
    public static $PERCENT_SYMBOL = NULL;

    /**
     * Simbolo de moneda.
     * @var string
     */
    public static $CURRENCY_SYMBOL = "$";

    /**
     * {@inheritdoc}
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->parse['textFieldExpression'] = ZappReport::parseExpression((string)$this->data->textFieldExpression);
    }

    /**
     * Devuelve el valor en la propiedad textFieldExpression.
     *
     * @return string El resultado de evaluar la propiedad textFieldExpression.
     */
    public function text()
    {
        if ((string)$this->data->textFieldExpression == "new java.util.Date()") {
            return date($this->formatDate($this->pattern()));
        }

        $traslate = ZappReport::get_instance()->lang((string)$this->data->textFieldExpression);
        $result = ZappReport::get_instance()->analyse($traslate, $this->parse['textFieldExpression']);

        //obtenemos el patron que usa para los datos
        $pattern = $this->pattern();

        //evaluamos el patron
        if ($pattern) {
            if (is_numeric($result)) {
                return $this->numberPattern($pattern, $result);
            }

            $date = date_create($result);

            if ($date !== FALSE) {
                $format = $this->formatDate($this->pattern());
                return date_format($date, $format);
            }
        }

        if ($this->isBlankWhenNull() && ($result == "NULL" || $result == NULL)) {
            return '';
        }

        //analizamos para capturar expresiones
        return utf8_decode(ZappReport::get_instance()->lang($result));
    }

    /**
     * Devuelve el valor en la propiedad pattern.
     *
     * @return string Propiedad pattern.
     */
    public function pattern()
    {
        return isset($this->data['pattern']) ? (string)$this->data['pattern'] : NULL;
    }

    /**
     * Devuelve el valor en la propiedad isBlankWhenNull.
     *
     * @return string Propiedad isBlankWhenNull.
     */
    public function isBlankWhenNull()
    {
        return isset($this->data['isBlankWhenNull']) ? TRUE : FALSE;
    }

    /**
     * Devuelve el valor en la propiedad isStretchWithOverflow.
     *
     * @return boolean Propiedad isStretchWithOverflow.
     */
    public function isStretchWithOverflow()
    {
        return (string)$this->data['isStretchWithOverflow'] == "true";
    }

    /**
     * Convierte los patrones de fecha en java a patrones php.
     *
     * @param string $pattern Patron en java.
     * @return string Los patrones java parseado a php.
     */
    public function formatDate($pattern)
    {
        $total = 0;
        $chunk = array();
        $key = NULL;

        foreach ($this->java_format_date as $key => $val) {
            $chunk = preg_split("$key", $pattern);
            $total = count($chunk);
            if ($total > 1) {
                break;
            }
        }

        $new_pattern = "";

        if ($total > 1) {
            foreach ($chunk as $ch => $v) {
                if ($v != "") {
                    $new_pattern .= $this->formatDate($v);
                }
                if ($ch + 1 < $total) {
                    $new_pattern .= $this->java_format_date[$key];
                }
            }
        } else {
            $new_pattern = $pattern;
        }

        return $new_pattern;
    }

    /**
     * Aplica el valor de pattern a un número.
     *
     * @param string $pattern El patron numérico a aplicar.
     * @param string $value El numero.
     * @return mixed Un numero con el patron aplicado.
     */
    public function numberPattern($pattern, $value)
    {
        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter('de_DE', NumberFormatter::IGNORE, $pattern);

            if ($pattern[count($pattern) - 1] == '%' || $pattern[count($pattern) - 1] == '‰') {
                $fmt = new NumberFormatter('de_DE', NumberFormatter::PERCENT, $pattern);
            }

            if (self::$GROUPING_SEPARATOR_SYMBOL) {
                $fmt->setSymbol(NumberFormatter::GROUPING_SEPARATOR_SYMBOL, self::$GROUPING_SEPARATOR_SYMBOL);
            }
            if (self::$DECIMAL_SEPARATOR_SYMBOL) {
                $fmt->setSymbol(NumberFormatter::DECIMAL_SEPARATOR_SYMBOL, self::$DECIMAL_SEPARATOR_SYMBOL);
            }

            if ($fmt) {
                $result = $fmt->format($value);
                if (!intl_is_failure($fmt->getErrorCode())) {
                    return $result;
                }
            }
        }

        return $value;

    }

    /**
     * {@inheritdoc}
     */
    public function render($x, $y)
    {
        $report = ZappReport::get_instance();

        $time = $this->evaluationTime();

        //no se puede evaluar hasta terminar la pagina
        if ($report->readyPage == FALSE && $time == 'Page') {
            //adicionamos al registro para que sea evaluada al final
            $report->evaluatePage[] = array('x' => $x, 'y' => $y, 'index' => $report->REPORT_COUNT, 'object' => $this);
            return;
        }

        $fill = $this->style();

        $report->SetXY($x + $this->x(), $y + $this->y());
        if (!$this->isStretchWithOverflow()) {
            $text = $report->GetStringInWidth((string)$this->text(), $this->width());
            $report->MultiCell($this->width(), $this->height(), $text, 0, $this->taling(), $fill);
        } else {
            $report->MultiCell($this->width(), $this->height(), $this->text(), 0, $this->taling(), $fill);
        }
    }

}
