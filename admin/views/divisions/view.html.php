<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage divisions
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewDivisions
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewDivisions extends sportsmanagementView
{

	/**
	 * sportsmanagementViewDivisions::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$lists = array();
		$this->project_id = $this->app->getUserState("$this->option.pid", '0');
		$mdlProject = BaseDatabaseModel::getInstance("Project", "sportsmanagementModel");
		$this->projectws = $mdlProject->getProject($this->project_id);
		$this->table = Table::getInstance('division', 'sportsmanagementTable');
		$this->lists = $lists;
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE');

		if ($this->user->username == 'admin')
		{
			ToolbarHelper::publish('divisions.divisiontoproject', 'Division to Projekt', true);
		}

		ToolbarHelper::publish('divisions.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('divisions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::checkin('divisions.checkin');
		ToolbarHelper::apply('divisions.saveshort');
		ToolbarHelper::divider();
		ToolbarHelper::addNew('division.add');
		ToolbarHelper::editList('division.edit');

		parent::addToolbar();
	}
}
