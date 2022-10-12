<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewMatches
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewMatches extends sportsmanagementView
{

	/**
	 * sportsmanagementViewMatches::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');
		$model  = $this->getModel();
		$params = ComponentHelper::getParams($option);
//		$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/jquery.datetimepicker.css');
//		$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/jquery.datetimepicker.js');
//		$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/datetimepicker.css');
//		$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/datetimepicker.js');
		
		$view = $jinput->get('view');
		$_db  = sportsmanagementHelper::getDBConnection(); // The method is contextual so we must have a DBO

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$table_info = $_db->getTableColumns('#__sportsmanagement_match', true);
		}
		else
		{
			$fieldsArray = $_db->getTableFields('#__sportsmanagement_match', true);
			$table_info  = array_shift($fieldsArray);
		}

		$this->projectteamsel = Factory::getApplication()->input->getvar('projectteam', 0);

		$this->table       = Table::getInstance('match', 'sportsmanagementTable');
		$this->project_id     = $app->getUserState("$option.pid", '0');
		$this->project_art_id = $app->getUserState("$option.project_art_id", '0');

		$this->project_id = $jinput->get('pid', 0);

		if (!$this->project_id)
		{
			$this->project_id = $app->getUserState("$option.pid", '0');
		}

		$this->rid = $jinput->get('rid', 0);

		if (!$this->rid)
		{
			$this->rid = $app->getUserState("$option.rid", '0');
		}

		$mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$this->projectws  = $mdlProject->getProject($this->project_id);

		$mdlRound = BaseDatabaseModel::getInstance('Round', 'sportsmanagementModel');
		$roundws  = $mdlRound->getRound($this->rid);

		/** Build the html selectlist for rounds */
		$ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true);

		/** dadurch werden die spaltenbreiten optimiert */
		$this->document->addStyleSheet(Uri::root() . 'administrator/components/com_sportsmanagement/assets/css/form_control.css');

		foreach ($ress as $res)
		{
			$datum                = sportsmanagementHelper::convertDate($res->round_date_first, 1) . ' - ' . sportsmanagementHelper::convertDate($res->round_date_last, 1);
			$project_roundslist[] = HTMLHelper::_('select.option', $res->id, sprintf("%s (%s)", $res->name, $datum));
		}

		$lists['project_rounds'] = HTMLHelper::_(
			'select.genericList', $project_roundslist, 'rid', 'class="inputbox" ' .
			'onChange="document.getElementById(\'short_act\').value=\'rounds\';' .
			'document.roundForm.submit();" ', 'value', 'text', $roundws->id
		);

		$lists['project_rounds2'] = HTMLHelper::_('select.genericList', $project_roundslist, 'rid', 'class="inputbox" ', 'value', 'text', $roundws->id);

		/** Diddipoeler rounds for change in match */
		$project_change_roundslist = array();

		if ($ress = sportsmanagementHelper::getRoundsOptions($this->project_id, 'ASC', true))
		{
			$project_change_roundslist = array_merge($project_change_roundslist, $ress);
		}

		$lists['project_change_rounds'] = $project_change_roundslist;
		unset($project_change_roundslist);

		/** Build the html options for teams */
		foreach ($this->items as $row)
		{
			if ($row->divhomeid == '')
			{
				$row->divhomeid = 0;
			}

			if ($row->divawayid == '')
			{
				$row->divawayid = 0;
			}

			if ($this->project_art_id == 3)
			{
				$teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PERSON'));
			}
			else
			{
				$teams[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
			}

			$divhomeid = 0;

			// Apply the filter only if both teams are from the same division
			// teams are not from the same division in tournament mode with divisions
			if ($row->divhomeid == $row->divawayid)
			{
				$divhomeid = $row->divhomeid;
			}
			else
			{
				$row->divhomeid = 0;
				$row->divawayid = 0;
			}

			if ($projectteams = $mdlProject->getProjectTeamsOptions($this->project_id, $divhomeid))
			{
				$teams = array_merge($teams, $projectteams);
			}
            else
            {
                $teams = array();
            }

			$lists['teams_' . $divhomeid] = $teams;
			$lists['projectteams']        = $teams;
			unset($teams);

			/** Sind die verzeichnisse vorhanden ? */
			$dest = JPATH_ROOT . '/images/com_sportsmanagement/database/matchreport/' . $row->id;

			if (Folder::exists($dest))
			{
			}
			else
			{
				$result = Folder::create($dest);
			}
		}

		/** Build the html options for extratime */
		$match_result_type[]        = JHtmlSelect::option('0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_RT'));
		$match_result_type[]        = JHtmlSelect::option('1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_OT'));
		$match_result_type[]        = JHtmlSelect::option('2', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_SO'));
		$lists['match_result_type'] = $match_result_type;
		unset($match_result_type);

		/** Build the html options for article */
		$articles[] = JHtmlSelect::option('0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ARTICLE'));

		if ($res = sportsmanagementHelper::getArticleList($this->projectws->category_id))
		{
			$articles = array_merge($articles, $res);
		}

		$lists['articles'] = $articles;
		unset($articles);

		/** Build the html options for divisions */
		$divisions[]  = JHtmlSelect::option('0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
		$mdlDivisions = BaseDatabaseModel::getInstance('divisions', 'sportsmanagementModel');

		if ($res = $mdlDivisions->getDivisions($this->project_id))
		{
			$divisions = array_merge($divisions, $res);
		}

		$lists['divisions'] = $divisions;
		unset($divisions);

		$playground[]  = JHtmlSelect::option('0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYGROUND'));
		$mdlPlayground      = BaseDatabaseModel::getInstance("Playgrounds", "sportsmanagementModel");
		$res = $mdlPlayground->getPlaygrounds(true);
		$this->playgrounds = array_merge($playground, $res);
		
		$this->document->addScript(Uri::base() . 'components/' . $option . '/assets/js/matches.js');

		$selectlist = array();

		if (isset($table_info['#__sportsmanagement_match']))
		{
			foreach ($table_info['#__sportsmanagement_match'] as $field => $value)
			{
				$select_Options = sportsmanagementHelper::getExtraSelectOptions($view, $field);

				if ($select_Options)
				{
					$select[]           = JHtmlSelect::option('0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'));
					$select             = array_merge($select, $select_Options);
					$selectlist[$field] = $select;
					unset($select);
				}
			}
		}

		$this->user       = Factory::getUser();
		$this->lists      = $lists;
		$this->selectlist = $selectlist;
		$this->option     = $option;
		$this->matches    = $this->model->prepareItems($this->items);
		$this->ress       = $ress;
