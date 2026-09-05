<?php
/**
 * Legacy compatibility bridge for the native administrator Databasetool model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\DatabasetoolModel;

if (!class_exists(DatabasetoolModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/DatabasetoolModel.php';
}

if (!class_exists('sportsmanagementModeldatabasetool', false)) {
    class_alias(DatabasetoolModel::class, 'sportsmanagementModeldatabasetool');
}
