<?php
/**
 * SportsManagement Handball legacy model compatibility bridge.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Backward-compatible model name for the currently inactive Handball extension.
 *
 * The extension exposes no importer implementation at present. Keeping this
 * class allows legacy extension loaders to resolve the historic model name
 * without pulling Joomla 6 deprecated HTTP, filesystem or Factory APIs.
 */
class sportsmanagementModeljsmhandball extends BaseDatabaseModel
{
    public static $success_text = '';
    public $storeFailedColor = 'red';
    public $storeSuccessColor = 'green';
    public $existingInDbColor = 'orange';
    public $success_text_teams = '';
    public $success_text_results = '';
}