//		$this->projectws  = $projectws;
		$this->roundws    = $roundws;
		$this->prefill    = $params->get('use_prefilled_match_roster', 0);


		if (!array_key_exists('search_mode', $this->lists))
		{
			$this->lists['search_mode'] = '';
		}

		switch ($this->getLayout())
		{
			case 'massadd':
			case 'massadd_3':
			case 'massadd_4':
				/** Build the html options for massadd create type */
				$createTypes = array(0 => Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD'),
				                     1 => Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_1'),
				                     2 => Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_2')
				);
				$ctOptions   = array();

				foreach ($createTypes AS $key => $value)
				{
					$ctOptions[] = JHtmlSelect::option($key, $value);
				}

				$lists['createTypes'] = JHtmlSelect::genericlist($ctOptions, 'ct[]', 'class="inputbox" onchange="javascript:displayTypeView();"', 'value', 'text', 1, 'ct');
				unset($createTypes);

				/** Build the html radio for adding into one round / all rounds */
				$createYesNo = array(0 => Text::_('JNO'), 1 => Text::_('JYES'));
				$ynOptions   = array();

				foreach ($createYesNo AS $key => $value)
				{
					$ynOptions[] = JHtmlSelect::option($key, $value);
				}

				$lists['addToRound'] = JHtmlSelect::radiolist($ynOptions, 'addToRound', 'class="inputbox"', 'value', 'text', 0);

				/** Build the html radio for auto publish new matches */
				$lists['autoPublish'] = JHtmlSelect::radiolist($ynOptions, 'autoPublish', 'class="inputbox"', 'value', 'text', 0);
				$this->lists          = $lists;
				$this->setLayout('massadd');
				break;
		}
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{

		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Store the variable that we would like to keep for next time
		// function syntax is setUserState( $key, $value );
		$app->setUserState("$option.rid", $this->rid);
		$app->setUserState("$option.pid", $this->project_id);

		$massadd = $jinput->getInt('massadd', 0);

		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_TITLE');

		if (!$massadd)
		{
			ToolbarHelper::publish('match.insertgooglecalendar', 'JLIB_HTML_CALENDAR', true);
			ToolbarHelper::divider();
			ToolbarHelper::publish('matches.count_result_yes', 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL', true);
			ToolbarHelper::unpublish('matches.count_result_no', 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_F_AD_INCL', true);
			ToolbarHelper::divider();
			ToolbarHelper::publish('matches.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('matches.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::divider();

			ToolbarHelper::apply('matches.saveshort');
			ToolbarHelper::divider();

			ToolbarHelper::custom('match.massadd', 'new.png', 'new_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_MATCHES'), false);
			ToolbarHelper::addNew('match.addmatch', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_ADD_MATCH'));
			ToolbarHelper::divider();

			ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=rounds');
		}
		else
		{
			ToolbarHelper::custom('match.cancelmassadd', 'cancel.png', 'cancel_f2.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_MASSADD_CANCEL_MATCHADD'), false);
		}

		parent::addToolbar();
	}

}

