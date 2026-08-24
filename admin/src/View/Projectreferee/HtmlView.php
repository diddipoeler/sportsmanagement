<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Projectreferee;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('projectreferee');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/projectreferee/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewprojectreferee', __NAMESPACE__ . '\\HtmlView');
}
