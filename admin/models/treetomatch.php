<?php
/**
 * Legacy compatibility bridge for the native tournament-tree match model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Model\TreetomatchModel;

if (!class_exists('sportsmanagementModelTreetomatch', false)) {
    class_alias(TreetomatchModel::class, 'sportsmanagementModelTreetomatch');
}
