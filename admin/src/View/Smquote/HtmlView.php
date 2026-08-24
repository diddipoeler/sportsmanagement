<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smquote;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('smquote');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/smquote/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewsmquote', __NAMESPACE__ . '\\HtmlView');
}
