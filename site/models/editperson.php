<?php
/**
 * Legacy compatibility bridge for the native frontend Editperson model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;

if (!class_exists(EditpersonModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/EditpersonModel.php';
}

if (!class_exists('sportsmanagementModelEditPerson', false)) {
    class_alias(EditpersonModel::class, 'sportsmanagementModelEditPerson');
}
