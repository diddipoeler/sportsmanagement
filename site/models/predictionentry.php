<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictionentry
 * @file       predictionentry.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementModelPredictionEntry
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelPredictionEntry extends BaseDatabaseModel
{
	static $roundID = 0;
	static $pjID = 0;
	public $_predictionGame = null;
	public $predictionGameID = 0;
	public $_predictionMember = null;
	public $predictionMemberID = 0;
	public $_predictionProjectS = null;
	public $predictionProjectSIDs = null;
	public $_predictionProject = null;
	public $predictionProjectID = null;
	public $show_debug_info = false;
	public $joomlaUserID = 0;
	public $pggroup = 0;
	public $pggrouprank = 0;
	public $isNewMember = 0;
	public $tippEntryDone = 0;
	public $from = 0;
	public $to = 0;
	public $type = 0;
	public $page = 0;

	/**
	 * sportsmanagementModelPredictionEntry::__construct()
	 *
	 * @return void
	 */
	function __construct()
	{
		$this->jsmapp = Factory::getApplication();
		$this->jsmjinput = $this->jsmapp->input;
		$this->jsmoption = $this->jsmjinput->getCmd('option');
        $this->jsmdb = Factory::getDBO();
		$this->jsmquery = $this->jsmdb->getQuery(true);
        $this->date = Factory::getDate();
		$this->user = Factory::getUser();

		$prediction = new sportsmanagementModelPrediction;

		$post = $this->jsmjinput->post->getArray(array());

		self::$pjID = $this->jsmjinput->getVar('pj', '0');

		sportsmanagementModelPrediction::$roundID = $this->jsmjinput->getVar('r', '0');
		sportsmanagementModelPrediction::$pjID    = $this->jsmjinput->getVar('pj', '0');
		sportsmanagementModelPrediction::$from    = $this->jsmjinput->getVar('from', $this->jsmjinput->getVar('r', '0'));
		sportsmanagementModelPrediction::$to      = $this->jsmjinput->getVar('to', $this->jsmjinput->getVar('r', '0'));
		sportsmanagementModelPrediction::$predictionGameID = $this->jsmjinput->getVar('prediction_id', '0');
		sportsmanagementModelPrediction::$predictionMemberID = $this->jsmjinput->getInt('uid', 0);
		sportsmanagementModelPrediction::$joomlaUserID       = $this->jsmjinput->getInt('juid', 0);
		sportsmanagementModelPrediction::$pggroup     = $this->jsmjinput->getInt('pggroup', 0);
		sportsmanagementModelPrediction::$pggrouprank = $this->jsmjinput->getInt('pggrouprank', 0);
		sportsmanagementModelPrediction::$isNewMember   = $this->jsmjinput->getInt('s', 0);
		sportsmanagementModelPrediction::$tippEntryDone = $this->jsmjinput->getInt('eok', 0);
		sportsmanagementModelPrediction::$type = $this->jsmjinput->getInt('type', 0);
		sportsmanagementModelPrediction::$page = $this->jsmjinput->getInt('page', 1);

		sportsmanagementModelPrediction::checkRoundID(sportsmanagementModelPrediction::$pjID, sportsmanagementModelPrediction::$roundID);

		parent::__construct();
	}

	/**
	 * sportsmanagementModelPredictionEntry::createHelptText()
	 *
	 * @param   integer  $gameMode
	 *
	 * @return
	 */
	public static function createHelptText($gameMode = 0)
	{
		// $option = Factory::getApplication()->input->getCmd('option').'_';
		$gameModeStr = ($gameMode == 0) ? Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_STANDARD_MODE') : Text::_($option . 'JL_PRED_ENTRY_TOTO_MODE');

		$helpText = '<hr><h3>' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_TITLE') . '</h3>';

		$helpText .= '<ul>';
		$helpText .= '<li>';
		$helpText .= Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_01', '<b>' . $gameModeStr . '</b>');
		$helpText .= '</li>';
		$helpText .= '<li>';
		$helpText .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_02');
		$helpText .= '</li>';
		$helpText .= '<li>';
		$helpText .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_03');
		$helpText .= '</li>';
		$helpText .= '<li>';
		$helpText .= Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_04');
		$helpText .= '</li>';
		$helpText .= '</ul>';
		$helpText .= '<hr>';

		return $helpText;
	}

	/**
	 * sportsmanagementModelPredictionEntry::getTippCount()
	 *
	 * @param   mixed    $predictionProjectID
	 * @param   mixed    $matchID
	 * @param   integer  $total
	 *
	 * @return
	 */
	public static function getTippCount($predictionProjectID, $matchID, $total = 3)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$db     = Factory::getDBO();
		$query  = $db->getQuery(true);
		$query->select('count(tipp)');
		$query->from('#__sportsmanagement_prediction_result');
		$query->where('prediction_id = ' . (int) sportsmanagementModelPrediction::$predictionGameID);
		$query->where('project_id = ' . (int) $predictionProjectID);
		$query->where('match_id = ' . (int) $matchID);

		switch ($total)
		{
			case 0:
			case 1:
			case 2:
				$query->where('tipp = ' . $total);
				break;
			case 3:
				break;
		}

		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * sportsmanagementModelPredictionEntry::getMatchesDataForPredictionEntry()
	 *
	 * @param   mixed  $predictionGameID
	 * @param   mixed  $predictionProjectID
	 * @param   mixed  $projectRoundID
	 * @param   mixed  $userID
	 * @param   mixed  $match_ids
	 * @param   mixed  $round_ids
	 * @param   mixed  $proteams_ids
	 *
	 * @return
	 */
	public static function getMatchesDataForPredictionEntry($predictionGameID, $predictionProjectID, $projectRoundID, $userID, $match_ids = null, $round_ids = null, $proteams_ids = null)
	{

		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		try
		{
			$query->select('m.id,m.round_id,m.match_date,m.projectteam1_id,m.projectteam2_id,m.team1_result,m.team2_result,m.team1_result_decision,m.team2_result_decision');
			$query->select('r.id AS roundcode');
			$query->select('pr.tipp,pr.tipp_home,pr.tipp_away,pr.joker,pr.id AS prid');
			$query->from('#__sportsmanagement_match AS m');
			$query->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id');
			$query->join(
				'LEFT', '#__sportsmanagement_prediction_result AS pr ON pr.match_id = m.id
                  AND pr.prediction_id = ' . (int) $predictionGameID . ' AND pr.user_id = ' . $userID . ' AND pr.project_id = ' . (int) $predictionProjectID
			);
			$query->join('LEFT', '#__sportsmanagement_prediction_game AS pg ON pg.id = ' . (int) $predictionGameID);
			$query->where('r.project_id = ' . (int) $predictionProjectID);
			$query->where('r.id = ' . (int) $projectRoundID);
			$query->where('m.published = 1');
			$query->where('m.match_date <> \'0000-00-00 00:00:00\'');
			$query->where('(m.cancel IS NULL OR m.cancel = 0)');

			/**
			 * bestimmte spiele selektieren
			 */
			if ($match_ids)
			{
				$query->where('m.id IN (' . implode(',', $match_ids) . ')');
			}

			/**
			 * bestimmte mannschaften selektieren
			 */
			if ($proteams_ids)
			{
				$query->where('( m.projectteam1_id IN (' . implode(',', $proteams_ids) . ')' . ' OR ' . 'm.projectteam2_id IN (' . implode(',', $proteams_ids) . ') )');
			}

			$query->order('m.match_date ASC');

			$db->setQuery($query);
			$results = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			$msg  = $e->getMessage(); // Returns "Normally you would have other code...
			$code = $e->getCode(); // Returns '500';
		}

		if (!$results)
		{
			$app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_NO_PROJECT'), 'Notice');
		}

		return $results;
	}

	/**
	 * sportsmanagementModelPredictionEntry::newMemberCheck()
	 *
	 * @return
	 */
	function newMemberCheck()
	{
		if ($this->isNewMember == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * sportsmanagementModelPredictionEntry::tippEntryDoneCheck()
	 *
	 * @return
	 */
	function tippEntryDoneCheck()
	{
		if ($this->tippEntryDone == 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return mixed    A JForm object on success, false on failure
	 * @since  1.7
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$app = Factory::getApplication('site');

		// Get the form.
		$form = $this->loadForm(
			'com_sportsmanagement.' . $this->name, $this->name,
			array('load_data' => $loadData)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * sportsmanagementModelPredictionEntry::store()
	 *
	 * @param   mixed  $data
	 *
	 * @return
	 */
	function store($data)
	{
		$option   = Factory::getApplication()->input->getCmd('option');
		$app      = Factory::getApplication();
		$document = Factory::getDocument();

		// Get the table
		$row = self::getTable();

		// Bind the form fields to the hello table
		if (!$row->bind($data))
		{
			// $this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the hello record is valid
		if (!$row->check())
		{
			//  $this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$row->store())
		{
			// $this->setError( $row->getErrorMsg() );
			return false;
		}

		return true;
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return Table    A database object
	 * @since  1.6
	 */
	public function getTable($type = 'predictionentry', $prefix = 'sportsmanagementtable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * sportsmanagementModelPredictionEntry::savePredictions()
	 *
	 * @param   bool  $allowedAdmin
	 *
	 * @return
	 */
	function savePredictions($allowedAdmin = false)
	{
		$document = Factory::getDocument();



		// Create a new query object.
		$db    = Factory::getDBO();
		$query = $db->getQuery(true);

		$starttime = microtime();

		$result = true;

		$post = $this->jsmjinput->post->getArray();

		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_frontend'))
		{
			Factory::getApplication()->enqueueMessage(__METHOD__ . ' ' . __LINE__ . ' post <pre>' . print_r($post, true) . '</pre>', '');
		}

		$pids = $this->jsmjinput->getVar('pids', null, 'post', 'array');
		ArrayHelper::toInteger($pids);

		$cids   = $this->jsmjinput->getVar('cids', array(), 'post', 'array');
		$prids  = $this->jsmjinput->getVar('prids', array(), 'post', 'array');
		$homes  = $this->jsmjinput->getVar('homes', array(), 'post', 'array');
		$aways  = $this->jsmjinput->getVar('aways', array(), 'post', 'array');
		$tipps  = $this->jsmjinput->getVar('tipps', array(), 'post', 'array');
		$jokers = $this->jsmjinput->getVar('jokers', array(), 'post', 'array');
		$mID       = $this->jsmjinput->get('memberID', 0, 'int');
		$ptippmode = $this->jsmjinput->getVar('ptippmode', array(), 'post', 'array');
		$RoundID   = $this->jsmjinput->get('r', 0, 'int');
		$ProjectID = $this->jsmjinput->get('pjID', 0, 'int');
		$predictionGameID = $this->jsmjinput->get('prediction_id', 0, 'int');
		$joomlaUserID     = $this->jsmjinput->get('user_id', 0, 'int');

		$temp                   = new stdClass;
		$temp->goals            = $post['goals'][$ProjectID][$RoundID];
		$temp->penalties        = $post['penalties'][$ProjectID][$RoundID];
		$temp->yellow_cards     = $post['yellowcards'][$ProjectID][$RoundID];
		$temp->yellow_red_cards = $post['yellowredcards'][$ProjectID][$RoundID];
		$temp->red_cards        = $post['redcards'][$ProjectID][$RoundID];
		$temp->prediction_id    = $predictionGameID;
		$temp->user_id          = $joomlaUserID;
		$temp->project_id       = $ProjectID;
		$temp->round_id         = $RoundID;
		$temp->modified_by      = $joomlaUserID;
		$temp->modified         = Factory::getDate()->toSql();

		try
		{
		$resultquery = Factory::getDbo()->insertObject('#__sportsmanagement_prediction_result_round', $temp);
		}
		catch (Exception $e)
		{
        //$this->jsmapp->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
        /** update machen */
        $this->jsmquery->clear();
        /** Fields to update. */
        $fields = array(
        $this->jsmdb->quoteName('goals') . ' = '. $post['goals'][$ProjectID][$RoundID],
        $this->jsmdb->quoteName('penalties') . ' = '. $post['penalties'][$ProjectID][$RoundID],
        $this->jsmdb->quoteName('yellow_cards') . ' = '. $post['yellowcards'][$ProjectID][$RoundID],
        $this->jsmdb->quoteName('yellow_red_cards') . ' = '. $post['yellowredcards'][$ProjectID][$RoundID],
        $this->jsmdb->quoteName('red_cards') . ' = '. $post['redcards'][$ProjectID][$RoundID],
		$this->jsmdb->quoteName('modified') . ' = ' . $this->jsmdb->Quote('' . $this->date->toSql() . '') . '',
		$this->jsmdb->quoteName('modified_by') . '=' . $this->user->get('id')
		);
/** Conditions for which records should be updated. */
$conditions = array(
    $this->jsmdb->quoteName('prediction_id') . ' = '.$predictionGameID,
    $this->jsmdb->quoteName('user_id') . ' = '.$joomlaUserID,
    $this->jsmdb->quoteName('project_id') . ' = '.$ProjectID, 
    $this->jsmdb->quoteName('round_id') . ' = ' . $RoundID
);        
$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_prediction_result_round'))->set($fields)->where($conditions);

try
{
$this->jsmdb->setQuery($this->jsmquery);
$result = $this->jsmdb->execute();
}
catch (Exception $e)
{
$this->jsmapp->enqueueMessage(Text::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
}
        
		}

		$configavatar         = sportsmanagementModelPrediction::getPredictionTemplateConfig('predictionusers');
		$predictionMemberInfo = sportsmanagementModelPrediction::getPredictionMember($configavatar);

		$changedResultArray = array();

		for ($x = 0; $x < count($pids); $x++)
		{
			for ($y = 0; $y < count($cids[$pids[$x]]); $y++)
			{
				$tippmode   = $ptippmode[$pids[$x]];
				$dProjectID = $pids[$x];
				$dMatchID   = $cids[$pids[$x]][$y];
				$dprID      = $prids[$pids[$x]][$dMatchID];

				$dHome     = $homes[$pids[$x]][$cids[$pids[$x]][$y]];
				$tmp_dHome = $dHome;

				if ((!isset($homes[$pids[$x]][$cids[$pids[$x]][$y]])) || (trim($dHome == '')))
				{
					$dHome = "NULL";
				}
				else
				{
					$dHome = $dHome;
				}

				$dAway     = $aways[$pids[$x]][$cids[$pids[$x]][$y]];
				$tmp_dAway = $dAway;

				if ((!isset($aways[$pids[$x]][$cids[$pids[$x]][$y]])) || (trim($dAway == '')))
				{
					$dAway = "NULL";
				}
				else
				{
					$dAway = $dAway;
				}

				$dJoker    = (isset($jokers[$pids[$x]][$cids[$pids[$x]][$y]])) ? '1' : '0';
				$dTipp     = $tipps[$pids[$x]][$cids[$pids[$x]][$y]];
				$tmp_dTipp = $dTipp;

				if ((!isset($tipps[$pids[$x]][$cids[$pids[$x]][$y]])) || (trim($dTipp == '')))
				{
					$dTipp = "NULL";
				}
				else
				{
					$dTipp = $dTipp;
				}

				if (((isset($homes[$pids[$x]][$cids[$pids[$x]][$y]]))
						&& (trim($dHome) != "NULL")
						&& (isset($aways[$pids[$x]][$cids[$pids[$x]][$y]]))
						&& (trim($dAway) != "NULL"))
					|| ($dTipp != "NULL")
				)
				{
					// If ($dTipp=="NULL")
					// {
					if ($tmp_dHome > $tmp_dAway)
					{
						$dTipp = "1";
					}
					elseif ($tmp_dHome < $tmp_dAway)
					{
						$dTipp = "2";
					}
					else
					{
						$dTipp = "0";
					}

					// }

					if ($tippmode)
					{
						$dHome = null;
						$dAway = null;
						$dTipp = $tipps[$pids[$x]][$cids[$pids[$x]][$y]];
					}

					if (!empty($dprID))
					{
						$temp              = new stdClass;
						$temp->id          = (int) $dprID;
						$temp->tipp        = $dTipp;
						$temp->tipp_home   = $dHome;
						$temp->tipp_away   = $dAway;
						$temp->joker       = $dJoker;
						$temp->modified_by = $joomlaUserID;
						$temp->modified    = Factory::getDate()->toSql();

						try
						{
							$resultquery = Factory::getDbo()->updateObject('#__sportsmanagement_prediction_result', $temp, 'id', true);
						}
						catch (Exception $e)
						{
							// Catch any database errors.
						}

						if (!$resultquery)
						{
							$result = false;
						}
					}
					else
					{
						$temp                = new stdClass;
						$temp->prediction_id = $predictionGameID;
						$temp->user_id       = $joomlaUserID;
						$temp->project_id    = $dProjectID;
						$temp->match_id      = $dMatchID;
						$temp->tipp          = $dTipp;
						$temp->tipp_home     = $dHome;
						$temp->tipp_away     = $dAway;
						$temp->joker         = $dJoker;
						$temp->modified_by   = $joomlaUserID;
						$temp->modified      = Factory::getDate()->toSql();

						// Insert the object
						try
						{
							$resultquery = Factory::getDbo()->insertObject('#__sportsmanagement_prediction_result', $temp);
						}
						catch (Exception $e)
						{
							// Catch any database errors.
						}

						if (!$resultquery)
						{
							$result = false;
						}
					}
				}
				else
				{
					// Der Query wird erstellt

					$query->clear();
					$query->from('#__sportsmanagement_prediction_result');
					$query->delete();
					$query->where('prediction_id = ' . $predictionGameID);
					$query->where('user_id = ' . $joomlaUserID);
					$query->where('project_id = ' . $pids[$x]);
					$query->where('match_id = ' . $cids[$pids[$x]][$y]);

					$db->setQuery($query);

					if (!$db->execute())
					{
						$result = false;
					}
				}
			}
		}

		// Must be a valid primary key value.
		$object            = new stdClass;
		$object->id        = $mID;
		$object->last_tipp = date('Y-m-d H:i:s');

		// Update their details in the table using id as the primary key.
		$resultquery = Factory::getDbo()->updateObject('#__sportsmanagement_prediction_member', $object, 'id');

		if (!$resultquery)
		{
			$result = false;
		}

		/**
		 * email mit tippergebnissen senden
		 */
		if ($predictionMemberInfo->receipt)
		{
			sportsmanagementModelPrediction::sendMemberTipResults($mID, $predictionGameID, $RoundID, $ProjectID, $joomlaUserID);
		}

		return $result;
	}

}
