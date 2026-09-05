<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 Round form model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\RoundModel;

if (!class_exists(RoundModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementAdminModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RoundModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(RoundModel::class)) {
    throw new \RuntimeException('SportsManagement native Round model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelround', false)) {
    class_alias(RoundModel::class, 'sportsmanagementModelround');
}
