<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage agegroups
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewagegroups
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewagegroups extends sportsmanagementView
{
        /**
	 * A \JForm instance with filter fields.
	 *
	 * @var    \JForm
	 * @since  3.6.3
	 */
	public $filterForm;

	/**
	 * An array with active filters.
	 *
	 * @var    array
	 * @since  3.6.3
	 */
	public $activeFilters;

	/**
	 * sportsmanagementViewagegroups::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$mdlSportsType = BaseDatabaseModel::getInstance('SportsType', 'sportsmanagementModel');
		$this->table = Table::getInstance('agegroup', 'sportsmanagementTable');

		/** Build the html select list for sportstypes */
		$sportstypes[]     = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$mdlSportsTypes    = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes    = $mdlSportsTypes->getSportsTypes();
		$sportstypes       = array_merge($sportstypes, $allSportstypes);
		$this->sports_type = $allSportstypes;

		$lists['sportstypes'] = HTMLHelper::_(
			'select.genericList',
			$sportstypes,
			'filter_sports_type',
			'class="inputbox" onChange="this.form.submit();" style="width:120px"',
			'id',
			'name',
			$this->state->get('filter.sports_type')
		);
		unset($sportstypes);

		/** Build the html options for nation */
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY'));

		if ($res = JSMCountries::getCountryOptions())
		{
			$nation              = array_merge($nation, $res);
			$this->search_nation = $res;
		}

		$lists['nation']  = $nation;
		$lists['nation2'] = JHtmlSelect::genericlist(
			$nation,
			'filter_search_nation',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);

		foreach ($this->items as $item)
		{
			$sportstype = $mdlSportsType->getSportstype($item->sportstype_id);

			if ($sportstype)
			{
				$item->sportstype = $sportstype->name;
			}
			else
			{
				$item->sportstype = null;
			}
		}

		if (count($this->items) == 0)
		{
			$databasetool    = BaseDatabaseModel::getInstance("databasetool", "sportsmanagementModel");
			$insert_agegroup = $databasetool->insertAgegroup($this->state->get('filter.search_nation'), $this->state->get('filter.sports_type'));
			$this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_NO_RESULT'), 'Error');
		}

		$this->lists = $lists;
try
{		
$this->filterForm    = $this->model->getFilterForm();
$this->activeFilters = $this->model->getActiveFilters();
}
catch (Exception $e)
{
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');	
}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_TITLE');
		ToolbarHelper::addNew('agegroup.add');
		ToolbarHelper::editList('agegroup.edit');
		ToolbarHelper::apply('agegroups.saveshort');
		ToolbarHelper::custom('agegroups.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('agegroup.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();
	}
}
