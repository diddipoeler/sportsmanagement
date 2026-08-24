<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionmember;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictionmember');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictionmember/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictionmember', __NAMESPACE__ . '\\HtmlView');
}
