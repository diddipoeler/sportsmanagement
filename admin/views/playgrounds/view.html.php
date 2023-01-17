<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playgrounds
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Form\Form;

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

		if (!array_key_exists('search_mode', $this->lists))
		{
			$this->lists['search_mode'] = '';
		}
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
		parent::addToolbar();
	}
}
