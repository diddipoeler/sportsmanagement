<?PHP
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage libraries
 * @file       model.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Log\Log;

/**
 * JSMModelAdmin
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class JSMModelAdmin extends AdminModel
{


	/**
	 * JSMModelAdmin::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
		$this->jsmdb = sportsmanagementHelper::getDBConnection();
		parent::setDbo($this->jsmdb);
		$this->jsmquery       = $this->jsmdb->getQuery(true);
		$this->jsmsubquery1   = $this->jsmdb->getQuery(true);
		$this->jsmsubquery2   = $this->jsmdb->getQuery(true);
		$this->jsmsubquery3   = $this->jsmdb->getQuery(true);
		$this->jsmapp         = Factory::getApplication();
		$this->jsmjinput      = $this->jsmapp->input;
		$this->jsmoption      = $this->jsmjinput->getCmd('option');
		$this->jsmview        = $this->jsmjinput->getCmd('view');
		$this->jsmdocument    = Factory::getDocument();
		$this->jsmuser        = Factory::getUser();
		$this->jsmdate        = Factory::getDate();
		$this->jsmmessage     = '';
		$this->jsmmessagetype = 'notice';

		$this->project_id = $this->jsmjinput->getint('pid');

		if (!$this->project_id)
		{
			$post = $this->jsmjinput->post->getArray();

			if (isset($post['pid']))
			{
				$this->project_id = $post['pid'];
			}

			if (!$this->project_id)
			{
				$this->project_id = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
			}
		}

		$this->jsmjinput->set('pid', $this->project_id);
		$this->jsmapp->setUserState("$this->jsmoption.pid", $this->project_id);

		/**
		 * abfrage nach backend und frontend
		 */
		if ($this->jsmapp->isClient('administrator'))
		{
		}

		if ($this->jsmapp->isClient('site'))
		{
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array    The form data.
	 *
	 * @return boolean    True on success.
	 * @since  1.6
	 */
	public function save($data)
	{
		$post          = $this->jsmjinput->post->getArray();
		$address_parts = array();
		$person_double = array();
		$parentsave    = true;

		// $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' task '.$this->jsmjinput->get('task')), '');

		$input_options = InputFilter::getInstance(
			array(
				'img', 'p', 'a', 'u', 'i', 'b', 'strong', 'span', 'div', 'ul', 'li', 'ol', 'h1', 'h2', 'h3', 'h4', 'h5',
				'table', 'tr', 'td', 'th', 'tbody', 'theader', 'tfooter', 'br'
			),
			array(
				'src', 'width', 'height', 'alt', 'style', 'href', 'rel', 'target', 'align', 'valign', 'border', 'cellpading',
				'cellspacing', 'title', 'id', 'class'
			)
		);

		$postData = new Input($this->jsmjinput->get('jform', '', 'array'), array('filter' => $input_options));

		if (array_key_exists('notes', $data))
		{
			$html          = $postData->get('notes', '', 'raw');
			$data['notes'] = $html;
		}

		if (isset($post['extended']) && is_array($post['extended']))
		{
			/**
			 *              Convert the extended field to a string.
			 */
			$parameter = new Registry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string) $parameter;
		}

		if (isset($post['extendeduser']) && is_array($post['extendeduser']))
		{
			/**
			 *              Convert the extended field to a string.
			 */
			$parameter = new Registry;
			$parameter->loadArray($post['extendeduser']);
			$data['extendeduser'] = (string) $parameter;
		}

		/**
		 *         Set the values
		 */
		$data['modified']         = $this->jsmdate->toSql();
		$data['modified_by']      = $this->jsmuser->get('id');
		$data['checked_out']      = 0;
		$data['checked_out_time'] = $this->jsmdb->getNullDate();

		/**
		 * differenzierung zwischen den views
		 */
		switch ($this->jsmview)
		{
			/**
			 * gruppen
			 */
			case 'division':
				if (!$data['id'])
				{
					$data['project_id'] = $this->project_id;
				}

				if (isset($post['extended']) && is_array($post['extended']))
				{
					// Convert the extended field to a string.
					$parameter = new Registry;
					$parameter->loadArray($post['extended']);
					$data['rankingparams'] = (string) $parameter;
				}

				break;
			/**
			 * runde
			 */
			case 'round':
				if ($data['round_date_first'] != '00-00-0000' && $data['round_date_first'] != '')
				{
					$data['round_date_first'] = sportsmanagementHelper::convertDate($data['round_date_first'], 0);
				}

				if ($data['round_date_last'] != '00-00-0000' && $data['round_date_last'] != '')
				{
					$data['round_date_last'] = sportsmanagementHelper::convertDate($data['round_date_last'], 0);
				}
				break;
			/**
			 * runden
			 */
			case 'rounds':
				$data['round_date_first'] = sportsmanagementHelper::convertDate($data['round_date_first'], 0);
				$data['round_date_last']  = sportsmanagementHelper::convertDate($data['round_date_last'], 0);

				if (!isset($data['id']))
				{
					$data['id']         = 0;
					$data['project_id'] = $post['pid'];
					$data['roundcode']  = $post['next_roundcode'];
				}
				break;
			/**
			 * projektteam
			 */
			case 'projectteam':
				if (array_key_exists('copy_jform', $post))
				{
					$data['picture']     = $post['copy_jform']['picture'];
					$data['trikot_home'] = $post['copy_jform']['trikot_home'];
					$data['trikot_away'] = $post['copy_jform']['trikot_away'];
				}

				if ($post['delete'])
				{
					$mdlTeam = BaseDatabaseModel::getInstance("Team", "sportsmanagementModel");
					$mdlTeam->DeleteTrainigData($post['delete'][0]);
				}

				if ($post['add_trainingData'])
				{
					$row = Table::getInstance('seasonteam', 'sportsmanagementTable');
					$row->load((int) $post['jform']['team_id']);
					$mdlTeam = BaseDatabaseModel::getInstance("Team", "sportsmanagementModel");
					$mdlTeam->addNewTrainigData($row->team_id);
				}

				if ($post['tdids'])
				{
					$mdlTeam = BaseDatabaseModel::getInstance("Team", "sportsmanagementModel");
					$mdlTeam->UpdateTrainigData($post);
				}

				/**
				 * das mannschaftsfoto wird zusätzlich abgespeichert,
				 * damit man die historischen kader sieht
				 */
				// Create an object for the record we are going to update.
				$object = new stdClass;

				// Must be a valid primary key value.
				$object->id          = (int) $post['jform']['team_id'];
				$object->picture     = $data['picture'];
				$object->modified    = $this->jsmdate->toSql();
				$object->modified_by = $this->jsmuser->get('id');

				// Update their details in the table using id as the primary key.
				$result = Factory::getDbo()->updateObject('#__sportsmanagement_season_team_id', $object, 'id');

				break;
			/**
			 * liga
			 */
			case 'league':
				if (array_key_exists('copy_jform', $post))
				{
					$data['picture'] = $post['copy_jform']['picture'];
				}

				$data['sports_type_id'] = $data['request']['sports_type_id'];
				$data['agegroup_id']    = $data['request']['agegroup_id'];
				if ($data['founded'] != '0000-00-00' && $data['founded'] != '')
				{
					$data['founded'] = sportsmanagementHelper::convertDate($data['founded'], 0);
				}
				if ($data['dissolved'] != '0000-00-00' && $data['dissolved'] != '')
				{
					$data['dissolved'] = sportsmanagementHelper::convertDate($data['dissolved'], 0);
				}
				break;
			/**
			 * person
			 */
			case 'player':
				if ($data['height'] == '')
				{
					$data['height'] = null;
				}

				if ($data['weight'] == '')
				{
					$data['weight'] = null;
				}

				if ($data['contact_id'] == '')
				{
					$data['contact_id'] = null;
				}

				if ($data['birthday'] == '')
				{
					$data['birthday'] = '0000-00-00';
				}

				if ($data['deathday'] == '')
				{
					$data['deathday'] = '0000-00-00';
				}

				if ($data['injury_date_start'] == '')
				{
					$data['injury_date_start'] = '0000-00-00';
				}

				if ($data['injury_date_end'] == '')
				{
					$data['injury_date_end'] = '0000-00-00';
				}

				if ($data['susp_date_start'] == '')
				{
					$data['susp_date_start'] = '0000-00-00';
				}

				if ($data['susp_date_end'] == '')
				{
					$data['susp_date_end'] = '0000-00-00';
				}

				if ($data['away_date_start'] == '')
				{
					$data['away_date_start'] = '0000-00-00';
				}

				if ($data['away_date_end'] == '')
				{
					$data['away_date_end'] = '0000-00-00';
				}

				$data['person_art']     = $data['request']['person_art'];
				$data['person_id1']     = $data['request']['person_id1'];
				$data['person_id2']     = $data['request']['person_id2'];
				$data['sports_type_id'] = $data['request']['sports_type_id'];
				$data['position_id']    = $data['request']['position_id'];
				$data['agegroup_id']    = $data['request']['agegroup_id'];

				if (array_key_exists('copy_jform', $post))
				{
					$data['picture'] = $post['copy_jform']['picture'];
				}

				switch ($data['person_art'])
				{
					case 1:
						break;
					case 2:
						if ($data['person_id1'] && $data['person_id2'])
						{
							$person_1 = $data['person_id1'];
							$person_2 = $data['person_id2'];
							$table    = 'person';
							$row      = Table::getInstance($table, 'sportsmanagementTable');
							$row->load((int) $person_1);
							$person_double[] = $row->firstname . ' ' . $row->lastname;
							$row->load((int) $person_2);
							$person_double[]   = $row->firstname . ' ' . $row->lastname;
							$data['lastname']  = implode(" - ", $person_double);
							$data['firstname'] = '';
						}
						break;
				}

				/**
				 * hat der user die bildfelder geleert, werden die standards gesichert.
				 */
				if (empty($data['picture']))
				{
					switch ($data['gender'])
					{
						case 0:
							$data['picture'] = ComponentHelper::getParams($this->jsmoption)->get('ph_player', '');
							break;
						case 1:
							$data['picture'] = ComponentHelper::getParams($this->jsmoption)->get('ph_player_men_small', '');
							break;
						case 2:
							$data['picture'] = ComponentHelper::getParams($this->jsmoption)->get('ph_player_woman_small', '');
							break;
					}
				}

				if ($data['birthday'] != '0000-00-00' && $data['birthday'] != '')
				{
					$data['birthday'] = sportsmanagementHelper::convertDate($data['birthday'], 0);
				}

				if ($data['deathday'] != '0000-00-00' && $data['deathday'] != '')
				{
					$data['deathday'] = sportsmanagementHelper::convertDate($data['deathday'], 0);
				}

				if ($data['injury_date_start'] != '0000-00-00' && $data['injury_date_start'] != '')
				{
					$data['injury_date_start'] = sportsmanagementHelper::convertDate($data['injury_date_start'], 0);
				}

				if ($data['injury_date_end'] != '0000-00-00' && $data['injury_date_end'] != '')
				{
					$data['injury_date_end'] = sportsmanagementHelper::convertDate($data['injury_date_end'], 0);
				}

				if ($data['susp_date_start'] != '0000-00-00' && $data['susp_date_start'] != '')
				{
					$data['susp_date_start'] = sportsmanagementHelper::convertDate($data['susp_date_start'], 0);
				}

				if ($data['susp_date_end'] != '0000-00-00' && $data['susp_date_end'] != '')
				{
					$data['susp_date_end'] = sportsmanagementHelper::convertDate($data['susp_date_end'], 0);
				}

				if ($data['away_date_start'] != '0000-00-00' && $data['away_date_start'] != '')
				{
					$data['away_date_start'] = sportsmanagementHelper::convertDate($data['away_date_start'], 0);
				}

				if ($data['away_date_end'] != '0000-00-00' && $data['away_date_end'] != '')
				{
					$data['away_date_end'] = sportsmanagementHelper::convertDate($data['away_date_end'], 0);
				}
				break;
			/**
			 * template
			 */
			case 'template':
				if (isset($post['params']['colors_ranking']) && is_array($post['params']['colors_ranking']))
				{
					$colors = array();

					foreach ($post['params']['colors_ranking'] as $key => $value)
					{
						if (!empty($value['von']))
						{
							$colors[] = implode(",", $value);
						}
					}

					$post['params']['colors'] = implode(";", $colors);
				}
				break;
			/**
			 * verein
			 */
			case 'club':
				/**
				 * gibt es vereinsnamen zum ändern ?
				 */
				if (isset($post['team_id']) && is_array($post['team_id']))
				{
					foreach ($post['team_id'] as $key => $value)
					{
						$team_id   = $post['team_id'][$key];
						$team_name = $post['team_value_id'][$key];

						// Create an object for the record we are going to update.
						$object = new stdClass;

						// Must be a valid primary key value.
						$object->id    = $team_id;
						$object->name  = $team_name;
						$object->alias = OutputFilter::stringURLSafe($team_name);

						// Update their details in the table using id as the primary key.
						$result = Factory::getDbo()->updateObject('#__sportsmanagement_team', $object, 'id');
					}
				}

				/**
				 * hat der user die bildfelder geleert, werden die standards gesichert.
				 */
				if (array_key_exists('copy_jform', $post))
				{
					$data['logo_big']    = $post['copy_jform']['logo_big'];
					$data['logo_middle'] = $post['copy_jform']['logo_middle'];
					$data['logo_small']  = $post['copy_jform']['logo_small'];
					$data['trikot_home'] = $post['copy_jform']['trikot_home'];
					$data['trikot_away'] = $post['copy_jform']['trikot_away'];
				}

				if (empty($data['logo_big']))
				{
					$data['logo_big'] = ComponentHelper::getParams($option)->get('ph_logo_big', '');
				}

				if (empty($data['logo_middle']))
				{
					$data['logo_middle'] = ComponentHelper::getParams($option)->get('ph_logo_medium', '');
				}

				if (empty($data['logo_small']))
				{
					$data['logo_small'] = ComponentHelper::getParams($option)->get('ph_logo_small', '');
				}

				/**
				 * wurden jahre mitgegeben ?
				 */
				if ($data['founded'] != '0000-00-00' && $data['founded'] != '')
				{
					$data['founded'] = sportsmanagementHelper::convertDate($data['founded'], 0);
				}

				if ($data['dissolved'] != '0000-00-00' && $data['dissolved'] != '')
				{
					$data['dissolved'] = sportsmanagementHelper::convertDate($data['dissolved'], 0);
				}

				if ($data['founded'] == '0000-00-00' || $data['founded'] == '')
				{
					$data['founded'] = '0000-00-00';
				}

				if ($data['founded'] != '0000-00-00' && $data['founded'] != '')
				{
					$data['founded_year']      = date('Y', strtotime($data['founded']));
					$data['founded_timestamp'] = sportsmanagementHelper::getTimestamp($data['founded']);
				}
				else
				{
					$data['founded_year'] = $data['founded_year'];
				}

				if ($data['dissolved'] == '0000-00-00' || $data['dissolved'] == '')
				{
					$data['dissolved'] = '0000-00-00';
				}

				if ($data['dissolved'] != '0000-00-00' && $data['dissolved'] != '')
				{
					$data['dissolved_year']      = date('Y', strtotime($data['dissolved']));
					$data['dissolved_timestamp'] = sportsmanagementHelper::getTimestamp($data['dissolved']);
				}
				else
				{
					$data['dissolved_year'] = $data['dissolved_year'];
				}
				
				if ( !$data['founded_year'] )
            {
            $data['founded_year'] = 'kein';
            }
				
				break;
			/**
			 * mannschaft
			 */
			case 'team':
				if (array_key_exists('copy_jform', $post))
				{
					$data['picture'] = $post['copy_jform']['picture'];
				}

				if ($post['delete'])
				{
					sportsmanagementModelteam::DeleteTrainigData($post['delete'][0]);
				}

				if ($post['tdids'])
				{
					sportsmanagementModelteam::UpdateTrainigData($post);
				}

				if ($post['add_trainingData'])
				{
					sportsmanagementModelteam::addNewTrainigData($data[id]);
				}
				break;

			/**
			 * playground
			 */
			case 'playground':
				if (array_key_exists('copy_jform', $post))
				{
					$data['picture'] = $post['copy_jform']['picture'];
				}
				break;

			/**
			 * projekt
			 */
			case 'project':
				if (array_key_exists('copy_jform', $post))
				{
					$data['picture'] = $post['copy_jform']['picture'];
				}

				$data['start_date']         = sportsmanagementHelper::convertDate($data['start_date'], 0);
				$data['sports_type_id']     = $data['request']['sports_type_id'];
				$data['agegroup_id']        = $data['request']['agegroup_id'];
				$data['modified_timestamp'] = sportsmanagementHelper::getTimestamp($data['modified']);

				if (!$post['jform']['fav_team'])
				{
					$data['fav_team'] = '';
				}
				else
				{
					$data['fav_team'] = implode(',', $post['jform']['fav_team']);
				}
				break;
			/**
			 * tippspiel
			 */
			case 'predictiongame':
				$data['alias'] = OutputFilter::stringURLSafe($data['name']);

				if (isset($data['notify_to']))
				{
				}
				else
				{
					$data['notify_to'] = '-';
				}
				break;
			default:
				break;
		}

		if (isset($post['params']) && is_array($post['params']))
		{
			/**
			 *              Convert the params field to a string.
			 */
			$paramsString   = json_encode($post['params']);
			$data['params'] = $paramsString;
		}

		/**
		 * Alter the title for Save as Copy
		 */
		if ($this->jsmjinput->get('task') == 'save2copy')
		{
			$orig_table = $this->getTable();
			$orig_table->load((int) $this->jsmjinput->getInt('id'));
			$data['id'] = 0;
			/**
			 * differenzierung zwischen den views
			 */
			switch ($this->jsmview)
			{
				/**
				 * template
				 */
				case 'template':
					$data['project_id'] = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
					$data['title']      = $post['title'];
					$data['template']   = $post['template'];
					break;
				/**
				 * projekt
				 */
				case 'project':
					$data['current_round'] = 0;
					$project_old           = (int) $this->jsmjinput->getInt('id');
					break;
				default:
					break;
			}

			if ($data['name'] == $orig_table->name)
			{
				$data['name']  .= ' ' . Text::_('JGLOBAL_COPY');
				$data['alias'] = OutputFilter::stringURLSafe($data['name']);
			}
		}

		/**
		 * zuerst sichern, damit wir bei einer neuanlage die id haben
		 */
		try
		{
			$parentsave = parent::save($data);
			$table      = $this->getTable();

			foreach ($table->getErrors() as $error)
			{
				$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $error), 'error');
			}
		}
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), 'error');
			$parentsave = false;
		}

		//    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' parentsave '.$parentsave), '');
		//    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' getState id '.$this->getState($this->getName().'.id') ), '');
		//    $this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' jsmjinput id '.$this->jsmjinput->getInt('id') ), '');
		if ($parentsave)
		{
			$id         = (int) $this->getState($this->getName() . '.id');
			$isNew      = $this->getState($this->getName() . '.new');
			$data['id'] = $id;
			$this->jsmapp->setUserState("$this->jsmoption.club_id", $id);
			$this->jsmapp->setUserState("$this->jsmoption.person_id", $id);
			$this->jsmapp->setUserState("$this->jsmoption.insert_project_id", $id);
			$this->jsmjinput->set('insert_id', $id);
			$this->jsmjinput->set('person_id', $id);
			$this->jsmjinput->set('insert_project_id', $id);

			if ($isNew)
			{
				/**
				 * Here you can do other tasks with your newly saved record...
				 */
				$this->jsmapp->enqueueMessage(Text::plural(strtoupper($this->jsmoption) . '_N_ITEMS_CREATED', $id), '');

				if ($this->jsmjinput->get('task') == 'save2copy')
				{
					/**
					 * differenzierung zwischen den views
					 */
					switch ($this->jsmview)
					{
						case 'project':
							$this->jsmquery->clear();
							$this->jsmquery->select('*');
							$this->jsmquery->from('#__sportsmanagement_division');
							$this->jsmquery->where('project_id =' . $project_old);
							$this->jsmdb->setQuery($this->jsmquery);
							$result = $this->jsmdb->loadObjectList();

							foreach ($result as $field)
							{
								// Create and populate an object.
								$profile             = new stdClass;
								$profile->project_id = $id;
								$profile->name       = $field->name;
								$profile->alias      = $field->alias;
								$profile->shortname  = $field->shortname;
								$profile->published  = $field->published;
								$profile->ordering   = $field->ordering;

								// Insert the object into the user profile table.
								$insertresult = $this->jsmdb->insertObject('#__sportsmanagement_division', $profile);
							}

							break;
						default:
							break;
					}
				}
			}

			/**
			 * differenzierung zwischen den views
			 */
			switch ($this->jsmview)
			{
				/**
				 * person
				 */
				case 'player':
					if (isset($data['season_ids']) && is_array($data['season_ids']))
					{
						$message       = '';
						$delete_season = array();

						foreach ($data['season_ids'] as $key => $value)
						{
							$this->jsmquery->clear();
							$this->jsmquery->select('spi.id,s.name');
							$this->jsmquery->from('#__sportsmanagement_season_person_id as spi');
							$this->jsmquery->join('INNER', '#__sportsmanagement_season AS s ON s.id = spi.season_id ');
							$this->jsmquery->where('spi.person_id =' . $data['id']);
							$this->jsmquery->where('spi.season_id =' . $value);
							$this->jsmdb->setQuery($this->jsmquery);
							$res             = $this->jsmdb->loadObject();
							$delete_season[] = $value;

							if (!$res)
							{
								$this->jsmquery->clear();

								// Insert columns.
								$columns = array('person_id', 'season_id', 'modified', 'modified_by');

								// Insert values.
								$values = array($data['id'], $value, $this->jsmdb->Quote('' . $data['modified'] . ''), $data['modified_by']);

								// Prepare the insert query.
								$this->jsmquery
									->insert($this->jsmdb->quoteName('#__sportsmanagement_season_person_id'))
									->columns($this->jsmdb->quoteName($columns))
									->values(implode(',', $values));
								$this->jsmdb->setQuery($this->jsmquery);

								try
								{
									sportsmanagementModeldatabasetool::runJoomlaQuery();
								}
								catch (Exception $e)
								{
								}

								$message .= 'Saisonzuordnung : ' . $res->name . ' angelegt.<br>';
							}
							else
							{
								$message .= 'Saisonzuordnung : ' . $res->name . ' schon vorhanden.<br>';
							}
						}

						// Delete all custom keys
						$this->jsmquery->clear();
						$this->jsmquery->delete()->from('#__sportsmanagement_season_person_id')->where('season_id NOT IN (' . implode(",", $delete_season) . ') AND person_id = ' . $data['id']);
						$this->jsmdb->setQuery($this->jsmquery);
						$result = $this->jsmdb->execute();

						$this->jsmapp->enqueueMessage($message, 'message');
					}

					/**
					 *
					 * -------extra fields-----------
					 */
					sportsmanagementHelper::saveExtraFields($post, $data['id']);

					break;
				/**
				 * position
				 */
				case 'position':
					/**
					 * ereignisse der positionen speichern
					 */
					if (isset($post['position_eventslist']) && is_array($post['position_eventslist']))
					{
						if ($data['id'])
						{
							$mdl = BaseDatabaseModel::getInstance("positioneventtype", "sportsmanagementModel");
							$mdl->store($post, $data['id']);
						}
					}

					/**
					 * statistiken der positionen speichern
					 */
					if (isset($post['position_statistic']) && is_array($post['position_statistic']))
					{
						if ($data['id'])
						{
							$mdl = BaseDatabaseModel::getInstance("positionstatistic", "sportsmanagementModel");
							$mdl->store($post, $data['id']);
						}
					}
					break;
				/**
				 * verein
				 */
				case 'club':
					sportsmanagementHelper::saveExtraFields($post, $data['id']);
					$this->jsmapp->setUserState("$this->jsmoption.club_id", $data['id']);
					break;
				/**
				 * projekt
				 */
				case 'project':
					sportsmanagementHelper::saveExtraFields($post, $data['id']);
					break;
				/**
				 * mannschaft
				 */
				case 'team':
					$delete_season = array();

					if (isset($data['season_ids']) && is_array($data['season_ids']))
					{
						foreach ($data['season_ids'] as $key => $value)
						{
							$this->jsmquery->clear();
							$this->jsmquery->select('id');
							$this->jsmquery->from('#__sportsmanagement_season_team_id');
							$this->jsmquery->where('team_id =' . $data['id']);
							$this->jsmquery->where('season_id =' . $value);
							$this->jsmdb->setQuery($this->jsmquery);
							$result          = $this->jsmdb->loadObjectList();
							$delete_season[] = $value;

							if (!$result)
							{
								$this->jsmquery->clear();

								// Insert columns.
								$modified    = $this->jsmdate->toSql();
								$modified_by = $this->jsmuser->get('id');
								$columns     = array('team_id', 'season_id', 'modified', 'modified_by');

								// Insert values.
								$values = array($data['id'], $value, $this->jsmdb->Quote('' . $modified . ''), $modified_by);

								// Prepare the insert query.
								$this->jsmquery
									->insert($this->jsmdb->quoteName('#__sportsmanagement_season_team_id'))
									->columns($this->jsmdb->quoteName($columns))
									->values(implode(',', $values));

								// Set the query using our newly populated query object and execute it.
								$this->jsmdb->setQuery($this->jsmquery);

								if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
								{
								}
							}
						}

						// Delete all custom keys
						$this->jsmquery->clear();
						$this->jsmquery->delete()->from('#__sportsmanagement_season_team_id')->where('season_id NOT IN (' . implode(",", $delete_season) . ') AND team_id = ' . $data['id']);
						$this->jsmdb->setQuery($this->jsmquery);
						$result = $this->jsmdb->execute();
					}

					sportsmanagementHelper::saveExtraFields($post, $data['id']);
					$this->jsmapp->setUserState("$this->jsmoption.team_id", $data['id']);
					break;
				default:
					break;
			}

			return true;
		}
		else
		{
			$id = $this->jsmjinput->getInt('id');

			//        $isNew = $this->getState($this->getName() . '.new');
			//        $data['id'] = $id;
			$this->jsmapp->setUserState("$this->jsmoption.club_id", $id);
			$this->jsmapp->setUserState("$this->jsmoption.person_id", $id);
			$this->jsmjinput->set('insert_id', $id);
			$this->jsmjinput->set('person_id', $id);

			return false;
		}
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    The table type to instantiate
	 * @param   string    A prefix for the table class name. Optional.
	 * @param   array    Configuration array for model. Optional.
	 *
	 * @return JTable    A database object
	 * @since  1.6
	 */
	public function getTable($type = '', $prefix = 'sportsmanagementTable', $config = array())
	{
		$config['dbo'] = sportsmanagementHelper::getDBConnection();

		if (empty($type))
		{
			$type = $this->getName();
		}

		return Table::getInstance($type, $prefix, $config);
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
		$show_team_community  = ComponentHelper::getParams($this->jsmoption)->get('show_team_community', 0);
		$cfg_use_plz_table    = ComponentHelper::getParams($this->jsmoption)->get('cfg_use_plz_table', 0);

		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.' . $this->getName(), $this->getName(), array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$joomladirectory = '';

		if ($cfg_which_media_tool == 'media')
		{
			/**
			 *
			 * welche joomla version ?
			 */
			if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
			{
				$joomladirectory = 'local-0:/';
			}
		}

		$prefix = $this->jsmapp->getCfg('dbprefix');

		switch ($this->getName())
		{
			case 'position':
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_position' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}
				break;
			case 'statistic':
				$form->setFieldAttribute('icon', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_icon', ''));
				$form->setFieldAttribute('icon', 'directory', $joomladirectory . 'com_sportsmanagement/database/statistics');
				$form->setFieldAttribute('icon', 'type', $cfg_which_media_tool);
				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_statistic' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}
				break;
			case 'projectreferee':
				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_player',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/projectreferees');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'division':
				$form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_icon', ''));
				$form->setFieldAttribute('picture', 'directory', $joomladirectory . 'com_sportsmanagement/database/divisions');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'teamperson':
				$form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_player', ''));
				$form->setFieldAttribute('picture', 'directory', $joomladirectory . 'com_sportsmanagement/database/teamplayers');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'smquote':
				$form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_big', ''));
				$form->setFieldAttribute('picture', 'directory', $joomladirectory . 'com_sportsmanagement/database/persons');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'jlextfederation':
				// $form->setFieldAttribute('assocflag', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_flags',''));
				// $form->setFieldAttribute('assocflag', 'directory', $joomladirectory.'com_sportsmanagement/database/flags_associations');
				$form->setFieldAttribute('assocflag', 'type', $cfg_which_media_tool);

				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/associations');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_federations' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}
				break;
			case 'jlextcountry':
				$form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_flags', ''));
				$form->setFieldAttribute('picture', 'directory', $joomladirectory . 'com_sportsmanagement/database/flags');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_countries' ");

				try
				{
					$this->jsmdb->setQuery($this->jsmquery);
					$result = $this->jsmdb->loadObjectList();

					foreach ($result as $field)
					{
						switch ($field->DATA_TYPE)
						{
							case 'varchar':
								$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
								break;
						}
					}
				}
				catch (Exception $e)
				{
					//    // catch any database errors.
					//    $db->transactionRollback();
					//    JErrorPage::render($e);
				}

				break;
			case 'jlextassociation':
				// $form->setFieldAttribute('assocflag', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_flags',''));
				// $form->setFieldAttribute('assocflag', 'directory', $joomladirectory.'com_sportsmanagement/database/flags_associations');
				$form->setFieldAttribute('assocflag', 'type', $cfg_which_media_tool);

				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/associations');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_jlextassociation' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}
				break;
			case 'eventtype':
				$form->setFieldAttribute('icon', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_icon', ''));
				$form->setFieldAttribute('icon', 'directory', $joomladirectory . 'com_sportsmanagement/database/events');
				$form->setFieldAttribute('icon', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_eventtype' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}
				break;
			case 'round':
				$form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_team', ''));
				$form->setFieldAttribute('picture', 'directory', $joomladirectory . 'com_sportsmanagement/database/rounds');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'project':
				if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
				{
					$form->setFieldAttribute('use_legs', 'type', 'radio');
					$form->setFieldAttribute('use_legs', 'class', 'switcher');
				}

				$sports_type_id = $form->getValue('sports_type_id');
				$this->jsmquery->clear();

				// Select some fields
				$this->jsmquery->select('name');

				// From table
				$this->jsmquery->from('#__sportsmanagement_sports_type');

				// Where
				$this->jsmquery->where('id = ' . (int) $sports_type_id);
				$this->jsmdb->setQuery($this->jsmquery);
				$result = $this->jsmdb->loadResult();

				/*
				switch ($result)
				{
			 case 'COM_SPORTSMANAGEMENT_ST_TENNIS';
			 break;
			 default:
			 $form->setFieldAttribute('use_tie_break', 'type', 'hidden');
			 $form->setFieldAttribute('tennis_single_matches', 'type', 'hidden');
			 $form->setFieldAttribute('tennis_double_matches', 'type', 'hidden');
			 break;
				}
				*/
				switch (ComponentHelper::getParams($this->jsmoption)->get('which_article_component'))
				{
					case 'com_content':
						$form->setFieldAttribute('category_id', 'type', 'category');
						$form->setFieldAttribute('category_id', 'extension', 'com_content');
						break;
					case 'com_k2':
						$form->setFieldAttribute('category_id', 'type', 'categorylistk2');

						break;
				}

				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/projects');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				break;
			case 'projectteam':
				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/projectteams');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				// $form->setFieldAttribute('trikot_home', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
				// $form->setFieldAttribute('trikot_home', 'directory', $joomladirectory.'com_sportsmanagement/database/projectteams/trikot_home');
				$form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);

				// $form->setFieldAttribute('trikot_away', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
				// $form->setFieldAttribute('trikot_away', 'directory', $joomladirectory.'com_sportsmanagement/database/projectteams/trikot_away');
				$form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);
				break;
			case 'club':
				$row = $this->getTable();
				$row->load((int) $form->getValue('id'));
				$country = $row->country;

				/**
				 * soll die postleitzahlendatentabelle genutzt werden ?
				 */
				if ($cfg_use_plz_table)
				{
					/**
					 * wenn es aber zu dem land keine einträge
					 * in der plz tabelle gibt, dann die normale
					 * eingabe dem user anbieten
					 */
					$this->jsmquery->clear();
					$this->jsmquery->select('count(*) as anzahl');
					$this->jsmquery->from('#__sportsmanagement_countries_plz as a');
					$this->jsmquery->join('INNER', '#__sportsmanagement_countries AS c ON c.alpha2 = a.country_code');
					$this->jsmquery->where('c.alpha3 LIKE ' . $this->jsmdb->Quote('' . $country . ''));
					$this->jsmdb->setQuery($this->jsmquery);
					$result = $this->jsmdb->loadResult();

					if ($result)
					{
						$form->setFieldAttribute('zipcode', 'type', 'dependsql', 'request');
						$form->setFieldAttribute('zipcode', 'size', '10', 'request');
						$form->setFieldAttribute('location', 'type', 'dependsql', 'request');
						$form->setFieldAttribute('location', 'size', '10', 'request');
					}
				}

				if (!$show_team_community)
				{
					$form->setFieldAttribute('merge_teams', 'type', 'hidden');
				}

