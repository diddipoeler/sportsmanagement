<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage positions
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
 * sportsmanagementViewPositions
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewPositions extends sportsmanagementView
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
	 * sportsmanagementViewPositions::init()
	 *
	 * @return void
	 */
	public function init()
	{

		$this->table = Table::getInstance('position', 'sportsmanagementTable');

		// Build the html options for parent position
		$parent_id[] = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));

		if ($res = $this->model->getParentsPositions())
		{
			foreach ($res as $re)
			{
				$re->text = Text::_($re->text);
			}

			$parent_id = array_merge($parent_id, $res);
		}

		$lists['parent_id'] = $parent_id;
		unset($parent_id);

		// Build the html select list for sportstypes
		$sportstypes[]  = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_SPORTSTYPE_FILTER'), 'id', 'name');
		$allSportstypes = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementmodel')->getSportsTypes();
		$sportstypes    = array_merge($sportstypes, $allSportstypes);

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

		$this->lists = $lists;
		$this->filterForm    = $this->model->getFilterForm();
		$this->activeFilters = $this->model->getActiveFilters();
	
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->filterForm ,true).'</pre>' ), Log::NOTICE, 'jsmerror');		
//Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . '<pre>'.print_r($this->activeFilters ,true).'</pre>' ), Log::NOTICE, 'jsmerror');	

	}


	/**
	 * sportsmanagementViewPositions::addToolbar()
	 *
	 * @return void
	 */
	protected function addToolbar()
	{

		// Set toolbar items for the page
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TITLE');

		ToolbarHelper::publish('positions.publish', 'JTOOLBAR_PUBLISH', true);
		ToolbarHelper::unpublish('positions.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		ToolbarHelper::divider();

		ToolbarHelper::apply('positions.saveshort');
		ToolbarHelper::editList('position.edit');
		ToolbarHelper::addNew('position.add');
		ToolbarHelper::custom('position.import', 'upload', 'upload', Text::_('JTOOLBAR_UPLOAD'), false);
		ToolbarHelper::archiveList('position.export', Text::_('JTOOLBAR_EXPORT'));

		parent::addToolbar();
	}
}
