<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 extended XML editors model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorsModel;

if (!class_exists(SmextxmleditorsModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SmextxmleditorsModel.php';
}

if (!class_exists('sportsmanagementModelsmextxmleditors', false)) {
    class_alias(SmextxmleditorsModel::class, 'sportsmanagementModelsmextxmleditors');
}
