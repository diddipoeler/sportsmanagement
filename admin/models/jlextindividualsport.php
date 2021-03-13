<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       jlextindividualsport.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModeljlextindividualsport
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModeljlextindividualsport extends JSMModelAdmin
{

	/**
	 * sportsmanagementModeljlextindividualsport::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
		$this->jsmdb     = sportsmanagementHelper::getDBConnection();
		$this->jsmquery  = $this->jsmdb->getQuery(true);
		$this->jsmapp    = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$cfg_which_media_tool = ComponentHelper::getParams($this->jsmoption)->get('cfg_which_media_tool', 0);
		$form                 = $this->loadForm($this->jsmoption . '.jlextindividualsport', 'jlextindividualsport', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * sportsmanagementModeljlextindividualsport::saveshort()
	 *
	 * @return void
	 */
	function saveshort()
	{
		$this->jsmquery->clear();
		$pks      = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
		$post     = Factory::getApplication()->input->post->getArray(array());
		$match_id = $post['match_id'];

		$result_tie_break = 0;

		$this->jsmquery->select('p.use_tie_break,p.game_parts,p.sports_type_id');
		$this->jsmquery->from('#__sportsmanagement_project as p');
		$this->jsmquery->where('p.id = ' . (int) $post['project_id']);
		$this->jsmdb->setQuery($this->jsmquery);
		$use_tie_break    = $this->jsmdb->loadObject();
		$result_tie_break = $use_tie_break->game_parts;
        
        $this->jsmquery->clear();
        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_sports_type as p');
        $this->jsmquery->where('p.id = ' . $use_tie_break->sports_type_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$sports_type_id_name   = $this->jsmdb->loadObject();

        switch ($sports_type_id_name->name)
		{
		case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION':
        $ringetotal = 0;
        
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . '<pre>'.print_r($pks,true).'</pre>'), 'error');
        $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . '<pre>'.print_r($post,true).'</pre>'), 'error');
        for ($x = 0; $x < count($pks); $x++)
		{
		$rowmatch                          = new stdClass;
		$rowmatch->id                      = $pks[$x];
        $rowmatch->teamplayer1_id       = $post['teamplayer1_id' . $pks[$x]];
        
        $rowmatch->team1_result       = $post['team1_result' . $pks[$x]];
        $rowmatch->team2_result       = $post['team2_result' . $pks[$x]];
        
        if ( !$rowmatch->team1_result )
        {
            $rowmatch->team1_result = 0;
        }
        if ( !$rowmatch->team2_result )
        {
            $rowmatch->team2_result = 0;
        }
        
        $rowmatch->ringetotal       = $rowmatch->team1_result;
        
        $rowmatch->modified    = $this->jsmdate->toSql();
		$rowmatch->modified_by = $this->jsmuser->get('id');
        try
		{
			$result_update = $this->jsmdb->updateObject('#__sportsmanagement_match_single', $rowmatch, 'id', true);
            $ringetotal += $rowmatch->ringetotal;
		}
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
          
        }
        
        $rowmatch = new stdClass;
        $rowmatch->id = $match_id;
        $rowmatch->team1_result = $ringetotal;
        $rowmatch->ringetotal = $ringetotal;
        $rowmatch->modified    = $this->jsmdate->toSql();
		$rowmatch->modified_by = $this->jsmuser->get('id');
        try
		{
			$result_update = $this->jsmdb->updateObject('#__sportsmanagement_match', $rowmatch, 'id', true);
            $ringetotal += $rowmatch->ringetotal;
		}
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}




        break;
        case 'COM_SPORTSMANAGEMENT_ST_TENNIS':

		if ($use_tie_break->use_tie_break)
		{
			$result_tie_break = $result_tie_break - 1;
		}

		$this->jsmquery = $this->jsmdb->getQuery(true);
		$this->jsmquery->clear();
		$this->jsmquery->select('name,id');
		$this->jsmquery->from('#__sportsmanagement_eventtype');
		$this->jsmquery->where('sports_type_id = ' . (int) $use_tie_break->sports_type_id);
		$this->jsmdb->setQuery($this->jsmquery);
		$event_list = $this->jsmdb->loadObjectList('name');

		$result = true;

		for ($x = 0; $x < count($pks); $x++)
		{
			// änderungen im datum oder der uhrzeit
			$tbl = $this->getTable();;
			$tbl->load((int) $pks[$x]);

			list($date, $time) = explode(" ", $tbl->match_date);
			$this->_match_time_new = $post['match_time' . $pks[$x]] . ':00';
			$this->_match_date_new = $post['match_date' . $pks[$x]];
			$this->_match_time_old = $time;
			$this->_match_date_old = sportsmanagementHelper::convertDate($date);

			$post['match_date' . $pks[$x]] = sportsmanagementHelper::convertDate($post['match_date' . $pks[$x]], 0);
			$post['match_date' . $pks[$x]] = $post['match_date' . $pks[$x]] . ' ' . $post['match_time' . $pks[$x]] . ':00';

			$tblMatch                       = self::getTable();
			$tblMatch->id                   = $pks[$x];
			$tblMatch->match_number         = $post['match_number' . $pks[$x]];
			$tblMatch->match_date           = $post['match_date' . $pks[$x]];
			$tblMatch->crowd                = $post['crowd' . $pks[$x]];
			$tblMatch->round_id             = $post['round_id' . $pks[$x]];
			$tblMatch->division_id          = $post['division_id' . $pks[$x]];
			$tblMatch->projectteam1_id      = $post['projectteam1_id' . $pks[$x]];
			$tblMatch->projectteam2_id      = $post['projectteam2_id' . $pks[$x]];
			$tblMatch->teamplayer1_id       = $post['teamplayer1_id' . $pks[$x]];
			$tblMatch->teamplayer2_id       = $post['teamplayer2_id' . $pks[$x]];
			$tblMatch->double_team1_player1 = $post['double_team1_player1' . $pks[$x]];
			$tblMatch->double_team1_player2 = $post['double_team1_player2' . $pks[$x]];
			$tblMatch->double_team2_player1 = $post['double_team2_player1' . $pks[$x]];
			$tblMatch->double_team2_player2 = $post['double_team2_player2' . $pks[$x]];

			$tblMatch->team1_result = null;
			$tblMatch->team2_result = null;

			foreach ($post['team1_result_split' . $pks[$x]] as $key => $value)
			{
				if ($post['team1_result_split' . $pks[$x]][$key] != '' && $post['team2_result_split' . $pks[$x]][$key] != '')
				{
					if ($post['team1_result_split' . $pks[$x]][$key] > $post['team2_result_split' . $pks[$x]][$key])
					{
						$tblMatch->team1_result += 1;
						$tblMatch->team2_result += 0;
					}

					if ($post['team1_result_split' . $pks[$x]][$key] < $post['team2_result_split' . $pks[$x]][$key])
					{
						$tblMatch->team1_result += 0;
						$tblMatch->team2_result += 1;
					}

					if ($post['team1_result_split' . $pks[$x]][$key] == $post['team2_result_split' . $pks[$x]][$key])
					{
						$tblMatch->team1_result += 1;
						$tblMatch->team2_result += 1;
					}
				}
				else
				{
					//                        $tblMatch->team1_result	= NULL;
					//                        $tblMatch->team2_result	= NULL;
				}
			}

			$tblMatch->team1_result_split = implode(";", $post['team1_result_split' . $pks[$x]]);
			$tblMatch->team2_result_split = implode(";", $post['team2_result_split' . $pks[$x]]);

			if (!$tblMatch->store())
			{
				$result = false;
			}
			else
			{
			}

			// Ereignisse speichern heim
			if ($tblMatch->teamplayer1_id)
			{
				if ($tblMatch->team1_result > $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_SINGLE_WON']->id;
				}

				if ($tblMatch->team1_result < $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_SINGLE_LOST']->id;
				}

				self::deleteevents($post['match_id'], $tblMatch->teamplayer1_id, $event_id);
				self::insertevents($post['match_id'], $post['projectteam1_id'], $tblMatch->teamplayer1_id, $event_id);
			}

			// Ereignisse speichern heim
			if ($tblMatch->double_team1_player1)
			{
				if ($tblMatch->team1_result > $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_WON']->id;
				}

				if ($tblMatch->team1_result < $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_LOST']->id;
				}

				self::deleteevents($post['match_id'], $tblMatch->double_team1_player1, $event_id);
				self::insertevents($post['match_id'], $post['projectteam1_id'], $tblMatch->double_team1_player1, $event_id);
			}

			// Ereignisse speichern heim
			if ($tblMatch->double_team1_player2)
			{
				if ($tblMatch->team1_result > $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_WON']->id;
				}

				if ($tblMatch->team1_result < $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_LOST']->id;
				}

				self::deleteevents($post['match_id'], $tblMatch->double_team1_player2, $event_id);
				self::insertevents($post['match_id'], $post['projectteam1_id'], $tblMatch->double_team1_player2, $event_id);
			}

			// Ereignisse speichern gast
			if ($tblMatch->teamplayer2_id)
			{
				if ($tblMatch->team1_result < $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_SINGLE_WON']->id;
				}

				if ($tblMatch->team1_result > $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_SINGLE_LOST']->id;
				}

				self::deleteevents($post['match_id'], $tblMatch->teamplayer2_id, $event_id);
				self::insertevents($post['match_id'], $post['projectteam2_id'], $tblMatch->teamplayer2_id, $event_id);
			}

			// Ereignisse speichern gast
			if ($tblMatch->double_team2_player1)
			{
				if ($tblMatch->team1_result < $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_WON']->id;
				}

				if ($tblMatch->team1_result > $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_LOST']->id;
				}

				self::deleteevents($post['match_id'], $tblMatch->double_team2_player1, $event_id);
				self::insertevents($post['match_id'], $post['projectteam2_id'], $tblMatch->double_team2_player1, $event_id);
			}

			// Ereignisse speichern gast
			if ($tblMatch->double_team2_player2)
			{
				if ($tblMatch->team1_result < $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_WON']->id;
				}

				if ($tblMatch->team1_result > $tblMatch->team2_result)
				{
					// Ereignis_id
					$event_id = $event_list['COM_SPORTSMANAGEMENT_TENNIS_E_DOUBLE_LOST']->id;
				}

				self::deleteevents($post['match_id'], $tblMatch->double_team2_player2, $event_id);
				self::insertevents($post['match_id'], $post['projectteam2_id'], $tblMatch->double_team2_player2, $event_id);
			}
		}

		// Alles ok
		// jetzt die einzelergebnisse zum hauptspiel addieren
		$this->jsmquery->clear();
		$this->jsmquery->select('mc.*');
		$this->jsmquery->from('#__sportsmanagement_match_single AS mc');
		$this->jsmquery->where('mc.match_id = ' . $match_id);
		$this->jsmdb->setQuery($this->jsmquery);

		$result = $this->jsmdb->loadObjectList();
		$temp   = new stdClass;

		foreach ($result as $row)
		{
			if ($row->team1_result > $row->team2_result)
			{
				$temp->team1_result += 1;
				$temp->team2_result += 0;
			}

			if ($row->team1_result < $row->team2_result)
			{
				$temp->team1_result += 0;
				$temp->team2_result += 1;
			}

			if ($row->team1_result == $row->team2_result)
			{
				$temp->team1_result += 1;
				$temp->team2_result += 1;
			}

			$temp->team1_single_sets += $row->team1_result;
			$temp->team2_single_sets += $row->team2_result;

			$team1_result_split = explode(";", $row->team1_result_split);
			$team2_result_split = explode(";", $row->team2_result_split);

			foreach ($team1_result_split as $key => $value)
			{
				if ($use_tie_break->use_tie_break)
				{
					if ($key < $result_tie_break)
					{
						$temp->team1_single_games += $value;
					}
				}
				else
				{
					$temp->team1_single_games += $value;
				}
			}

			foreach ($team2_result_split as $key => $value)
			{
				if ($use_tie_break->use_tie_break)
				{
					if ($key < $result_tie_break)
					{
						$temp->team2_single_games += $value;
					}
				}
				else
				{
					$temp->team2_single_games += $value;
				}
			}

			if ($use_tie_break->use_tie_break)
			{
				if ($team1_result_split[$result_tie_break] > $team2_result_split[$result_tie_break])
				{
					$temp->team1_single_games += 1;
					$temp->team2_single_games += 0;
				}

				if ($team1_result_split[$result_tie_break] < $team2_result_split[$result_tie_break])
				{
					$temp->team1_single_games += 0;
					$temp->team2_single_games += 1;
				}
			}
			else
			{
				if ($team1_result_split[$result_tie_break] > $team2_result_split[$result_tie_break])
				{
					$temp->team1_single_games += $team1_result_split[$result_tie_break];
					$temp->team2_single_games += $team2_result_split[$result_tie_break];
				}

				if ($team1_result_split[$result_tie_break] < $team2_result_split[$result_tie_break])
				{
					$temp->team1_single_games += $team1_result_split[$result_tie_break];
					$temp->team2_single_games += $team2_result_split[$result_tie_break];
				}
			}
		}

		$rowmatch                          = new stdClass;
		$rowmatch->id                      = $match_id;
		$rowmatch->team1_result            = $temp->team1_result;
		$rowmatch->team2_result            = $temp->team2_result;
		$rowmatch->team1_single_matchpoint = $temp->team1_result;
		$rowmatch->team2_single_matchpoint = $temp->team2_result;
		$rowmatch->team1_single_sets       = $temp->team1_single_sets;
		$rowmatch->team2_single_sets       = $temp->team2_single_sets;
		$rowmatch->team1_single_games      = $temp->team1_single_games;
		$rowmatch->team2_single_games      = $temp->team2_single_games;

		try
		{
			$result_update = $this->jsmdb->updateObject('#__sportsmanagement_match', $rowmatch, 'id', true);
		}
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
        break;
        }

		// Proceed with the save
		// return parent::save($data);

	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return Table    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'MatchSingle', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * sportsmanagementModeljlextindividualsport::deleteevents()
	 *
	 * @param   mixed  $match_id
	 * @param   mixed  $teamplayer1_id
	 * @param   mixed  $event_id
	 *
	 * @return void
	 */
	function deleteevents($match_id, $teamplayer1_id, $event_id)
	{

//		// Create a new query object.
//		$db    = sportsmanagementHelper::getDBConnection();
//		$query = $db->getQuery(true);
//
//		// Alte ereignisse löschen
//		$query = $db->getQuery(true);
        
		$this->jsmquery->clear();
		$this->jsmquery->delete()->from('#__sportsmanagement_match_event')->where('match_id = ' . $match_id . ' AND teamplayer_id = ' . $teamplayer1_id . ' AND event_type_id = ' . $event_id);
		
		try{
		  $this->jsmdb->setQuery($this->jsmquery);
		$resultdel = $this->jsmdb->execute();
}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
                $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
              $this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->jsmquery->dump(),true).'</pre>'), 'error');
			}
	
