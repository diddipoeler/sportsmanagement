<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       joomleagueimports.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Filesystem\File;

$maxImportTime = 1920;

if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = '512M';

if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	@ini_set('memory_limit', $maxImportMemory);
}

/**
 * sportsmanagementModeljoomleagueimports
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljoomleagueimports extends ListModel
{
	static $db_num_rows = 0;
	static $storeFailedColor = 'red';
    static $storeFailedText = 'nicht aktualisiert';
	static $storeSuccessColor = 'green';
    static $storeSuccessText = 'aktualisiert';
	static $existingInDbColor = 'orange';
	static $storeInfo = 'black';
	static $_success = array();
	static $team_player = array();
	static $project_referee = array();
	static $team_staff = array();

	/**
	 * sportsmanagementModeljoomleagueimports::joomleaguesetagegroup()
	 *
	 * @return void
	 */
	function joomleaguesetagegroup()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$post   = $jinput->post->getArray(array());
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);
		$a      = 0;

		foreach ($post['agegroup'] as $key => $value)
		{
			$query = $db->getQuery(true);
			$query->clear();

			// Fields to update.
			$fields = array(
				$db->quoteName('agegroup_id') . ' = ' . $value
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('info') . ' LIKE ' . $db->Quote('' . $key . '')
			);

			$query->update($db->quoteName('#__sportsmanagement_team'))->set($fields)->where($conditions);

			$db->setQuery($query);

			try
			{
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}
			catch (Exception $e)
			{
			}

			$a++;
		}

		return $a;

	}

	/**
	 * sportsmanagementModeljoomleagueimports::get_info_fields()
	 *
	 * @return void
	 */
	function get_info_fields()
	{
		$conf   = Factory::getConfig();
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_sportsmanagement');
		$db     = Factory::getDbo();
		$query  = $db->getQuery(true);

		$query->clear();
		$query->select('info,agegroup_id');
		$query->from('#__sportsmanagement_team');

		// $query->join('INNER', '#__joomleague_project_position AS pt ON pt.project_id = pr.project_id and pt.position_id = pr.position_id ');
		$query->where('info NOT LIKE ' . $db->Quote('' . ''));
		$query->group('info,agegroup_id');
		$db->setQuery($query);
		$result = $db->loadObjectList();

		return $result;
	}


	/**
	 * sportsmanagementModeljoomleagueimports::check_database()
	 *
	 * @return void
	 */
	function check_database()
	{
		$conf   = Factory::getConfig();
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_sportsmanagement');
        $jl_dberror = 0;
		//$db     = Factory::getDbo();
		//$query  = $db->getQuery(true);

		/**
		 * welche joomla version ?
		 */
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			$debug = $conf->get('config.debug');
		}
		else
		{
			$debug = $conf->getValue('config.debug');
		}

		$option             = array(); // Prevent problems
		$option['driver']   = $params->get('jl_dbtype');      //       Database driver name
		$option['host']     = $params->get('jl_host') ? $params->get('jl_host') : $conf->get('host');     // Database host name
		$option['user']     = $params->get('jl_user') ? $params->get('jl_user') : $conf->get('user');        // User for database authentication
		$option['password'] = $params->get('jl_password') ? $params->get('jl_password') : $conf->get('password');    // Password for database authentication
		$option['database'] = $params->get('jl_db') ? $params->get('jl_db') : $conf->get('db');       // Database name
		$option['prefix']   = $params->get('jl_dbprefix');    //          Database prefix (may be empty)
		/**
		 *  zuerst noch überprüfen, ob der user
		 *  überhaupt den zugriff auf die datenbank hat.
		 */
        if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
		//$jl_access = JDatabaseDriver::getInstance($option);
        $db = JDatabaseDriver::getInstance($option);
		}
		else
		{
		//$jl_access = JDatabase::getInstance($option);
        $db = JDatabase::getInstance($option);
		}
        $query  = $db->getQuery(true);
        
//        Log::add('option <pre>'.print_r($option,true).'</pre>', Log::NOTICE, 'jsmerror'); 
//        Log::add('db <pre>'.print_r($db,true).'</pre>', Log::NOTICE, 'jsmerror');

		/**
		 * fehlende jl felder hinzufügen für alte versionen
		 */
		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_division` ADD `tree_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
        Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_player` ADD `position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_player` ADD `project_position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_referee` ADD `referee_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_referee` ADD `position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_referee` ADD `project_position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_staff` ADD `staff_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_staff` ADD `position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_match_staff` ADD `project_position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_project_referee` ADD `position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_team_player` ADD `position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_team_player` ADD `project_position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_team_staff` ADD `position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_team_staff` ADD `project_position_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_team_trainingdata` ADD `team_id_in_project` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_project_team` ADD `mark` int(11) DEFAULT NULL ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_project` ADD `serveroffset` varchar(6) NOT NULL DEFAULT '-01:00' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_project` ADD `tree_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_project` ADD `admin` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `#__joomleague_project` ADD `editor` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
            $db->execute();
		}
		catch (Exception $e)
		{
		Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
        $jl_dberror++;
		}
        
