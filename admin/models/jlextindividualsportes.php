<?php
/** Legacy compatibility bridge for the native administrator individual-sports list model. */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportesModel;

if (!class_exists(JlextindividualsportesModel::class)) {
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Model/JlextindividualsportesModel.php';
}

if (!class_exists('sportsmanagementModeljlextindividualsportes', false)) {
    class_alias(JlextindividualsportesModel::class, 'sportsmanagementModeljlextindividualsportes');
}
