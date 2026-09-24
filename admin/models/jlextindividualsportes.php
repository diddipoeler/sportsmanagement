<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Jlextindividualsportes model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportesModel;

if (!class_exists(JlextindividualsportesModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextindividualsportesModel.php';
}

if (!class_exists(JlextindividualsportesModel::class)) {
    throw new \RuntimeException('SportsManagement native Jlextindividualsportes model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljlextindividualsportes', false)) {
    class_alias(JlextindividualsportesModel::class, 'sportsmanagementModeljlextindividualsportes');
}
