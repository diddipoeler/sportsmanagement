<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlexthandballnet;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlexthandballnet');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlexthandballnet/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlexthandballnet', __NAMESPACE__ . '\\HtmlView');
}
