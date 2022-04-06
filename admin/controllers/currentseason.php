<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       currentseason.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
//jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');

/**
 * sportsmanagementControllercurrentseason
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllercurrentseason extends JSMControllerAdmin
{
	protected $view_list = 'currentseasons';

	/**
	 * sportsmanagementControllercurrentseason::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 * sportsmanagementControllercurrentseason::display()
	 *
	 * @return void
	 */
	function display()
	{

		parent::display();
	}

}

