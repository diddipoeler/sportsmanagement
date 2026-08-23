<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage team
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewTeam extends sportsmanagementView
{
    public function init()
    {
        $lists = [];
        $trainingData = [];
        $view = $this->app->getInput()->getCmd('view', 'team');

        $this->change_training_date = $this->app->getUserState("$this->option.change_training_date", '0');

        if (empty($this->item->id)) {
            $clubId = $this->app->getUserState("$this->option.club_id", '0');
            $this->form->setValue('club_id', null, $clubId);
            $this->item->club_id = $clubId;
        }

        $this->extended = sportsmanagementHelper::getExtended($this->item->extended, 'team');
        $this->extendeduser = sportsmanagementHelper::getExtendedUser($this->item->extendeduser, 'team');
        $this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('backend', 0, $view);

        if ($this->checkextrafields && $this->item->id) {
            $lists['ext_fields'] = sportsmanagementHelper::getUserExtraFields(
                $this->item->id,
                'backend',
                0,
                $view
            );
        }

        if ($this->item->id) {
            $trainingData = $this->model->getTrainigData($this->item->id);
        }

        if ($trainingData) {
            $daysOfWeek = [
                0 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
                1 => Text::_('MONDAY'),
                2 => Text::_('TUESDAY'),
                3 => Text::_('WEDNESDAY'),
                4 => Text::_('THURSDAY'),
                5 => Text::_('FRIDAY'),
                6 => Text::_('SATURDAY'),
                7 => Text::_('SUNDAY'),
            ];
            $dayOptions = [];

            foreach ($daysOfWeek as $key => $value) {
                $dayOptions[] = HTMLHelper::_('select.option', $key, $value);
            }

            foreach ($trainingData as $training) {
                $lists['dayOfWeek'][$training->id] = HTMLHelper::_(
                    'select.genericlist',
                    $dayOptions,
                    'dayofweek[' . $training->id . ']',
                    'class="inputbox"',
                    'value',
                    'text',
                    $training->dayofweek
                );
            }
        }

        $this->trainingData = $trainingData;
        $this->lists = $lists;
    }

    protected function addToolBar()
    {
        $this->jinput->set('hidemainmenu', true);
        $this->title = $this->item->id
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_EDIT')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_ADD_NEW');
        $this->icon = 'team';
        parent::addToolbar();
    }
}
