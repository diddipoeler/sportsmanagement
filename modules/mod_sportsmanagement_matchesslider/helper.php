<?php
/**
 * Legacy helper alias for Joomla 5/6 match slider compatibility.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;

if (!class_exists(MatchesSliderHelper::class)) {
    require_once __DIR__ . '/src/Helper/MatchesSliderHelper.php';
}

if (!class_exists('modMatchesSliderHelper', false)) {
    class_alias(MatchesSliderHelper::class, 'modMatchesSliderHelper');
}
