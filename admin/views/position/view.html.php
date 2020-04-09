<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage position
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewPosition
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementViewPosition extends sportsmanagementView
{

	/**
	 * sportsmanagementViewPosition::display()
	 *
	 * @param   mixed  $tpl
	 *
	 * @return
	 */
	public function init()
	{

		// Build the html options for parent position
		$parent_id    = array();
		$parent_id[]  = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'));
		$mdlPositions = BaseDatabaseModel::getInstance('Positions', 'sportsmanagementModel');

		if ($res = $mdlPositions->getParentsPositions())
		{
			foreach ($res as $re)
			{
				$re->text = Text::_($re->text);
			}

			$parent_id = array_merge($parent_id, $res);
		}

		$lists            = array();
		$lists['parents'] = HTMLHelper::_('select.genericlist', $parent_id, 'parent_id', 'class="inputbox" size="1"', 'value', 'text', $this->item->parent_id);

		unset($parent_id);

		$mdlEventtypes = BaseDatabaseModel::getInstance('Eventtypes', 'sportsmanagementModel');

		// Build the html select list for events
		$res           = array();
		$res1          = array();
		$notusedevents = array();

		// Nur wenn die position angelegt ist, hat sie auch events
		if ($this->item->id)
		{
			if ($res = $mdlEventtypes->getEventsPosition($this->item->id))
			{
				$lists['position_events'] = HTMLHelper::_(
					'select.genericlist', $res, 'position_eventslist[]',
					' style="width:250px; height:300px;" class="inputbox" multiple="true" size="' . max(10, count($res)) . '"',
					'value', 'text'
				);
			}
			else
			{
				$lists['position_events'] = '<select name="position_eventslist[]" id="position_eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
			}
		}
		else
		{
			$lists['position_events'] = '<select name="position_eventslist[]" id="position_eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		$res1 = $mdlEventtypes->getEvents($this->item->sports_type_id);

		if ($res = $mdlEventtypes->getEventsPosition($this->item->id))
		{
			if ($res1 != "")
			{
				foreach ($res1 as $miores1)
				{
					$used = 0;

					foreach ($res as $miores)
					{
						if ($miores1->text == $miores->text)
						{
							$used = 1;
						}
					}

					if ($used == 0)
					{
						$notusedevents[] = $miores1;
					}
				}
			}
		}
		else
		{
			$notusedevents = $res1;
		}

		if ($this->item->id)
		{
			// Build the html select list for events
			if (($notusedevents) && (count($notusedevents) > 0))
			{
				$lists['events'] = HTMLHelper::_(
					'select.genericlist', $notusedevents, 'eventslist[]',
					' style="width:250px; height:300px;" class="inputbox" multiple="true" size="' . max(10, count($notusedevents)) . '"',
					'value', 'text'
				);
			}
			else
			{
				$lists['events'] = '<select name="eventslist[]" id="eventslist" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
			}
		}

		else
		{
			$lists['events'] = HTMLHelper::_(
				'select.genericlist', $res1, 'eventslist[]',
				' style="width:250px; height:300px;" class="inputbox" multiple="true" size="' . max(10, count($res1)) . '"',
				'value', 'text'
			);
		}

		unset($res);
		unset($res1);
		unset($notusedevents);

		// Position statistics
		$mdlStatistics = BaseDatabaseModel::getInstance('Statistics', 'sportsmanagementModel');

		$position_stats = $mdlStatistics->getPositionStatsOptions($this->item->id);

		if (!empty($position_stats))
		{
			$lists['position_statistic'] = HTMLHelper::_(
				'select.genericlist', $position_stats, 'position_statistic[]',
				' style="width:250px; height:300px;" class="inputbox" id="position_statistic" multiple="true" size="' . max(10, count($position_stats)) . '"',
				'value', 'text'
			);
		}
		else
		{
			$lists['position_statistic'] = '<select name="position_statistic[]" id="position_statistic" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		$available_stats = $mdlStatistics->getAvailablePositionStatsOptions($this->item->id);

		if (!empty($available_stats))
		{
			$lists['statistic'] = HTMLHelper::_(
				'select.genericlist', $available_stats, 'statistic[]',
				' style="width:250px; height:300px;" class="inputbox" id="statistic" multiple="true" size="' . max(10, count($available_stats)) . '"',
				'value', 'text'
			);
		}
		else
		{
			$lists['statistic'] = '<select name="statistic[]" id="statistic" style="width:250px; height:300px;" class="inputbox" multiple="true" size="10"></select>';
		}

		$this->document->addScript(Uri::base() . 'components/com_sportsmanagement/assets/js/sm_functions.js');

		$this->lists = $lists;
		unset($lists);

	}


	/**
	 * sportsmanagementViewPosition::addToolBar()
	 *
	 * @return void
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew      = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITION_NEW');
		$this->icon = 'position';
		parent::addToolbar();
	}


}
