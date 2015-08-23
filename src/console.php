<?php

require_once '../ZappReport.php';

/**
 * Proporciona una variable llamada $bulkdata con datos de muestra
 * y $dataset1 con datos para un dataset.
 */
require_once 'data.php';

$report = new ZappReport();

$report->setPath('jrxml/');

$type = $_GET['t'];

switch ($type) {
    case 'basic_report':
        $report->load($type, $bulkdata);
        break;
    case 'parameters_and_variables':
        $params = array('Country' => "Italy");
        $report->load($type, $bulkdata, $params);
        break;
    case 'fonts':
        $report->load($type, $bulkdata);
        break;
    case 'wood':
        $report->load($type, $bulkdata);
        break;
    case 'simple':
        $report->load($type, $bulkdata);
        break;
    case 'groups':
        $report->load($type, $bulkdata);
        break;
    case 'overflow_text':
        $params = array('Country' => "Italy");
        $report->load($type, $bulkdata, $params);
        break;
    case 'decorado':
        $report->load($type, $bulkdata);
        break;
    case 'list':
        $report->addDataset('dataset1', $dataset1);
        $report->addDataset('dataset2', $dataset2);
        $report->load($type, $bulkdata);
        break;
    case 'company':
        $report->addDataset('dataset1', $dataset1);
        $report->addDataset('dataset2', $dataset2);
        $report->load($type, $bulkdata);
        break;
    case 'advanced_group':
        $parameters = array(
            'organismo' => 'ORMGE',
            'empresa' => 'CARTON',
            'finicio' => '01/07/2015',
            'ffin' => '01/08/2015'
        );
        $report->load($type, $group, $parameters);
        break;
    case 'factura':
        $params = array(
            //cliente
            'numero' => '0023004',
            'cliente' => 'Toms Spezialit',
            'domicilio1' => 'Corsan itrarn 212, Green Imat',
            'ciudad' => 'Velascuain',
            'estado' => 'Flörida',
            'codigopostal' => '54000',
            //vehiculo
            'placa' => 'BR943783',
            'year' => '2012',
            'kilometraje' => '0000274',
            'noeconomico' => 23894363478675,
            'marca' => 'Holden',
            'modelo' => 'H700',
            'fechainicio' => '20/12/2013',
            'presupuesto' => 'Si',
            'nota' => 'Calibrado y aceitado, auto en perfecto estado técnico.',
            //datos de la empresa
            'empresa' => 'Automatris Argentina Co.',
            'direccion' => 'Suft Decroix, 34567, Saint Bernard.',
            'telefono' => '(345)349-483-343',
            'correo' => 'automatrisargentina@support.co.ar',
            //summary
            'totalmo' => '200.40',
            'totalpartes' => 600.05,
            'subtotal' => 800.45,
            'iva' => 70.12,
            'realtotal' => 870.57,
            'anticipo' => '200.00',
            'resto' => 670.57,
        );
        $bulkdata = array(
            array('cantidad' => 1, 'descripcion' => 'Crack Sildpet', 'marca_parte' => 'Tmabar', 'precio' => 70.00, 'costo' => 150.00),
            array('cantidad' => 2, 'descripcion' => 'Miller Freto', 'marca_parte' => 'Hijto', 'precio' => 80.00, 'costo' => 200.00),
            array('cantidad' => 4, 'descripcion' => 'Orama Kilem', 'marca_parte' => 'Wenar', 'precio' => 50.00, 'costo' => 200.00),
            array('cantidad' => 1, 'descripcion' => 'Amaret dturpe', 'marca_parte' => 'Qjuet', 'precio' => 20.00, 'costo' => 50.00),
        );
        $report->load($type, $bulkdata, $params);
        break;

    default:
        break;
}
$report->show();
