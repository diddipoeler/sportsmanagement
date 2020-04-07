<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage team
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * sportsmanagementViewTeam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeam extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeam::init()
	 *
	 * @return
	 */
	public function init()
	{

			  $lists = array();

		$this->change_training_date    = $this->app->getUserState("$this->option.change_training_date", '0');

		if (empty($this->item->id))
		{
			$this->form->setValue('club_id', null, $this->app->getUserState("$this->option.club_id", '0'));
			$this->item->club_id = $this->app->getUserState("$this->option.club_id", '0');
		}

			$extended = sportsmanagementHelper::getExtended($this->item->extended, 'team');
			$this->extended = $extended;
			$extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'team');
			$this->extendeduser = $extendeduser;

			  $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields();

		if ($this->checkextrafields)
		{
			if ($this->item->id)
			{
				$lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields($this->item->id);
			}
		}

			 /**
 * build the html select list for days of week
 */
		if ($trainingData = $this->model->getTrainigData($this->item->id))
		{
			$daysOfWeek = array( 0 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
			1 => Text::_('MONDAY'),
			2 => Text::_('TUESDAY'),
			3 => Text::_('WEDNESDAY'),
			4 => Text::_('THURSDAY'),
			5 => Text::_('FRIDAY'),
			6 => Text::_('SATURDAY'),
			7 => Text::_('SUNDAY') );
			$dwOptions = array();

			foreach ($daysOfWeek AS $key => $value)
			{
					   $dwOptions[] = HTMLHelper::_('select.option', $key, $value);
			}

			foreach ($trainingData AS $td)
			{
					 $lists['dayOfWeek'][$td->id] = HTMLHelper::_('select.genericlist', $dwOptions, 'dayofweek[' . $td->id . ']', 'class="inputbox"', 'value', 'text', $td->dayofweek);
			}

			unset($daysOfWeek);
			unset($dwOptions);
		}

			$this->trainingData = $trainingData;
			$this->lists = $lists;

	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		$this->jinput->set('hidemainmenu', true);
		$isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_ADD_NEW');
		$this->icon = 'team';
		parent::addToolbar();
	}


}