if ( $form->getValue('logo_big') == 'images/com_sportsmanagement/database/clubs/large/placeholder_150.png' )
{
$form->setFieldAttribute('logo_big', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));	
}

				
				//        $form->setFieldAttribute('logo_small', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
				//        $form->setFieldAttribute('logo_small', 'directory', $joomladirectory.'com_sportsmanagement/database/clubs/small');
				$form->setFieldAttribute('logo_small', 'type', $cfg_which_media_tool);

				//        $form->setFieldAttribute('logo_middle', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_medium',''));
				//        $form->setFieldAttribute('logo_middle', 'directory', $joomladirectory.'com_sportsmanagement/database/clubs/medium');
				$form->setFieldAttribute('logo_middle', 'type', $cfg_which_media_tool);

				//        $form->setFieldAttribute('logo_big', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_big',''));
				//        $form->setFieldAttribute('logo_big', 'directory', $joomladirectory.'com_sportsmanagement/database/clubs/large');
				$form->setFieldAttribute('logo_big', 'type', $cfg_which_media_tool);

				//        $form->setFieldAttribute('trikot_home', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
				//        $form->setFieldAttribute('trikot_home', 'directory', $joomladirectory.'com_sportsmanagement/database/clubs/trikot');
				$form->setFieldAttribute('trikot_home', 'type', $cfg_which_media_tool);

				//        $form->setFieldAttribute('trikot_away', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_logo_small',''));
				//        $form->setFieldAttribute('trikot_away', 'directory', $joomladirectory.'com_sportsmanagement/database/clubs/trikot');
				$form->setFieldAttribute('trikot_away', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_club' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->COLUMN_NAME)
					{
						case 'country':
						case 'merge_teams':
							break;
						default:
							switch ($field->DATA_TYPE)
							{
								case 'varchar':
									$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
									break;
							}
							break;
					}
				}
				break;
			case 'team':
				if (!$show_team_community)
				{
					$form->setFieldAttribute('merge_clubs', 'type', 'hidden');
				}

				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/teams');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_team' ");
				$this->jsmdb->setQuery($this->jsmquery);
				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->COLUMN_NAME)
					{
						case 'country':
						case 'merge_clubs':
							break;
						default:
							switch ($field->DATA_TYPE)
							{
								case 'varchar':
									$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
									break;
							}
							break;
					}
				}

				break;
			case 'sportstype':
				// $form->setFieldAttribute('icon', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
				// $form->setFieldAttribute('icon', 'directory', $joomladirectory.'com_sportsmanagement/database/sport_types');
				$form->setFieldAttribute('icon', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_sports_type' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}

				break;

			case 'playground':
				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_team',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/playgrounds');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_playground' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}
				break;
			case 'agegroup':
				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/agegroups');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'league':
				// $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_icon',''));
				// $form->setFieldAttribute('picture', 'directory', $joomladirectory.'com_sportsmanagement/database/leagues');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
				break;
			case 'predictionproject':
				if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
				{
					$form->setFieldAttribute('champ', 'type', 'radio');
					$form->setFieldAttribute('champ', 'class', 'switcher');
					$form->setFieldAttribute('joker', 'type', 'radio');
					$form->setFieldAttribute('joker', 'class', 'switcher');
				}

				break;
			case 'player':
				switch ($form->getValue('person_art'))
				{
					case 1:
						//            $form->setFieldAttribute('person_id1', 'type', 'hidden');
						//            $form->setFieldAttribute('person_id2', 'type', 'hidden');
						break;
					case 2:
						//            $form->setFieldAttribute('person_id1', 'type', 'personlist');
						//            $form->setFieldAttribute('person_id2', 'type', 'personlist');
						break;
				}

				//        $form->setFieldAttribute('picture', 'default', ComponentHelper::getParams($this->jsmoption)->get('ph_player_men_small',''));
				//        $form->setFieldAttribute('picture', 'directory', 'com_sportsmanagement/database/persons');
				$form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);

				if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
				{
					$form->setFieldAttribute('injury', 'type', 'radio');
					$form->setFieldAttribute('injury', 'class', 'switcher');
					$form->setFieldAttribute('suspension', 'type', 'radio');
					$form->setFieldAttribute('suspension', 'class', 'switcher');
					$form->setFieldAttribute('away', 'type', 'radio');
					$form->setFieldAttribute('away', 'class', 'switcher');
				}

				$this->jsmquery->clear();
				$this->jsmquery->select('*');
				$this->jsmquery->from('information_schema.columns');
				$this->jsmquery->where("TABLE_NAME LIKE '" . $prefix . "sportsmanagement_person' ");

				$this->jsmdb->setQuery($this->jsmquery);

				$result = $this->jsmdb->loadObjectList();

				foreach ($result as $field)
				{
					switch ($field->DATA_TYPE)
					{
						case 'varchar':
							$form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
							break;
					}
				}

				break;
		}

		return $form;
	}

	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string    Script files
	 */
	public function getScript()
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}

	/**
	 * Method to save item order
	 *
	 * @access public
	 * @return boolean    True on success
	 * @since  1.5
	 */
	function saveorder($pks = null, $order = null)
	{

		$row = $this->getTable();

		// Update ordering values
		for ($i = 0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);

			if ($row->ordering != $order[$i])
			{
				switch ($this->getName())
				{
					case 'season':
						$row->ordering = substr($row->name, 0, 4);
						break;
					default:
						$row->ordering = $order[$i];
						break;
				}

				$row->modified    = $this->jsmdate->toSql();
				$row->modified_by = $this->jsmuser->get('id');

				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

					return Text::_('JGLOBAL_SAVE_SORT_NO');
				}
			}
			else
			{
				switch ($this->getName())
				{
					case 'season':
						$row->ordering    = substr($row->name, 0, 4);
						$row->modified    = $this->jsmdate->toSql();
						$row->modified_by = $this->jsmuser->get('id');

						if (!$row->store())
						{
							sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);

							return Text::_('JGLOBAL_SAVE_SORT_NO');
						}
						break;
				}
			}
		}

		return Text::_('JGLOBAL_SAVE_SORT_YES');
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed    The data for the form.
	 * @since  1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_sportsmanagement.edit.' . $this->getName() . '.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		switch ($this->getName())
		{
			case 'league':
				if (!$data->middle_name)
				{
					$data->middle_name = $data->name;
				}

				if (!$data->short_name)
				{
					$data->short_name = $data->name;
				}
				break;
		}

		return $data;
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
		// Check specific edit permission then general edit permission.
		return Factory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) || parent::allowEdit($data, $key);
	}

}

