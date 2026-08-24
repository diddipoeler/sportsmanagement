<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiongroups;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictiongroups');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictiongroups/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictiongroups', __NAMESPACE__ . '\\HtmlView');
}
