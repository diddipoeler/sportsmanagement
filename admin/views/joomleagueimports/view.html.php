<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage joomleagueimports
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementViewjoomleagueimports
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewjoomleagueimports extends sportsmanagementView
{

	/**
	 * sportsmanagementViewjoomleagueimports::init()
	 *
	 * @return void
	 */
	public function init()
	{
		$this->cfg_jl_import        = ComponentHelper::getParams($this->option)->get('cfg_jl_import', 1);
		$this->jl_table_import_step = $this->jinput->get('jl_table_import_step', 0);

		if (!$this->jl_table_import_step)
		{
			$this->model->check_database();
		}

		if ($this->cfg_jl_import)
		{
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_JL_IMPORT_ALLOWED_YES'), Log::NOTICE, 'jsmerror');
		}
		else
		{
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_JL_IMPORT_ALLOWED_NO'), Log::ERROR, 'jsmerror');
		}

		$this->model->check_database();

		/** Build the html select list for sportstypes */
		$sportstypes[]  = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$mdlSportsTypes = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes    = array_merge($sportstypes, $allSportstypes);

		$variable = $this->jinput->get('filter_sports_type', 0);

		$lists['sportstype']  = $sportstypes;
		$lists['sportstypes'] = HTMLHelper::_(
			'select.genericList',
			$sportstypes,
			'filter_sports_type',
			'class="inputbox" onChange="" style="width:120px"',
			'id',
			'name',
			$variable
		);
		unset($sportstypes);

		$this->lists   = $lists;
		$this->success = $this->app->getUserStateFromRequest($this->option . ".jl_table_import_success", 0);

		switch ($this->getLayout())
		{
			case 'infofield';
			case 'infofield_3';
			case 'infofield_4';
				$myoptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP'));
				$mdlagegroup = BaseDatabaseModel::getInstance('agegroups', 'sportsmanagementModel');

				if ($res = $mdlagegroup->getAgeGroups())
				{
					$myoptions = array_merge($myoptions, $res);
					$this->assignRef('search_agegroup', $res);
				}

				$lists['agegroup'] = $myoptions;

				$this->get_info_fields = $this->model->get_info_fields();

				$this->lists = $lists;
				$this->setLayout('infofield');
				break;
		}

	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since 1.7
	 */
	protected function addToolbar()
	{
		$this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT');
		$this->icon  = 'joomleague-import';

		switch ($this->getLayout())
		{
			case 'default';
			case 'default_3';
			case 'default_4';
			ToolbarHelper::custom('joomleagueimports.importjoomleaguenew', 'edit', 'edit', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_START_BUTTON'), false);
			break;
			case 'infofield';
			case 'infofield_3';
			case 'infofield_4';
			ToolbarHelper::custom('joomleagueimports.joomleaguesetagegroup', 'edit', 'edit', Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_SETAGEGROUP_START_BUTTON'), false);
			break;
		}

		ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=projects');
		ToolbarHelper::divider();
		parent::addToolbar();
	}

}
