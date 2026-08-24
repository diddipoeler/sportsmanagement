<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextlmoimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextlmoimports');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextlmoimports/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextlmoimports', __NAMESPACE__ . '\\HtmlView');
}
