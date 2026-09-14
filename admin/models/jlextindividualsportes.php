<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator individual-sports list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextindividualsportesModel;

if (!class_exists(JlextindividualsportesModel::class)) {
    require_once JPATH_COMPONENT_ADMINISTRATOR . '/src/Model/JlextindividualsportesModel.php';
}

if (!class_exists('sportsmanagementModeljlextindividualsportes', false)) {
    class_alias(JlextindividualsportesModel::class, 'sportsmanagementModeljlextindividualsportes');
}
