<?php
/**
 * Legacy compatibility bridge for the native administrator image handler model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagehandlerModel;

if (!class_exists(ImagehandlerModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/ImagehandlerModel.php';
}

if (!class_exists('sportsmanagementModelImagehandler', false)) {
    class_alias(ImagehandlerModel::class, 'sportsmanagementModelImagehandler');
}
