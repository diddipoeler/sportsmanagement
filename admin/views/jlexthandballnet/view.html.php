<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlexthandballnet
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;


/**
 * sportsmanagementViewjlexthandballnet
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2024
 * @version $Id$
 * @access public
 */
class sportsmanagementViewjlexthandballnet extends sportsmanagementView
{

	
	/**
	 * sportsmanagementViewjlexthandballnet::init()
	 * 
	 * @return void
	 */
	public function init()
	{

	

	}




	



	/**
	 * sportsmanagementViewjlexthandballnet::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{
		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();
		$app      = Factory::getApplication();
		$jinput   = $app->input;
		$option   = $jinput->getCmd('option');

		// Set toolbar items for the page
		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css' . '" type="text/css" />' . "\n";
		$document->addCustomTag($stylelink);

		// Set toolbar items for the page
		ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');

		parent::addToolbar();

	}
}

