<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       jlexthandballnet.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

$maxImportTime = 480;

if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}


/**
 * sportsmanagementModeljlexthandballnet
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2024
 * @version $Id$
 * @access public
 */
class sportsmanagementModeljlexthandballnet extends JSMModelLegacy
{


	/**
	 * sportsmanagementModeljlexthandballnet::_loadData()
	 * 
	 * @return void
	 */
	function _loadData()
	{

	}

	
	/**
	 * sportsmanagementModeljlexthandballnet::_initData()
	 * 
	 * @return void
	 */
	function _initData()
	{

	}

	
	/**
	 * sportsmanagementModeljlexthandballnet::getProjectType()
	 * 
	 * @param integer $project_id
	 * @return void
	 */
	function getProjectType($project_id = 0)
	{
	

	}

	
	/**
	 * sportsmanagementModeljlexthandballnet::getProjectteams()
	 * 
	 * @param integer $project_id
	 * @param integer $division_id
	 * @return void
	 */
	function getProjectteams($project_id = 0, $division_id = 0)
	{
	

	}

	


	/**
	 * sportsmanagementModeljlexthandballnet::getCountry()
	 * 
	 * @param integer $project_id
	 * @return
	 */
	function getCountry($project_id = 0)
	{
		$this->jsmquery->clear();
		$this->jsmquery->select('l.country');
		$this->jsmquery->from('#__sportsmanagement_league as l');
		$this->jsmquery->join('LEFT', '#__sportsmanagement_project as p on p.league_id = l.id');
		$this->jsmquery->where('p.id = ' . $project_id);

		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$country = $this->jsmdb->loadResult();
			return $country;
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			return false;
		}

	}










	


}

