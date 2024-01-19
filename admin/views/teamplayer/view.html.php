<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplayer
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewteamplayer
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeamPlayer extends sportsmanagementView
{

	/**
	 * sportsmanagementViewteamplayer::init()
	 *
	 * @return
	 */
	public function init()
	{
		$lists = array();
$this->persontype = $this->app->getUserState("$this->option.persontype", '0');
$this->project_id = $this->app->getUserState("$this->option.pid", '0');	
$this->team_id = $this->app->getUserState("$this->option.team_id", '0');
$this->project_team_id = $this->app->getUserState("$this->option.project_team_id", '0');
$this->season_id  = $this->app->getUserState("$this->option.season_id", '0');	
		
		$this->project_art_id  = $this->app->getUserState("$this->option.project_art_id", '0');
		$this->sports_type_id  = $this->app->getUserState("$this->option.sports_type_id", '0');
		$this->season_team_id  = $this->app->getUserState("$this->option.season_team_id", '0');
		
        //$this->_persontype     = $this->jinput->get('persontype');
		

		
		

		$mdlProject    = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$this->project       = $mdlProject->getProject($this->project_id);
		//$this->project = $project;

		if (isset($this->item->projectteam_id))
		{
			$project_team       = $mdlProject->getProjectTeam($this->item->projectteam_id);
			$this->project_team = $project_team;
		}

		$mdlPerson      = BaseDatabaseModel::getInstance("player", "sportsmanagementModel");
		$this->project_person = $mdlPerson->getPerson($this->item->person_id);

		/** Build the html options for position */
		$position_id           = array();
		$mdlPositions          = BaseDatabaseModel::getInstance('Positions', 'sportsmanagementModel');
		$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, 1);

		if ($project_ref_positions)
		{
			$position_id = array_merge($position_id, $project_ref_positions);
		}

		$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, 2);

		if ($project_ref_positions)
		{
			$position_id = array_merge($position_id, $project_ref_positions);
		}

		/** name für titel setzen */
		$this->item->name = $this->project_person->lastname . ' - ' . $this->project_person->firstname;

		/** personendaten setzen */
$this->form->setValue('position_id', null, $this->project_person->position_id);
$this->form->setValue('projectteam_id', null, $this->project_team_id);
$this->form->setValue('injury', null, $this->project_person->injury);
$this->form->setValue('injury_date', null, $this->project_person->injury_date);
$this->form->setValue('injury_end', null, $this->project_person->injury_end);
$this->form->setValue('injury_detail', null, $this->project_person->injury_detail);
$this->form->setValue('injury_date_start', null, $this->project_person->injury_date_start == "0000-00-00" ? null : $this->project_person->injury_date_start);
$this->form->setValue('injury_date_end', null, $this->project_person->injury_date_end == "0000-00-00" ? null : $this->project_person->injury_date_end);

$this->form->setValue('suspension', null, $this->project_person->suspension);
$this->form->setValue('suspension_date', null, $this->project_person->suspension_date);
$this->form->setValue('suspension_end', null, $this->project_person->suspension_end);
$this->form->setValue('suspension_detail', null, $this->project_person->suspension_detail);
$this->form->setValue('susp_date_start', null, $this->project_person->susp_date_start == "0000-00-00" ? null : $this->project_person->susp_date_start);
$this->form->setValue('susp_date_end', null, $this->project_person->susp_date_end == "0000-00-00" ? null : $this->project_person->susp_date_end);

$this->form->setValue('away', null, $this->project_person->away);
$this->form->setValue('away_date', null, $this->project_person->away_date);
$this->form->setValue('away_end', null, $this->project_person->away_end);
$this->form->setValue('away_detail', null, $this->project_person->away_detail);
$this->form->setValue('away_date_start', null, $this->project_person->away_date_start == "0000-00-00" ? null : $this->project_person->away_date_start);
$this->form->setValue('away_date_end', null, $this->project_person->away_date_end == "0000-00-00" ? null : $this->project_person->away_date_end);
$this->form->setValue('persontype', null, $this->app->getUserState("$this->option.persontype", '0'));
        
        if ($this->item->id)
		{
			/** Alles ok */
			if ($this->item->contract_from == '0000-00-00')
			{
				$this->item->contract_from = '';
				$this->form->setValue('contract_from',null, '');
			}

			if ($this->item->contract_to == '0000-00-00')
			{
				$this->item->contract_to = '';
				$this->form->setValue('contract_to',null, '');
			}
		}
		else
		{
			$this->form->setValue('contract_from',null, '');
			$this->form->setValue('contract_to',null, '');
		}

		$project_position_id = $this->form->getValue('project_position_id');

		if (!$project_position_id)
		{
			foreach ($position_id as $items => $item)
			{
				if ($item->position_id == $this->project_person->position_id)
				{
					$results = $item->value;
				}
			}

			$this->form->setValue('project_position_id', null, $results);
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_TEAMPERSON_PROJECT_POSITION'), 'notice');
		}

		$this->extended       = sportsmanagementHelper::getExtended($this->item->extended, 'teamplayer');
		$this->lists    = $lists;

		if (ComponentHelper::getParams($this->option)->get('show_debug_info_backend'))
		{
		}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.6
	 */
	protected function addToolbar()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$jinput->set('hidemainmenu', true);

		if (isset($this->item->projectteam_id))
		{
			$app->setUserState("$option.project_team_id", $this->item->projectteam_id);
		}

		$app->setUserState("$option.pid", $this->project_id);
		$app->setUserState("$option.team_id", $this->team_id);
		$app->setUserState("$option.season_id", $this->season_id);

		$user   = Factory::getUser();
		$userId = $user->id;
		$isNew  = $this->item->id == 0;
		$canDo  = sportsmanagementHelper::getActions($this->item->id);

		if ($this->item->persontype == 1)
		{
			//ToolbarHelper::title($isNew ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_NEW') : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_EDIT', $this->item->name ), 'teamplayer');
            $this->title = $isNew ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_NEW') : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_EDIT', $this->item->name );
		}
		elseif ($this->item->persontype == 2)
		{
			//ToolbarHelper::title($isNew ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_NEW') : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_EDIT', $this->item->name ), 'teamstaff');
            $this->title = $isNew ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_NEW') : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_EDIT', $this->item->name );
		}

		parent::addToolbar();
	}

}
