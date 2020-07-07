<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playgrounds
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewPlaygrounds
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewPlaygrounds extends sportsmanagementView
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
	 * sportsmanagementViewPlaygrounds::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->table = Table::getInstance('playground', 'sportsmanagementTable');

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

		$this->lists = $lists;
		$this->filterForm    = $this->model->getFilterForm();
		$this->activeFilters = $this->model->getActiveFilters();
	
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->filterForm ,true)'</pre>' ), Log::NOTICE, 'jsmerror');		
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->activeFilters ,true)'</pre>' ), Log::NOTICE, 'jsmerror');		

	}


	/**
	 * sportsmanagementViewPlaygrounds::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_TITLE');
		ToolbarHelper::editList('playground.edit');
		ToolbarHelper::addNew('playground.add');
		ToolbarHelper::custom('playground.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('playground.export', Text::_('JTOOLBAR_EXPORT'));

/*
		$toolbar = Toolbar::getInstance('toolbar');
        $dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fas fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();
        $childBar->publish('users.activate', 'COM_USERS_TOOLBAR_ACTIVATE', true);
			$childBar->unpublish('users.block', 'COM_USERS_TOOLBAR_BLOCK', true);
*/
		parent::addToolbar();
	}
}
