<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextcountries
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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewjlextcountries
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewjlextcountries extends sportsmanagementView
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
	 * sportsmanagementViewjlextcountries::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$inputappend = '';

		$this->table = Table::getInstance('jlextcountry', 'sportsmanagementTable');

		/** Build the html options for nation */
		$nation[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_FEDERATION'));

		if ($res = $this->get('Federation'))
		{
			$nation           = array_merge($nation, $res);
			$this->federation = $res;
		}

		$lists['federation'] = JHtmlSelect::genericlist(
			$nation,
			'filter_federation',
			$inputappend . 'class="inputbox" style="width:140px; " onchange="this.form.submit();"',
			'value',
			'text',
			$this->state->get('filter.federation')
		);

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
		ToolbarHelper::addNew('jlextcountry.add');
		ToolbarHelper::editList('jlextcountry.edit');
		ToolbarHelper::custom('jlextcountry.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::custom('jlextcountries.importplz', 'upload', 'upload', Text::_('COM_SPORTSMANAGEMENT_ADMIN_COUNTRY_IMPORT_PLZ'), true);
		ToolbarHelper::archiveList('jlextcountry.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();
	}
}
