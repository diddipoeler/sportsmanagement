<?php
/**
 * Joomla 5/6 model for the public prediction entry view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

class PredictionentryModel extends SportsManagementPredictionReadModel
{
    private ?object $entryMember = null;
    private ?object $entryProject = null;
    private ?array $entryConfig = null;

    /**
     * Combine prediction-overall and entry-specific display settings.
     * Caches the merged template configuration and supplies defaults for
     * match selection, table appearance, deadlines, logos and help text.
     * @return array<string,mixed> Effective entry presentation configuration.
     */
    public function getEntryConfig(): array
    {
        if ($this->entryConfig !== null) {
            return $this->entryConfig;
        }

        $this->entryConfig = array_merge(
            $this->getPredictionTemplateConfig('predictionoverall'),
            $this->getPredictionTemplateConfig('predictionentry')
        );
        $this->entryConfig += [
            'table_class' => 'table',
            'show_help' => 3,
            'show_tipp_tendence' => 0,
            'prediction_team_name' => 'short_name',
            'show_logo_small' => 'logo_big',
            'club_logo_height' => 20,
            'closing_time' => 0,
            'use_pred_select_matches' => 0,
            'use_pred_select_rounds' => 0,
            'use_pred_select_proteams' => 0,
            'predictionmatchid' => '',
            'predictionroundid' => '',
            'predictionproteamid' => '',
            'seperator' => ':',
            'ownername' => '',
            'send_admin_user_tipentry' => 0,
        ];

        return $this->entryConfig;
    }

    /**
     * Resolve the selected prediction project against eligible projects.
     * Preserves a valid selected project, otherwise selects the first
     * configured prediction project and uses zero when none are available.
     * @return int Current valid project ID, or zero.
     */
    public function getProjectId(): int
    {
        $projects = $this->getPredictionProjects();
        if (!$projects) {
            $this->projectId = 0;
            return 0;
        }

        $validIds = array_values(array_filter(array_map(
            static fn(object $project): int => (int) ($project->project_id ?? 0),
            $projects
        )));

        if ($this->projectId > 0 && in_array($this->projectId, $validIds, true)) {
            return $this->projectId;
        }

        $this->projectId = $validIds[0] ?? 0;
        return $this->projectId;
    }

    /**
     * Select a round belonging to the chosen prediction project.
     * Validates project/round relations and optional allowed-round filters,
     * prefers the current project round, then falls back to an allowed option.
     * @return int Valid project round ID, or zero if unavailable.
     */
    public function getRoundId(): int
    {
        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            $this->roundId = 0;
            return 0;
        }

        $allowedRoundIds = $this->allowedRoundIds();
        if ($this->roundId > 0
            && $this->roundBelongsToProject($projectId, $this->roundId)
            && (!$allowedRoundIds || in_array($this->roundId, $allowedRoundIds, true))) {
            return $this->roundId;
        }

        $project = $this->getPredictionProject($projectId);
        $currentRound = (int) ($project->current_round ?? 0);
        if ($currentRound > 0
            && $this->roundBelongsToProject($projectId, $currentRound)
            && (!$allowedRoundIds || in_array($currentRound, $allowedRoundIds, true))) {
            $this->roundId = $currentRound;
            return $this->roundId;
        }

        $options = $this->getRoundOptions();
        $this->roundId = isset($options[0]) ? (int) $options[0]->value : 0;
        return $this->roundId;
    }

    /**
     * Load and cache the project selected for prediction entry.
     * Rejects missing selections and reuses a cached project record only
     * while it matches the currently resolved project identifier.
     * @return object|null Selected project record or null.
     */
    public function getEntryProject(): ?object
    {
        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            return null;
        }
        if ($this->entryProject && (int) ($this->entryProject->project_id ?? 0) === $projectId) {
            return $this->entryProject;
        }

        foreach ($this->getPredictionProjects() as $project) {
            if ((int) ($project->project_id ?? 0) === $projectId) {
                $this->entryProject = $project;
                return $project;
            }
        }

        return null;
    }

    /**
     * Find the authenticated prediction member allowed to enter tips.
     * Returns an empty member for guests or unknown games, prevents normal
     * members from switching to other accounts and allows administrators
     * to select a member only according to prediction permissions.
     * @return object Resolved member or a zero-valued unauthorised record.
     */
    public function getEntryMember(): object
    {
        if ($this->entryMember !== null) {
            return $this->entryMember;
        }

        $identityId = (int) ($this->siteApplication()->getIdentity()?->id ?? 0);
        if ($identityId <= 0 || $this->predictionGameId <= 0) {
            return $this->entryMember = $this->emptyMember();
        }

        if (!$this->isAllowedAdmin()) {
            $this->predictionMemberId = 0;
        }

        $member = parent::getPredictionMember();
        $memberId = $this->extractMemberId($member->pmID ?? $member->id ?? 0);

        if ($memberId <= 0 && $this->isAllowedAdmin() && $this->predictionMemberId <= 0) {
            return $this->entryMember = $this->emptyMember();
        }

        if (!$this->isAllowedAdmin() && (int) ($member->user_id ?? 0) !== $identityId) {
            return $this->entryMember = $this->emptyMember();
        }

        $this->predictionMemberId = $memberId;
        return $this->entryMember = $member;
    }

    /**
     * Return the validated numeric ID of the entry member.
     * Parses legacy prediction member ID:alias values and returns zero
     * for invalid or unselected membership records.
     * @return int Selected positive member ID, or zero.
     */
    public function getSelectedMemberNumericId(): int
    {
        $member = $this->getEntryMember();
        return $this->extractMemberId($member->pmID ?? $member->id ?? 0);
    }

    /**
     * Check whether the signed-in Joomla user belongs to the game.
     * Rejects guest identities and invalid games, then queries membership
     * by both bound game ID and authenticated Joomla user ID.
     * @return bool True when an associated member exists.
     */
    public function isCurrentUserMember(): bool
    {
        $identityId = (int) ($this->siteApplication()->getIdentity()?->id ?? 0);
        if ($identityId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = :currentMemberPredictionId')
            ->where($db->quoteName('user_id') . ' = :currentMemberUserId')
            ->bind(':currentMemberPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':currentMemberUserId', $identityId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return (bool) $db->loadResult();
    }

    /**
     * Determine whether the logged-in game member awaits approval.
     * Checks the membership approval flag for the active user using bound
     * game/user selectors without exposing unrelated member records.
     * @return bool True when the current membership is unapproved.
     */
    public function isNotApprovedCurrentMember(): bool
    {
        $identityId = (int) ($this->siteApplication()->getIdentity()?->id ?? 0);
        if ($identityId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('approved'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = :approvalPredictionId')
            ->where($db->quoteName('user_id') . ' = :approvalUserId')
            ->bind(':approvalPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':approvalUserId', $identityId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $approved = $db->loadResult();
        return $approved !== null && (int) $approved !== 1;
    }

    /**
     * Verify that tips may be submitted for the selected game member.
     * Requires valid game/member IDs, allows an approved member to act
     * for their own Joomla identity or an authorised game administrator.
     * @param object|null $member Member to check, defaults to selected entry member.
     * @return bool Whether this caller may act for the member.
     */
    public function canActAsEntryMember(?object $member = null): bool
    {
        $member ??= $this->getEntryMember();
        $memberId = $this->extractMemberId($member->pmID ?? $member->id ?? 0);
        $memberUserId = (int) ($member->user_id ?? 0);
        if ($memberId <= 0 || $memberUserId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $identityId = (int) ($this->siteApplication()->getIdentity()?->id ?? 0);
        if ($identityId === $memberUserId) {
            return (int) ($member->approved ?? 0) === 1;
        }

        return $this->isAllowedAdmin();
    }

    /**
     * List prediction members selectable by an authorised administrator.
     * Uses the game's member records and configured username/full-name
     * display preference; normal users never receive the full selection.
     * @return array<int,object> Member options with approval information.
     */
    public function getMemberOptions(): array
    {
        if (!$this->isAllowedAdmin() || $this->predictionGameId <= 0) {
            return [];
        }

        $config = $this->getPredictionTemplateConfig('predictionusers');
        $nameField = !empty($config['show_full_name']) ? 'name' : 'username';
        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pm.id', 'value'),
                $db->quoteName('u.' . $nameField, 'text'),
                $db->quoteName('pm.approved'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = :memberOptionsPredictionId')
            ->order($db->quoteName('u.' . $nameField) . ' ASC')
            ->bind(':memberOptionsPredictionId', $predictionGameId, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Build selector options for prediction projects in the game.
     * Maps valid configured project identifiers and display names into
     * the value/text objects expected by Joomla form controls.
     * @return array<int,object> Available prediction projects.
     */
    public function getProjectOptions(): array
    {
        $options = [];
        foreach ($this->getPredictionProjects() as $project) {
            $projectId = (int) ($project->project_id ?? 0);
            if ($projectId > 0) {
                $options[] = (object) [
                    'value' => $projectId,
                    'text' => (string) ($project->projectName ?? $projectId),
                ];
            }
        }
        return $options;
    }

    /**
     * Load allowed rounds belonging to the selected project.
     * Filters the Joomla round table by the bound project ID and
     * optional configured allowed rounds, ordered by ascending round ID.
     * @return array<int,object> Project round value/text options.
     */
    public function getRoundOptions(): array
    {
        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :roundOptionsProjectId')
            ->order($db->quoteName('id') . ' ASC')
            ->bind(':roundOptionsProjectId', $projectId, ParameterType::INTEGER);

        $allowedRoundIds = $this->allowedRoundIds();
        if ($allowedRoundIds) {
            $query->whereIn($db->quoteName('id'), $allowedRoundIds, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Load fixtures and existing tips for the selected entry context.
     * Combines game, project, round and member selectors to retrieve
     * match information, tip values, deadlines and presentation fields
     * for the current prediction-entry table.
     * @return array<int,object> Fixture and member tip entry records.
     */
    public function getEntryMatches(): array
    {
        $project = $this->getEntryProject();
        $member = $this->getEntryMember();
        $projectId = $this->getProjectId();
        $roundId = $this->getRoundId();
        $memberUserId = (int) ($member->user_id ?? 0);
        $predictionGameId = $this->predictionGameId;

        if (!$project || $projectId <= 0 || $roundId <= 0 || $memberUserId <= 0) {
            return [];
        }

        $config = $this->getEntryConfig();
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.round_id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.team1_result_decision'),
                $db->quoteName('m.team2_result_decision'),
                $db->quoteName('pr.id', 'prediction_result_id'),
                $db->quoteName('pr.tipp'),
                $db->quoteName('pr.tipp_home'),
                $db->quoteName('pr.tipp_away'),
                $db->quoteName('pr.joker'),
                $db->quoteName('th.name', 'home_name'),
                $db->quoteName('th.short_name', 'home_short_name'),
                $db->quoteName('th.middle_name', 'home_middle_name'),
                $db->quoteName('ta.name', 'away_name'),
                $db->quoteName('ta.short_name', 'away_short_name'),
                $db->quoteName('ta.middle_name', 'away_middle_name'),
                $db->quoteName('ch.logo_small', 'home_logo_small'),
                $db->quoteName('ch.logo_middle', 'home_logo_middle'),
                $db->quoteName('ch.logo_big', 'home_logo_big'),
                $db->quoteName('ch.country', 'home_country'),
                $db->quoteName('ca.logo_small', 'away_logo_small'),
                $db->quoteName('ca.logo_middle', 'away_logo_middle'),
                $db->quoteName('ca.logo_big', 'away_logo_big'),
                $db->quoteName('ca.country', 'away_country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_prediction_result', 'pr')
                . ' ON ' . $db->quoteName('pr.match_id') . ' = ' . $db->quoteName('m.id')
                . ' AND ' . $db->quoteName('pr.prediction_id') . ' = :entryPredictionId'
                . ' AND ' . $db->quoteName('pr.user_id') . ' = :entryUserId'
                . ' AND ' . $db->quoteName('pr.project_id') . ' = :entryProjectId')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pth') . ' ON ' . $db->quoteName('pth.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'sth') . ' ON ' . $db->quoteName('sth.id') . ' = ' . $db->quoteName('pth.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 'th') . ' ON ' . $db->quoteName('th.id') . ' = ' . $db->quoteName('sth.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'ch') . ' ON ' . $db->quoteName('ch.id') . ' = ' . $db->quoteName('th.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pta') . ' ON ' . $db->quoteName('pta.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'sta') . ' ON ' . $db->quoteName('sta.id') . ' = ' . $db->quoteName('pta.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 'ta') . ' ON ' . $db->quoteName('ta.id') . ' = ' . $db->quoteName('sta.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'ca') . ' ON ' . $db->quoteName('ca.id') . ' = ' . $db->quoteName('ta.club_id'))
            ->where($db->quoteName('r.project_id') . ' = :entryProjectId')
            ->where($db->quoteName('r.id') . ' = :entryRoundId')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.match_date') . " <> '0000-00-00 00:00:00'")
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->order([$db->quoteName('m.match_date') . ' ASC', $db->quoteName('m.id') . ' ASC'])
            ->bind(':entryPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':entryUserId', $memberUserId, ParameterType::INTEGER)
            ->bind(':entryProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':entryRoundId', $roundId, ParameterType::INTEGER);

        $allowedMatchIds = !empty($config['use_pred_select_matches'])
            ? $this->normaliseIdList($config['predictionmatchid'] ?? null)
            : [];
        if (!empty($config['use_pred_select_matches']) && !$allowedMatchIds) {
            return [];
        }
        if ($allowedMatchIds) {
            $query->whereIn($db->quoteName('m.id'), $allowedMatchIds, ParameterType::INTEGER);
        }

        $allowedProjectTeams = !empty($config['use_pred_select_proteams'])
            ? $this->normaliseIdList($config['predictionproteamid'] ?? null)
            : [];
        if (!empty($config['use_pred_select_proteams']) && !$allowedProjectTeams) {
            return [];
        }
        if ($allowedProjectTeams) {
            $homeProjectTeamPlaceholders = $query->bindArray($allowedProjectTeams, ParameterType::INTEGER);
            $awayProjectTeamPlaceholders = $query->bindArray($allowedProjectTeams, ParameterType::INTEGER);
            $query->extendWhere('AND', [
                $db->quoteName('m.projectteam1_id') . ' IN (' . implode(',', $homeProjectTeamPlaceholders) . ')',
                $db->quoteName('m.projectteam2_id') . ' IN (' . implode(',', $awayProjectTeamPlaceholders) . ')',
            ], 'OR');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $tendencies = !empty($config['show_tipp_tendence']) ? $this->getTipTendencies(array_column($rows, 'id')) : [];

        foreach ($rows as $row) {
            $row->editable = $this->isMatchEditable($row, $project, $member, $config);
            $deadline = $this->getMatchDeadline($row, $project, $config);
            $row->deadline = $deadline?->format('Y-m-d H:i:s') ?? '';
            $row->display_match_date = $this->formatMatchDate((string) $row->match_date, $project);
            $row->home_display_name = $this->teamDisplayName($row, 'home', $config);
            $row->away_display_name = $this->teamDisplayName($row, 'away', $config);
            $row->home_logo = $this->teamLogo($row, 'home', $config);
            $row->away_logo = $this->teamLogo($row, 'away', $config);
            $row->tendency = (!$row->editable || $this->isAllowedAdmin())
                ? ($tendencies[(int) $row->id] ?? ['total' => 0, 'home' => 0, 'draw' => 0, 'away' => 0])
                : ['total' => 0, 'home' => 0, 'draw' => 0, 'away' => 0];
        }

        return $rows;
    }

    /**
     * Load the current member's additional round prediction values.
     * Looks up goals, penalties and card predictions for the active
     * game, project and round; returns zeroed defaults when not selected.
     * @return object Extra round-tip fields with default zeros.
     */
    public function getRoundExtras(): object
    {
        $member = $this->getEntryMember();
        $projectId = $this->getProjectId();
        $roundId = $this->getRoundId();
        $userId = (int) ($member->user_id ?? 0);
        $empty = (object) [
            'goals' => 0,
            'penalties' => 0,
            'yellow_cards' => 0,
            'yellow_red_cards' => 0,
            'red_cards' => 0,
        ];
        if ($userId <= 0 || $projectId <= 0 || $roundId <= 0) {
            return $empty;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_result_round'))
            ->where($db->quoteName('prediction_id') . ' = :roundResultPredictionId')
            ->where($db->quoteName('user_id') . ' = :roundResultUserId')
            ->where($db->quoteName('project_id') . ' = :roundResultProjectId')
            ->where($db->quoteName('round_id') . ' = :roundResultRoundId')
            ->bind(':roundResultPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':roundResultUserId', $userId, ParameterType::INTEGER)
            ->bind(':roundResultProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':roundResultRoundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: $empty;
    }

    /**
     * Count jokers already used by a member within the project.
     * Uses a bound game, member and project scope to sum recorded joker
     * values so entry limits can be enforced by the form.
     * @return int Number of previously used project jokers.
     */
    public function getMemberProjectJokerCount(): int
    {
        $member = $this->getEntryMember();
        $userId = (int) ($member->user_id ?? 0);
        $projectId = $this->getProjectId();
        if ($userId <= 0 || $projectId <= 0) {
            return 0;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COALESCE(SUM(' . $db->quoteName('joker') . '), 0)')
            ->from($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('prediction_id') . ' = :jokerPredictionId')
            ->where($db->quoteName('user_id') . ' = :jokerUserId')
            ->where($db->quoteName('project_id') . ' = :jokerProjectId')
            ->bind(':jokerPredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':jokerUserId', $userId, ParameterType::INTEGER)
            ->bind(':jokerProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    /**
     * Render translated instructions for the prediction entry view.
     * Selects standard or Toto game wording and composes an HTML
     * help section from the component's Joomla language strings.
     * @param int $gameMode Zero for standard mode, nonzero for Toto.
     * @return string HTML-formatted entry help text.
     */
    public function createHelpText(int $gameMode = 0): string
    {
        $mode = $gameMode === 0
            ? Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_STANDARD_MODE')
            : Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_TOTO_MODE');

        return '<hr><h3>' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_TITLE') . '</h3>'
            . '<ul>'
            . '<li>' . Text::sprintf('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_01', '<b>' . $mode . '</b>') . '</li>'
            . '<li>' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_02') . '</li>'
            . '<li>' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_03') . '</li>'
            . '<li>' . Text::_('COM_SPORTSMANAGEMENT_PRED_ENTRY_HELP_04') . '</li>'
            . '</ul><hr>';
    }

    /**
     * Detect the frontend request for creating a prediction member.
     * Checks Joomla's numeric s parameter without accepting arbitrary
     * non-numeric values as an enabled request.
     * @return bool Whether the new-member view was requested.
     */
    public function isNewMemberRequest(): bool
    {
        return $this->siteApplication()->getInput()->getInt('s', 0) === 1;
    }

    /**
     * Detect completion of the prediction entry flow.
     * Checks the eok request parameter to select the successful
     * submission state used by the frontend template.
     * @return bool Whether the completed-entry flag is set.
     */
    public function isEntryDoneRequest(): bool
    {
        return $this->siteApplication()->getInput()->getInt('eok', 0) === 1;
    }

    /**
     * Check whether any fixture in this round remains open for editing.
     * Uses the already-calculated editable flag on each fixture to
     * decide if round-level extra predictions should still be shown.
     * @param array<int,object> $matches Prepared prediction fixtures.
     * @return bool True when at least one fixture permits editing.
     */
    public function isRoundExtrasEditable(array $matches): bool
    {
        foreach ($matches as $match) {
            if (!empty($match->editable)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine whether an individual fixture may still be tipped.
     * Allows an administrator tip override when configured; otherwise
     * rejects matches with results or expired deadlines, using the
     * project timezone and the configured round closing rule.
     * @param object $match Fixture being evaluated.
     * @param object $project Prediction project with timezone and start settings.
     * @param object $member Selected prediction member.
     * @param array $config Effective entry rules.
     * @return bool Whether the tip can be changed.
     */
    protected function isMatchEditable(object $match, object $project, object $member, array $config): bool
    {
        if ($this->isAllowedAdmin() && !empty($member->admintipp)) {
            return true;
        }

        if ($this->matchHasResult($match)) {
            return false;
        }

        $deadline = $this->getMatchDeadline($match, $project, $config);
        if (!$deadline) {
            return false;
        }

        try {
            $timezone = $this->projectTimezone($project);
            return new DateTimeImmutable('now', $timezone) < $deadline;
        } catch (\Throwable) {
            return time() < $deadline->getTimestamp();
        }
    }

    /**
     * Validate configured match, team or round selector identifiers.
     * Parses numeric Joomla IDs and optional ID:alias slugs, rejects
     * partial casts and integer overflow, and deduplicates positive IDs
     * before they are used in SQL filters.
     * @param mixed $value Raw configured IDs or delimited selector string.
     * @return array<int> Unique validated positive IDs.
     */
    protected function normaliseIdList(mixed $value): array
    {
        if (is_array($value)) {
            $values = $value;
        } elseif (is_int($value) || is_string($value)) {
            $values = preg_split('/[;,\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } else {
            return [];
        }

        $ids = [];
        foreach ($values as $item) {
            if (!is_int($item) && !is_string($item)) {
                continue;
            }

            $raw = trim((string) $item);
            if (!preg_match('/^([0-9]+)(?::[^:\s,;]+)?$/D', $raw, $match)) {
                continue;
            }

            $digits = ltrim($match[1], '0');
            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            if ($id !== false) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Parse a legacy prediction-member numeric ID or ID:alias slug.
     * Rejects arrays, malformed strings, negative values and oversized
     * identifiers instead of relying on partial integer casting.
     * @param mixed $value Raw member selection.
     * @return int Positive valid member ID or zero.
     */
    protected function extractMemberId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);
        if (!preg_match('/^([0-9]+)(?::[^:\s,;]+)?$/D', $raw, $matches)) {
            return 0;
        }

        $digits = ltrim($matches[1], '0');
        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Construct a safe placeholder for missing or unauthorised members.
     * Sets membership, user and permission fields to zero so site views
     * do not accidentally treat a guest as an approved prediction member.
     * @return object Empty prediction membership record.
     */
    private function emptyMember(): object
    {
        return (object) ['id' => 0, 'pmID' => 0, 'user_id' => 0, 'approved' => 0, 'admintipp' => 0];
    }

    /**
     * Read an optional configured list of rounds available for tipping.
     * Only applies the selection when the entry form enables restricted
     * round picking and validates all resulting IDs before querying.
     * @return array<int> Allowed round IDs, or empty for no restriction.
     */
    private function allowedRoundIds(): array
    {
        $config = $this->getEntryConfig();
        if (empty($config['use_pred_select_rounds'])) {
            return [];
        }
        return $this->normaliseIdList($config['predictionroundid'] ?? null);
    }

    /**
     * Confirm that a round is assigned to the selected project.
     * Uses both identifiers as bound SQL parameters to prevent selecting
     * a round from an unrelated SportsManagement project.
     * @param int $projectId Current prediction project.
     * @param int $roundId Round to validate.
     * @return bool Whether the round belongs to the project.
     */
    private function roundBelongsToProject(int $projectId, int $roundId): bool
    {
        if ($projectId <= 0 || $roundId <= 0) {
            return false;
        }
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = :belongsRoundId')
            ->where($db->quoteName('project_id') . ' = :belongsProjectId')
            ->bind(':belongsRoundId', $roundId, ParameterType::INTEGER)
            ->bind(':belongsProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return (bool) $db->loadResult();
    }

    /**
     * Calculate a fixture's final prediction submission instant.
     * Uses the match start, first fixture of a prediction round or project
     * start according to configured closing rules, and adjusts using
     * the project's timezone and allowed closing-time offset.
     * @param object $match Fixture date and round identifier.
     * @param object $project Prediction project with start date/time.
     * @param array $config Entry closing-time configuration.
     * @return DateTimeImmutable|null Deadline, or null when no valid date exists.
     */
    private function getMatchDeadline(object $match, object $project, array $config): ?DateTimeImmutable
    {
        $baseDate = (string) ($match->match_date ?? '');
        if ($baseDate === '' || $baseDate === '0000-00-00 00:00:00') {
            return null;
        }

        $rule = $this->getRoundDeadlineRule((int) ($match->round_id ?? 0), (int) ($project->project_id ?? 0));
        if ($rule === 'FIRSTMATCH_OF_TIPPGAME') {
            $baseDate = trim((string) ($project->start_date ?? '') . ' ' . (string) ($project->start_time ?? '00:00:00'));
        } elseif ($rule === 'FIRSTMATCH_OF_TIPPROUND') {
            $firstMatch = $this->getFirstRoundMatchDate((int) ($match->round_id ?? 0));
            if ($firstMatch !== '') {
                $baseDate = $firstMatch;
            }
        }

        try {
            $deadline = new DateTimeImmutable($baseDate, $this->projectTimezone($project));
            $seconds = max(0, (int) ($config['closing_time'] ?? 0));
            return $seconds > 0 ? $deadline->sub(new DateInterval('PT' . $seconds . 'S')) : $deadline;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Look up the prediction deadline rule for a game/project round.
     * Reads the published tip-round configuration and falls back to
     * beginning-of-match closing when no specific rule is stored.
     * @param int $roundId Round whose deadline is requested.
     * @param int $projectId Project owning the round.
     * @return string Configured deadline strategy.
     */
    private function getRoundDeadlineRule(int $roundId, int $projectId): string
    {
        if ($roundId <= 0 || $projectId <= 0) {
            return 'BEGIN_OF_MATCH';
        }
        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('rien_ne_va_plus'))
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = :deadlinePredictionId')
            ->where($db->quoteName('project_id') . ' = :deadlineProjectId')
            ->where($db->quoteName('round_id') . ' = :deadlineRoundId')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':deadlinePredictionId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':deadlineProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':deadlineRoundId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return (string) ($db->loadResult() ?: 'BEGIN_OF_MATCH');
    }

    /**
     * Find the earliest published, non-cancelled fixture in a round.
     * Uses a bound round ID and SQL MIN() to establish the closing
     * deadline when predictions close at the first match of the round.
     * @param int $roundId Selected match round identifier.
     * @return string Earliest scheduled match date or empty string.
     */
    private function getFirstRoundMatchDate(int $roundId): string
    {
        if ($roundId <= 0) {
            return '';
        }
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('MIN(' . $db->quoteName('match_date') . ')')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = :firstRoundMatchId')
            ->where($db->quoteName('published') . ' = 1')
            ->where('(' . $db->quoteName('cancel') . ' IS NULL OR ' . $db->quoteName('cancel') . ' = 0)')
            ->bind(':firstRoundMatchId', $roundId, ParameterType::INTEGER);
        $db->setQuery($query);
        return (string) ($db->loadResult() ?: '');
    }

    /**
     * Resolve the prediction project's configured timezone safely.
     * Builds a DateTimeZone from project settings and uses UTC when
     * the stored timezone string is invalid.
     * @param object $project Prediction project timezone configuration.
     * @return DateTimeZone Effective timezone for entry deadlines.
     */
    private function projectTimezone(object $project): DateTimeZone
    {
        try {
            return new DateTimeZone((string) ($project->timezone ?? 'UTC'));
        } catch (\Throwable) {
            return new DateTimeZone('UTC');
        }
    }

    /**
     * Check whether a fixture already contains scored or decided results.
     * Looks at regular and decision scores for either team so completed
     * matches cannot be edited after recorded results are available.
     * @param object $match Fixture result fields.
     * @return bool Whether any match result is present.
     */
    private function matchHasResult(object $match): bool
    {
        return $match->team1_result !== null
            || $match->team2_result !== null
            || $match->team1_result_decision !== null
            || $match->team2_result_decision !== null;
    }

    /**
     * Choose the preferred team-name field for the prediction table.
     * Accepts only full, middle or short-name settings and falls back
     * to the stored full team name when a selected variant is unavailable.
     * @param object $row Prepared match/team row.
     * @param string $side Team field prefix for the match.
     * @param array $config Prediction display settings.
     * @return string Trimmed team display name.
     */
    private function teamDisplayName(object $row, string $side, array $config): string
    {
        $field = (string) ($config['prediction_team_name'] ?? 'short_name');
        if (!in_array($field, ['name', 'short_name', 'middle_name'], true)) {
            $field = 'short_name';
        }
        $property = $side . '_' . $field;
        $fallback = $side . '_name';
        return trim((string) ($row->{$property} ?? $row->{$fallback} ?? ''));
    }

    /**
     * Find the requested club logo variant in a match row.
     * Only accepts configured small, middle and large logo fields,
     * returning an empty path when logos are disabled or absent.
     * @param object $row Match/team presentation fields.
     * @param string $side Team field prefix for the match.
     * @param array $config Logo display settings.
     * @return string Configured logo path or empty string.
     */
    private function teamLogo(object $row, string $side, array $config): string
    {
        $choice = (string) ($config['show_logo_small'] ?? 'no_logo');
        if (!in_array($choice, ['logo_small', 'logo_middle', 'logo_big'], true)) {
            return '';
        }
        $property = $side . '_' . $choice;
        return trim((string) ($row->{$property} ?? ''));
    }

    /**
     * Format a fixture time using the prediction project's timezone.
     * Leaves invalid or zeroed dates empty and falls back to the
     * original date text if the PHP date parser rejects it.
     * @param string $value Stored match date/time.
     * @param object $project Project timezone configuration.
     * @return string Localised display date or original value.
     */
    private function formatMatchDate(string $value, object $project): string
    {
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return '';
        }
        try {
            return (new DateTimeImmutable($value, $this->projectTimezone($project)))->format('D d.m.Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    /**
     * Aggregate submitted tendency tips for selected match identifiers.
     * Uses a bound prediction game and integer match ID filter, groups
     * by match and tendency, and returns counts for optional table stats.
     * @param array $matchIds Fixture IDs for the current entry round.
     * @return array Grouped tendency totals by match.
     */
    private function getTipTendencies(array $matchIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $matchIds)));
        if (!$ids || $this->predictionGameId <= 0) {
            return [];
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('match_id'),
                $db->quoteName('tipp'),
                'COUNT(*) AS total',
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('prediction_id') . ' = :tendencyPredictionId')
            ->whereIn($db->quoteName('match_id'), $ids, ParameterType::INTEGER)
            ->where($db->quoteName('tipp') . ' IS NOT NULL')
            ->group([$db->quoteName('match_id'), $db->quoteName('tipp')])
            ->bind(':tendencyPredictionId', $predictionGameId, ParameterType::INTEGER);
        $db->setQuery($query);

        $stats = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $matchId = (int) $row->match_id;
            $stats[$matchId] ??= ['total' => 0, 'home' => 0, 'draw' => 0, 'away' => 0];
            $count = (int) $row->total;
            $stats[$matchId]['total'] += $count;
            if ((string) $row->tipp === '1') {
                $stats[$matchId]['home'] += $count;
            } elseif ((string) $row->tipp === '2') {
                $stats[$matchId]['away'] += $count;
            } elseif ((string) $row->tipp === '0') {
                $stats[$matchId]['draw'] += $count;
            }
        }
        return $stats;
    }
}
