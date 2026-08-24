<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroup;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictiongroup');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictiongroup/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictiongroup', __NAMESPACE__ . '\\HtmlView');
}
