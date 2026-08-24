<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlextcountries;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('jlextcountries');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/jlextcountries/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewjlextcountries', __NAMESPACE__ . '\\HtmlView');
}
