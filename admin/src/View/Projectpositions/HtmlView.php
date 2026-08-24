<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projectpositions;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('projectpositions');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/projectpositions/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewprojectpositions', __NAMESPACE__ . '\\HtmlView');
}
