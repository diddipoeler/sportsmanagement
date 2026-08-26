<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editmatch;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\PersonNameFormatter;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchLineupViewDataService;
use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchViewDataService;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;

trait EditmatchLineupViewTrait
{
    public int $tid = 0;
    public string $teamname = '';
    public array $playersoptionsout = [];
    public array $playersoptionsin = [];
    public array $substitutions = [];
    public array $starters = [];

    private function prepareLineupLayout(EditmatchViewDataService $viewService): void
    {
        if (!$this->match) {
            return;
        }

        $lineupService = $this->lineupViewDataService();
        $matchId = (int) $this->match->id;
        $this->tid = $this->input->getInt('team', 0);
        $this->default_name_format = 0;

        $teams = $lineupService->getMatchTeams($matchId);

        if (!$teams || $this->tid <= 0) {
            throw new \RuntimeException('SportsManagement lineup team is unavailable.', 404);
        }

        $this->teams = $teams;
        $this->teamname = $this->tid === (int) $teams->projectteam1_id
            ? (string) $teams->team1
            : (string) $teams->team2;

        $seasonId = (int) ($this->project->season_id ?? 0);
        $assignedPlayers = $viewService->getMatchPersons($this->tid, $matchId);
        $assignedPlayerIds = array_map('intval', array_keys($assignedPlayers));
        $notAssignedPlayers = $lineupService->getTeamPersons(
            $this->tid,
            $assignedPlayerIds,
            1,
            $seasonId,
            $this->project_id
        );

        $playersOptionsOut = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_OUT')),
        ];
        $playersOptionsIn = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_PLAYER_IN')),
        ];

        if ($notAssignedPlayers === [] && $assignedPlayerIds === []) {
            $this->playersoptionsout = $playersOptionsOut;
            $this->playersoptionsin = $playersOptionsIn;
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_PLAYERS_MATCH'), Log::WARNING, 'jsmerror');
            return;
        }

        $projectPositions = $viewService->getProjectPositionsOptions($this->project_id, 1);

        if ($projectPositions === []) {
            $this->playersoptionsout = $playersOptionsOut;
            $this->playersoptionsin = $playersOptionsIn;
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_NO_POS'), Log::WARNING, 'jsmerror');
            return;
        }

        $lists = [];

        if ((string) ($this->project->sport_type_name ?? '') === 'COM_SPORTSMANAGEMENT_ST_GOLF_BILLARD') {
            $lists['team_players_billard'] = $notAssignedPlayers;
            $lists['team_players_billard_assign'] = $assignedPlayers;
        }

        $notAssignedOptions = [];

        foreach ($notAssignedPlayers as $player) {
            $notAssignedOptions[] = HTMLHelper::_(
                'select.option',
                (int) $player->value,
                '[' . (string) ($player->jerseynumber ?? '') . '] '
                . $this->lineupPersonName($player)
                . ' - (' . Text::_((string) ($player->positionname ?? '')) . ')'
            );
        }

        $lists['team_players'] = HTMLHelper::_(
            'select.genericlist',
            $notAssignedOptions,
            'roster[]',
            'style="font-size:12px;height:auto;min-width:15em;" class="inputbox" multiple="true" size="18"',
            'value',
            'text'
        );

        $selectPositions = [
            HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_IN_POSITION')),
        ];
        $selectPositions = array_merge($selectPositions, array_values($projectPositions));
        $lists['projectpositions'] = HTMLHelper::_(
            'select.genericlist',
            $selectPositions,
            'project_position_id',
            'class="inputbox" size="1"',
            'posid',
            'text',
            null,
            false,
            true
        );

        foreach ($assignedPlayers as $player) {
            $playersOptionsOut[] = HTMLHelper::_(
                'select.option',
                (int) $player->value,
                $this->lineupPersonName($player)
                . ' - (' . Text::_((string) ($player->positionname ?? '')) . ')'
            );
        }
        $this->playersoptionsout = $playersOptionsOut;

        foreach ($notAssignedPlayers as $player) {
            $playersOptionsIn[] = HTMLHelper::_(
                'select.option',
                (int) $player->value,
                $this->lineupPersonName($player)
                . ' - (' . Text::_((string) ($player->positionname ?? '')) . ')'
            );
        }
        $this->playersoptionsin = $playersOptionsIn;

        $starters = [];

        foreach ($projectPositions as $positionId => $position) {
            $starters[$positionId] = $lineupService->getRoster(
                $this->tid,
                (int) $position->value,
                $matchId,
                $this->project_id
            );
        }

        foreach ($starters as $positionId => $players) {
            $options = [];

            foreach ($players as $player) {
                $options[] = HTMLHelper::_(
                    'select.option',
                    (int) $player->value,
                    '[' . (string) ($player->jerseynumber ?? '') . '] ' . $this->lineupPersonName($player)
                );
            }

            $lists['team_players' . $positionId] = HTMLHelper::_(
                'select.genericlist',
                $options,
                'position' . $positionId . '[]',
                'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-starters" multiple="true"',
                'value',
                'text'
            );
        }

        $staffPositions = $viewService->getProjectPositionsOptions($this->project_id, 2);
        $assignedStaff = $lineupService->getMatchStaff($this->tid, $matchId, $this->project_id);
        $assignedStaffIds = array_map('intval', array_keys($assignedStaff));
        $notAssignedStaff = $lineupService->getTeamPersons(
            $this->tid,
            $assignedStaffIds,
            2,
            $seasonId,
            $this->project_id
        );
        $notAssignedStaffOptions = [];

        foreach ($notAssignedStaff as $staff) {
            $notAssignedStaffOptions[] = HTMLHelper::_(
                'select.option',
                (int) $staff->value,
                $this->lineupPersonName($staff)
                . ' - (' . Text::_((string) ($staff->positionname ?? '')) . ')'
            );
        }

        $lists['team_staffs'] = HTMLHelper::_(
            'select.genericlist',
            $notAssignedStaffOptions,
            'staff[]',
            'style="font-size:12px;height:auto;min-width:15em;" size="18" class="inputbox" multiple="true"',
            'value',
            'text'
        );

        foreach ($staffPositions as $positionId => $position) {
            $options = [];

            foreach ($assignedStaff as $staff) {
                if ((int) $staff->position_id !== (int) $position->pposid) {
                    continue;
                }

                $options[] = HTMLHelper::_(
                    'select.option',
                    (int) $staff->team_staff_id,
                    $this->lineupPersonName($staff)
                );
            }

            $lists['team_staffs' . $positionId] = HTMLHelper::_(
                'select.genericlist',
                $options,
                'staffposition' . $positionId . '[]',
                'style="font-size:12px;height:auto;min-width:15em;" size="4" class="position-staff" multiple="true"',
                'value',
                'text'
            );
        }

        $lists['captain'] = [
            HTMLHelper::_('select.option', '0', Text::_('JNO')),
            HTMLHelper::_('select.option', '1', Text::_('JYES')),
        ];

        $this->positions = $projectPositions;
        $this->staffpositions = $staffPositions;
        $this->substitutions = $lineupService->getSubstitutions($this->tid, $matchId, $this->project_id);
        $this->starters = $starters;
        $this->lists = array_merge($this->lists, $lists);

        $assets = $this->getDocument()->getWebAssetManager();
        $assets->registerAndUseScript(
            'com_sportsmanagement.editmatch-editing',
            Uri::root() . 'components/com_sportsmanagement/assets/js/editmatch-editing.js'
        );
        $assets->registerAndUseScript(
            'com_sportsmanagement.editmatch-lists',
            Uri::root() . 'components/com_sportsmanagement/assets/js/editmatch-lists.js'
        );
        $assets->addInlineScript(
            'window.baseajaxurl = ' . json_encode(
                Uri::root() . 'index.php?option=com_sportsmanagement',
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) . ";\n"
            . 'window.matchid = ' . $matchId . ";\n"
            . 'window.teamid = ' . $this->tid . ";\n"
            . 'window.projecttime = ' . $this->eventsprojecttime . ";\n"
            . 'window.useeventtime = ' . $this->useeventtime . ";\n"
            . 'window.str_delete = ' . json_encode(
                Text::_('JACTION_DELETE'),
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) . ';'
        );
    }

    private function lineupPersonName(object $person): string
    {
        return PersonNameFormatter::format(
            null,
            (string) ($person->firstname ?? ''),
            (string) ($person->nickname ?? ''),
            (string) ($person->lastname ?? ''),
            0
        );
    }

    private function lineupViewDataService(): EditmatchLineupViewDataService
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return new EditmatchLineupViewDataService($database);
    }
}
