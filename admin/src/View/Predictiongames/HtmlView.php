<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongames;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictiongames');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictiongames/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictiongames', __NAMESPACE__ . '\\HtmlView');
}
