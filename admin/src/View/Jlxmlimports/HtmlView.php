<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlimports;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlxmlimports');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlxmlimports/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlxmlimports', __NAMESPACE__ . '\\HtmlView');
}
