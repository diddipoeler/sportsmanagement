<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 matches slider helper.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;

if (!class_exists(MatchesSliderHelper::class)) {
    $nativeHelper = __DIR__ . '/src/Helper/MatchesSliderHelper.php';

    if (is_file($nativeHelper)) {
        require_once $nativeHelper;
    }
}

if (!class_exists(MatchesSliderHelper::class)) {
    throw new \RuntimeException('SportsManagement native MatchesSlider module helper could not be loaded.', 500);
}

if (!class_exists('modMatchesSliderHelper', false)) {
    class_alias(MatchesSliderHelper::class, 'modMatchesSliderHelper');
}
