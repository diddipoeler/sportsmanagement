<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectreferees
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Log\Log;

/**
 * HTML View class for the Sportsmanagement Component
 *
 * @static
 * @package Sportsmanagement
 * @since   0.1
 */
class sportsmanagementViewprojectreferees extends sportsmanagementView
{

	/**
	 * sportsmanagementViewprojectreferees::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->table       = Table::getInstance('projectreferee', 'sportsmanagementTable');

		$this->_persontype = $this->jinput->get('persontype');
		$mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$this->project    = $mdlProject->getProject($this->project_id);

		if (empty($this->_persontype))
		{
			$this->_persontype = $this->app->getUserState("$this->option.persontype", '0');
		}

		/** build the html options for position */
		$position_id[]         = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_REFEREE_FUNCTION'));
		$mdlPositions          = BaseDatabaseModel::getInstance('Positions', 'sportsmanagementModel');
		$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);

		if ($project_ref_positions)
		{
			$position_id               = array_merge($position_id, $project_ref_positions);
			$this->project_position_id = $project_ref_positions;
		}

		$lists['project_position_id'] = $position_id;
		unset($position_id);

		$this->lists      = $lists;
		
		if ( !$this->items )
		{
		$countreferess = $this->model->getProjectRefereesCount($this->project_id);
			if ( $countreferess )
			{
		Log::add(Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE2', '<i>' . $countreferess . '</i>'), Log::NOTICE, 'jsmerror');
$this->season_id = $this->app->getUserState("$this->option.season_id", '0');				
$this->app->setUserState("$this->option.season_id", 0);
$this->items = $this->get('Items2');				
$this->app->setUserState("$this->option.season_id", $this->season_id);				
			}
		}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->app->setUserState("$this->option.persontype", $this->_persontype);
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=project&layout=panel&id='.$this->project_id);
		ToolbarHelper::apply('projectreferees.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_APPLY'));
		sportsmanagementHelper::ToolbarButton('assignpersons', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_ASSIGN'), 'players', 3);
		parent::addToolbar();
	}

}

