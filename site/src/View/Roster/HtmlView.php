<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Roster;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\RosterModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementProjectHtmlView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

final class HtmlView extends SportsManagementProjectHtmlView
{
    public array $stafflist = [];
    public $team = null;
    public array $rows = [];
    public $projectteam = null;
    public string $lastseasondate = '0000-00-00';
    public array $projectpositions = [];
    public string $type = '';
    public string $typestaff = '';
    public array $positioneventtypes = [];
    public array $playereventstats = [];
    public array $playereventstatsdart = [];
    public array $stats = [];
    public array $playerstats = [];
    public array $lists = [];

    protected function prepareView(): void
    {
        /** @var RosterModel $model */
        $model = $this->getModel();
        if (!$model instanceof RosterModel) {
            throw new \RuntimeException('Roster view requires RosterModel.', 500);
        }

        RosterModel::$seasonid = (int) ($this->project->season_id ?? RosterModel::$seasonid);
        $this->projectteam = $model->getProjectTeam($this->config['team_picture_which'] ?? 'pt');
        $this->lastseasondate = $model->getLastSeasonDate();
        $this->projectpositions = $model->getProjectPositions();

        $this->type = (string) $this->input->getCmd('type', '');
        $this->typestaff = (string) $this->input->getCmd('typestaff', '');
        if ($this->type === '') {
            $this->type = (string) ($this->config['show_players_layout'] ?? 'player_standard');
        }
        if ($this->typestaff === '') {
            $this->typestaff = (string) ($this->config['show_staff_layout'] ?? 'staff_standard');
        }
        $this->config['show_players_layout'] = $this->type;
        $this->config['show_staff_layout'] = $this->typestaff;

        if ($this->projectteam) {
            $this->team = $model->getTeam();
            $this->rows = (array) $model->getTeamPlayers(1);

            if (!empty($this->config['show_events_stats'])) {
                $this->positioneventtypes = $model->getPositionEventTypes();
                $isDart = ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_DART';
                if ($isDart) {
                    $this->playereventstats = $model->getPlayerEventStats(true, true);
                    $this->playereventstatsdart = $model->getPlayerEventStats(true, false);
                } else {
                    $this->playereventstats = $model->getPlayerEventStats(false, false);
                }
            }

            if (!empty($this->config['show_stats'])) {
                $this->stats = $model->getProjectStats();
                $this->playerstats = $model->getRosterStats();
            }

            $this->stafflist = (array) $model->getTeamPlayers(2);
            $teamName = is_object($this->team) ? (string) ($this->team->name ?? '') : '';
            $this->getDocument()->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', $teamName));
        } else {
            $this->getDocument()->setTitle(
                Text::sprintf(
                    'COM_SPORTSMANAGEMENT_ROSTER_TITLE',
                    Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_TEAM')
                )
            );
        }

        $this->getDocument()->addStyleSheet(
            Uri::root(true) . '/components/com_sportsmanagement/assets/css/roster.css'
        );

        $this->lists['type'] = [
            HTMLHelper::_('select.option', 'player_standard', Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION1_PLAYER_STANDARD')),
            HTMLHelper::_('select.option', 'player_card', Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION2_PLAYER_CARD')),
            HTMLHelper::_('select.option', 'player_johncage', Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION3_PLAYER_CARD')),
        ];
        $this->lists['typestaff'] = [
            HTMLHelper::_('select.option', 'staff_standard', Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION1_STAFF_STANDARD')),
            HTMLHelper::_('select.option', 'staff_card', Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION2_STAFF_CARD')),
            HTMLHelper::_('select.option', 'staff_johncage', Text::_('COM_SPORTSMANAGEMENT_FES_ROSTER_PARAM_OPTION3_STAFF_CARD')),
        ];

        $this->config['table_class'] = $this->config['table_class'] ?? 'table';
        $this->headertitle = is_object($this->team)
            ? Text::sprintf('COM_SPORTSMANAGEMENT_ROSTER_TITLE', (string) ($this->team->name ?? ''))
            : Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_TEAM');
    }
}
