<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       round.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;

/**
 * sportsmanagementModelround
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelround extends JSMModelAdmin
{
	static $db_num_rows = 0;
	var $_identifier = "rounds";
	var $_tables_to_delete = array();

	/**
	 * sportsmanagementModelround::getRoundcode()
	 *
	 * @param   mixed  $round_id
	 *
	 * @return
	 */
	public static function getRoundcode($round_id = 0, $cfg_which_database = 0)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('roundcode');

		// From table
		$query->from('#__sportsmanagement_round');

		// Where
		$query->where('id = ' . (int) $round_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$app->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$result = false;
		}

		return $result;
	}

	/**
	 *
	 * @param $roundcode
	 * @param $project_id
	 */
	public static function getRoundId($roundcode, $project_id, $cfg_which_database = 0)
	{
		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		// Select some fields
		// $query->select('id');
		$query->select('CONCAT_WS( \':\', id, alias ) AS id');

		// From table
		$query->from('#__sportsmanagement_round');

		// Where
		$query->where('roundcode = ' . $roundcode);
		$query->where('project_id = ' . (int) $project_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * sportsmanagementModelround::getRound()
	 *
	 * @param   mixed    $round_id
	 * @param   integer  $cfg_which_database
	 *
	 * @return
	 */
	public static function getRound($round_id, $cfg_which_database = 0)
	{
		// Get a db connection.
		$db    = sportsmanagementHelper::getDBConnection(true, $cfg_which_database);
		$query = $db->getQuery(true);

		// Select some fields
		$query->select('*');

		// From table
		$query->from('#__sportsmanagement_round');

		// Where
		$query->where('id = ' . (int) $round_id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to update checked project rounds
	 *
	 * @access public
	 * @return boolean    True on success
	 */
	public function saveshort()
	{
		// Reference global application object
		$app  = Factory::getApplication();
		$date = Factory::getDate();
		$user = Factory::getUser();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		//  Get the input
		$pks = $jinput->get('cid', array(), 'array');

		if (!$pks)
		{
			return Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE_NO_SELECT');
		}

		$post = $jinput->post->getArray();

		for ($x = 0; $x < count($pks); $x++)
		{
			$tblRound             = new stdClass();
			$tblRound->id         = $pks[$x];
			$tblRound->roundcode  = $post['roundcode' . $pks[$x]];
			$tblRound->tournement = $post['tournementround' . $pks[$x]];
			$tblRound->name       = $post['name' . $pks[$x]];

			$tblRound->alias = OutputFilter::stringURLSafe($post['name' . $pks[$x]]);

			// Set the values
			$tblRound->modified    = $date->toSql();
			$tblRound->modified_by = $user->get('id');
			
			if ( !$post['round_date_first' . $pks[$x]] )
			{
			$tblRound->round_date_first = '0000-00-00';
			$tblRound->round_date_last  = '0000-00-00';	
			$tblRound->rdatefirst_timestamp = 0;
			$tblRound->rdatelast_timestamp  = 0;
			}
			elseif ( $post['round_date_first' . $pks[$x]] && !$post['round_date_last' . $pks[$x]] )
			{
			$tblRound->round_date_first = sportsmanagementHelper::convertDate($post['round_date_first' . $pks[$x]], 0);;
			$tblRound->round_date_last  = sportsmanagementHelper::convertDate($post['round_date_first' . $pks[$x]], 0);;	
			$tblRound->rdatefirst_timestamp = sportsmanagementHelper::getTimestamp($tblRound->round_date_first);;
			$tblRound->rdatelast_timestamp  = sportsmanagementHelper::getTimestamp($tblRound->round_date_first);;
			}
			else
			{
			$tblRound->round_date_first = sportsmanagementHelper::convertDate($post['round_date_first' . $pks[$x]], 0);
			$tblRound->round_date_last  = sportsmanagementHelper::convertDate($post['round_date_last' . $pks[$x]], 0);
			$tblRound->rdatefirst_timestamp = sportsmanagementHelper::getTimestamp($tblRound->round_date_first);
			$tblRound->rdatelast_timestamp  = sportsmanagementHelper::getTimestamp($tblRound->round_date_last);	
			}

			
/**
			if (($tblRound->round_date_last == '0000-00-00' || $tblRound->round_date_last == '') && $tblRound->round_date_first != '0000-00-00')
			{
				$tblRound->round_date_last = $tblRound->round_date_first;
			}
*/
			
			
try{
	//$tblRound->store();
	$resultupdate = Factory::getDbo()->updateObject('#__sportsmanagement_round', $tblRound, 'id', true);
	}
		catch (Exception $e)
		{
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
			return false;
		}
			
			
			
			
			
		}

		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE');
	}

	/**
	 * sportsmanagementModelround::massadd()
	 *
	 * @return
	 */
	function massadd()
	{
		$post            = Factory::getApplication()->input->post->getArray(array());
		$project_id      = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
		$add_round_count = (int) $post['add_round_count'];
		$project_type = $post['project_type'];
		$max = 0;
		$round_divisions_league4 = array();
		$round_divisions_league4[1] = 'Gruppenspiele';
		$round_divisions_league4[2] = 'Halbfinale';
		$round_divisions_league4[3] = '3.Platz';
		$round_divisions_league4[4] = 'Finale';
		
		$round_divisions_league5 = array();
		$round_divisions_league5[1] = 'Gruppenspiele';
		$round_divisions_league5[2] = 'Viertelfinale';
		$round_divisions_league5[3] = 'Halbfinale';
		$round_divisions_league5[4] = '3.Platz';
		$round_divisions_league5[5] = 'Finale';

		if ($add_round_count > 0) // Only MassAdd a number of new and empty rounds
		{
			$max = $this->getMaxRound($project_id);
			$max++;
			$i = 0;

			for ($x = 0; $x < $add_round_count; $x++)
			{
				$i++;
				$tblRound             =& $this->getTable();
				$tblRound->project_id = $project_id;
				$tblRound->roundcode  = $max;
				
				if ( $add_round_count == 4 )
				{
				switch ( $project_type )
				{
				case 'DIVISIONS_LEAGUE':
				$tblRound->name = $round_divisions_league4[$i];		
				break;
				default:
				$tblRound->name = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUND_NAME', $max);
				break;
				}	
				}
				elseif ( $add_round_count == 5 )
				{
				switch ( $project_type )
				{
				case 'DIVISIONS_LEAGUE':
				$tblRound->name = $round_divisions_league5[$i];		
				break;
				default:
				$tblRound->name = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUND_NAME', $max);
				break;
				}	
				}
				else
				{
				$tblRound->name = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUND_NAME', $max);	
				}
				
				
				
                $tblRound->alias = OutputFilter::stringURLSafe($tblRound->name);
                $tblRound->modified         = $this->jsmdate->toSql();
		        $tblRound->modified_by      = $this->jsmuser->get('id');

				if ($tblRound->store())
				{
					$msg = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUNDS_ADDED', $i);
				}
				else
				{
					$msg = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ERROR_ADD') . $this->_db->getErrorMsg();
				}

				$max++;
			}
		}

		return $msg;

	}

	/**
	 * return
	 *
	 * @param   int project_id
	 *
	 * @return integer
	 */
	function getMaxRound($project_id)
	{
        $this->jsmquery->clear();
		$this->jsmquery->select('COUNT(roundcode)');
		$this->jsmquery->from('#__sportsmanagement_round');
		$this->jsmquery->where('project_id = ' . (int) $project_id);
		$result = 0;

		if ($project_id > 0)
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadResult();
		}

		return $result;
	}

	/**
	 * Method to remove rounds
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  0.1
	 */
	public function delete(&$pks)
	{
		$app = Factory::getApplication();

		$success = $this->deleteRoundMatches($pks);

		if ($success)
		{
			return parent::delete($pks);
		}

	}

	/**
	 * Method to remove matchdays from round
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  0.1
	 */
	public function deleteRoundMatches($pks = array())
	{
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		if (count($pks))
		{
			// Matches
			$query->clear();
			$query->select('m.id');
			$query->from('#__sportsmanagement_match as m');
			$query->where('m.round_id IN (' . implode(",", $pks) . ')');
			Factory::getDBO()->setQuery($query);
			$matches = Factory::getDbo()->loadColumn();

			if ($matches)
			{
				$field       = 'match_id';
				$id          = implode(",", $matches);
				$temp        = new stdClass;
				$temp->table = '_match_statistic';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp->table = '_match_commentary';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp->table = '_match_staff_statistic';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp->table = '_match_staff';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp->table = '_match_event';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp->table = '_match_referee';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
				$temp->table = '_match_player';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;

				$field       = 'round_id';
				$id          = implode(",", $pks);
				$temp        = new stdClass;
				$temp->table = '_match';
				$temp->field = $field;
				$temp->id    = $id;
				$export[]    = $temp;
			

			$this->_tables_to_delete = array_merge($export);

			// Jetzt starten wir das löschen
			foreach ($this->_tables_to_delete as $row_to_delete)
			{
				$query->clear();
				$query->delete()->from('#__sportsmanagement' . $row_to_delete->table)->where($row_to_delete->field . ' IN (' . $row_to_delete->id . ')');
				Factory::getDbo()->setQuery($query);
				sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

				if (self::$db_num_rows)
				{
					$app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT' . strtoupper($row_to_delete->table) . '_ITEMS_DELETED', self::$db_num_rows), '');
				}
			}
				}
		}

		return true;
	}


}
