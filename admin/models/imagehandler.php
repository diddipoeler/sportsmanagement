<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Imagehandler model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagehandlerModel;

if (!class_exists(ImagehandlerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ImagehandlerModel.php';
}

if (!class_exists(ImagehandlerModel::class)) {
    throw new \RuntimeException('SportsManagement native Imagehandler model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelImagehandler', false)) {
    class_alias(ImagehandlerModel::class, 'sportsmanagementModelImagehandler');
}
