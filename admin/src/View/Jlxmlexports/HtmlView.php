<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlexports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlxmlexports');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlxmlexports/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlxmlexports', __NAMESPACE__ . '\\HtmlView');
}
