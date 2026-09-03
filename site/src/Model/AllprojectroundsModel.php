<?php
/**
 * Joomla 5/6 model for the public all-project-rounds view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class AllprojectroundsModel extends SportsManagementProjectModel
{
    /** @var array<string,mixed> */
    public array $_params = [];
    /** @var array<int,object> */
    public array $ProjectTeams = [];
    /** @var array<int,object> */
    public array $result = [];

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $template = $this->getTemplateConfig('allprojectrounds');
        $this->_params = [
            'Itemid' => $input->getInt('Itemid', 0),
            'show_columns' => $input->getInt('show_columns', (int) ($template['show_columns'] ?? 0)),
            'show_sectionheader' => $input->getInt('show_sectionheader', (int) ($template['show_sectionheader'] ?? 0)),
            'show_firstroster' => (int) ($template['show_firstroster'] ?? 0),
            'show_firstsubst' => (int) ($template['show_firstsubst'] ?? 0),
            'show_firstevents' => (int) ($template['show_firstevents'] ?? 0),
            'show_secondroster' => (int) ($template['show_secondroster'] ?? 0),
            'show_secondsubst' => (int) ($template['show_secondsubst'] ?? 0),
            'show_secondevents' => (int) ($template['show_secondevents'] ?? 0),
            'show_favteaminfo' => $input->getInt('show_favteaminfo', (int) ($template['show_favteaminfo'] ?? 0)),
            'show_matchreport' => (int) ($template['show_matchreport'] ?? 1),
            's' => $input->getInt('s', 0),
            'p' => $this->getProjectId(),
            'table_class' => $input->getCmd('table_class', (string) ($template['table_class'] ?? 'table')),
            'view' => $input->getCmd('view', 'allprojectrounds'),
            'option' => $input->getCmd('option', 'com_sportsmanagement'),
        ];
    }

    /** @return array<int,object> */
    public function getProjectMatches(): array
    {
        if ($this->getProjectId() <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'm.*',
                'DATE_FORMAT(m.time_present, "%H:%i") AS time_present',
                $db->quoteName('playground.name', 'playground_name'),
                $db->quoteName('playground.short_name', 'playground_short_name'),
                $db->quoteName('r.name', 'round_name'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('t1.name', 'home_name'),
                $db->quoteName('t1.short_name', 'home_short_name'),
                $db->quoteName('t1.middle_name', 'home_middle_name'),
                $db->quoteName('t2.name', 'away_name'),
                $db->quoteName('t2.short_name', 'away_short_name'),
                $db->quoteName('t2.middle_name', 'away_middle_name'),
                $db->quoteName('pt1.project_id'),
                $db->quoteName('d1.name', 'divhome'),
                $db->quoteName('d2.name', 'divaway'),
                "CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) ELSE m.id END AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd1') . ' ON ' . $db->quoteName('d1.id') . ' = ' . $db->quoteName('pt1.division_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd2') . ' ON ' . $db->quoteName('d2.id') . ' = ' . $db->quoteName('pt2.division_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('r.project_id') . ' = ' . $this->getProjectId())
            ->order($db->quoteName('r.roundcode') . ' ASC, ' . $db->quoteName('m.match_date') . ' ASC, ' . $db->quoteName('m.match_number') . ' ASC');

        $db->setQuery($query);
        $this->result = $db->loadObjectList() ?: [];
        if (!$this->result) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES'), Log::INFO, 'jsmerror');
        }

        return $this->result;
    }

    /** @return array<int,object> */
    public function getProjectTeamID(array $favteams): array
    {
        $teams = $this->getProjectTeams();
        if (!$teams) {
            $this->ProjectTeams = [];
            return [];
        }

        if (!$favteams) {
            $this->ProjectTeams = array_values($teams);
            return $this->ProjectTeams;
        }

        $favoriteIds = array_flip(array_map('intval', $favteams));
        $selected = [];
        foreach ($teams as $team) {
            $teamId = (int) ($team->id ?? 0);
            if ($teamId > 0 && isset($favoriteIds[$teamId])) {
                $selected[] = $team;
            }
        }

        $this->ProjectTeams = $selected ?: array_values($teams);
        return $this->ProjectTeams;
    }

    /** @return array<string,mixed> */
    public function getAllRoundsParams(): array
    {
        return $this->_params;
    }

    public function getRoundsColumn(array $rounds, array $config): string
    {
        if (!$rounds) {
            return '';
        }

        if (!$this->result) {
            $this->getProjectMatches();
        }

        $showColumns = (int) ($config['show_columns'] ?? $this->_params['show_columns'] ?? 0) === 1;
        if (!$showColumns) {
            $html = '';
            foreach ($rounds as $round) {
                $html .= $this->renderRound($round, $config, 'col-12');
            }
            return $html;
        }

        $html = '';
        foreach (array_chunk($rounds, 2) as $pair) {
            $html .= '<div class="row g-3">';
            foreach ($pair as $round) {
                $html .= $this->renderRound($round, $config, 'col-12 col-lg-6');
            }
            $html .= '</div>';
        }
        return $html;
    }

    private function renderRound(object $round, array $config, string $columnClass): string
    {
        $roundId = (int) ($round->id ?? 0);
        $matches = array_values(array_filter(
            $this->result,
            static fn(object $match): bool => (int) ($match->round_id ?? 0) === $roundId
        ));

        $html = '<div class="' . $columnClass . '">';
        $html .= '<div class="text-center fw-bold mb-2">' . $this->escape((string) ($round->name ?? '')) . '</div>';

        foreach ($matches as $match) {
            $html .= '<div class="row align-items-center mb-1">';
            $html .= '<div class="col-5 text-end">' . $this->escape((string) ($match->home_name ?? '')) . '</div>';
            $html .= '<div class="col-1 text-center">' . $this->escapeResult($match->team1_result ?? '') . '</div>';
            $html .= '<div class="col-1 text-center">' . $this->escapeResult($match->team2_result ?? '') . '</div>';
            $html .= '<div class="col-5">' . $this->escape((string) ($match->away_name ?? '')) . '</div>';
            $html .= '</div>';
            $html .= $this->renderMatchDetails($match, $config);
        }

        $html .= '</div>';
        return $html;
    }

    private function renderMatchDetails(object $match, array $config): string
    {
        if (!(int) ($this->_params['show_matchreport'] ?? 1)) {
            return '';
        }

        $allowedProjectTeams = [];
        if ((int) ($config['show_favteaminfo'] ?? $this->_params['show_favteaminfo'] ?? 0) === 1) {
            foreach ($this->ProjectTeams as $team) {
                $projectTeamId = (int) ($team->projectteamid ?? 0);
                if ($projectTeamId > 0) {
                    $allowedProjectTeams[$projectTeamId] = true;
                }
            }
        }

        $sides = [
            ['id' => (int) ($match->projectteam1_id ?? 0), 'prefix' => 'first'],
            ['id' => (int) ($match->projectteam2_id ?? 0), 'prefix' => 'second'],
        ];
        $html = '<div class="row mb-3">';
        foreach ($sides as $side) {
            $projectTeamId = $side['id'];
            $prefix = $side['prefix'];
            $html .= '<div class="col-12 col-lg-6 small">';
            if ($projectTeamId > 0 && (!$allowedProjectTeams || isset($allowedProjectTeams[$projectTeamId]))) {
                if ((int) ($config['show_' . $prefix . 'roster'] ?? 0) === 1) {
                    $html .= $this->detailLine(
                        Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_STARTING_LINE-UP'),
                        $this->getMatchPlayers((int) $match->id, $projectTeamId)
                    );
                }
                if ((int) ($config['show_' . $prefix . 'subst'] ?? 0) === 1) {
                    $html .= $this->detailLine(
                        Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_SUBSTITUTES'),
                        $this->getSubstitutes((int) $match->id, $projectTeamId)
                    );
                }
                if ((int) ($config['show_' . $prefix . 'events'] ?? 0) === 1) {
                    $html .= $this->detailLine(
                        Text::_('COM_SPORTSMANAGEMENT_MATCHREPORT_EVENTS'),
                        $this->getPlayersEvents((int) $match->id, $projectTeamId),
                        true
                    );
                }
            }
            $html .= '</div>';
        }
        $html .= '</div>';
        return $html;
    }

    private function detailLine(string $label, array $values, bool $allowHtml = false): string
    {
        if (!$values) {
            return '';
        }
        $content = $allowHtml
            ? implode(', ', $values)
            : implode(', ', array_map(fn(string $value): string => $this->escape($value), $values));
        return '<div><strong>' . $this->escape($label) . ':</strong> ' . $content . '</div>';
    }

    /** @return array<int,string> */
    public function getMatchPlayers(int $matchId = 0, int $projectTeamId = 0): array
    {
        if ($matchId <= 0 || $projectTeamId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('stp.person_id'),
                $db->quoteName('stp.jerseynumber'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('stp.person_id'))
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('mp.came_in') . ' = 0')
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('mp.ordering') . ', ' . $db->quoteName('stp.jerseynumber') . ', ' . $db->quoteName('p.lastname'));
        $db->setQuery($query);
        $players = $db->loadObjectList() ?: [];

        if (!$players) {
            return [Text::_('Keine Spieler vorhanden')];
        }

        $result = [];
        foreach ($players as $player) {
            $subQuery = $db->getQuery(true)
                ->select($db->quoteName('in_out_time'))
                ->from($db->quoteName('#__sportsmanagement_match_player'))
                ->where($db->quoteName('match_id') . ' = ' . $matchId)
                ->where($db->quoteName('in_for') . ' = ' . (int) $player->teamplayer_id);
            $db->setQuery($subQuery, 0, 1);
            $minute = (string) ($db->loadResult() ?? '');

            $text = '(' . (string) ($player->jerseynumber ?? '') . ')' .
                (string) ($player->firstname ?? '') . ' ' . (string) ($player->lastname ?? '');
            if ($minute !== '') {
                $text .= ' (' . $minute . ')';
            }
            $result[] = trim($text);
        }
        return $result;
    }

    /** @return array<int,string> */
    public function getSubstitutes(int $matchId = 0, int $projectTeamId = 0): array
    {
        if ($matchId <= 0 || $projectTeamId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.in_out_time'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p2.firstname', 'out_firstname'),
                $db->quoteName('p2.lastname', 'out_lastname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('stp.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp2') . ' ON ' . $db->quoteName('stp2.id') . ' = ' . $db->quoteName('mp.in_for'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p2') . ' ON ' . $db->quoteName('p2.id') . ' = ' . $db->quoteName('stp2.person_id'))
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('mp.came_in') . ' > 0')
            ->where('(' . $db->quoteName('p.published') . ' = 1 OR ' . $db->quoteName('p.id') . ' IS NULL)')
            ->order('CAST(' . $db->quoteName('mp.in_out_time') . ' AS UNSIGNED) ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        if (!$rows) {
            return [Text::_('Keine Auswechselungen vorhanden')];
        }

        $result = [];
        foreach ($rows as $row) {
            $in = trim((string) ($row->firstname ?? '') . ' ' . (string) ($row->lastname ?? ''));
            $out = trim((string) ($row->out_firstname ?? '') . ' ' . (string) ($row->out_lastname ?? ''));
            $minute = trim((string) ($row->in_out_time ?? ''));
            $text = $in;
            if ($out !== '') {
                $text .= ' für ' . $out;
            }
            if ($minute !== '') {
                $text .= ' (' . $minute . ')';
            }
            $result[] = trim($text);
        }
        return $result;
    }

    /** @return array<int,string> */
    public function getPlayersEvents(int $matchId = 0, int $projectTeamId = 0): array
    {
        if ($matchId <= 0 || $projectTeamId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'ev.*',
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('et.name', 'event_name'),
                $db->quoteName('et.icon', 'event_icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'ev'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('ev.event_type_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.id') . ' = ' . $db->quoteName('ev.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('stp.person_id'))
            ->where($db->quoteName('ev.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('ev.projectteam_id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->group($db->quoteName('ev.id'))
            ->order($db->quoteName('ev.event_time') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        if (!$rows) {
            return [Text::_('Keine Ereignisse vorhanden')];
        }

        $result = [];
        foreach ($rows as $row) {
            $icon = trim((string) ($row->event_icon ?? ''));
            $iconHtml = $icon !== ''
                ? HTMLHelper::_('image', $icon, Text::_($icon), ['alt' => Text::_((string) ($row->event_name ?? ''))])
                : '';
            $result[] = $iconHtml . $this->escape(
                trim(
                    Text::_((string) ($row->event_name ?? '')) . ' ' .
                    (string) ($row->notice ?? '') . ' ' .
                    (string) ($row->firstname ?? '') . ' ' .
                    (string) ($row->lastname ?? '') . ' (' .
                    (string) ($row->event_time ?? '') . ')'
                )
            );
        }
        return $result;
    }

    /** @return array<int,object> */
    public function getMatchReferees(int $matchId = 0): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id'),
                $db->quoteName('pref.id', 'person_id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('mr.project_position_id'),
                $db->quoteName('pref.picture'),
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
                "CONCAT(p.firstname, ' - ', p.lastname) AS text",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pref.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('spi.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('pos.name') . ' ASC, ' . $db->quoteName('mr.ordering') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function escapeResult(mixed $value): string
    {
        return $this->escape((string) ($value ?? ''));
    }
}
