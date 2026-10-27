<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 administrator GitHub installer model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\GithubinstallModel;

$nativeDependencies = [
    GithubinstallModel::class => __DIR__ . '/../src/Model/GithubinstallModel.php',
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

if (!class_exists(GithubinstallModel::class)) {
    throw new \RuntimeException('SportsManagement native Githubinstall model could not be loaded.', 500);
}

if (!class_exists('sportsmanagementModelgithubinstall', false)) {
    class_alias(GithubinstallModel::class, 'sportsmanagementModelgithubinstall');
}
