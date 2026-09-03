<?php
/**
 * Legacy model bridge for the native Joomla 5/6 tournament tree model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php';
require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/JltournamenttreeModel.php';

if (!class_exists('sportsmanagementModeljltournamenttree', false)) {
    class_alias(
        \Diddipoeler\Component\SportsManagement\Site\Model\JltournamenttreeModel::class,
        'sportsmanagementModeljltournamenttree'
    );
}
