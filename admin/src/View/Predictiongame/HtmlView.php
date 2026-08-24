<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongame;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictiongame');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictiongame/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictiongame', __NAMESPACE__ . '\\HtmlView');
}
