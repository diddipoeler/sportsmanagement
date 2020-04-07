<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       scoresheet.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage helpers
 */
defined('_JEXEC') or die('Restricted access');

define('FPDM_DIRECT', true);

require_once "scoresheet/fpdm.php";

require_once "scoresheet/FilterASCIIHex.php";
require_once "scoresheet/FilterASCII85.php";
require_once "scoresheet/FilterFlate.php";
require_once "scoresheet/FilterLZW.php";
require_once "scoresheet/FilterStandard.php";
