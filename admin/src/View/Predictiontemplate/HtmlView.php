<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiontemplate;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictiontemplate');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictiontemplate/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictiontemplate', __NAMESPACE__ . '\\HtmlView');
}
