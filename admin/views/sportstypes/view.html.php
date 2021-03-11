<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sportstypes
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewSportsTypes
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewSportsTypes extends sportsmanagementView
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
	 * sportsmanagementViewSportsTypes::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$myoptions   = array();
		$myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSART_TEAM'));
		$myoptions[] = HTMLHelper::_('select.option', '1', Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSART_SINGLE'));

		$this->table = Table::getInstance('sportstype', 'sportsmanagementTable');

		// Sportart filter
		$lists['sportart'] = $myoptions;
		$this->lists       = $lists;
        
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
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_TITLE');
		ToolbarHelper::addNew('sportstype.add');
		ToolbarHelper::editList('sportstype.edit');
		ToolbarHelper::custom('sportstype.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('sportstype.export', Text::_('JTOOLBAR_EXPORT'));
		parent::addToolbar();
	}
}
