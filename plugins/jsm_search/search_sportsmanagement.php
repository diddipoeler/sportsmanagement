<?php
/**
 * Legacy compatibility bridge for the Joomla Finder SportsManagement plugin.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Diddipoeler\Plugin\Finder\Sportsmanagement\Extension\Sportsmanagement;

if (!class_exists(Sportsmanagement::class)) {
    require_once __DIR__ . '/src/Extension/Sportsmanagement.php';
}

if (!class_exists('PlgFinderJsm_search', false)) {
    class_alias(Sportsmanagement::class, 'PlgFinderJsm_search');
}

if (!class_exists('PlgFinderJsmSearch', false)) {
    class_alias(Sportsmanagement::class, 'PlgFinderJsmSearch');
}
