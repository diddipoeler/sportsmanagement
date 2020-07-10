<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leagues
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewLeagues
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewLeagues extends sportsmanagementView
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
	 * sportsmanagementViewLeagues::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$inputappend = '';
		$startmemory = memory_get_usage();
		$this->table = Table::getInstance('league', 'sportsmanagementTable');
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
			$inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_nation')
		);

		unset($nation);
		$nation[]       = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
		$mdlassociation = BaseDatabaseModel::getInstance('jlextassociations', 'sportsmanagementModel');

		if ($res = $mdlassociation->getAssociations($this->state->get('filter.federation')))
		{
			$nation                   = array_merge($nation, $res);
			$this->search_association = $res;
		}

		$lists['association'] = array();

		foreach ($res as $row)
		{
			if (array_key_exists($row->country, $lists['association']))
			{
				$lists['association'][$row->country][] = $row;
			}
			else
			{
				$lists['association'][$row->country][] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_ASSOCIATION'));
				$lists['association'][$row->country][] = $row;
			}
		}

		$lists['association2'] = JHtmlSelect::genericlist(
			$nation,
			'filter_search_association',
			$inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_association')
		);

		unset($myoptions);

		$myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
		$mdlagegroup = BaseDatabaseModel::getInstance('agegroups', 'sportsmanagementModel');

		if ($res = $mdlagegroup->getAgeGroups())
		{
			$myoptions             = array_merge($myoptions, $res);
			$this->search_agegroup = $res;
		}

		$lists['agegroup']  = $myoptions;
		$lists['agegroup2'] = JHtmlSelect::genericlist(
			$myoptions,
			'filter_search_agegroup',
			'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.search_agegroup')
		);
		unset($myoptions);

		$mdlassociation = BaseDatabaseModel::getInstance('jlextassociations', 'sportsmanagementModel');

		if ($res = $mdlassociation->getAssociations())
		{
			$nation           = array_merge($nation, $res);
			$this->federation = $res;
		}

		$this->lists = $lists;
        $this->filterForm    = $this->model->getFilterForm();
		$this->activeFilters = $this->model->getActiveFilters();
	
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->filterForm ,true).'</pre>' ), Log::NOTICE, 'jsmerror');		
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->activeFilters ,true).'</pre>' ), Log::NOTICE, 'jsmerror');

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_TITLE');
		ToolbarHelper::apply('leagues.saveshort');
		ToolbarHelper::addNew('league.add');
		ToolbarHelper::editList('league.edit');
		ToolbarHelper::custom('league.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('league.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();
	}
}
