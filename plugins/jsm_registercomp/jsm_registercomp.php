<?php
/** Legacy compatibility bridge for the Joomla 5/6 registercomp plugin. */
\defined('_JEXEC') or die;

use Diddipoeler\Plugin\System\SportsmanagementRegistercomp\Extension\SportsmanagementRegistercomp;

if (!class_exists(SportsmanagementRegistercomp::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementRegistercomp.php';
}

if (!class_exists('PlgSystemjsm_registercomp', false)) {
    class_alias(SportsmanagementRegistercomp::class, 'PlgSystemjsm_registercomp');
}