return $jl_dberror;

	}

	/**
	 * sportsmanagementModeljoomleagueimports::get_success()
	 *
	 * @return
	 */
	function get_success()
	{
		return self::$_success;
	}


	/**
	 * sportsmanagementModeljoomleagueimports::importjoomleaguenew()
	 *
	 * @param   integer  $importstep
	 * @param   integer  $sports_type_id
	 *
	 * @return
	 */
	function importjoomleaguenew($importstep = 0, $sports_type_id = 0)
	{
		$conf   = Factory::getConfig();
		$app    = Factory::getApplication();
		$params = ComponentHelper::getParams('com_sportsmanagement');
		$jinput      = $app->input;
		$option      = $jinput->getCmd('option');
		$date        = Factory::getDate();
		$user        = Factory::getUser();
		$modified    = $date->toSql();
		$modified_by = $user->get('id');
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $jl_dbprefix   = $params->get('jl_dbprefix');

		self::$_success       = array();
		$my_text              = '';
		$starttime            = sportsmanagementModeldatabasetool::getRunTime();
		$jl_table_import_step = $jinput->get('jl_table_import_step', 0);
		$jinput->set('filter_sports_type', $sports_type_id);

//		$db    = Factory::getDbo();
        $option             = array(); // Prevent problems
		$option['driver']   = $params->get('jl_dbtype');      //       Database driver name
		$option['host']     = $params->get('jl_host') ? $params->get('jl_host') : $conf->get('host');     // Database host name
		$option['user']     = $params->get('jl_user') ? $params->get('jl_user') : $conf->get('user');        // User for database authentication
		$option['password'] = $params->get('jl_password') ? $params->get('jl_password') : $conf->get('password');    // Password for database authentication
		$option['database'] = $params->get('jl_db') ? $params->get('jl_db') : $conf->get('db');       // Database name
		$option['prefix']   = $params->get('jl_dbprefix');    //          Database prefix (may be empty)
		/**
		 *  zuerst noch überprüfen, ob der user
		 *  überhaupt den zugriff auf die datenbank hat.
		 */
        if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
		//$jl_access = JDatabaseDriver::getInstance($option);
        $db = JDatabaseDriver::getInstance($option);
		}
		else
		{
		//$jl_access = JDatabase::getInstance($option);
        $db = JDatabase::getInstance($option);
		}
        
        $dbjsm    = Factory::getDbo();
		$query = $db->getQuery(true);
        $queryjsm = $dbjsm->getQuery(true);

		$exportfields1 = array();
		$exportfields2 = array();
		$table_copy    = array();

		/** importschritt 0 */
		if ($jl_table_import_step == 0)
		{
			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE #__joomleague_match_player ADD INDEX `match_id` (`match_id`) ";
				$db->setQuery($query);
                $db->execute();
                $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
			}
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}
            $my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match_player ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE #__joomleague_match_staff ADD INDEX `match_id` (`match_id`) ";
				$db->setQuery($query);
                $db->execute();
                $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
			}
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}
            $my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match_staff ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE #__joomleague_match_referee ADD INDEX `match_id` (`match_id`) ";
				$db->setQuery($query);
                $db->execute();
                $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
			}
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}
            $my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match_referee ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE #__joomleague_match ADD INDEX `round_id` (`round_id`) ";
				$db->setQuery($query);
                $db->execute();
                $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
			}
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror');
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}
            $my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';

			/** alle personen veröffentlichen */
			$query = $db->getQuery(true);
			$query->clear();

			$fields = array(
				$db->quoteName('published') . ' = 1'
			);
			$conditions = array(
				$db->quoteName('published') . ' = 0'
			);

			$query->update($db->quoteName('#__joomleague_person'))->set($fields)->where($conditions);

			try
			{
			$db->setQuery($query);
            $db->execute();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
			}
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}

			$my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_person ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';
			$endtime   = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime = ($endtime - $starttime);
			self::$_success['Laufzeit:']  = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['JL-Update:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			return self::$_success;
		}

		/** importschritt 1 */
		if ($jl_table_import_step == 1)
		{
			/** die positionen bei den schiedsrichtern setzen */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('pr.id,pr.project_id,pr.position_id,pt.id as project_position_id');
			$query->from('#__joomleague_project_referee as pr');
			$query->join('INNER', '#__joomleague_project_position AS pt ON pt.project_id = pr.project_id and pt.position_id = pr.position_id ');
			$query->where('pr.position_id != 0');
            try
			{
			$db->setQuery($query);
			$result = $db->loadObjectList();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
            }
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText;
			}

			foreach ($result as $row)
			{
				$mdlTable                      = new stdClass;
				$mdlTable->id                  = $row->id;
				$mdlTable->project_position_id = $row->project_position_id;

				try
				{
					$result_update = $db->updateObject('#__joomleague_project_referee', $mdlTable, 'id');
                    $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
				}
				catch (Exception $e)
				{
				    Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
				}
			}

			$my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_project_referee ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';
			$endtime   = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime = ($endtime - $starttime);
			self::$_success['Laufzeit:']  = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['JL-Update:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			return self::$_success;
		}

		/** importschritt 2 */
		if ($jl_table_import_step == 2)
		{
			/** die positionen bei den betreuern setzen */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('pr.id,pr.position_id,pt.project_id');
			$query->from('#__joomleague_team_staff as pr');
			$query->join('INNER', '#__joomleague_project_team AS pt ON pt.id = pr.projectteam_id');
			$query->where('pr.project_position_id = 0');
			$query->where('pr.position_id != 0');
			try
			{
			$db->setQuery($query);
			$result = $db->loadObjectList();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
            }
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}

			foreach ($result as $row)
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('id');
				$query->from('#__joomleague_project_position');
				$query->where('position_id = ' . $row->position_id);
				$query->where('project_id = ' . $row->project_id);
				$db->setQuery($query);
				$mdlTable                      = new stdClass;
				$mdlTable->id                  = $row->id;
				$mdlTable->project_position_id = $db->loadResult();
				$mdlTable->published           = 1;

				try
				{
					$result_update = $db->updateObject('#__joomleague_team_staff', $mdlTable, 'id');
				}
				catch (Exception $e)
				{
				}
			}

			$my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_team_staff ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';
			$endtime   = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime = ($endtime - $starttime);
			self::$_success['Laufzeit:']  = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['JL-Update:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			return self::$_success;
		}

		/** importschritt 3 */
		if ($jl_table_import_step == 3)
		{
			/** die positionen bei den spielern setzen */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('pr.id,pr.position_id,pt.project_id');
			$query->from('#__joomleague_team_player as pr');
			$query->join('INNER', '#__joomleague_project_team AS pt ON pt.id = pr.projectteam_id');
			$query->where('pr.position_id != 0');
			$query->where('pr.project_position_id = 0');
			try
			{
			$db->setQuery($query);
			$result = $db->loadObjectList();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
            }
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}

			foreach ($result as $row)
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('id');
				$query->from('#__joomleague_project_position');
				$query->where('position_id = ' . $row->position_id);
				$query->where('project_id = ' . $row->project_id);
				$db->setQuery($query);
				$mdlTable                      = new stdClass;
				$mdlTable->id                  = $row->id;
				$mdlTable->project_position_id = $db->loadResult();
				$mdlTable->published           = 1;

				try
				{
					$result_update = $db->updateObject('#__joomleague_team_player', $mdlTable, 'id');
				}
				catch (Exception $e)
				{
				}
			}

			$my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_team_player ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';
			$endtime                      = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                    = ($endtime - $starttime);
			self::$_success['Laufzeit:']  = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['JL-Update:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			return self::$_success;
		}

		/** importschritt 4 */
		if ($jl_table_import_step == 4)
		{
			/** die positionen bei den spielen spieler setzen */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('mp.id,mp.position_id,r.project_id');
			$query->from('#__joomleague_match_player as mp');
			$query->join('INNER', '#__joomleague_match AS m ON m.id = mp.match_id');
			$query->join('INNER', '#__joomleague_round AS r ON r.id = m.round_id');
			$query->where('mp.position_id != 0');
			$query->where('mp.project_position_id = 0');
			try
			{
			$db->setQuery($query);
			$result = $db->loadObjectList();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
            }
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText.' ('.$e->getMessage().') ';
			}

			foreach ($result as $row)
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('id');
				$query->from('#__joomleague_project_position');
				$query->where('position_id = ' . $row->position_id);
				$query->where('project_id = ' . $row->project_id);
				$db->setQuery($query);
				$mdlTable                      = new stdClass;
				$mdlTable->id                  = $row->id;
				$mdlTable->project_position_id = $db->loadResult();

				try
				{
					$result_update = $db->updateObject('#__joomleague_match_player', $mdlTable, 'id');
				}
				catch (Exception $e)
				{
				}
			}

			$my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match_player ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';
			$endtime                      = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                    = ($endtime - $starttime);
			self::$_success['Laufzeit:']  = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['JL-Update:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);
			return self::$_success;
		}

		/** importschritt 5 */
		if ($jl_table_import_step == 5)
		{
			/** die positionen bei den spielen betreuern setzen */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('mp.id,mp.position_id,r.project_id');
			$query->from('#__joomleague_match_staff as mp');
			$query->join('INNER', '#__joomleague_match AS m ON m.id = mp.match_id');
			$query->join('INNER', '#__joomleague_round AS r ON r.id = m.round_id');
			$query->where('mp.position_id != 0');
			$query->where('mp.project_position_id = 0');
			try
			{
			$db->setQuery($query);
			$result = $db->loadObjectList();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
            }
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText;
			}

			foreach ($result as $row)
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('id');
				$query->from('#__joomleague_project_position');
				$query->where('position_id = ' . $row->position_id);
				$query->where('project_id = ' . $row->project_id);
				$db->setQuery($query);
				$mdlTable                      = new stdClass;
				$mdlTable->id                  = $row->id;
				$mdlTable->project_position_id = $db->loadResult();
				try
				{
					$result_update = $db->updateObject('#__joomleague_match_staff', $mdlTable, 'id');
				}
				catch (Exception $e)
				{
				}
			}

			$my_text .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match_staff ) ' . $infotext . '!</strong>' . '</span>';
			$my_text .= '<br />';
			$endtime                      = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                    = ($endtime - $starttime);
			self::$_success['Laufzeit:']  = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['JL-Update:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);
			return self::$_success;
		}

		/** importschritt 6 */
		if ($jl_table_import_step == 6)
		{
			/** die positionen bei den spielen schiedsrichter setzen */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('mp.id,mp.position_id,r.project_id,mp.referee_id');
			$query->from('#__joomleague_match_referee as mp');
			$query->join('INNER', '#__joomleague_match AS m ON m.id = mp.match_id');
			$query->join('INNER', '#__joomleague_round AS r ON r.id = m.round_id');
			$query->where('mp.position_id != 0');
			$query->where('mp.project_position_id = 0');
			try
			{
			$db->setQuery($query);
			$result = $db->loadObjectList();
            $infocolor = self::$storeSuccessColor;
            $infotext = self::$storeSuccessText;
            }
			catch (Exception $e)
			{
			Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
            $infocolor = self::$storeFailedColor;
            $infotext = self::$storeFailedText;
			}

			foreach ($result as $row)
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('id');
				$query->from('#__joomleague_project_position');
				$query->where('position_id = ' . $row->position_id);
				$query->where('project_id = ' . $row->project_id);
				$db->setQuery($query);
				$mdlTable                      = new stdClass;
				$mdlTable->id                  = $row->id;
				$mdlTable->project_referee_id  = $row->referee_id;
				$mdlTable->project_position_id = $db->loadResult();
				try
				{
					$result_update = $db->updateObject('#__joomleague_match_referee', $mdlTable, 'id');
				}
				catch (Exception $e)
				{
				}
			}

			$my_text                          .= '<span style="color:' . $infocolor . '"<strong>Daten in der Tabelle: ( __joomleague_match_referee ) ' . $infotext . '!</strong>' . '</span>';
			$my_text                          .= '<br />';
			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Tabellenkopie:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);
			return self::$_success;
		}

		/** importschritt 7 */
		if ($jl_table_import_step == 7)
		{
			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Tabellenkopie:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);
			return self::$_success;
		}

		/** importschritt 8 */
		if ($jl_table_import_step == 8)
		{
			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Tabellenkopie:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);
			return self::$_success;
		}

		/** importschritt 9 */
		if ($jl_table_import_step == 9)
		{
			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Tabellenkopie:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);
			return self::$_success;
		}

		/** importschritt 10 */
		if ($jl_table_import_step == 10)
		{
			$table_copy[] = 'associations';
			$table_copy[] = 'club';
			$table_copy[] = 'division';
			$table_copy[] = 'league';
			$table_copy[] = 'match';
			$table_copy[] = 'match_event';
			$table_copy[] = 'match_player';
			$table_copy[] = 'match_referee';
			$table_copy[] = 'match_staff';
			$table_copy[] = 'match_staff_statistic';
			$table_copy[] = 'match_statistic';
			$table_copy[] = 'match_commentary';
			$table_copy[] = 'person';
			$table_copy[] = 'playground';
			$table_copy[] = 'position';
			$table_copy[] = 'eventtype';
			$table_copy[] = 'position_eventtype';
			$table_copy[] = 'position_statistic';
			$table_copy[] = 'project';
			$table_copy[] = 'project_position';
			$table_copy[] = 'project_referee';
			$table_copy[] = 'project_team';
			$table_copy[] = 'rosterposition';
			$table_copy[] = 'round';
			$table_copy[] = 'season';
			$table_copy[] = 'statistic';
			$table_copy[] = 'team';
			$table_copy[] = 'team_trainingdata';
			$table_copy[] = 'team_player';
			$table_copy[] = 'team_staff';
			$table_copy[] = 'template_config';
			$table_copy[] = 'user_extra_fields';
			$table_copy[] = 'user_extra_fields_values';
			$table_copy[] = 'prediction_admin';
			$table_copy[] = 'prediction_game';
			$table_copy[] = 'prediction_groups';
			$table_copy[] = 'prediction_member';
			$table_copy[] = 'prediction_project';
			$table_copy[] = 'prediction_result';
			$table_copy[] = 'prediction_template';
			$tables = $db->getTableList();

			/** als erstes kommen die tabellen, die nur kopiert werden ! */
			$my_text = '';
			$my_text .= '<span style="color:' . self::$storeInfo . '"<strong> ( ' . __METHOD__ . ' )  ( ' . __LINE__ . ' ) </strong>' . '</span>';
			$my_text .= '<br />';

			foreach ($table_copy as $key => $value)
			{
				$jl_table  = '#__joomleague_' . $value;
				$jsm_table = '#__sportsmanagement_' . $value;

				/** hier überprüfen wir noch sicherheitshalber ob die jl tabelle existiert */
				$prefix    = $db->getPrefix();
				$key_table = array_search($prefix . 'joomleague_' . $value, $tables);

				if ($key_table)
				{
					/** hier muss auch wieder zwischen den joomla versionen unterschieden werden */
					if (version_compare(JVERSION, '3.0.0', 'ge'))
					{
						/** Joomla! 3.0 code here */
						$jl_fields              = $db->getTableColumns('#__joomleague_' . $value);
						$jsm_fields             = $dbjsm->getTableColumns('#__sportsmanagement_' . $value);
						$jl_fields[$jl_table]   = $jl_fields;
						$jsm_fields[$jsm_table] = $jsm_fields;
					}
//					elseif (version_compare(JVERSION, '2.5.0', 'ge'))
//					{
//						/** Joomla! 2.5 code here */
//						$jl_fields  = $db->getTableFields('#__joomleague_' . $value);
//						$jsm_fields = $db->getTableFields('#__sportsmanagement_' . $value);
//					}

					/** importschritt 0 */
					if (count($jl_fields[$jl_table]) === 0)
					{
						$my_text .= '<span style="color:' . self::$storeFailedColor . '"<strong>Die Tabelle: ( ' . $jl_table . ' ) kann nicht kopiert werden. Tabelle: ( ' . $jl_table . ' ) ist nicht vorhanden!</strong>' . '</span>';
						$my_text .= '<br />';
					}
					else
					{
						/** feld import_id einfügen */
						try
						{
							$queryjsm = $dbjsm->getQuery(true);
							//$queryjsm->clear();
							$queryjsm = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
							$dbjsm->setQuery($queryjsm);
							//sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
                            $dbjsm->execute();
						}
						catch (Exception $e)
						{
						Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		                Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
						}

						try
						{
                        $queryjsm = $dbjsm->getQuery(true);
						//$queryjsm->clear();
						/** löschen die das feld import_id gefüllt haben */
						$conditions = array(
							$dbjsm->quoteName('import_id') . ' != 0'
						);
						$queryjsm->delete($dbjsm->quoteName($jsm_table));
						$queryjsm->where($conditions);

						$dbjsm->setQuery($queryjsm);
						//sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
                        $dbjsm->execute();
                        }
						catch (Exception $e)
						{
						Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		                Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
						}

						/**
						 * die anzahl der einträge wird nicht mehr benötigt
						 * da wir nur die einträge löschen die das feld import_id gefüllt haben
						 */
						$totals = 0;

						if ($totals)
						{
							$my_text .= '<span style="color:' . self::$storeFailedColor . '"<strong>Daten aus der Tabelle: ( ' . $jl_table . ' ) koennen nicht kopiert werden. Tabelle: ( ' . $jsm_table . ' ) nicht leer!</strong>' . '</span>';
							$my_text .= '<br />';
						}
						else
						{
							unset($jsm_field_array);
							unset($exportfields1);
							unset($exportfields2);
							$jsm_field_array = $jsm_fields[$jsm_table];

							/**
							 * unique index anlegen auf die import_id
							 */
							/*
							try {
							$query = $db->getQuery(true);
							$query->clear();
							$query = "ALTER TABLE `".$jsm_table."` ADD UNIQUE `import_id` (`import_id`) "   ;
							$db->setQuery($query);
							sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
							//$result = $db->execute();
							}
							catch (Exception $e) {
							//    // catch any database errors.
							//    $db->transactionRollback();
							//    JErrorPage::render($e);
							}
							*/
							foreach ($jl_fields[$jl_table] as $key2 => $value2)
							{
								switch ($key2)
								{
									case 'import':
									case 'ordering':
									case 'checked_out':
									case 'checked_out_time':
									case 'modified':
									case 'modified_by':
									case 'out':
									case 'double':
										break;
									case 'id':
										if (array_key_exists($key2, $jsm_field_array))
										{
											$exportfields1[] = 'import_id';
											$exportfields2[] = 'id';
										}
										break;
									default:
										if (array_key_exists($key2, $jsm_field_array))
										{
											$exportfields1[] = $key2;
											$exportfields2[] = $key2;
										}
										break;
								}
							}

							$select_fields_1 = implode(',', $exportfields1);
							$select_fields_2 = implode(',', $exportfields2);
							$queryjsm = $dbjsm->getQuery(true);
							//$queryjsm->clear();
							$queryjsm = 'INSERT INTO ' . $jsm_table . ' (' . $select_fields_1 . ') SELECT ' . $select_fields_2 . ' FROM ' . $jl_table;

							try
							{
							//sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
                            $dbjsm->setQuery($queryjsm);
                            $dbjsm->execute();
                            $infocolor = self::$storeSuccessColor;
                            $infotext = self::$storeSuccessText;
							}
							catch (Exception $e)
							{
							Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		                    Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
                            $infocolor = self::$storeFailedColor;
                            $infotext = self::$storeFailedText; 
							}

							$my_text .= '<span style="color:' . $infocolor . '"<strong>' . $dbjsm->getAffectedRows() . ' Daten aus der Tabelle: ( ' . $jl_table . ' ) in die Tabelle: ( ' . $jsm_table . ' ) importiert!</strong>' . '</span>';
							$my_text .= '<br />';

							if ($value == 'position')
							{
								$queryjsm = $dbjsm->getQuery(true);
								//$queryjsm->clear();
								$fields = array(
									$dbjsm->quoteName('sports_type_id') . ' = ' . $sports_type_id
								);
								$conditions = array(
									$dbjsm->quoteName('sports_type_id') . ' != ' . $sports_type_id
								);

								$queryjsm->update($dbjsm->quoteName('#__sportsmanagement_position'))->set($fields)->where($conditions);

								try
								{
								//sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
                                $dbjsm->setQuery($queryjsm);
                                $dbjsm->execute();
                                $infocolor = self::$storeSuccessColor;
                                $infotext = self::$storeSuccessText;
								}
								catch (Exception $e)
								{
								Log::add(Text::_($e->getMessage()), Log::ERROR, 'jsmerror');
		                        Log::add(Text::_($e->getCode()), Log::ERROR, 'jsmerror'); 
                                $infocolor = self::$storeFailedColor;
                                $infotext = self::$storeFailedText; 
								}
							}
						}
					}
				}

				/**
				 * unique index wieder löschen
				 */
				/*
                try {
                $query = $db->getQuery(true);
                $query->clear();
                $query = "ALTER TABLE `".$jsm_table."` DROP INDEX import_id "   ;
                $db->setQuery($query);
                sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
                //$result = $db->execute();
                }
                catch (Exception $e) {
                //    // catch any database errors.
                //    $db->transactionRollback();
                //    JErrorPage::render($e);
                }
                */
			}

			// $mtime = microtime();
			// $mtime = explode(" ",$mtime);
			// $mtime = $mtime[1] + $mtime[0];
			$endtime                     = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                   = ($endtime - $starttime);
			self::$_success['Laufzeit:'] = Text::sprintf('This page was created in %1$s seconds', $totaltime);

			self::$_success['Tabellenkopie:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 11
		 */
		if ($jl_table_import_step == 11)
		{
			/**
			 * nach der kopie der tabellen müssen wir die sportart bei den mannschaften setzen.
			 * sonst gibt es in der übersicht der projektmannschaften eine fehlermeldung.
			 */
			$query   = $db->getQuery(true);
			$my_text = '';

			// Fields to update.
			$fields = array(
				$db->quoteName('sports_type_id') . ' = ' . $sports_type_id
			);

			// Conditions for which records should be updated.
			$conditions = array(
				$db->quoteName('sports_type_id') . ' != ' . $sports_type_id
			);
			$query->update($db->quoteName('#__sportsmanagement_team'))->set($fields)->where($conditions);
			$db->setQuery($query);

			try
			{
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}
			catch (Exception $e)
			{
			}

			$query->update($db->quoteName('#__sportsmanagement_project'))->set($fields)->where($conditions);
			$db->setQuery($query);

			try
			{
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}
			catch (Exception $e)
			{
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . self::$db_num_rows . ' Daten in der Tabelle: ( __sportsmanagement_project ) aktualisiert!</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                                   = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                 = ($endtime - $starttime);
			self::$_success['Laufzeit:']               = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Tabellenaktualisierung:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 12
		 */
		if ($jl_table_import_step == 12)
		{
			/**
			 * jetzt werden die importierten vereine zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_club');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result  = $db->loadObjectList();
			$zaehler = 1;

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('club_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('club_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_playground'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
				}

				$zaehler++;
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . 'Verein in den Mannschaften/Spielorte aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			/**
			 * jetzt werden die importierten spielorte zugeordnet
			 */
			$zaehler = 1;
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_playground');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('standard_playground') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('standard_playground') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$zaehler++;
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . 'Sportstätten in den Vereinen aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			/**
			 * jetzt werden die importierten landesverbände zugeordnet
			 */
			$zaehler = 1;
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_associations');
			$query->where('import_id != 0 AND id != import_id');

			try
			{
				$db->setQuery($query);
				$result = $db->loadObjectList();

				foreach ($result as $row)
				{
					// Fields to update.
					$fields = array(
						$db->quoteName('associations') . ' = ' . $row->id
					);

					// Conditions for which records should be updated.
					$conditions = array(
						$db->quoteName('associations') . ' = ' . $row->import_id,
						$db->quoteName('import_id') . ' != 0'
					);
					$query      = $db->getQuery(true);
					$query->clear();
					$query->update($db->quoteName('#__sportsmanagement_club'))->set($fields)->where($conditions);
					$db->setQuery($query);

					try
					{
						sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
					}
					catch (Exception $e)
					{
					}

					$zaehler++;
				}

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . 'Sportstätten in den Vereinen aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}
			catch (Exception $e)
			{
			}

			$endtime                                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']                      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Mannschaften/Spielorte:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 13
		 */
		if ($jl_table_import_step == 13)
		{
			/**
			 * jetzt werden die saisons zugeordnet
			 */

			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_season');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('season_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('season_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
				}

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Saison ' . $row->name . ' im Projekt aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Saison:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 14
		 */
		if ($jl_table_import_step == 14)
		{
			/**
			 * jetzt werden die ligen zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_league');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('league_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('league_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
				}

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Liga ' . $row->name . ' im Projekt aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                        = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                      = ($endtime - $starttime);
			self::$_success['Laufzeit:']    = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Liga:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 15
		 */
		if ($jl_table_import_step == 15)
		{
			/**
			 * jetzt werden die projekte zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_project');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('project_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('project_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_round'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_division'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_position'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_referee'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_template_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_project'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_result'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_result_round'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Projekt ' . $row->name . ' im Runden/Gruppen/Projektpositionen/Projektschiedsrichter/Projektmannschaft aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			/**
			 * master template setzen
			 */
			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('master_template') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('master_template') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}
			}

			$endtime                                                                                            = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                                                                          = ($endtime - $starttime);
			self::$_success['Laufzeit:']                                                                        = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Runden/Gruppen/Projektpositionen/Projektschiedsrichter/Projektmannschaft:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 16
		 */
		if ($jl_table_import_step == 16)
		{
			/**
			 * jetzt werden die positionen/events zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_position');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('position_id') . ' = ' . $row->id,
					$db->quoteName('modified') . ' = ' . $db->Quote('' . $date->toSql() . ''),
					$db->quoteName('modified_by') . ' = ' . $user->get('id')
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('position_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_position'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_position_eventtype'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_position_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Position ' . $row->name . ' im Spieler/Projektpositionen aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_eventtype');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('eventtype_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('eventtype_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_position_eventtype'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				// Fields to update.
				$fields = array(
					$db->quoteName('event_type_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('event_type_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Eventtype ' . $row->name . ' im Positionen aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                                              = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                            = ($endtime - $starttime);
			self::$_success['Laufzeit:']                          = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Personen/Projektpositionen:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 17
		 */
		if ($jl_table_import_step == 17)
		{
			/**
			 * jetzt werden die personen zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('alias as name,id,import_id');
			$query->from('#__sportsmanagement_person');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('person_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('person_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_referee'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Spieler ' . $row->name . ' im Team-Spieler/Team-Staff aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                                           = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                         = ($endtime - $starttime);
			self::$_success['Laufzeit:']                       = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Team-Spieler/Team-Staff:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 18
		 */
		if ($jl_table_import_step == 18)
		{
			/**
			 * jetzt werden die teams zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_team');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('team_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('team_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
				$db->setQuery($query);

				try
				{
					sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				}
				catch (Exception $e)
				{
				}

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Verein ' . $row->name . ' im Projektteam aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                               = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                             = ($endtime - $starttime);
			self::$_success['Laufzeit:']           = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Projektteam:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 19
		 */
		if ($jl_table_import_step == 19)
		{
			/**
			 * jetzt werden die teamplayer/teamstaff zugeordnet
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_project_team');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('projectteam_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('projectteam_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>ProjektTeam ' . $row->name . ' im Teamplayer/Teamstaff aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$my_text = '';
			$query   = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_project_position');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('project_position_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('project_position_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_team_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_referee'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_referee'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>ProjektTeam ' . $row->name . ' im Teamplayer/Teamstaff aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                                        = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                      = ($endtime - $starttime);
			self::$_success['Laufzeit:']                    = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Teamplayer/Teamstaff:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 20
		 */
		if ($jl_table_import_step == 20)
		{
			/**
			 * jetzt werden die spiele aktualisiert staff/player
			 */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_team_staff');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result  = $db->loadObjectList();
			$my_text = '';

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('team_staff_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('team_staff_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Spieler ' . $row->name . ' im Matchstaff aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_team_player');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result  = $db->loadObjectList();
			$my_text = '';

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('teamplayer_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('teamplayer_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// Fields to update.
				$fields = array(
					$db->quoteName('in_for') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('in_for') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Spieler ' . $row->name . ' im Matchplayer aktualisiert !</strong>' . '</span>';
				$my_text .= '<br />';
			}

			$endtime                                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']                      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Matchplayer/Matchstaff:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 21
		 */
		if ($jl_table_import_step == 21)
		{
			/**
			 * jetzt werden die spiele aktualisiert
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_round');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			$zaehler = 1;

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('round_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('round_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$zaehler++;

				// $my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Runden '.$row->name.' in den Spielen aktualisiert !</strong>'.'</span>';
				// $my_text .= '<br />';
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . ' Runden in den Spielen aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_division');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			// $my_text = '';

			$zaehler = 1;

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('division_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('division_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_project_team'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_result_round'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$zaehler++;

				// $my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Gruppen '.$row->name.' in den Spielen/Projektteam aktualisiert !</strong>'.'</span>';
				// $my_text .= '<br />';
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . ' Gruppen in den Spielen/Projektteam aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_project_team');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			// $my_text = '';
			$zaehler = 1;

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('projectteam1_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('projectteam1_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// Fields to update.
				$fields = array(
					$db->quoteName('projectteam2_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('projectteam2_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// Fields to update.
				$fields = array(
					$db->quoteName('projectteam_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('projectteam_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$zaehler++;

				// $my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Mannschaften-Paarung '.$row->name.' in den Spielen aktualisiert !</strong>'.'</span>';
				// $my_text .= '<br />';
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . ' Mannschaften-Paarung in den Spielen aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Spiele:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 22
		 */
		if ($jl_table_import_step == 22)
		{
			/**
			 * jetzt werden die spiel id´s eingetragen
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_match');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result  = $db->loadObjectList();
			$zaehler = 1;

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('match_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('match_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_referee'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_result'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$zaehler++;

				// $my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Spiel-ID '.$row->name.' in den Match-Player/Match-Staff/Match-Referee/Match-Event aktualisiert !</strong>'.'</span>';
				// $my_text .= '<br />';
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . ' Spiel-ID in den Match-Player/Match-Staff/Match-Referee/Match-Event aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Spiele:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 23
		 */
		if ($jl_table_import_step == 23)
		{
			/**
			 * ab hier beginnt die umsetzung in die neue jsm struktur
			 */
			$my_text = '';
			/**
			 * tabelle: sportsmanagement_season_team_id
			 * feld import_id einfügen
			 */
			$jsm_table = '#__sportsmanagement_season_team_id';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $result = $db->execute();
			}
			catch (Exception $e)
			{
				//    // catch any database errors.
				//    $db->transactionRollback();
				//    JErrorPage::render($e);
			}

			$query = $db->getQuery(true);
			$query->clear();
			/**
			 * löschen die das feld import_id gefüllt haben
			 */
			$conditions = array(
				$db->quoteName('import_id') . ' != 0'
			);
			$query->delete($db->quoteName($jsm_table));
			$query->where($conditions);

			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

			/**
			 * tabelle: sportsmanagement_season_person_id
			 * feld import_id einfügen
			 */
			$jsm_table = '#__sportsmanagement_season_person_id';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $result = $db->execute();
			}
			catch (Exception $e)
			{
				//    // catch any database errors.
				//    $db->transactionRollback();
				//    JErrorPage::render($e);
			}

			$query = $db->getQuery(true);
			$query->clear();
			/**
			 * löschen die das feld import_id gefüllt haben
			 */
			$conditions = array(
				$db->quoteName('import_id') . ' != 0'
			);
			$query->delete($db->quoteName($jsm_table));
			$query->where($conditions);

			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

			/**
			 * tabelle: sportsmanagement_season_team_person_id
			 * feld import_id einfügen
			 */
			$jsm_table = '#__sportsmanagement_season_team_person_id';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $result = $db->execute();
			}
			catch (Exception $e)
			{
				//    // catch any database errors.
				//    $db->transactionRollback();
				//    JErrorPage::render($e);
			}

			$query = $db->getQuery(true);
			$query->clear();
			/**
			 * löschen die das feld import_id gefüllt haben
			 */
			$conditions = array(
				$db->quoteName('import_id') . ' != 0'
			);
			$query->delete($db->quoteName($jsm_table));
			$query->where($conditions);

			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

			/**
			 * tabelle: sportsmanagement_person_project_position
			 * feld import_id einfügen
			 */
			$jsm_table = '#__sportsmanagement_person_project_position';

			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $result = $db->execute();
			}
			catch (Exception $e)
			{
				//    // catch any database errors.
				//    $db->transactionRollback();
				//    JErrorPage::render($e);
			}

			$query = $db->getQuery(true);
			$query->clear();
			/**
			 * löschen die das feld import_id gefüllt haben
			 */
			$conditions = array(
				$db->quoteName('import_id') . ' != 0'
			);
			$query->delete($db->quoteName($jsm_table));
			$query->where($conditions);

			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

			/**
			 * jetzt als erstes die mannschaften umsetzen
			 */

			/**
			 * unique index löschen project_id/team_id
			 */
			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE `#__sportsmanagement_project_team` DROP INDEX combi ";
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $result = $db->execute();
			}
			catch (Exception $e)
			{
				//    // catch any database errors.
				//    $db->transactionRollback();
				//    JErrorPage::render($e);
			}

			$query = $db->getQuery(true);
			$query->clear();
			$query->select('p.id,p.season_id,pt.id as pt_id,pt.team_id');
			$query->from('#__sportsmanagement_project as p');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.project_id = p.id');
			$query->where('p.import_id != 0');
			$query->where('pt.import_id != 0');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$new_id = 0;
				$query->clear();
				$query->select('id');

				// From table
				$query->from('#__sportsmanagement_season_team_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = ' . $row->team_id);
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp              = new stdClass;
					$temp->season_id   = $row->season_id;
					$temp->team_id     = $row->team_id;
					$temp->import_id   = 1;
					$temp->published   = 1;
					$temp->modified    = $db->Quote('' . $modified . '');
					$temp->modified_by = $modified_by;

					try
					{
						$result_insert = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_id', $temp);
					}
					catch (Exception $e)
					{
						// Catch any database errors.
						//    $db->transactionRollback();
						JErrorPage::render($e);
					}

					if ($result_insert)
					{
						$new_id = $db->insertid();
					}
				}

				// Create an object for the record we are going to update.
				$object = new stdClass;

				// Must be a valid primary key value.
				$object->id      = $row->pt_id;
				$object->team_id = $new_id;

				// Update their details in the users table using id as the primary key.
				$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_project_team', $object, 'id');
			}

			/**
			 * unique index anlegen auf project_id/team_id
			 */
			try
			{
				$query = $db->getQuery(true);
				$query->clear();
				$query = "ALTER TABLE `#__sportsmanagement_project_team` ADD UNIQUE `combi` (`project_id`, `team_id`) ";
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $result = $db->execute();
			}
			catch (Exception $e)
			{
				//    // catch any database errors.
				//    $db->transactionRollback();
				//    JErrorPage::render($e);
			}

			/**
			 * tabelle: sportsmanagement_match_staff
			 * schlüsselfelder: match_id, team_staff_id, project_position_id
			 *
			 * team_staff_id ist verknüpft mit der tabelle: sportsmanagement_team_staff
			 * schlüsselfelder: projectteam_id, person_id, project_position_id
			 */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('ts.id as team_staff_id,ts.projectteam_id,ts.person_id');
			$query->select('p.id as project_id,p.season_id');
			$query->select('sst.team_id');
			$query->from('#__sportsmanagement_team_staff as ts');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.id = ts.projectteam_id');
			$query->join('INNER', '#__sportsmanagement_project as p ON p.id = pt.project_id');
			$query->join('INNER', '#__sportsmanagement_season_team_id as sst ON sst.id = pt.team_id and sst.season_id = p.season_id');
			$query->where('p.import_id != 0');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$new_id = 0;
				$query->clear();
				$query->select('id');

				// From table
				$query->from('#__sportsmanagement_season_person_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('person_id = ' . $row->person_id);
				$query->where('team_id = ' . $row->team_id);
				$query->where('persontype = 2');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp              = new stdClass;
					$temp->season_id   = $row->season_id;
					$temp->person_id   = $row->person_id;
					$temp->team_id     = $row->team_id;
					$temp->persontype  = 2;
					$temp->import_id   = 1;
					$temp->published   = 1;
					$temp->modified    = $db->Quote('' . $modified . '');
					$temp->modified_by = $modified_by;

					try
					{
						$result_insert = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
					}
					catch (Exception $e)
					{
					}

					if ($result_insert)
					{
						$new_id = $db->insertid();
					}
				}

				$new_id = 0;
				$query->clear();
				$query->select('id');

				// From table
				$query->from('#__sportsmanagement_season_team_person_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('person_id = ' . $row->person_id);
				$query->where('team_id = ' . $row->team_id);
				$query->where('persontype = 2');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp              = new stdClass;
					$temp->season_id   = $row->season_id;
					$temp->person_id   = $row->person_id;
					$temp->team_id     = $row->team_id;
					$temp->persontype  = 2;
					$temp->import_id   = 1;
					$temp->published   = 1;
					$temp->modified    = $db->Quote('' . $modified . '');
					$temp->modified_by = $modified_by;

					try
					{
						$result_insert = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_person_id', $temp);
					}
					catch (Exception $e)
					{
					}

					if ($result_insert)
					{
						$new_id = $db->insertid();
					}
				}

				// Fields to update.
				$fields = array(
					$db->quoteName('team_staff_id') . ' = ' . $new_id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('team_staff_id') . ' = ' . $row->team_staff_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}

			/**
			 * tabelle: sportsmanagement_match_player
			 * schlüsselfelder: match_id, teamplayer_id, project_position_id
			 *
			 * team_staff_id ist verknüpft mit der tabelle: sportsmanagement_team_layer
			 * schlüsselfelder: projectteam_id, person_id, project_position_id
			 */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('ts.id as teamplayer_id,ts.projectteam_id,ts.person_id');
			$query->select('p.id as project_id,p.season_id');
			$query->select('sst.team_id');
			$query->from('#__sportsmanagement_team_player as ts');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.id = ts.projectteam_id');
			$query->join('INNER', '#__sportsmanagement_project as p ON p.id = pt.project_id');
			$query->join('INNER', '#__sportsmanagement_season_team_id as sst ON sst.id = pt.team_id and sst.season_id = p.season_id');
			$query->where('p.import_id != 0');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$new_id = 0;
				$query->clear();
				$query->select('id');

				// From table
				$query->from('#__sportsmanagement_season_person_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('person_id = ' . $row->person_id);
				$query->where('team_id = ' . $row->team_id);
				$query->where('persontype = 1');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp              = new stdClass;
					$temp->season_id   = $row->season_id;
					$temp->person_id   = $row->person_id;
					$temp->team_id     = $row->team_id;
					$temp->persontype  = 1;
					$temp->import_id   = 1;
					$temp->published   = 1;
					$temp->modified    = $db->Quote('' . $modified . '');
					$temp->modified_by = $modified_by;

					try
					{
						$result_insert = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
					}
					catch (Exception $e)
					{
					}

					if ($result_insert)
					{
						$new_id = $db->insertid();
					}
				}

				$new_id = 0;
				$query->clear();
				$query->select('id');

				// From table
				$query->from('#__sportsmanagement_season_team_person_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('person_id = ' . $row->person_id);
				$query->where('team_id = ' . $row->team_id);
				$query->where('persontype = 1');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp              = new stdClass;
					$temp->season_id   = $row->season_id;
					$temp->person_id   = $row->person_id;
					$temp->team_id     = $row->team_id;
					$temp->persontype  = 1;
					$temp->import_id   = 1;
					$temp->published   = 1;
					$temp->modified    = $db->Quote('' . $modified . '');
					$temp->modified_by = $modified_by;

					try
					{
						// Insert the object into table.
						$result_insert = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_person_id', $temp);
					}
					catch (Exception $e)
					{
						// Catch any database errors.
						//    $db->transactionRollback();
						JErrorPage::render($e);
					}

					if ($result_insert)
					{
						$new_id = $db->insertid();
					}
				}

				// Fields to update.
				$fields = array(
					$db->quoteName('teamplayer_id') . ' = ' . $new_id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('teamplayer_id') . ' = ' . $row->teamplayer_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->update($db->quoteName('#__sportsmanagement_match_event'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				$query->update($db->quoteName('#__sportsmanagement_match_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// Fields to update.
				$fields = array(
					$db->quoteName('in_for') . ' = ' . $new_id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('in_for') . ' = ' . $row->teamplayer_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}

			/**
			 * jetzt werden die project referees umgesetzt
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_project_referee');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('project_referee_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('project_referee_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_referee'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				// $my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Match-Referee '.$row->name.' in den Match aktualisiert !</strong>'.'</span>';
				// $my_text .= '<br />';
			}

			$query = $db->getQuery(true);
			$query->clear();
			$query->select('pr.id,pr.project_id,pr.person_id,p.season_id');
			$query->from('#__sportsmanagement_project_referee as pr');
			$query->join('INNER', '#__sportsmanagement_project AS p ON pr.project_id = p.id');
			$query->where('pr.import_id != 0');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$new_id = 0;
				$query->clear();
				$query->select('id');

				// From table
				$query->from('#__sportsmanagement_season_person_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('person_id = ' . $row->person_id);
				$query->where('persontype = 3');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp            = new stdClass;
					$temp->season_id = $row->season_id;
					$temp->person_id = $row->person_id;

					// $temp->team_id = $row->team_id;
					$temp->persontype  = 3;
					$temp->import_id   = 1;
					$temp->published   = 1;
					$temp->modified    = $db->Quote('' . $modified . '');
					$temp->modified_by = $modified_by;

					try
					{
						// Insert the object into table.
						$result_insert = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
					}
					catch (Exception $e)
					{
					}

					if ($result_insert)
					{
						$new_id = $db->insertid();
					}
				}

				// Create an object for the record we are going to update.
				$object = new stdClass;

				// Must be a valid primary key value.
				$object->id        = $row->id;
				$object->person_id = $new_id;

				// Update their details in the users table using id as the primary key.
				$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_project_referee', $object, 'id');
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong> Spieler wurden umgesetzt !</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                          = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                        = ($endtime - $starttime);
			self::$_success['Laufzeit:']      = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Spiele:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 24
		 */
		if ($jl_table_import_step == 24)
		{
			/**
			 * statistic id umsetzen
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('id as name,id,import_id');
			$query->from('#__sportsmanagement_statistic');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			$zaehler = 1;

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('statistic_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('statistic_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_staff_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_match_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_position_statistic'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$zaehler++;

				// $my_text .= '<span style="color:'.self::$storeSuccessColor. '"<strong>Match-Statistic '.$row->name.' in den Match aktualisiert !</strong>'.'</span>';
				// $my_text .= '<br />';
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . ' Match-Statistic in den Match aktualisiert !</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                                   = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                 = ($endtime - $starttime);
			self::$_success['Laufzeit:']               = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Match-Statistic:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 25
		 */
		if ($jl_table_import_step == 25)
		{
			/**
			 * projekt positionen pro spieler einfügen
			 */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('p.id as project_id, p.season_id,st.team_id');
			$query->from('#__sportsmanagement_project as p');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.project_id = p.id');
			$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
			$db->setQuery($query);
			$result  = $db->loadObjectList();
			$mdl     = BaseDatabaseModel::getInstance("TeamPersons", "sportsmanagementModel");
			$zaehler = 1;

			foreach ($result as $row)
			{
				$mdl->checkProjectPositions($row->project_id, 1, $row->team_id, $row->season_id, 1);
				$mdl->checkProjectPositions($row->project_id, 2, $row->team_id, $row->season_id, 1);
				$zaehler++;
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>' . $zaehler . ' Projektpositionen pro Spieler eingefügt !</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                                     = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                                   = ($endtime - $starttime);
			self::$_success['Laufzeit:']                 = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Projektpositionen:'] = $my_text;
			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 26
		 */
		if ($jl_table_import_step == 26)
		{
			$my_text = '';

			/**
			 * zum schluss werden noch die bilderpfade umgesetzt
			 */
			$mdl = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel");

			$mdl->setNewPicturePath();
			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Bilderpfade angepasst !</strong>' . '</span>';
			$my_text .= '<br />';

			$mdl->setNewComponentName();
			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Komponentenname angepasst !</strong>' . '</span>';
			$my_text .= '<br />';

			$mdl->setParamstoJSON();
			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Feld Params in template_config in JSON umgesetzt !</strong>' . '</span>';
			$my_text .= '<br />';

			/**
			 * timestamp im projekt setzen
			 */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('p.id,p.modified');
			$query->from('#__sportsmanagement_project as p');
			$query->where("p.modified_timestamp = 0");
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $projekt)
			{
				if ($projekt->modified != $db->getNullDate())
				{
					$projekt->modified_timestamp = sportsmanagementHelper::getTimestamp($projekt->modified);

					// Create an object for the record we are going to update.
					$object = new stdClass;

					// Must be a valid primary key value.
					$object->id                 = $projekt->id;
					$object->modified_timestamp = $projekt->modified_timestamp;

					// Update their details in the table using id as the primary key.
					$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_project', $object, 'id');
				}
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Timestamp in den projekten gesetzt !</strong>' . '</span>';
			$my_text .= '<br />';

			/**
			 * timestamp in den spielen setzen
			 */
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('m.id,m.match_date');
			$query->from('#__sportsmanagement_match as m');
			$query->where("m.match_timestamp = 0");
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $match)
			{
				if ($match->match_date != $db->getNullDate())
				{
					$match->match_timestamp = sportsmanagementHelper::getTimestamp($match->match_date);

					// Create an object for the record we are going to update.
					$object = new stdClass;

					// Must be a valid primary key value.
					$object->id              = $match->id;
					$object->match_timestamp = $match->match_timestamp;

					// Update their details in the table using id as the primary key.
					$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id');
				}
			}

			$my_text .= '<span style="color:' . self::$storeSuccessColor . '"<strong>Timestamp in den spielen gesetzt !</strong>' . '</span>';
			$my_text .= '<br />';

			$endtime                               = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                             = ($endtime - $starttime);
			self::$_success['Laufzeit:']           = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Bilderpfade:'] = $my_text;

			$my_text = '';
			$query->clear();
			$query->select('p.id');
			$query->from('#__sportsmanagement_project as p');
			$db->setQuery($query);
			$resultp = $db->loadObjectList();

			foreach ($resultp as $project)
			{
				$query->clear();
				$query->select('mp.match_id');
				$query->from('#__sportsmanagement_match_player as mp');
				$query->join('INNER', '#__sportsmanagement_match as m ON m.id = mp.match_id');
				$query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
				$query->where('r.project_id = ' . $project->id);
				$query->where('mp.project_position_id != 0');
				$db->setQuery($query);
				$position = $db->loadColumn();

				if ($position)
				{
					$result    = array_unique($position);
					$match_ids = implode(",", $result);
					$query->clear();
					$query->select('pt.*,pt.id AS positiontoolid');
					$query->from('#__sportsmanagement_project_position AS pt');
					$query->select('po.*,po.name AS name');
					$query->join('LEFT', '#__sportsmanagement_position po ON pt.position_id = po.id');
					$query->select('pid.name AS parent_name');
					$query->join('LEFT', '#__sportsmanagement_position pid ON po.parent_id = pid.id');
					$query->where('pt.project_id = ' . $project->id);
					$db->setQuery($query);
					$items = $db->loadObjectList();

					foreach ($items as $item)
					{
						$query->clear();

						// Fields to update.
						$fields = array(
							$db->quoteName('project_position_id') . ' = ' . $item->position_id
						);

						// Conditions for which records should be updated.
						$conditions = array(
							$db->quoteName('project_position_id') . ' = ' . $item->positiontoolid,
							$db->quoteName('match_id') . ' IN (' . $match_ids . ')'
						);

						$query->update($db->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
						$db->setQuery($query);
						$resultupdate = $db->execute();
					}
				}
			}

			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', $jl_table_import_step);
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

		/**
		 * importschritt 27
		 */
		if ($jl_table_import_step == 27)
		{
			/**
			 * fix prediction game (prediction_ids && fav_team && champ tipps)
			 */
			$my_text = '';

			// $my_text .= '<span style="color:'.self::$storeInfo. '"<strong> ( '.__METHOD__.' )  ( '.__LINE__.' ) </strong>'.'</span>';
			// $my_text .= '<br />';
			$query = $db->getQuery(true);
			$query->clear();
			$query->select('name,id,import_id');
			$query->from('#__sportsmanagement_prediction_game');
			$query->where('import_id != 0 AND id != import_id');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Fields to update.
				$fields = array(
					$db->quoteName('prediction_id') . ' = ' . $row->id
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('prediction_id') . ' = ' . $row->import_id,
					$db->quoteName('import_id') . ' != 0'
				);
				$query      = $db->getQuery(true);

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_admin'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_member'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_project'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_result'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_result_round'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_template'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}

			/**
			 * now update fav_team && champ tipps ( <project_id>,<project_tem_id>[;<project_id>,<project_tem_id>;....] )
			 */
			// Create mappings for ids
			$mapped_project_id[0]      = 0;
			$mapped_project_team_id[0] = 0;

			$query->clear();
			$query->select('id,import_id');
			$query->from('#__sportsmanagement_project');
			$query->where('import_id != 0');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$mapped_project_id[$row->import_id] = $row->id;
			}

			$query->clear();
			$query->select('id,import_id');
			$query->from('#__sportsmanagement_project_team');
			$query->where('import_id != 0');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$mapped_project_team_id[$row->import_id] = $row->id;
			}

			// Now iterate trough member table and update ids
			$query->clear();
			$query->select('id,fav_team,champ_tipp');
			$query->from('#__sportsmanagement_prediction_member');
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				if ($row->id == 165)
				{
					$ttt = 0;
				}

				$fav_teams   = array();
				$champ_teams = array();

				if ($row->fav_team)
				{
					$dFavTeamsList = array();
					$sFavTeamsList = explode(';', $row->fav_team);

					foreach ($sFavTeamsList AS $key => $value)
					{
						$dFavTeamsList[] = explode(',', $value);
					}

					foreach ($dFavTeamsList AS $key => $value)
					{
						$fav_teams[$value[0]] = $value[1];
					}
				}

				if ($row->champ_tipp)
				{
					$dChampTeamsList = array();
					$sChampTeamsList = explode(';', $row->champ_tipp);

					foreach ($sChampTeamsList AS $key => $value)
					{
						$dChampTeamsList[] = explode(',', $value);
					}

					foreach ($dChampTeamsList AS $key => $value)
					{
						$champ_teams[$value[0]] = $value[1];
					}
				}

				$dFavTeams = '';

				foreach ($fav_teams AS $key => $value)
				{
					$dFavTeams .= $mapped_project_id[$key] . ',' . $mapped_project_team_id[$value] . ';';
				}

				$dFavTeams   = trim($dFavTeams, ';');
				$dChampTeams = '';

				foreach ($champ_teams AS $key => $value)
				{
					$dChampTeams .= $mapped_project_id[$key] . ',' . $mapped_project_team_id[$value] . ';';
				}

				$dChampTeams = trim($dChampTeams, ';');

				// Fields to update.
				$fields = array(
					$db->quoteName('fav_team') . ' = ' . $db->quote($dFavTeams),
					$db->quoteName('champ_tipp') . ' = ' . $db->quote($dChampTeams)
				);

				// Conditions for which records should be updated.
				$conditions = array(
					$db->quoteName('id') . ' = ' . $row->id
				);

				$query->clear();
				$query->update($db->quoteName('#__sportsmanagement_prediction_member'))->set($fields)->where($conditions);
				$db->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
			}

			$endtime                              = sportsmanagementModeldatabasetool::getRunTime();
			$totaltime                            = ($endtime - $starttime);
			self::$_success['Laufzeit:']          = Text::sprintf('This page was created in %1$s seconds', $totaltime);
			self::$_success['Update Prediction:'] = $my_text;

			$jl_table_import_step++;
			$jinput->set('jl_table_import_step', 'ENDE');
			Factory::getDocument()->addScriptOptions('success', self::$_success);

			return self::$_success;
		}

	}

}
