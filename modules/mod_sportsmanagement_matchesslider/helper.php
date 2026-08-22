<?php
/** Legacy helper alias for Joomla 5/6 match slider compatibility. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;

if (!class_exists(MatchesSliderHelper::class)) {
    require_once __DIR__ . '/src/Helper/MatchesSliderHelper.php';
}

if (!class_exists('modMatchesSliderHelper', false)) {
    class_alias(MatchesSliderHelper::class, 'modMatchesSliderHelper');
}
