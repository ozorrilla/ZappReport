<?php

require_once '../ZappReport.php';
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

    default:
        break;
}
$report->show();