/**
 * JSMModelList
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class JSMModelList extends ListModel
{

	/**
	 * JSMModelList::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$this->jsmapp = Factory::getApplication();
		$this->jsmjinput      = $this->jsmapp->input;
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
		$this->jsmdb = sportsmanagementHelper::getDBConnection();
		parent::setDbo($this->jsmdb);
		$this->jsmquery     = $this->jsmdb->getQuery(true);
		$this->jsmsubquery1 = $this->jsmdb->getQuery(true);
		$this->jsmsubquery2 = $this->jsmdb->getQuery(true);
		$this->jsmsubquery3 = $this->jsmdb->getQuery(true);

//		$this->jsmapp = Factory::getApplication();
//		$this->jsmjinput      = $this->jsmapp->input;
        
		$this->jsmoption      = $this->jsmjinput->getCmd('option');
		$this->jsmdocument    = Factory::getDocument();
		$this->jsmuser        = Factory::getUser();
		$this->jsmpks         = $this->jsmjinput->get('cid', array(), 'array');
		$this->jsmpost        = $this->jsmjinput->post->getArray(array());
		$this->jsmmessage     = '';
		$this->jsmmessagetype = 'notice';
		$this->project_id     = $this->jsmjinput->getint('pid');

		if (!$this->project_id)
		{
			$this->project_id = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');
		}

		$this->jsmjinput->set('pid', $this->project_id);
		$this->jsmapp->setUserState("$this->jsmoption.pid", $this->project_id);

		/**
		 * abfrage nach backend und frontend
		 */
		/*
	   if ( $this->jsmapp->isClient('administrator') )
	   {

	   }
	   if( $this->jsmapp->isClient('site') )
	   {

	   }
		*/
		/**
		 * alle fehlermeldungen online ausgeben
		 * mit der kategorie: jsmerror
		 */
		Log::addLogger(array('logger' => 'messagequeue'), Log::ALL, array('jsmerror'));

	}

}


