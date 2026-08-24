<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Githubinstall;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('githubinstall');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/githubinstall/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewgithubinstall', __NAMESPACE__ . '\\HtmlView');
}
