<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator GitHub model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\GithubModel;

if (!class_exists(GithubModel::class)) {
    require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/GithubModel.php';
}

if (!class_exists(GithubModel::class)) {
    throw new \RuntimeException('SportsManagement native Github model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelgithub', false)) {
    class_alias(GithubModel::class, 'sportsmanagementModelgithub');
}
