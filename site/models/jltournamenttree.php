<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend tournament tree model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\JltournamenttreeModel;

if (!class_exists(JltournamenttreeModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/TreetonodeModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/JltournamenttreeModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(JltournamenttreeModel::class)) {
    throw new \RuntimeException('SportsManagement native Jltournamenttree model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModeljltournamenttree', false)) {
    class_alias(JltournamenttreeModel::class, 'sportsmanagementModeljltournamenttree');
}
