<?php
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Helper\CountryOptionsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Joomla 5/6 administrator roster view for team players and staff. */
class sportsmanagementViewteamplayers extends sportsmanagementView
{
    public function init()
    {
        $this->state = $this->get('State');
        $this->items = $this->get('Items') ?: [];
        $this->pagination = $this->get('Pagination');
        $this->total = $this->get('Total');
        $this->sortDirection = (string) $this->state->get('list.direction', 'ASC');
        $this->sortColumn = (string) $this->state->get('list.ordering', 'ppl.lastname');

        $this->project_id = (int) $this->state->get('filter.pid', 0);
        $this->project_team_id = (int) $this->state->get('filter.project_team_id', 0);
        $this->team_id = (int) $this->state->get('filter.team_id', 0);
        $this->season_team_id = (int) $this->state->get('filter.season_team_id', 0);
        $this->season_id = (int) $this->state->get('filter.season_id', 0);
        $this->_persontype = (int) $this->state->get('filter.persontype', 1);
        $this->restartpage = false;

        $this->project = $this->model->getProjectContext();
        $this->project_team = $this->model->getTeamContext();

        if (!$this->project || !$this->project_team) {
            $this->app->enqueueMessage(Text::_('JGLOBAL_NO_MATCHING_RESULTS'), 'warning');
            $this->lists = ['project_position_id' => [], 'nation' => [], 'search_mode' => ''];

            return;
        }

        $positionOptions = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_FUNCTION')),
        ];
        $positionOptions = array_merge($positionOptions, $this->model->getProjectPositionOptions());

        $countries = [
            HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_COUNTRY')),
        ];
        $countries = array_merge(
            $countries,
            CountryOptionsHelper::getOptions($this->model->getDatabase())
        );

        $this->lists = [
            'project_position_id' => $positionOptions,
            'nation' => $countries,
            'search_mode' => '',
        ];
    }

    protected function addToolbar()
    {
        if (!$this->project || !$this->project_team) {
            return;
        }

        $teamName = (string) ($this->project_team->team_name ?? '');
        $this->title = ($this->_persontype === 2
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TSTAFFS_TITLE')
            : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_TITLE')) . ' ' . $teamName;

        ToolbarHelper::back(
            'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_BACK',
            'index.php?option=' . $this->option . '&view=projectteams&pid=' . $this->project_id
        );
        ToolbarHelper::apply('teamplayers.saveshort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_APPLY'));
        ToolbarHelper::publish('teamplayers.publish', 'JTOOLBAR_PUBLISH', true);
        ToolbarHelper::unpublish('teamplayers.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        ToolbarHelper::deleteList('', 'teamplayers.delete');
        ToolbarHelper::divider();

        $layout = new FileLayout('assignpersons', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
        Toolbar::getInstance('toolbar')->appendButton('Custom', $layout->render(), 'upload');
        $this->renderAssignModal('collapseModalassignPersons', 'assignpersons', false);

        if (ComponentHelper::getParams($this->option)->get('assign_club_position_to_player', 0)) {
            $layout = new FileLayout('assignpersonsclub', JPATH_ROOT . '/components/com_sportsmanagement/layouts');
            Toolbar::getInstance('toolbar')->appendButton('Custom', $layout->render(), 'upload');
            $this->renderAssignModal('collapseModalassignPersonsClub', 'assignpersonsclub', true);
        }

        ToolbarHelper::apply(
            'teamplayers.assignplayerscountry',
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_ASSIGN_COUNTRY')
        );
    }

    private function renderAssignModal(string $id, string $layout, bool $assignClub): void
    {
        $query = [
            'option' => 'com_sportsmanagement',
            'view' => 'players',
            'tmpl' => 'component',
            'layout' => $layout,
            'type' => $this->_persontype === 2 ? 1 : 0,
            'pid' => $this->project_id,
            'team_id' => $this->team_id,
            'persontype' => $this->_persontype,
            'season_id' => $this->season_id,
            'whichview' => 'teamplayers',
        ];
        if ($assignClub) {
            $query['assignclub'] = 1;
        }

        echo HTMLHelper::_('bootstrap.renderModal', $id, [
            'url' => 'index.php?' . http_build_query($query),
            'height' => $this->modalheight,
            'width' => $this->modalwidth,
            'modalWidth' => '60',
        ]);
    }
}
