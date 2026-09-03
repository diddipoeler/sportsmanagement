<?php
/**
 * Legacy compatibility bridge for the native administrator GitHub model.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\GithubModel;

if (!class_exists(GithubModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/GithubModel.php';
}

if (!class_exists('sportsmanagementModelgithub', false)) {
    class_alias(GithubModel::class, 'sportsmanagementModelgithub');
}
