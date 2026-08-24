<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictions');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictions/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictions', __NAMESPACE__ . '\\HtmlView');
}
