<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlxmlimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlxmlimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlxmlimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlxmlimport', __NAMESPACE__ . '\\HtmlView');
}
