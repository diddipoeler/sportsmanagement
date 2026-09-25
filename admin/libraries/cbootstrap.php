<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       cbootstrap.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package        Twitter Bootstrap Integration
 * @subpackage     com_cbootstrap
 * @copyright      Copyright (C) 2012 Conflate. All rights reserved.
 * @license        GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 * @link           http://www.conflate.nl
 */
\defined('_JEXEC') or die;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * CBootstrap
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class CBootstrap
{
	private static $_actions;
	private $_errors;

	/**
	 * CBootstrap::__construct()
	 *
	 * @return void
	 */
	public function __construct()
	{

	}

	/**
	 * CBootstrap::load()
	 *
	 * @return void
	 */
	public static function load()
	{
		// Joomla 5/6 ships Bootstrap through the core Web Asset Manager.
		// Keep the historical entry point without injecting an obsolete
		// Bootstrap 3 bundle from an external CDN.
		HTMLHelper::_('bootstrap.framework');
	}

}
