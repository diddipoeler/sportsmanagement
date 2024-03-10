<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectteam
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewProjectteam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewProjectteam extends sportsmanagementView
{

	/**
	 * sportsmanagementViewProjectteam::init()
	 *
	 * @return
	 */
	public function init()
	{
		$this->change_training_date = $this->app->getUserState("$this->option.change_training_date", '0');
		$this->season_id            = $this->app->getUserState("$this->option.season_id", '0');;

		$lists = array();

		$this->item->name = '';

		$project_id    = $this->item->project_id;
		$mdlProject    = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$this->project       = $mdlProject->getProject($project_id);
		//$this->project = $project;
		$team_id       = $this->item->team_id;

		$season_team = Table::getInstance('seasonteam', 'sportsmanagementTable');
		$season_team->load($team_id);
        
      //Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' season_team <pre>'.print_r($season_team->team_id,true).'</pre>'  ), '');
      //Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' season_team <pre>'.print_r($season_team->logo_big,true).'</pre>'  ), '');
      
      if ( $season_team->logo_big )
      {
        $this->item->logo_big = $season_team->logo_big;
        $this->form->setValue('logo_big', null, $season_team->logo_big);
      }

		$mdlTeam      = BaseDatabaseModel::getInstance('Team', 'sportsmanagementModel');
		$this->project_team = $mdlTeam->getTeam($season_team->team_id, 0);
		$this->trainingData = $mdlTeam->getTrainigData(0, $this->item->id);

		if (!$this->project_team->standard_playground)
		{
			$this->project_team->standard_playground = $this->model->getProjectTeamPlayground($team_id);
		}

		if (!$this->item->standard_playground)
		{
			$this->form->setValue('standard_playground', null, $this->project_team->standard_playground);
		}

		$daysOfWeek = array(0 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
		                    1 => Text::_('MONDAY'),
		                    2 => Text::_('TUESDAY'),
		                    3 => Text::_('WEDNESDAY'),
		                    4 => Text::_('THURSDAY'),
		                    5 => Text::_('FRIDAY'),
		                    6 => Text::_('SATURDAY'),
		                    7 => Text::_('SUNDAY'));

		$dwOptions = array();

		foreach ($daysOfWeek AS $key => $value)
		{
			$dwOptions[] = HTMLHelper::_('select.option', $key, $value);
		}

		if ($this->trainingData)
		{
			foreach ($this->trainingData AS $td)
			{
				$lists['dayOfWeek'][$td->id] = HTMLHelper::_('select.genericlist', $dwOptions, 'dayofweek[' . $td->id . ']', 'class="inputbox"', 'value', 'text', $td->dayofweek);
			}
		}

		$extended           = sportsmanagementHelper::getExtended($this->item->extended, 'projectteam');
		$this->extended     = $extended;
		//$this->project      = $project;
		$this->lists        = $lists;
		//$this->project_team = $project_team;
		//$this->trainingData = $trainingdata;
        
        //Factory::getApplication()->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' item <pre>'.print_r($this->item,true).'</pre>'  ), '');

	}


	/**
	 * sportsmanagementViewProjectteam::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$this->jinput->set('hidemainmenu', true);
		$this->jinput->set('pid', $this->item->project_id);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_EDIT') . ' ' . $this->project_team->name : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_NEW') . ' ' . $this->project_team->name;
		$this->icon = 'projectteam';
		parent::addToolbar();
	}

}
