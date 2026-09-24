<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Imagelist model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagelistModel;

if (!class_exists(ImagelistModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ImagelistModel.php';
}

if (!class_exists(ImagelistModel::class)) {
    throw new \RuntimeException('SportsManagement native Imagelist model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelimagelist', false)) {
    class_alias(ImagelistModel::class, 'sportsmanagementModelimagelist');
}
