<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smextxmleditor model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorModel;

if (!class_exists(SmextxmleditorModel::class)) {
    $nativeModel = __DIR__ . '/../src/Model/SmextxmleditorModel.php';

    if (!is_file($nativeModel)) {
        throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeModel), 500);
    }

    require_once $nativeModel;
}

if (!class_exists(SmextxmleditorModel::class)) {
    throw new \RuntimeException('SportsManagement native Smextxmleditor model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmextxmleditor', false)) {
    class_alias(SmextxmleditorModel::class, 'sportsmanagementModelsmextxmleditor');
}
