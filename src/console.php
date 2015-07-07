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

        $dataset2 = array();
        foreach ($bulkdata as $i) {
            $dataset2[] = $bulkdata;
        }

        $report->addDataset('dataset2', $dataset2);
        $report->load($type, $bulkdata);
        break;

    default:
        break;
}
$report->show();
