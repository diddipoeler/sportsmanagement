<?php
/**
 * Joomla 5/6 legacy entry point for the configured sport bootstrap plugin.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Diddipoeler\Plugin\Extension\SportsmanagementSportbootstrap\Extension\SportsmanagementSportbootstrap;

if (!class_exists(SportsmanagementSportbootstrap::class)) {
    require_once __DIR__ . '/src/Extension/SportsmanagementSportbootstrap.php';
}

if (!class_exists('PlgExtensionJsm_sportbootstrap', false)) {
    class_alias(SportsmanagementSportbootstrap::class, 'PlgExtensionJsm_sportbootstrap');
}
