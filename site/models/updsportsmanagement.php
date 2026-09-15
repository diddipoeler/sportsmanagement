<?php
/**
 * Legacy model bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/UpdsportsmanagementModel.php';

if (!class_exists('sportsmanagementModelUpdsportsmanagement', false)) {
    class_alias(
        \Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel::class,
        'sportsmanagementModelUpdsportsmanagement'
    );
}
