<?php
/**
 * SportsManagement score sheet compatibility bootstrap for Joomla 5/6.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage helpers
 * @file       scoresheet.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

\defined('FPDM_DIRECT') or define('FPDM_DIRECT', true);

require_once __DIR__ . '/scoresheet/fpdm.php';
require_once __DIR__ . '/scoresheet/FilterASCIIHex.php';
require_once __DIR__ . '/scoresheet/FilterASCII85.php';
require_once __DIR__ . '/scoresheet/FilterFlate.php';
require_once __DIR__ . '/scoresheet/FilterLZW.php';
require_once __DIR__ . '/scoresheet/FilterStandard.php';
