<?php
/**
 * Legacy helper bridge for the Joomla 5/6 SportsManagement liveticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper;

if (!class_exists(LivetickerHelper::class)) {
    require_once __DIR__ . '/src/Helper/LivetickerHelper.php';
}

if (!class_exists('modTurtushoutHelper', false)) {
    class_alias(LivetickerHelper::class, 'modTurtushoutHelper');
}
