<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdbbimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextdbbimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextdbbimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextdbbimport', __NAMESPACE__ . '\\HtmlView');
}