/**
 * JSMModelLegacy
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2016
 * @version   $Id$
 * @access    public
 */
class JSMModelLegacy extends BaseDatabaseModel
{

	/**
	 * JSMModelLegacy::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{

		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
		$this->jsmdb = sportsmanagementHelper::getDBConnection();
		parent::setDbo($this->jsmdb);
		$this->jsmquery     = $this->jsmdb->getQuery(true);
		$this->jsmsubquery1 = $this->jsmdb->getQuery(true);
		$this->jsmsubquery2 = $this->jsmdb->getQuery(true);
		$this->jsmsubquery3 = $this->jsmdb->getQuery(true);

		// Reference global application object
		$this->jsmapp = Factory::getApplication();

		// JInput object
		$this->jsmjinput      = $this->jsmapp->input;
		$this->jsmoption      = $this->jsmjinput->getCmd('option');
		$this->jsmdocument    = Factory::getDocument();
		$this->jsmuser        = Factory::getUser();
		$this->jsmpks         = $this->jsmjinput->get('cid', array(), 'array');
		$this->jsmpost        = $this->jsmjinput->post->getArray(array());
		$this->jsmmessage     = '';
		$this->jsmmessagetype = 'notice';

		/**
		 * abfrage nach backend und frontend
		 */
		/*
	   if ( $this->jsmapp->isClient('administrator') )
	   {

	   }
	   if( $this->jsmapp->isClient('site') )
	   {

	   }
		*/

	}

}


