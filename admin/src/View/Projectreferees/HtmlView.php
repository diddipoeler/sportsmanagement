<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projectreferees;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('projectreferees');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/projectreferees/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewprojectreferees', __NAMESPACE__ . '\\HtmlView');
}
