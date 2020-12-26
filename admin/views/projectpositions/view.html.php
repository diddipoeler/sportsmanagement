<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectpositions
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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewprojectpositions
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewprojectpositions extends sportsmanagementView
{

	/**
	 * sportsmanagementViewprojectpositions::init()
	 *
	 * @return
	 */
	public function init()
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		$model     = $this->getModel();
		$starttime = microtime();
		$tpl       = '';
        
        switch ($this->getLayout())
		{
			case 'editlist':
			case 'editlist_3':
			case 'editlist_4':
			$this->_displayEditlist($tpl);
			return;
		}

		$this->state         = $this->get('State');
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn    = $this->state->get('list.ordering');

		$items      = $this->get('Items');
		$total      = $this->get('Total');
		$pagination = $this->get('Pagination');

		$table       = Table::getInstance('projectposition', 'sportsmanagementTable');
		$this->table = $table;

		$this->project_id = $this->jinput->get('pid');
		$this->jinput->set('pid', $this->project_id);

		$this->model->updateprojectpositions($items, $this->project_id);

		$mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
		$this->project    = $mdlProject->getProject($this->project_id);

		$this->config       = Factory::getConfig();
		$this->positiontool = $items;
		$this->pagination   = $pagination;
	}

	/**
	 * sportsmanagementViewprojectpositions::_displayEditlist()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return void
	 */
	function _displayEditlist($tpl)
	{
		$app    = Factory::getApplication();
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		if (version_compare(JSM_JVERSION, '4', 'eq'))
		{
			$uri = Uri::getInstance();
		}
		else
		{
			$uri = Factory::getURI();
		}

		$model     = $this->getModel();
		$starttime = microtime();

		$items = $this->get('Items');

		/** Build the html select list for project assigned positions */
		$ress                  = array();
		$res1                  = array();
		$notusedpositions      = array();
		$project_positionslist = array();
		$lists                 = array();

		if ($items)
		{
			foreach ($items as $item)
			{
				$project_positionslist[] = HTMLHelper::_('select.option', $item->id, Text::_($item->name));
			}

			$lists['project_positions'] = HTMLHelper::_('select.genericlist', $project_positionslist, 'project_positionslist[]', 'style="width:250px; height:250px;" class="inputbox" multiple="true" size="' . max(15, count($items)) . '"', 'value', 'text');
		}
		else
		{
			$lists['project_positions'] = '<select name="project_positionslist[]" id="project_positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
		}

		$this->project_id = $this->jinput->get('pid');

		$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$project    = $mdlProject->getProject($this->project_id);

		if ($ress1 = $model->getSubPositions($project->sports_type_id))
		{
			if ($ress)
			{
				foreach ($ress1 as $res1)
				{
					if (!in_array($res1, $ress))
					{
						$res1->text         = Text::_($res1->text);
						$notusedpositions[] = $res1;
					}
				}
			}
			else
			{
				foreach ($ress1 as $res1)
				{
					$res1->text         = Text::_($res1->text);
					$notusedpositions[] = $res1;
				}
			}
		}
		else
		{
			Log::add('<br />' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_ASSIGN_POSITIONS_FIRST') . '<br /><br />', Log::WARNING, 'jsmerror');
		}

		// Build the html select list for positions
		if (count($notusedpositions) > 0)
		{
			$lists['positions'] = HTMLHelper::_('select.genericlist', $notusedpositions, 'positionslist[]', ' style="width:250px; height:250px;" class="inputbox" multiple="true" size="' . min(15, count($notusedpositions)) . '"', 'value', 'text');
		}
		else
		{
			$lists['positions'] = '<select name="positionslist[]" id="positionslist" style="width:250px; height:250px;" class="inputbox" multiple="true" size="10"></select>';
		}

		$this->document->addScript(Uri::base() . 'components/com_sportsmanagement/assets/js/sm_functions.js');
		$this->request_url = $uri->toString();
		$this->user        = Factory::getUser();
		$this->project     = $project;
		$this->lists       = $lists;
		$this->addToolbar_Editlist();
        
		$this->setLayout('editlist');

		unset($ress);
		unset($res1);
		unset($notusedpositions);
		unset($project_positionslist);
		unset($lists);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar_Editlist()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_EDIT_TITLE');
		ToolbarHelper::save('projectposition.save_positionslist');
		ToolbarHelper::cancel('projectposition.cancel', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_CLOSE'));
		parent::addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_TITLE');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=project&layout=panel&id='.$this->project_id);
		sportsmanagementHelper::ToolbarButton('editlist', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_P_POSITION_BUTTON_UN_ASSIGN'));
		parent::addToolbar();
	}

}

