<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Joomla\CMS\Language\Text;

class PredictionentryModel extends SportsManagementPredictionReadModel
{
    private ?object $entryMember = null;
    private ?object $entryProject = null;
    private ?array $entryConfig = null;

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

    public function getEntryMember(): object
    {
        if ($this->entryMember !== null) {
            return $this->entryMember;
        }

        $identityId = (int) $this->siteApplication()->getIdentity()->id;
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

    public function getSelectedMemberNumericId(): int
    {
        $member = $this->getEntryMember();
        return $this->extractMemberId($member->pmID ?? $member->id ?? 0);
    }

    public function isCurrentUserMember(): bool
    {
        $identityId = (int) $this->siteApplication()->getIdentity()->id;
        if ($identityId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $identityId);
        $db->setQuery($query, 0, 1);
        return (bool) $db->loadResult();
    }

    public function isNotApprovedCurrentMember(): bool
    {
        $identityId = (int) $this->siteApplication()->getIdentity()->id;
        if ($identityId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('approved'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $identityId);
        $db->setQuery($query, 0, 1);
        $approved = $db->loadResult();
        return $approved !== null && (int) $approved !== 1;
    }

    public function canActAsEntryMember(?object $member = null): bool
    {
        $member ??= $this->getEntryMember();
        $memberId = $this->extractMemberId($member->pmID ?? $member->id ?? 0);
        $memberUserId = (int) ($member->user_id ?? 0);
        if ($memberId <= 0 || $memberUserId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $identityId = (int) $this->siteApplication()->getIdentity()->id;
        if ($identityId === $memberUserId) {
            return (int) ($member->approved ?? 0) === 1;
        }

        return $this->isAllowedAdmin();
    }

    public function getMemberOptions(): array
    {
        if (!$this->isAllowedAdmin() || $this->predictionGameId <= 0) {
            return [];
        }

        $config = $this->getPredictionTemplateConfig('predictionusers');
        $nameField = !empty($config['show_full_name']) ? 'name' : 'username';
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pm.id', 'value'),
                $db->quoteName('u.' . $nameField, 'text'),
                $db->quoteName('pm.approved'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId)
            ->order($db->quoteName('u.' . $nameField) . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

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

    public function getRoundOptions(): array
    {
        $projectId = $this->getProjectId();
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->order($db->quoteName('id') . ' ASC');

        $allowedRoundIds = $this->allowedRoundIds();
        if ($allowedRoundIds) {
            $query->where($db->quoteName('id') . ' IN (' . implode(',', $allowedRoundIds) . ')');
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getEntryMatches(): array
    {
        $project = $this->getEntryProject();
        $member = $this->getEntryMember();
        $projectId = $this->getProjectId();
        $roundId = $this->getRoundId();
        $memberUserId = (int) ($member->user_id ?? 0);

        if (!$project || $projectId <= 0 || $roundId <= 0 || $memberUserId <= 0) {
            return [];
        }

        $config = $this->getEntryConfig();
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
                . ' AND ' . $db->quoteName('pr.prediction_id') . ' = ' . $this->predictionGameId
                . ' AND ' . $db->quoteName('pr.user_id') . ' = ' . $memberUserId
                . ' AND ' . $db->quoteName('pr.project_id') . ' = ' . $projectId)
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pth') . ' ON ' . $db->quoteName('pth.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'sth') . ' ON ' . $db->quoteName('sth.id') . ' = ' . $db->quoteName('pth.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 'th') . ' ON ' . $db->quoteName('th.id') . ' = ' . $db->quoteName('sth.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'ch') . ' ON ' . $db->quoteName('ch.id') . ' = ' . $db->quoteName('th.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pta') . ' ON ' . $db->quoteName('pta.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'sta') . ' ON ' . $db->quoteName('sta.id') . ' = ' . $db->quoteName('pta.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 'ta') . ' ON ' . $db->quoteName('ta.id') . ' = ' . $db->quoteName('sta.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'ca') . ' ON ' . $db->quoteName('ca.id') . ' = ' . $db->quoteName('ta.club_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('r.id') . ' = ' . $roundId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.match_date') . " <> '0000-00-00 00:00:00'")
            ->where('(' . $db->quoteName('m.cancel') . ' IS NULL OR ' . $db->quoteName('m.cancel') . ' = 0)')
            ->order([$db->quoteName('m.match_date') . ' ASC', $db->quoteName('m.id') . ' ASC']);

        $allowedMatchIds = !empty($config['use_pred_select_matches'])
            ? $this->normaliseIdList($config['predictionmatchid'] ?? null)
            : [];
        if (!empty($config['use_pred_select_matches']) && !$allowedMatchIds) {
            return [];
        }
        if ($allowedMatchIds) {
            $query->where($db->quoteName('m.id') . ' IN (' . implode(',', $allowedMatchIds) . ')');
        }

        $allowedProjectTeams = !empty($config['use_pred_select_proteams'])
            ? $this->normaliseIdList($config['predictionproteamid'] ?? null)
            : [];
        if (!empty($config['use_pred_select_proteams']) && !$allowedProjectTeams) {
            return [];
        }
        if ($allowedProjectTeams) {
            $ids = implode(',', $allowedProjectTeams);
            $query->where('(' . $db->quoteName('m.projectteam1_id') . ' IN (' . $ids . ') OR '
                . $db->quoteName('m.projectteam2_id') . ' IN (' . $ids . '))');
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

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_prediction_result_round'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $userId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('round_id') . ' = ' . $roundId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: $empty;
    }

    public function getMemberProjectJokerCount(): int
    {
        $member = $this->getEntryMember();
        $userId = (int) ($member->user_id ?? 0);
        $projectId = $this->getProjectId();
        if ($userId <= 0 || $projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COALESCE(SUM(' . $db->quoteName('joker') . '), 0)')
            ->from($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('user_id') . ' = ' . $userId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query);
        return (int) $db->loadResult();
    }

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

    public function isNewMemberRequest(): bool
    {
        return $this->siteApplication()->getInput()->getInt('s', 0) === 1;
    }

    public function isEntryDoneRequest(): bool
    {
        return $this->siteApplication()->getInput()->getInt('eok', 0) === 1;
    }

    public function isRoundExtrasEditable(array $matches): bool
    {
        foreach ($matches as $match) {
            if (!empty($match->editable)) {
                return true;
            }
        }
        return false;
    }

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

    protected function normaliseIdList(mixed $value): array
    {
        if (is_array($value)) {
            $values = $value;
        } elseif (is_scalar($value)) {
            $values = preg_split('/[;,\s]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } else {
            return [];
        }

        $ids = [];
        foreach ($values as $item) {
            if (is_scalar($item) && preg_match('/^\s*(\d+)/', (string) $item, $match)) {
                $id = (int) $match[1];
                if ($id > 0) {
                    $ids[$id] = $id;
                }
            }
        }
        return array_values($ids);
    }

    protected function extractMemberId(mixed $value): int
    {
        if (is_int($value)) {
            return max(0, $value);
        }
        return max(0, (int) strtok((string) $value, ':'));
    }

    private function emptyMember(): object
    {
        return (object) ['id' => 0, 'pmID' => 0, 'user_id' => 0, 'approved' => 0, 'admintipp' => 0];
    }

    private function allowedRoundIds(): array
    {
        $config = $this->getEntryConfig();
        if (empty($config['use_pred_select_rounds'])) {
            return [];
        }
        return $this->normaliseIdList($config['predictionroundid'] ?? null);
    }

    private function roundBelongsToProject(int $projectId, int $roundId): bool
    {
        if ($projectId <= 0 || $roundId <= 0) {
            return false;
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        return (bool) $db->loadResult();
    }

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

    private function getRoundDeadlineRule(int $roundId, int $projectId): string
    {
        if ($roundId <= 0 || $projectId <= 0) {
            return 'BEGIN_OF_MATCH';
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('rien_ne_va_plus'))
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('project_id') . ' = ' . $projectId)
            ->where($db->quoteName('round_id') . ' = ' . $roundId)
            ->where($db->quoteName('published') . ' = 1');
        $db->setQuery($query, 0, 1);
        return (string) ($db->loadResult() ?: 'BEGIN_OF_MATCH');
    }

    private function getFirstRoundMatchDate(int $roundId): string
    {
        if ($roundId <= 0) {
            return '';
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('MIN(' . $db->quoteName('match_date') . ')')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('round_id') . ' = ' . $roundId)
            ->where($db->quoteName('published') . ' = 1')
            ->where('(' . $db->quoteName('cancel') . ' IS NULL OR ' . $db->quoteName('cancel') . ' = 0)');
        $db->setQuery($query);
        return (string) ($db->loadResult() ?: '');
    }

    private function projectTimezone(object $project): DateTimeZone
    {
        try {
            return new DateTimeZone((string) ($project->timezone ?? 'UTC'));
        } catch (\Throwable) {
            return new DateTimeZone('UTC');
        }
    }

    private function matchHasResult(object $match): bool
    {
        return $match->team1_result !== null
            || $match->team2_result !== null
            || $match->team1_result_decision !== null
            || $match->team2_result_decision !== null;
    }

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

    private function teamLogo(object $row, string $side, array $config): string
    {
        $choice = (string) ($config['show_logo_small'] ?? 'no_logo');
        if (!in_array($choice, ['logo_small', 'logo_middle', 'logo_big'], true)) {
            return '';
        }
        $property = $side . '_' . $choice;
        return trim((string) ($row->{$property} ?? ''));
    }

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

    private function getTipTendencies(array $matchIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $matchIds)));
        if (!$ids || $this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('match_id'),
                $db->quoteName('tipp'),
                'COUNT(*) AS total',
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('match_id') . ' IN (' . implode(',', $ids) . ')')
            ->where($db->quoteName('tipp') . ' IS NOT NULL')
            ->group([$db->quoteName('match_id'), $db->quoteName('tipp')]);
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
