<?php
/**
 * Joomla 5/6 native connector bootstrap for the matches module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

if (!class_exists(MatchesHelper::class)) {
    require_once dirname(__DIR__) . '/src/Helper/MatchesHelper.php';
}
