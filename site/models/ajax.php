<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Ajax model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;

if (!class_exists(AjaxModel::class)) {
    $nativeModel = JPATH_SITE . '/components/com_sportsmanagement/src/Model/AjaxModel.php';

    if (is_file($nativeModel)) {
        require_once $nativeModel;
    }
}

if (!class_exists(AjaxModel::class)) {
    throw new \RuntimeException('SportsManagement native Ajax model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelAjax', false)) {
    class_alias(AjaxModel::class, 'sportsmanagementModelAjax');
}
