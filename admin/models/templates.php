<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 templates list model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\TemplatesModel;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementListModel;

$nativeDependencies = [
    SportsManagementListModel::class => __DIR__ . '/../src/Model/SportsManagementListModel.php',
    TemplatesModel::class => __DIR__ . '/../src/Model/TemplatesModel.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (class_exists($class, false)) {
        continue;
    }

    if (!is_file($file)) {
        throw new \RuntimeException('SportsManagement administrator model dependency is missing: ' . basename($file), 500);
    }

    require_once $file;
}

if (!class_exists(TemplatesModel::class)) {
    throw new \RuntimeException('SportsManagement native Templates model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelTemplates', false)) {
    class_alias(TemplatesModel::class, 'sportsmanagementModelTemplates');
}
