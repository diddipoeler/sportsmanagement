<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 frontend Hitlist model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\HitlistModel;

if (!class_exists(HitlistModel::class)) {
    foreach ([
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php',
        JPATH_SITE . '/components/com_sportsmanagement/src/Model/HitlistModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(HitlistModel::class)) {
    throw new \RuntimeException('SportsManagement native Hitlist model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelhitlist', false)) {
    class_alias(HitlistModel::class, 'sportsmanagementModelhitlist');
}
