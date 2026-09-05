<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 rounds list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\RoundsModel;

if (!class_exists(RoundsModel::class)) {
    foreach ([
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/SportsManagementListModel.php',
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Model/RoundsModel.php',
    ] as $nativeFile) {
        if (is_file($nativeFile)) {
            require_once $nativeFile;
        }
    }
}

if (!class_exists(RoundsModel::class)) {
    throw new \RuntimeException('SportsManagement native Rounds model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelRounds', false)) {
    class_alias(RoundsModel::class, 'sportsmanagementModelRounds');
}
