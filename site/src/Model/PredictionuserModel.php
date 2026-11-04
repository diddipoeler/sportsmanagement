<?php
/**
 * Joomla 5/6 prediction user editing model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use DateTimeImmutable;
use DateTimeZone;
use Joomla\Database\ParameterType;

class PredictionuserModel extends SportsManagementPredictionReadModel
{
    /**
     * Return the prediction-game member currently selected for editing.
     * Delegates membership lookup to the native prediction read model,
     * preserving its selected game and member context for the controller.
     * @return object Current prediction-member record.
     */
    public function getEditableMember(): object
    {
        return $this->getPredictionMember();
    }

    /**
     * Extract the validated numeric identifier of the selected member.
     * Normalises legacy ID:alias member links and rejects invalid or
     * oversized identifiers before they are reused in site routes.
     * @return int Positive member ID, or zero when invalid.
     */
    public function getSelectedMemberNumericId(): int
    {
        $member = $this->getEditableMember();
        return $this->memberId($member->pmID ?? $member->id ?? 0);
    }

    /**
     * Check ownership or administrator permission for a prediction member.
     * Rejects missing membership, invalid prediction games and anonymous Joomla
     * identities; grants access only to the owning user or an allowed
     * prediction administrator.
     * @param object|null $member Member to check, or current selection.
     * @return bool True if the active user can edit this member.
     */
    public function canEditMember(?object $member = null): bool
    {
        $member ??= $this->getEditableMember();
        $userId = (int) ($member->user_id ?? 0);
        if ($userId <= 0 || $this->predictionGameId <= 0) {
            return false;
        }

        $identity = $this->siteApplication()->getIdentity();

        // An anonymous or not-yet-initialised identity must not gain edit access.
        if ($identity === null) {
            return false;
        }

        $identityId = (int) ($identity->id ?? 0);
        return ($identityId > 0 && $identityId === $userId) || $this->isAllowedAdmin();
    }

    /**
     * Merge the prediction site's profile editing template options.
     * Combines overall, ranking and prediction-user settings so the
     * frontend form uses the same presentation configuration as views.
     * @return array Merged prediction member form configuration.
     */
    public function getEditConfig(): array
    {
        return array_merge(
            $this->getPredictionTemplateConfig('predictionoverall'),
            $this->getPredictionTemplateConfig('predictionranking'),
            $this->getPredictionTemplateConfig('predictionusers')
        );
    }

    /**
     * Provide the prediction-group options for a profile editor.
     * Returns the game's defined groups as Joomla select entries.
     * @return array Available prediction group options.
     */
    public function getPredictionGroupOptions(): array
    {
        return $this->getPredictionGroupList();
    }

    /**
     * List project-specific team options for prediction favourites.
     * Joins project teams through their season and club team records,
     * filters with a bound project identifier, and orders by team name.
     * @param int $projectId Project to load team choices for.
     * @return array<int,object> Team value/text entries for this project.
     */
    public function getProjectTeamOptions(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->where($db->quoteName('pt.project_id') . ' = :optionProjectId')
            ->bind(':optionProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Build editable predictions for each configured game project.
     * Parses saved favourite, champion and final-four selections, loads
     * each project's eligible teams and determines whether the project
     * is still open to competitive member changes.
     * @param object $member Prediction member containing stored selections.
     * @return array<int,array> Per-project form metadata and saved choices.
     */
    public function getEditProjects(object $member): array
    {
        $favourites = $this->parseMemberTeamSelections((string) ($member->fav_team ?? ''), false);
        $champions = $this->parseMemberTeamSelections((string) ($member->champ_tipp ?? ''), false);
        $final4 = $this->parseMemberTeamSelections((string) ($member->final4_tipp ?? ''), true);
        $rows = [];

        foreach ($this->getPredictionProjects() as $project) {
            $projectId = (int) ($project->project_id ?? 0);
            if ($projectId <= 0) {
                continue;
            }

            $rows[] = [
                'project' => $project,
                'project_id' => $projectId,
                'project_name' => (string) ($project->projectName ?? $projectId),
                'teams' => $this->getProjectTeamOptions($projectId),
                'fav_team' => (int) ($favourites[$projectId] ?? 0),
                'champ_tipp' => (int) ($champions[$projectId] ?? 0),
                'final4_tipp' => array_values(array_slice((array) ($final4[$projectId] ?? []), 0, 4)),
                'champ_enabled' => !empty($project->champ),
                'competitive_open' => $this->isProjectOpenForCompetitiveEdits($project),
            ];
        }

        return $rows;
    }

    /**
     * Determine whether a member may switch prediction groups.
     * Permits changes only while all configured prediction projects
     * remain open for competitive edits; missing projects deny changes.
     * @return bool Whether the group picker may be edited.
     */
    public function canChangeGroup(): bool
    {
        $projects = $this->getPredictionProjects();
        if (!$projects) {
            return false;
        }

        foreach ($projects as $project) {
            if (!$this->isProjectOpenForCompetitiveEdits($project)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check a project's configured prediction start deadline.
     * Converts the start date and time using its configured timezone,
     * rejects missing dates and falls back to a UNIX timestamp when
     * timezone or date parsing raises an exception.
     * @param object $project Project with start date/time and timezone.
     * @return bool Whether predictions are still editable.
     */
    public function isProjectOpenForCompetitiveEdits(object $project): bool
    {
        $startDate = trim((string) ($project->start_date ?? ''));
        if ($startDate === '' || $startDate === '0000-00-00') {
            return false;
        }

        $startTime = trim((string) ($project->start_time ?? ''));
        if ($startTime === '' || $startTime === '00:00:00') {
            $startTime = '00:00:00';
        }

        try {
            $timezone = new DateTimeZone((string) ($project->timezone ?? 'UTC'));
            $start = new DateTimeImmutable($startDate . ' ' . $startTime, $timezone);
            return new DateTimeImmutable('now', $timezone) < $start;
        } catch (\Throwable) {
            $timestamp = strtotime($startDate . ' ' . $startTime);
            return $timestamp !== false && time() < $timestamp;
        }
    }

    /**
     * Validate a requested prediction group against current game options.
     * Treats zero as an unset group and otherwise requires an exact
     * match to one of the available group identifiers.
     * @param int $groupId Selected prediction group or zero.
     * @return bool Whether the group may be selected.
     */
    public function isValidGroupId(int $groupId): bool
    {
        if ($groupId === 0) {
            return true;
        }

        foreach ($this->getPredictionGroupList() as $group) {
            if ((int) ($group->value ?? 0) === $groupId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify that a selected team belongs to its prediction project.
     * Treats zero as an unset selection and otherwise performs a bound
     * project/team lookup rather than accepting arbitrary team IDs.
     * @param int $projectId Project whose team is being selected.
     * @param int $projectTeamId Selected project-team identifier.
     * @return bool Whether the team choice is valid.
     */
    public function isValidProjectTeamId(int $projectId, int $projectTeamId): bool
    {
        if ($projectTeamId === 0) {
            return true;
        }
        return $this->getProjectTeamById($projectId, $projectTeamId) !== null;
    }

    /**
     * Decode stored favourite or champion selections for templates.
     * Converts semicolon-separated decimal project/team ID pairs into
     * a map, optionally allowing multiple team selections per project.
     * @param string $raw Stored project,team pairs delimited by semicolons.
     * @param bool $multiple Whether to retain multiple teams per project.
     * @return array<int,mixed> Valid selections indexed by project ID.
     */
    public function selectionMap(string $raw, bool $multiple = false): array
    {
        return $this->parseMemberTeamSelections($raw, $multiple);
    }

    /**
     * Strictly parse legacy project/team pairs without partial integer casts.
     * Trims separators and whitespace, ignores missing, malformed or
     * overflowing IDs and retains either one or several choices per
     * project according to the form field's multiplicity.
     * @param string $raw Stored semicolon-separated project/team values.
     * @param bool $multiple Whether to accumulate team IDs per project.
     * @return array<int,mixed> Accepted ID pairs grouped by project.
     */
    private function parseMemberTeamSelections(string $raw, bool $multiple): array
    {
        $result = [];
        foreach (array_filter(array_map('trim', explode(';', $raw))) as $part) {
            $pair = array_map('trim', explode(',', $part));

            if (count($pair) !== 2) {
                continue;
            }

            // Legacy values use decimal project/team pairs. Never turn a
            // malformed ID such as "17junk" into a valid project or team.
            $projectId = filter_var($pair[0], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $teamId = filter_var($pair[1], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

            if ($projectId === false || $teamId === false) {
                continue;
            }
            if ($multiple) {
                $result[$projectId][] = $teamId;
            } else {
                $result[$projectId] = $teamId;
            }
        }
        return $result;
    }

    /**
     * Resolve a project-team selection within the correct project.
     * Loads at most one project-team row with both IDs bound as integers
     * so a team from an unrelated project cannot pass validation.
     * @param int $projectId Project scope for the selection.
     * @param int $projectTeamId Team relation to verify.
     * @return object|null Matching project-team record or null.
     */
    private function getProjectTeamById(int $projectId, int $projectTeamId): ?object
    {
        if ($projectId <= 0 || $projectTeamId <= 0) {
            return null;
        }
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('pt.id'))
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->where($db->quoteName('pt.project_id') . ' = :validationProjectId')
            ->where($db->quoteName('pt.id') . ' = :validationProjectTeamId')
            ->bind(':validationProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':validationProjectTeamId', $projectTeamId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    /**
     * Parse a positive prediction member ID or Joomla ID:alias slug.
     * Rejects invalid types, partial numeric casts and values outside
     * the PHP integer range before returning a member identifier.
     * @param mixed $value Raw member ID or URL slug.
     * @return int Valid positive member ID, or zero.
     */
    private function memberId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        // Legacy links use ID:alias. Reject partial casts and oversized IDs.
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
}
