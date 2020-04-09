<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       transifex.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

JLoader::import('components.com_sportsmanagement.helpers.transifex', JPATH_ADMINISTRATOR);

/**
 * sportsmanagementControllertransifex
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllertransifex extends JSMControllerAdmin
{
	/**
	 * Class Constructor
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return void
	 * @since  1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * sportsmanagementControllertransifex::gettransifexinfo()
	 *
	 * @return void
	 */
	function gettransifexinfo()
	{

	}


}
