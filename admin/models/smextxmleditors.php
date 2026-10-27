<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator Smextxmleditors model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorsModel;

if (!class_exists(SmextxmleditorsModel::class)) {
    $nativeModel = __DIR__ . '/../src/Model/SmextxmleditorsModel.php';

    if (!is_file($nativeModel)) {
        throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($nativeModel), 500);
    }

    require_once $nativeModel;
}

if (!class_exists(SmextxmleditorsModel::class)) {
    throw new \RuntimeException('SportsManagement native Smextxmleditors model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelsmextxmleditors', false)) {
    class_alias(SmextxmleditorsModel::class, 'sportsmanagementModelsmextxmleditors');
}
