<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictiontemplates;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('predictiontemplates');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/predictiontemplates/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewpredictiontemplates', __NAMESPACE__ . '\\HtmlView');
}
