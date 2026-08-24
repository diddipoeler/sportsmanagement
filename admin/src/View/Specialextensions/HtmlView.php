<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Specialextensions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('specialextensions');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/specialextensions/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewspecialextensions', __NAMESPACE__ . '\\HtmlView');
}
