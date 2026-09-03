<?php
/**
 * Legacy controller bridge for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE . '/components/com_sportsmanagement/src/Controller/UpdsportsmanagementController.php';

if (!class_exists('sportsmanagementControllerUpdsportsmanagement', false)) {
    class_alias(
        \Diddipoeler\Component\SportsManagement\Site\Controller\UpdsportsmanagementController::class,
        'sportsmanagementControllerUpdsportsmanagement'
    );
}
