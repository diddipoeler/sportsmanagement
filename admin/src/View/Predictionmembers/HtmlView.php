<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionmembers;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictionmembers');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictionmembers/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictionmembers', __NAMESPACE__ . '\\HtmlView');
}
