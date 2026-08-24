<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextdfbkeyimport;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextdfbkeyimport');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextdfbkeyimport/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextdfbkeyimport', __NAMESPACE__ . '\\HtmlView');
}
