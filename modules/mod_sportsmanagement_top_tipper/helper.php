<?php
/**
 * Legacy compatibility bridge for the Joomla 5/6 Top Tipper module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementTopTipper\Site\Helper\TopTipperHelper;

if (!class_exists(TopTipperHelper::class)) {
    require_once __DIR__ . '/src/Helper/TopTipperHelper.php';
}

if (!class_exists('modJSMTopTipper', false)) {
    class_alias(TopTipperHelper::class, 'modJSMTopTipper');
}
