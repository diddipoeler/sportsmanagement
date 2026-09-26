<?php
/**
 * SportsManagement score sheet compatibility bootstrap for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       scoresheet.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

\defined('FPDM_DIRECT') or define('FPDM_DIRECT', true);

$scoreSheetDependencies = [
    'FilterASCIIHex' => __DIR__ . '/scoresheet/FilterASCIIHex.php',
    'FilterASCII85' => __DIR__ . '/scoresheet/FilterASCII85.php',
    'FilterFlate' => __DIR__ . '/scoresheet/FilterFlate.php',
    'FilterLZW' => __DIR__ . '/scoresheet/FilterLZW.php',
    'FilterStandard' => __DIR__ . '/scoresheet/FilterStandard.php',
    'FPDM' => __DIR__ . '/scoresheet/fpdm.php',
];

foreach ($scoreSheetDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

foreach (array_keys($scoreSheetDependencies) as $requiredClass) {
    if (!class_exists($requiredClass)) {
        throw new \RuntimeException(
            'SportsManagement ScoreSheet dependency could not be loaded: ' . $requiredClass,
            500
        );
    }
}