//		if (!$resultdel)
//		{
//		}

	}

	/**
	 * sportsmanagementModeljlextindividualsport::insertevents()
	 *
	 * @param   mixed  $match_id
	 * @param   mixed  $projectteam1_id
	 * @param   mixed  $teamplayer1_id
	 * @param   mixed  $event_id
	 *
	 * @return void
	 */
	function insertevents($match_id, $projectteam1_id, $teamplayer1_id, $event_id)
	{
//		$app                   = Factory::getApplication();
//		$db                    = sportsmanagementHelper::getDBConnection();
//		$query                 = $db->getQuery(true);
		$event                 = new stdClass;
		$event->match_id       = $match_id;
		$event->projectteam_id = $projectteam1_id;
		$event->teamplayer_id  = $teamplayer1_id;
		$event->event_type_id  = $event_id;
		$event->event_sum      = 1;

try{
		$resultins = $this->jsmdb->insertObject('#__sportsmanagement_match_event', $event);
}
			catch (Exception $e)
			{
				$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
                $this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
              //$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->jsmquery->dump(),true).'</pre>'), 'error');
			}
//		if (!$resultins)
//		{
//		}

	}

	/**
	 * Method to remove
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  0.1
	 */
	function delete($pk = array())
	{
		$app = Factory::getApplication();

		return parent::delete($pk);

		return true;
	}

	/**
	 * sportsmanagementModeljlextindividualsport::save_array()
	 *
	 * @param   mixed  $cid
	 * @param   mixed  $post
	 * @param   bool   $zusatz
	 * @param   mixed  $project_id
	 *
	 * @return
	 */
	function save_array($cid = null, $post = null, $zusatz = false, $project_id)
	{
		$app          = Factory::getApplication();
		$datatable[0] = '#__sportsmanagement_match_single';

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$fields = $this->_db->getTableColumns($datatable, true);
		}
		else
		{
			$fieldsArray = $this->_db->getTableFields($datatable, true);
			$fields      = array_shift($fieldsArray);
		}

		$sporttype     = $app->getUserState($this->jsmoption . 'sporttype');
		$defaultvalues = array();
		$game_parts    = $app->getUserState($this->jsmoption . 'game_parts');

		// In abhängigkeit von der sportart wird das ergebnis gespeichert
		switch (strtolower($sporttype))
		{
			case 'kegeln':

				$xmldir        = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->jsmoption . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'jlextindividualsport' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'extended';
				$file          = 'kegelnresults.xml';
				$defaultconfig = array();
				$out           = simplexml_load_file($xmldir . DIRECTORY_SEPARATOR . $file, 'SimpleXMLElement', LIBXML_NOCDATA);
				$temp          = '';
				$arr           = $this->obj2Array($out);
				$outName       = Text::_($out->name[0]);

				if (isset($arr["params"]["param"]))
				{
					foreach ($arr["params"]["param"] as $param)
					{
						$defaultconfig[$param["@attributes"]["name"]] = $param["@attributes"]["default"];
					}
				}

				$post['team1_result' . $cid] = 0;
				$post['team2_result' . $cid] = 0;

				// Alles auf null setzen
				$defaultconfig['JL_EXT_KEGELN_DATA_HOME_VOLLE'] = 0;
				$defaultconfig['JL_EXT_KEGELN_DATA_AWAY_VOLLE'] = 0;
				$defaultconfig['JL_EXT_KEGELN_DATA_HOME_ABR']   = 0;
				$defaultconfig['JL_EXT_KEGELN_DATA_AWAY_ABR']   = 0;
				$defaultconfig['JL_EXT_KEGELN_DATA_HOME_FW']    = 0;
				$defaultconfig['JL_EXT_KEGELN_DATA_AWAY_FW']    = 0;

				for ($a = 0; $a < 2; $a++)
				{
					$post['team1_result' . $cid] = $post['team1_result' . $cid] + $post['team1_result_split' . $cid][$a];
					$post['team2_result' . $cid] = $post['team2_result' . $cid] + $post['team2_result_split' . $cid][$a];
				}

				for ($a = 0; $a < $game_parts; $a++)
				{
					switch ($a)
					{
						case 0:
							$defaultconfig['JL_EXT_KEGELN_DATA_HOME_VOLLE'] = $post['team1_result_split' . $cid][$a];
							$defaultconfig['JL_EXT_KEGELN_DATA_AWAY_VOLLE'] = $post['team2_result_split' . $cid][$a];
							break;
						case 1:
							$defaultconfig['JL_EXT_KEGELN_DATA_HOME_ABR'] = $post['team1_result_split' . $cid][$a];
							$defaultconfig['JL_EXT_KEGELN_DATA_AWAY_ABR'] = $post['team2_result_split' . $cid][$a];
							break;
						case 2:
							$defaultconfig['JL_EXT_KEGELN_DATA_HOME_FW'] = $post['team1_result_split' . $cid][$a];
							$defaultconfig['JL_EXT_KEGELN_DATA_AWAY_FW'] = $post['team2_result_split' . $cid][$a];
							break;
					}
				}

				foreach ($defaultconfig as $key => $value)
				{
					$defaultvalues[] = $key . '=' . $value;
				}

				$temp = implode("\n", $defaultvalues);

				$query = "UPDATE #__sportsmanagement_match_single SET `extended`='" . $temp . "' WHERE id=" . $cid;
				$this->_db->setQuery($query);

				if (!$this->_db->execute())
				{
				}

				break;

			case 'tennis':
				$xmldir        = JPATH_SITE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . $this->jsmoption . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'jlextindividualsport' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'extended';
				$file          = 'tennisresults.xml';
				$defaultconfig = array();
				$out           = simplexml_load_file($xmldir . DIRECTORY_SEPARATOR . $file, 'SimpleXMLElement', LIBXML_NOCDATA);
				$temp          = '';
				$arr           = $this->obj2Array($out);
				$outName       = Text::_($out->name[0]);

				if (isset($arr["params"]["param"]))
				{
					foreach ($arr["params"]["param"] as $param)
					{
						$temp                                         .= $param["@attributes"]["name"] . "=" . $param["@attributes"]["default"] . "\n";
						$defaultconfig[$param["@attributes"]["name"]] = $param["@attributes"]["default"];
					}
				}

				// Alles auf null setzen
				$defaultconfig['JL_EXT_TENNIS_DATA_HOME_MATCHES'] = 0;
				$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_MATCHES'] = 0;
				$defaultconfig['JL_EXT_TENNIS_DATA_HOME_SETS']    = 0;
				$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_SETS']    = 0;
				$defaultconfig['JL_EXT_TENNIS_DATA_HOME_POINTS']  = 0;
				$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_POINTS']  = 0;

				for ($a = 0; $a < $game_parts; $a++)
				{
					$defaultconfig['JL_EXT_TENNIS_DATA_HOME_MATCHES'] = $defaultconfig['JL_EXT_TENNIS_DATA_HOME_MATCHES'] + $post['team1_result_split' . $cid][$a];
					$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_MATCHES'] = $defaultconfig['JL_EXT_TENNIS_DATA_AWAY_MATCHES'] + $post['team2_result_split' . $cid][$a];

					// Erst die sätze
					if ($post['team1_result_split' . $cid][$a] > $post['team2_result_split' . $cid][$a])
					{
						$defaultconfig['JL_EXT_TENNIS_DATA_HOME_SETS']++;
					}
					elseif ($post['team1_result_split' . $cid][$a] < $post['team2_result_split' . $cid][$a])
					{
						$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_SETS']++;
					}
				}

				// Zum schluss die punkte
				if ($defaultconfig['JL_EXT_TENNIS_DATA_HOME_SETS'] > $defaultconfig['JL_EXT_TENNIS_DATA_AWAY_SETS'])
				{
					$defaultconfig['JL_EXT_TENNIS_DATA_HOME_POINTS']++;
					$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_POINTS'] = 0;
				}
				elseif ($defaultconfig['JL_EXT_TENNIS_DATA_HOME_SETS'] < $defaultconfig['JL_EXT_TENNIS_DATA_AWAY_SETS'])
				{
					$defaultconfig['JL_EXT_TENNIS_DATA_AWAY_POINTS']++;
					$defaultconfig['JL_EXT_TENNIS_DATA_HOME_POINTS'] = 0;
				}

				$post['team1_result' . $cid] = $defaultconfig['JL_EXT_TENNIS_DATA_HOME_POINTS'];
				$post['team2_result' . $cid] = $defaultconfig['JL_EXT_TENNIS_DATA_AWAY_POINTS'];

				foreach ($defaultconfig as $key => $value)
				{
					$defaultvalues[] = $key . '=' . $value;
				}

				$temp = implode("\n", $defaultvalues);

				$query = "UPDATE #__sportsmanagement_match_single SET `extended`='" . $temp . "' WHERE id=" . $cid;
				$this->_db->setQuery($query);

				if (!$this->_db->execute())
				{
				}

				break;
		}

		foreach ($fields as $field)
		{
			$query     = '';
			$datafield = array_keys($field);

			if ($zusatz)
			{
				$fieldzusatz = $cid;
			}

			foreach ($datafield as $keys)
			{
				if (isset($post[$keys . $fieldzusatz]))
				{
					$result = $post[$keys . $fieldzusatz];

					if ($keys == 'match_date')
					{
						$result .= ' ' . $post['match_time' . $fieldzusatz];
					}

					if ($keys == 'team1_result_split' || $keys == 'team2_result_split' || $keys == 'homeroster' || $keys == 'awayroster')
					{
						$result = trim(join(';', $result));
					}

					if ($keys == 'alt_decision' && $post[$keys . $fieldzusatz] == 0)
					{
						$query .= ",team1_result_decision=NULL,team2_result_decision=NULL,decision_info='',team_won=0";
					}

					if ($keys == 'team1_result_decision' && strtoupper($post[$keys . $fieldzusatz]) == 'X' && $post['alt_decision' . $fieldzusatz] == 1)
					{
						$result = '';
					}

					if ($keys == 'team2_result_decision' && strtoupper($post[$keys . $fieldzusatz]) == 'X' && $post['alt_decision' . $fieldzusatz] == 1)
					{
						$result = '';
					}

					if (!is_numeric($result) || ($keys == 'match_number'))
					{
						$vorzeichen = "'";
					}
					else
					{
						$vorzeichen = '';
					}

					if (strstr(
							"crowd,formation1,formation2,homeroster,awayroster,show_report,team1_result,
								team1_bonus,team1_legs,team2_result,team2_bonus,team2_legs,
								team1_result_decision,team2_result_decision,team1_result_split,
								team2_result_split,team1_result_ot,team2_result_ot,
								team1_result_so,team2_result_so,team_won,", $keys . ','
						)
						&& $result == '' && isset($post[$keys . $fieldzusatz])
					)
					{
						$result     = 'NULL';
						$vorzeichen = '';
					}

					if ($keys == 'crowd' && $post['crowd' . $fieldzusatz] == '')
					{
						$result = '0';
					}

					if ($result != '' || $keys == 'summary' || $keys == 'match_result_detail')
					{
						if ($query)
						{
							$query .= ',';
						}

						$query .= $keys . '=' . $vorzeichen . $result . $vorzeichen;
					}

					if ($result == '' && $keys == 'time_present')
					{
						if ($query)
						{
							$query .= ',';
						}

						$query .= $keys . '=null';
					}

					if ($result == '' && $keys == 'match_number')
					{
						if ($query)
						{
							$query .= ',';
						}

						$query .= $keys . '=null';
					}
				}
			}
		}

		$user  =& Factory::getUser();
		$query = 'UPDATE #__sportsmanagement_match_single SET ' . $query . ',`modified`=NOW(),`modified_by`=' . $user->id . ' WHERE id=' . $cid;
		$this->_db->setQuery($query);
		$this->_db->query($query);

		// Now for ko mode
		$project =& $this->getProject();

		if ($project->project_type == 'TOURNAMENT_MODE')
		{
			return $this->save_team_to($cid, $post);
		}

		return true;
	}

	/**
	 * sportsmanagementModeljlextindividualsport::save_round_match_tennis()
	 *
	 * @return void
	 */
	function save_round_match_tennis()
	{
		$app  = Factory::getApplication();
		$post = Factory::getApplication()->input->post->getArray(array());
		$cid  = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		ArrayHelper::toInteger($cid);

		$sporttype     = $app->getUserState($this->jsmoption . 'sporttype');
		$defaultvalues = array();

		$match_id = $post['match_id'];

		$query = ' SELECT m.team1_result,
  m.team2_result,
  team1_result_split,
  team2_result_split,
  extended
  FROM #__sportsmanagement_match_single AS m
	WHERE m.match_id=' . (int) $match_id . ' AND m.published = 1';
		$this->_db->setQuery($query);
		$singlerows = $this->_db->loadObjectList();

		foreach ($singlerows as $row)
		{
			$update['resulthome'] += $row->team1_result;
			$update['resultaway'] += $row->team2_result;

			$params = explode("\n", $row->extended);

			foreach ($params AS $param)
			{
				list ($name, $value) = explode("=", $param);
				$configvalues[$name] += $value;
			}
		}

		foreach ($configvalues as $key => $value)
		{
			$defaultvalues[] = $key . '=' . $value;
		}

		$temp = implode("\n", $defaultvalues);

		$rowupdate = Table::getInstance('match', 'Table');
		$rowupdate->load($match_id);
		$rowupdate->team1_result = $update['resulthome'];
		$rowupdate->team2_result = $update['resultaway'];
		$rowupdate->extended     = $temp;

		if (!$rowupdate->store())
		{
			Log::add($rowupdate->getError());
		}

	}

	/**
	 * sportsmanagementModeljlextindividualsport::save_round_match_kegeln()
	 *
	 * @return void
	 */
	function save_round_match_kegeln()
	{
		$app  = Factory::getApplication();
		$post = Factory::getApplication()->input->post->getArray(array());
		$cid  = Factory::getApplication()->input->getVar('cid', array(), 'post', 'array');
		ArrayHelper::toInteger($cid);

		$sporttype     = $app->getUserState($this->jsmoption . 'sporttype');
		$defaultvalues = array();

		$match_id = $post['match_id'];

		$query = ' SELECT SUM(m.team1_result) AS resulthome,
  SUM(m.team2_result) AS resultaway,
  team1_result_split,
  team2_result_split
  FROM #__sportsmanagement_match_single AS m
	WHERE m.match_id=' . (int) $match_id . ' AND m.published = 1';
		$this->_db->setQuery($query);
		$row = $this->_db->loadAssoc();

		$row['team1_result_split'] = array();
		$row['team2_result_split'] = array();

		for ($x = 0; $x < count($cid); $x++)
		{
			$tempteam1 = $post['team1_result_split' . $cid[$x]];
			$tempteam2 = $post['team2_result_split' . $cid[$x]];

			for ($a = 0; $a < 3; $a++)
			{
				$row['team1_result_split'][$a] = $row['team1_result_split'][$a] + $tempteam1[$a];
				$row['team2_result_split'][$a] = $row['team2_result_split'][$a] + $tempteam2[$a];
			}
		}

		$row['team1_result_split'] = implode(";", $row['team1_result_split']);
		$row['team2_result_split'] = implode(";", $row['team2_result_split']);

		$query = ' SELECT
  extended
  FROM #__sportsmanagement_match_single AS m
	WHERE m.match_id=' . (int) $match_id . ' AND m.published = 1';
		$this->_db->setQuery($query);
		$singlerows = $this->_db->loadObjectList();

		foreach ($singlerows as $singlerow)
		{
			$params = explode("\n", $singlerow->extended);

			foreach ($params AS $param)
			{
				list ($name, $value) = explode("=", $param);
				$configvalues[$name] += $value;
			}
		}

		foreach ($configvalues as $key => $value)
		{
			$defaultvalues[] = $key . '=' . $value;
		}

		$temp      = implode("\n", $defaultvalues);
		$rowupdate = Table::getInstance('match', 'Table');
		$rowupdate->load($match_id);
		$rowupdate->team1_result       = $row['resulthome'];
		$rowupdate->team2_result       = $row['resultaway'];
		$rowupdate->team1_result_split = $row['team1_result_split'];
		$rowupdate->team2_result_split = $row['team2_result_split'];
		$rowupdate->extended           = $temp;

		if (!$rowupdate->store())
		{
			Log::add($rowupdate->getError());
		}

	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return boolean
	 * @since  1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return Factory::getUser()->authorise('core.edit', $this->jsmoption . '.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) || parent::allowEdit($data, $key);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		$data = Factory::getApplication()->getUserState($this->jsmoption . '.edit.jlextindividualsport.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

}
