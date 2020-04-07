<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage templates
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewTemplates
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTemplates extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTemplates::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$starttime = microtime();

		$this->state = $this->get('State');
		$this->sortDirection = $this->state->get('list.direction');
		$this->sortColumn = $this->state->get('list.ordering');

		// $this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$project = $mdlProject->getProject($this->project_id);
		$lists = array();

		// $allTemplates = $model->checklist($this->project_id);

		// Das sind die eigenen templates
		$templates = $this->get('Items');

		$total = $this->get('Total');

		if ($project->master_template)
		{
			// Das sind die templates aus einenm anderen projekt
			$this->model->set('_getALL', 1);
			$allMasterTemplates = $this->model->getMasterTemplatesList();
			$this->model->set('_getALL', 0);
			$masterTemplates = $this->model->getMasterTemplatesList();

			// Build in Text of template title here
			foreach ($masterTemplates as $temptext)
			{
				$temptext->text = Text::_($temptext->text);
			}

			$importlist = array();
			$importlist[] = HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_SELECT_FROM_MASTER'));
			$importlist = array_merge($importlist, $masterTemplates);
			$lists['mastertemplates'] = HTMLHelper::_('select.genericlist', $importlist, 'templateid', 'class="inputbox" onChange="Joomla.submitform(\'template.masterimport\', this.form);" ');
			$master = $this->model->getMasterName();
			$this->master = $master;
			$templates = array_merge($templates, $allMasterTemplates);

			$total = count($templates);
		}

		$pagination = $this->get('Pagination');

		// $this->user = Factory::getUser();
		$this->lists = $lists; // otherwise no indication of the list in default_data.php on line 64!
		$this->templates = $templates;
		$this->projectws = $project;
		$this->pagination = $pagination;

		// $this->request_url = $uri->toString();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TITLE');

			ToolbarHelper::editList('template.edit');

		if ($this->projectws->master_template)
		{
			ToolbarHelper::deleteList('', 'template.remove', 'JTOOLBAR_DELETE');
		}
		else
		{
			ToolbarHelper::custom('template.reset', 'restore', 'restore', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET'));
			ToolbarHelper::custom('template.update', 'update', 'update', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_UPDATE'));
		}

		ToolbarHelper::checkin('templates.checkin');
		parent::addToolbar();
	}

}

