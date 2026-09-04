<?php
/**
 * Legacy compatibility bridge for the native frontend Editperson model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;

if (!class_exists(EditpersonModel::class)) {
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditpersonModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(EditpersonModel::class)) {
    throw new \RuntimeException('SportsManagement native Editperson model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelEditPerson', false)) {
    class_alias(EditpersonModel::class, 'sportsmanagementModelEditPerson');
}
