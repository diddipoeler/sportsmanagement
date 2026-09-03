<?php
/**
 * Legacy compatibility bridge for the native administrator association form model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextassociationModel;

if (!class_exists(JlextassociationModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php';
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/JlextassociationModel.php';
}

if (!class_exists('sportsmanagementModeljlextassociation', false)) {
    class_alias(JlextassociationModel::class, 'sportsmanagementModeljlextassociation');
}
