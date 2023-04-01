<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

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
		$doc = Factory::getDocument();

		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			// Joomla! 3.0 code here
			Factory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js');

			// Factory::getDocument()->addScript('http://getbootstrap.com/2.3.2/assets/js/bootstrap-tab.js');
			Factory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
			Factory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css');
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			// Joomla! 2.5 code here
			Factory::getDocument()->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js');

			// Factory::getDocument()->addScript('http://getbootstrap.com/2.3.2/assets/js/bootstrap-tab.js');
			Factory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css');
			Factory::getDocument()->addStyleSheet('https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css');
		}
		elseif (version_compare(JVERSION, '1.7.0', 'ge'))
		{
			// Joomla! 1.7 code here
		}
		elseif (version_compare(JVERSION, '1.6.0', 'ge'))
		{
			// Joomla! 1.6 code here
		}
		else
		{
			// Joomla! 1.5 code here
		}

	}

}
