<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       joomleagueimport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filesystem\File;

$maxImportTime = 1920;

if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

/**
 * sportsmanagementModeljoomleagueimport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljoomleagueimport extends ListModel
{

	/**
	 * sportsmanagementModeljoomleagueimport::newstructurjlimport()
	 *
	 * @param   mixed  $season_id
	 * @param   mixed  $jl_table
	 * @param   mixed  $jsm_table
	 *
	 * @return void
	 */
	function newstructurjlimport($season_id, $jl_table, $jsm_table, $project_id)
	{
		$app       = Factory::getApplication();
		$db        = Factory::getDbo();
		$option    = Factory::getApplication()->input->getCmd('option');
		$starttime = microtime();
		$query     = $db->getQuery(true);

		/**
		 * hier muss auch wieder zwischen den joomla versionen unterschieden werden
		 * felder für den import auslesen
		 */
		if (version_compare(JVERSION, '3.0.0', 'ge'))
		{
			/** Joomla! 3.0 code here */
			$jl_fields              = $db->getTableColumns($jl_table);
			$jsm_fields             = $db->getTableColumns($jsm_table);
			$jl_fields[$jl_table]   = $jl_fields;
			$jsm_fields[$jsm_table] = $jsm_fields;
		}
		elseif (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			/** Joomla! 2.5 code here */
			$jl_fields  = $db->getTableFields($jl_table);
			$jsm_fields = $db->getTableFields($jsm_table);
		}

		/** umsetzung der project teams */
		if (preg_match("/project_team/i", $jsm_table))
		{
			$my_text;
			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeInfo . '"<strong> ( ' . __METHOD__ . ' )  ( ' . __LINE__ . ' ) </strong>' . '</span>';
			$my_text .= '<br />';
			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Daten aus der Tabelle: ( ' . $jl_table . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('p.name as importprojectname');
			$query->from($jl_table . ' AS pt');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
			$query->where('p.id = ' . $project_id);

			if ($season_id)
			{
				$query->select('p.season_id');
				$query->where('p.season_id = ' . $season_id);
			}

			$db->setQuery($query);
			$importprojectname = $db->loadResult();

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Daten aus dem Projekt: ( ' . $importprojectname . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('pt.*');
			$query->select('p.name as importprojectname');
			$query->from($jl_table . ' AS pt');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
			$query->where('p.id = ' . $project_id);

			if ($season_id)
			{
				$query->select('p.season_id');
				$query->where('p.season_id = ' . $season_id);
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$query->clear();
				$query->select('id');

				$query->from('#__sportsmanagement_season_team_id');
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = ' . $row->team_id);
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp            = new stdClass;
					$temp->season_id = $row->season_id;
					$temp->team_id   = $row->team_id;

					$result = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_id', $temp);

					if ($result)
					{
						$new_id = $db->insertid();
					}
				}

				$object          = new stdClass;
				$jsm_field_array = $jsm_fields[$jsm_table];

				foreach ($jl_fields[$jl_table] as $key2 => $value2)
				{
					if (array_key_exists($key2, $jsm_field_array))
					{
						$object->$key2 = $row->$key2;
					}
				}

				/** Jetzt die neue team_id */
				$object->team_id = $new_id;
				$result2 = Factory::getDbo()->insertObject($jsm_table, $object);

				if ($result2)
				{
					/** Alles in ordnung */
				}
				else
				{
					/** Eintrag schon vorhanden, ein update */
					$tblProjectteam = Table::getInstance('Projectteam', 'sportsmanagementtable');
					$tblProjectteam->load($row->id);

					if (empty($tblProjectteam->team_id))
					{
						$tblProjectteam->team_id = $new_id;

						if (!$tblProjectteam->store())
						{
						}
					}
				}

				$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeSuccessColor . '"<strong>Projectteam: ( ' . $row->team_id . ' ) mit ( ' . $new_id . ' ) umgesetzt!</strong>' . '</span>';
				$my_text .= '<br />';
			}

			sportsmanagementModeljoomleagueimports::$_success['Projectteam:'] .= $my_text;
		}
		elseif (preg_match("/team_player/i", $jsm_table))
		{
			/** umsetzung der teamplayer */
			sportsmanagementModeljoomleagueimports::$team_player[$project_id][0] = 0;
			$my_text;
			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeInfo . '"<strong> ( ' . __METHOD__ . ' )  ( ' . __LINE__ . ' ) </strong>' . '</span>';
			$my_text .= '<br />';

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Daten aus der Tabelle: ( ' . $jl_table . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('p.name as importprojectname');
			$query->from('#__sportsmanagement_project AS p');
			$query->where('p.id = ' . $project_id);
			$db->setQuery($query);
			$importprojectname = $db->loadResult();

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>team_player Daten aus dem Projekt: ( ' . $importprojectname . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('tp.*,st.team_id');
			$query->select('pers.firstname,pers.lastname');
			$query->from($jl_table . ' AS tp');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
			$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
			$query->join('INNER', '#__sportsmanagement_person as pers ON pers.id = tp.person_id ');

			$query->where('p.id = ' . $project_id);

			if ($season_id)
			{
				$query->select('p.season_id');
				$query->where('p.season_id = ' . $season_id);
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$query->clear();
				$query->select('id');
				$query->from('#__sportsmanagement_season_person_id');
				$query->where('person_id = ' . $row->person_id);
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = ' . $row->team_id);
				$query->where('persontype = 1');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					/** Als erstes wird der spieler der saison zugeordnet */
					$temp             = new stdClass;
					$temp->person_id  = $row->person_id;
					$temp->season_id  = $row->season_id;
					$temp->team_id    = $row->team_id;
					$temp->picture    = $row->picture;
					$temp->persontype = 1;
					$result = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
				}

				/** Ist der spieler schon in der season team person tabelle ? */
				$query->clear();
				$query->select('id');
				$query->from('#__sportsmanagement_season_team_person_id');
				$query->where('person_id = ' . $row->person_id);
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = ' . $row->team_id);
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp                      = new stdClass;
					$temp->season_id           = $row->season_id;
					$temp->team_id             = $row->team_id;
					$temp->person_id           = $row->person_id;
					$temp->picture             = $row->picture;
					$temp->project_position_id = $row->project_position_id;
					$temp->persontype          = 1;
					$temp->active              = 1;
					$temp->published           = 1;
					$result = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_person_id', $temp);

					if ($result)
					{
						$new_id = $db->insertid();
					}
					else
					{
					}

					$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeSuccessColor . '"<strong>Team PLayer: [' . $row->firstname . ' - ' . $row->lastname . ' ] ( ' . $row->person_id . ' ) mit ( ' . $new_id . ' ) umgesetzt!</strong>' . '</span>';
					$my_text .= '<br />';
				}
				else
				{
					$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Team PLayer: [' . $row->firstname . ' - ' . $row->lastname . ' ] ( ' . $row->person_id . ' ) mit ( ' . $new_id . ' ) vorhanden!</strong>' . '</span>';
					$my_text .= '<br />';
				}

				/** Kein update, sondern den datensatz aus der importierten tabelle löschen */
				$query->clear();
				$conditions = array(
					$db->quoteName('id') . ' = ' . $row->id
				);

				$query->delete($db->quoteName($jsm_table));
				$query->where($conditions);
				$db->setQuery($query);
				$result_update = $db->execute();

				if (!$result_update)
				{
				}
				else
				{
				}

				sportsmanagementModeljoomleagueimports::$team_player[$project_id][$row->id] = $new_id;
			}

			sportsmanagementModeljoomleagueimports::$_success['Team Player Projekt(' . $project_id . '):'] .= $my_text;
		}
		elseif (preg_match("/team_staff/i", $jsm_table))
		{
			/**
			 * umsetzung der team mitarbeiter
			 */

			$my_text;
			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeInfo . '"<strong> ( ' . __METHOD__ . ' )  ( ' . __LINE__ . ' ) </strong>' . '</span>';
			$my_text .= '<br />';

			sportsmanagementModeljoomleagueimports::$team_staff[$project_id][0] = 0;

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Daten aus der Tabelle: ( ' . $jl_table . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('p.name as importprojectname');
			$query->from('#__sportsmanagement_project AS p');
			$query->where('p.id = ' . $project_id);
			$db->setQuery($query);
			$importprojectname = $db->loadResult();

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>team_player Daten aus dem Projekt: ( ' . $importprojectname . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('tp.*,st.team_id');
			$query->from($jl_table . ' AS tp');
			$query->select('pers.firstname,pers.lastname');
			$query->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.id = tp.projectteam_id');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = pt.project_id');
			$query->join('INNER', '#__sportsmanagement_season_team_id as st ON st.id = pt.team_id ');
			$query->join('INNER', '#__sportsmanagement_person as pers ON pers.id = tp.person_id ');
			$query->where('p.id = ' . $project_id);

			if ($season_id)
			{
				$query->select('p.season_id');
				$query->where('p.season_id = ' . $season_id);
			}

			$db->setQuery($query);

			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$query->clear();
				$query->select('id');
				$query->from('#__sportsmanagement_season_person_id');
				$query->where('person_id = ' . $row->person_id);
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = ' . $row->team_id);
				$query->where('persontype = 2');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					/** Als erstes wird der spieler der saison zugeordnet */
					$temp             = new stdClass;
					$temp->person_id  = $row->person_id;
					$temp->season_id  = $row->season_id;
					$temp->team_id    = $row->team_id;
					$temp->picture    = $row->picture;
					$temp->persontype = 2;
					$result = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
				}

				/** Ist der spieler schon in der season team person tabelle ? */
				$query = $db->getQuery(true);
				$query->clear();
				$query->select('id');
				$query->from('#__sportsmanagement_season_team_person_id');
				$query->where('person_id = ' . $row->person_id);
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = ' . $row->team_id);
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					$temp                      = new stdClass;
					$temp->season_id           = $row->season_id;
					$temp->team_id             = $row->team_id;
					$temp->person_id           = $row->person_id;
					$temp->picture             = $row->picture;
					$temp->project_position_id = $row->project_position_id;
					$temp->persontype          = 2;
					$temp->active              = 1;
					$temp->published           = 1;
					$result = Factory::getDbo()->insertObject('#__sportsmanagement_season_team_person_id', $temp);

					if ($result)
					{
						$new_id = $db->insertid();
					}
					else
					{
					}

					$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeSuccessColor . '"<strong>Team Staff: [' . $row->firstname . ' - ' . $row->lastname . ' ] ( ' . $row->id . ' ) mit ( ' . $new_id . ' ) umgesetzt!</strong>' . '</span>';
					$my_text .= '<br />';
				}
				else
				{
					$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Team Staff: [' . $row->firstname . ' - ' . $row->lastname . ' ] ( ' . $row->id . ' ) mit ( ' . $new_id . ' ) vorhanden!</strong>' . '</span>';
					$my_text .= '<br />';
				}

				$query->clear();
				$conditions = array(
					$db->quoteName('id') . ' = ' . $row->id
				);

				$query->delete($db->quoteName($jsm_table));
				$query->where($conditions);
				$db->setQuery($query);
				$result_update = $db->execute();

				if (!$result_update)
				{
				}
				else
				{
				}

				sportsmanagementModeljoomleagueimports::$team_staff[$project_id][$row->id] = $new_id;
			}

			sportsmanagementModeljoomleagueimports::$_success['Team Staff (' . $project_id . '):'] .= $my_text;

		}
		elseif (preg_match("/project_referee/i", $jsm_table))
		{
			/** projekt schiedsrichter */
			$my_text;
			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeInfo . '"<strong> ( ' . __METHOD__ . ' )  ( ' . __LINE__ . ' ) </strong>' . '</span>';
			$my_text .= '<br />';

			sportsmanagementModeljoomleagueimports::$project_referee[$project_id][0] = 0;

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Daten aus der Tabelle: ( ' . $jsm_table . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('p.name as importprojectname');
			$query->from('#__sportsmanagement_project AS p');
			$query->where('p.id = ' . $project_id);
			$db->setQuery($query);
			$importprojectname = $db->loadResult();

			$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>team_player Daten aus dem Projekt: ( ' . $importprojectname . ' ) werden in die neue Struktur umgesetzt!"!</strong>' . '</span>';
			$my_text .= '<br />';

			$query->clear();
			$query->select('tp.*');
			$query->select('pers.firstname,pers.lastname');
			$query->from($jl_table . ' AS tp');
			$query->join('INNER', '#__sportsmanagement_project AS p ON p.id = tp.project_id');
			$query->join('INNER', '#__sportsmanagement_person as pers ON pers.id = tp.person_id ');
			$query->where('p.id = ' . $project_id);

			if ($season_id)
			{
				$query->select('p.season_id');
				$query->where('p.season_id = ' . $season_id);
			}

			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				$query->clear();
				$query->select('id');
				$query->from('#__sportsmanagement_season_person_id');
				$query->where('person_id = ' . $row->person_id);
				$query->where('season_id = ' . $row->season_id);
				$query->where('team_id = 0');
				$query->where('persontype = 3');
				$db->setQuery($query);
				$new_id = $db->loadResult();

				if (!$new_id)
				{
					/** Als erstes wird der spieler der saison zugeordnet */
					$temp             = new stdClass;
					$temp->person_id  = $row->person_id;
					$temp->season_id  = $row->season_id;
					$temp->team_id    = 0;
					$temp->picture    = $row->picture;
					$temp->persontype = 3;
					$temp->published  = 1;
					$result = Factory::getDbo()->insertObject('#__sportsmanagement_season_person_id', $temp);
				}

				if ($result)
				{
					$new_id  = $db->insertid();
					$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$storeSuccessColor . '"<strong>Project Referee: [' . $row->firstname . ' - ' . $row->lastname . ' ] person_id: ( ' . $row->person_id . ' ) mit ( ' . $new_id . ' ) umgesetzt!</strong>' . '</span>';
					$my_text .= '<br />';
				}
				else
				{
					$query = $db->getQuery(true);
					$query->clear();
					$query->select('id');
					$query->from('#__sportsmanagement_season_person_id');
					$query->where('person_id = ' . $row->person_id);
					$query->where('season_id = ' . $row->season_id);
					$query->where('persontype = 3');
					$db->setQuery($query);
					$new_id  = $db->loadResult();
					$my_text .= '<span style="color:' . sportsmanagementModeljoomleagueimports::$existingInDbColor . '"<strong>Project Referee: [' . $row->firstname . ' - ' . $row->lastname . ' ] person_id: ( ' . $row->person_id . ' ) mit ( ' . $new_id . ' ) vorhanden!</strong>' . '</span>';
					$my_text .= '<br />';
				}

				sportsmanagementModeljoomleagueimports::$project_referee[$project_id][$row->person_id] = $new_id;
			}

			sportsmanagementModeljoomleagueimports::$_success['Project Referee neue Struktur (' . $project_id . '):'] .= $my_text;
		}

	}

}

