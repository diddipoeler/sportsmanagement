<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubs
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewClubs
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewClubs extends sportsmanagementView
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
	 * sportsmanagementViewClubs::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$inputappend         = '';
		$this->search_nation = '';
		$this->association   = '';
		$this->table         = Table::getInstance('club', 'sportsmanagementTable');

		/** build the html select list for seasons */
		$seasons[]        = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON_FILTER'), 'id', 'name');
		$mdlSeasons       = BaseDatabaseModel::getInstance('Seasons', 'sportsmanagementModel');
		$allSeasons       = $mdlSeasons->getSeasons();
		$seasons          = array_merge($seasons, $allSeasons);
		$this->season     = $allSeasons;
		$lists['seasons'] = HTMLHelper::_(
			'select.genericList',
			$seasons,
			'filter_season',
			'class="inputbox" onChange="this.form.submit();" style="width:120px"',
			'id',
			'name',
			$this->state->get('filter.season')
		);

		unset($seasons);

		/** build the html options for nation */
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

		if ($this->state->get('filter.search_nation'))
		{
			$mdlassociations   = BaseDatabaseModel::getInstance('jlextassociations', 'sportsmanagementModel');
			$this->association = $mdlassociations->getAssociations();
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
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_TITLE');
		ToolbarHelper::apply('clubs.saveshort');
		ToolbarHelper::divider();
		ToolbarHelper::addNew('club.add');
		ToolbarHelper::editList('club.edit');
		ToolbarHelper::custom('club.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('club.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();
	}
}
