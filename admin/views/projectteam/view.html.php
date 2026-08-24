<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectteam
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewProjectteam
 */
class sportsmanagementViewProjectteam extends sportsmanagementView
{
    public function init()
    {
        $this->change_training_date = $this->app->getUserState("$this->option.change_training_date", '0');
        $this->season_id = $this->app->getUserState("$this->option.season_id", '0');
        $lists = [];
        $this->item->name = '';

        $factory = $this->app->bootComponent('com_sportsmanagement')->getMVCFactory();
        $projectId = (int) $this->item->project_id;
        $projectModel = $factory->createModel('Project', 'Administrator');
        $this->project = $projectModel ? $projectModel->getProject($projectId) : null;

        $seasonTeamId = (int) $this->item->team_id;
        $seasonTeam = $factory->createTable('Seasonteam', 'Administrator');

        if (!$seasonTeam) {
            throw new \RuntimeException('Seasonteam table could not be loaded.', 500);
        }

        $seasonTeam->load($seasonTeamId);

        if (!empty($seasonTeam->logo_big)) {
            $this->item->logo_big = $seasonTeam->logo_big;
            $this->form->setValue('logo_big', null, $seasonTeam->logo_big);
        }

        $teamModel = $factory->createModel('Team', 'Administrator');
        $this->project_team = $teamModel
            ? $teamModel->getTeam((int) ($seasonTeam->team_id ?? 0), 0)
            : null;
        $this->trainingData = $teamModel
            ? $teamModel->getTrainigData(0, (int) $this->item->id)
            : [];

        if (!$this->project_team) {
            $this->project_team = (object) [
                'name' => '',
                'standard_playground' => 0,
            ];
        }

        if (empty($this->project_team->standard_playground)) {
            $this->project_team->standard_playground = $this->model->getProjectTeamPlayground($seasonTeamId);
        }

        if (empty($this->item->standard_playground)) {
            $this->form->setValue('standard_playground', null, $this->project_team->standard_playground);
        }

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
        $dwOptions = [];

        foreach ($daysOfWeek as $key => $value) {
            $dwOptions[] = HTMLHelper::_('select.option', $key, $value);
        }

        if ($this->trainingData) {
            foreach ($this->trainingData as $td) {
                $lists['dayOfWeek'][$td->id] = HTMLHelper::_(
                    'select.genericlist',
                    $dwOptions,
                    'dayofweek[' . $td->id . ']',
                    'class="inputbox"',
                    'value',
                    'text',
                    $td->dayofweek
                );
            }
        }

        $this->extended = sportsmanagementHelper::getExtended($this->item->extended, 'projectteam');
        $this->lists = $lists;
    }

    protected function addToolbar()
    {
        $this->jinput->set('hidemainmenu', true);
        $this->jinput->set('pid', $this->item->project_id);
        $this->title = $this->item->id
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_EDIT') . ' ' . $this->project_team->name
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_NEW') . ' ' . $this->project_team->name;
        $this->icon = 'projectteam';
        parent::addToolbar();
    }
}
