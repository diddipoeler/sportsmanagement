<?php

/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlxmlexports
 * @file       jlxmlexports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * sportsmanagementControllerjlxmlexports
 *
 * @package
 * @author    Dieter Pl�ger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerjlxmlexports extends BaseController
{

	/**
	 * sportsmanagementControllerjlxmlexports::display()
	 *
	 * @return void
	 */
	function display()
	{

		$this->showranking();
	}

	/**
	 * sportsmanagementControllerjlxmlexports::showranking()
	 *
	 * @return void
	 */
	function showranking()
	{

	}

}
