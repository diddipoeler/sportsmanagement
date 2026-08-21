<?php
/** Legacy compatibility bridge for the native frontend Editperson model. */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;

if (!class_exists(EditpersonModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditpersonModel.php';
}

if (!class_exists('sportsmanagementModelEditPerson', false)) {
    class_alias(EditpersonModel::class, 'sportsmanagementModelEditPerson');
}
