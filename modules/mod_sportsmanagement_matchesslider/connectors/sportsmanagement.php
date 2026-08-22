<?php
/** Legacy connector alias for Joomla 5/6 match slider compatibility. */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;

if (!class_exists(MatchesSliderHelper::class)) {
    require_once dirname(__DIR__) . '/src/Helper/MatchesSliderHelper.php';
}

if (!class_exists('MatchesSliderSportsmanagementConnector', false)) {
    class_alias(MatchesSliderHelper::class, 'MatchesSliderSportsmanagementConnector');
}
