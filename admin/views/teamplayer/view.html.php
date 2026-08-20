<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

/** Joomla 5/6 administrator form view for one team-person assignment. */
class sportsmanagementViewTeamPlayer extends sportsmanagementView
{
    public function init()
    {
        $input = $this->app->getInput();
        $this->persontype = $input->getInt('persontype')
            ?: (int) $this->app->getUserState($this->option . '.persontype', 1);
        $this->project_id = $input->getInt('pid')
            ?: (int) $this->app->getUserState($this->option . '.pid', 0);
        $this->team_id = $input->getInt('team_id')
            ?: (int) $this->app->getUserState($this->option . '.team_id', 0);
        $this->project_team_id = $input->getInt('project_team_id')
            ?: (int) $this->app->getUserState($this->option . '.project_team_id', 0);
        $this->season_id = $input->getInt('season_id')
            ?: (int) $this->app->getUserState($this->option . '.season_id', 0);
        $this->season_team_id = $input->getInt('season_team_id')
            ?: (int) $this->app->getUserState($this->option . '.season_team_id', 0);

        foreach ([
            'pid' => $this->project_id,
            'team_id' => $this->team_id,
            'project_team_id' => $this->project_team_id,
            'season_id' => $this->season_id,
            'season_team_id' => $this->season_team_id,
            'persontype' => $this->persontype,
        ] as $key => $value) {
            if ($value > 0) {
                $this->app->setUserState($this->option . '.' . $key, $value);
            }
        }

        $this->project = $this->model->getProject($this->project_id);
        $personId = (int) ($this->item->person_id ?? $input->getInt('person_id'));
        $this->project_person = $this->model->getPerson($personId);

        if (!$this->project_person) {
            $this->app->enqueueMessage(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');

            return;
        }

        $this->item->name = trim(
            (string) $this->project_person->lastname . ' - ' . (string) $this->project_person->firstname,
            ' -'
        );

        $this->form->setValue('person_id', null, $personId);
        $this->form->setValue('projectteam_id', null, $this->project_team_id);
        $this->form->setValue('position_id', null, (int) ($this->project_person->position_id ?? 0));
        $this->form->setValue('persontype', null, $this->persontype);

        foreach ([
            'injury', 'injury_date', 'injury_end', 'injury_detail', 'injury_date_start', 'injury_date_end',
            'suspension', 'suspension_date', 'suspension_end', 'suspension_detail', 'susp_date_start', 'susp_date_end',
            'away', 'away_date', 'away_end', 'away_detail', 'away_date_start', 'away_date_end',
        ] as $field) {
            $value = $this->project_person->{$field} ?? null;
            if ($value === '0000-00-00') {
                $value = '';
            }
            $this->form->setValue($field, null, $value);
        }

        foreach (['contract_from', 'contract_to'] as $field) {
            if (($this->item->{$field} ?? '') === '0000-00-00') {
                $this->item->{$field} = '';
                $this->form->setValue($field, null, '');
            }
        }

        if (!(int) $this->form->getValue('project_position_id')) {
            foreach ($this->model->getProjectPositions($this->project_id, $this->persontype) as $position) {
                if ((int) $position->position_id === (int) ($this->project_person->position_id ?? 0)) {
                    $this->form->setValue('project_position_id', null, (int) $position->value);
                    break;
                }
            }
        }

        $this->extended = sportsmanagementHelper::getExtended(
            (string) ($this->item->extended ?? ''),
            'teamplayer'
        );
        $this->lists = [];
    }

    protected function addToolbar()
    {
        $this->app->getInput()->set('hidemainmenu', true);
        $isNew = empty($this->item->id);

        if ((int) ($this->item->persontype ?? $this->persontype) === 2) {
            $this->title = $isNew
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_NEW')
                : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFF_EDIT', $this->item->name ?? '');
        } else {
            $this->title = $isNew
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_NEW')
                : Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMPLAYER_EDIT', $this->item->name ?? '');
        }

        parent::addToolbar();
    }
}
