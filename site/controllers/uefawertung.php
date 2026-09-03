<?php
/**
 * Legacy compatibility bridge for the native Joomla 5/6 UEFA rating controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Controller\UefawertungController;

if (!class_exists(UefawertungController::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/UefawertungController.php';
}

if (!class_exists('sportsmanagementControlleruefawertung', false)) {
    class_alias(UefawertungController::class, 'sportsmanagementControlleruefawertung');
}
