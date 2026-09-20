<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 administrator Transifex view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Transifex;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Legacy\LegacyBootstrap;

LegacyBootstrap::bootForView('transifex');
require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/views/transifex/view.html.php';

if (!class_exists(__NAMESPACE__ . '\\HtmlView', false)) {
    class_alias('sportsmanagementViewtransifex', __NAMESPACE__ . '\\HtmlView');
}
