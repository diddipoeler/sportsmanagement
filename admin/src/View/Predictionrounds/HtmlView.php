<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionrounds;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictionrounds');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictionrounds/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictionrounds', __NAMESPACE__ . '\\HtmlView');
}
