<?php

require_once(__DIR__ . '/../../../config.php');

$courseid = required_param('id', PARAM_INT);
$type = required_param('type', PARAM_TEXT);
$categories = $_POST['categories'];

if ($categories) {
    if ($type == 'multiple') {
        $multiplegradebook = new gradereport_gradeconfigwizard\multiplegradebook($courseid);
        $success = $multiplegradebook->process($categories);
    } else if ($type == 'weighted') {
        $weightedgradebook = new gradereport_gradeconfigwizard\weightedgradebook($courseid);
        $success = $weightedgradebook->process($categories);
    } else {
        $success = false;
    }
}

$redirecturl = new moodle_url('/grade/report/gradeconfigwizard/index.php', array('id' => $courseid));
if ($success) {
    redirect($redirecturl, 'Gradebook configurado correctamente', null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    redirect($redirecturl, 'Error al configurar el gradebook', null, \core\output\notification::NOTIFY_ERROR);
}
