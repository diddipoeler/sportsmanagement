<?php
/**
 * Base model for native Joomla 5/6 SportsManagement prediction models.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

abstract class SportsManagementPredictionModel extends SportsManagementModel
{
    protected int $predictionGameId = 0;
    protected int $predictionMemberId = 0;
    protected int $projectId = 0;
    protected int $roundId = 0;
    protected int $fromRoundId = 0;
    protected int $toRoundId = 0;
    protected int $groupId = 0;
    protected int $groupRank = 0;
    protected int $joomlaUserId = 0;
    protected int $databaseSelector = 0;
    protected int $isNewMember = 0;
    protected int $tippEntryDone = 0;
    protected int $type = 0;
    protected int $page = 1;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        $this->predictionGameId = $input->getInt('prediction_id', 0);
        $this->predictionMemberId = $input->getInt('uid', 0);
        $this->projectId = $input->getInt('pj', 0);
        $this->roundId = $input->getInt('r', 0);
        $this->fromRoundId = $input->getInt('from', $this->roundId);
        $this->toRoundId = $input->getInt('to', $this->roundId);
        $this->groupId = $input->getInt('pggroup', 0);
        $this->groupRank = $input->getInt('pggrouprank', 0);
        $this->joomlaUserId = $input->getInt('juid', 0);
        $this->databaseSelector = $input->getInt('cfg_which_database', 0);
        $this->isNewMember = $input->getInt('s', 0);
        $this->tippEntryDone = $input->getInt('eok', 0);
        $this->type = $input->getInt('type', 0);
        $this->page = max(1, $input->getInt('page', 1));
    }

    /**
     * Apply the historical static prediction context to a native model instance.
     */
    public function setLegacyContext(
        int $predictionGameId = 0,
        int $predictionMemberId = 0,
        int $projectId = 0,
        int $roundId = 0,
        int $databaseSelector = 0
    ): void {
        $this->predictionGameId = max(0, $predictionGameId);
        $this->predictionMemberId = max(0, $predictionMemberId);
        $this->projectId = max(0, $projectId);
        $this->roundId = max(0, $roundId);
        $this->fromRoundId = $this->fromRoundId > 0 ? $this->fromRoundId : $this->roundId;
        $this->toRoundId = $this->toRoundId > 0 ? $this->toRoundId : $this->roundId;
        $this->databaseSelector = max(0, $databaseSelector);
        $this->setDatabaseSelector($this->databaseSelector);
    }

    public function getPredictionGameId(): int
    {
        return $this->predictionGameId;
    }

    public function getPredictionMemberId(): int
    {
        return $this->predictionMemberId;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getRoundId(): int
    {
        return $this->roundId;
    }

    public function getFromRoundId(): int
    {
        return $this->fromRoundId;
    }

    public function getToRoundId(): int
    {
        return $this->toRoundId;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getGroupRank(): int
    {
        return $this->groupRank;
    }

    public function getJoomlaUserId(): int
    {
        return $this->joomlaUserId;
    }

    public function getDatabaseSelector(): int
    {
        return $this->databaseSelector;
    }

    public function isNewMemberRequest(): bool
    {
        return $this->isNewMember === 1;
    }

    public function isTippEntryDone(): bool
    {
        return $this->tippEntryDone === 1;
    }

    public function getPredictionType(): int
    {
        return $this->type;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPredictionGame(): ?object
    {
        if ($this->predictionGameId <= 0) {
            return null;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->select("CONCAT_WS(':', id, alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = :predictionGameId')
            ->where($db->quoteName('published') . ' = 1')
            ->bind(':predictionGameId', $predictionGameId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getPredictionTemplateConfig(string $template): array
    {
        $defaults = $this->loadDefaultTemplateConfig($template);
        if ($this->predictionGameId <= 0) {
            return $defaults;
        }

        $params = $this->loadSavedTemplateParams($template, $this->predictionGameId);
        if ($params === null) {
            $game = $this->getPredictionGame();
            $masterId = (int) ($game->master_template ?? 0);
            if ($masterId > 0 && $masterId !== $this->predictionGameId) {
                $params = $this->loadSavedTemplateParams($template, $masterId);
            }
        }

        if ($params === null || $params === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString((string) $params);
            $values = array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            $values = $defaults;
        }

        if ($template === 'predictionoverall') {
            $values += [
                'sort_order_1' => 'points',
                'sort_order_2' => 'correct_tipps',
                'sort_order_3' => 'correct_diffs',
                'sort_order_4' => 'correct_tend',
                'sort_order_5' => 'count_tipps_p',
            ];
        }

        return $values;
    }

    public function getPredictionMember(): object
    {
        $empty = (object) ['id' => 0, 'pmID' => 0, 'user_id' => 0];
        if ($this->predictionGameId <= 0) {
            return $empty;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                "CONCAT_WS(':', pm.id, u.username) AS pmID",
                "CONCAT_WS(':', u.id, u.username) AS joomuserID",
                $db->quoteName('pm.registerDate', 'pmRegisterDate'),
                'pm.*',
                $db->quoteName('u.name'),
                $db->quoteName('u.username'),
                $db->quoteName('pg.id', 'pg_group_id'),
                $db->quoteName('pg.name', 'pg_group_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_prediction_groups', 'pg') . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('pm.group_id'))
            ->where($db->quoteName('pm.prediction_id') . ' = :predictionMemberGameId')
            ->bind(':predictionMemberGameId', $predictionGameId, ParameterType::INTEGER);

        if ($this->predictionMemberId > 0) {
            $predictionMemberId = $this->predictionMemberId;
            $query->where($db->quoteName('pm.id') . ' = :predictionMemberId')
                ->bind(':predictionMemberId', $predictionMemberId, ParameterType::INTEGER);
        } else {
            $userId = (int) $this->siteApplication()->getIdentity()->id;
            if ($userId <= 0) {
                return $empty;
            }
            $query->where($db->quoteName('pm.user_id') . ' = :predictionMemberUserId')
                ->bind(':predictionMemberUserId', $userId, ParameterType::INTEGER);
        }

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: $empty;
    }

    public function getPredictionProjects(): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'pp.*',
                $db->quoteName('p.name', 'projectName'),
                $db->quoteName('p.start_date'),
                $db->quoteName('p.start_time'),
                $db->quoteName('p.timezone'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_project', 'pp'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pp.project_id'))
            ->where($db->quoteName('pp.prediction_id') . ' = :predictionProjectsGameId')
            ->where($db->quoteName('pp.published') . ' = 1')
            ->bind(':predictionProjectsGameId', $predictionGameId, ParameterType::INTEGER);
        $db->setQuery($query);
        $projects = $db->loadObjectList() ?: [];

        foreach ($projects as $project) {
            if (($project->start_date ?? '') === '0000-00-00') {
                $roundProjectId = (int) $project->project_id;
                $roundQuery = $db->createQuery()
                    ->select('MIN(' . $db->quoteName('round_date_first') . ')')
                    ->from($db->quoteName('#__sportsmanagement_round'))
                    ->where($db->quoteName('project_id') . ' = :predictionRoundProjectId')
                    ->bind(':predictionRoundProjectId', $roundProjectId, ParameterType::INTEGER);
                $db->setQuery($roundQuery);
                $project->start_date = $db->loadResult();
            }
        }

        return $projects;
    }

    public function isAllowedAdmin(int $memberUserId = 0): bool
    {
        $user = $this->siteApplication()->getIdentity();
        if ((int) $user->id <= 0) {
            return false;
        }

        if ($memberUserId > 0 && $memberUserId === (int) $user->id) {
            return true;
        }

        $groups = method_exists($user, 'getAuthorisedGroups') ? $user->getAuthorisedGroups() : [];
        if (array_intersect([7, 8], array_map('intval', $groups))) {
            return true;
        }

        $game = $this->getPredictionGame();
        if (!$game || empty($game->admin_tipp)) {
            return false;
        }

        $gameId = (int) $game->id;
        $userId = (int) $user->id;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('user_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_admin'))
            ->where($db->quoteName('prediction_id') . ' = :predictionAdminGameId')
            ->where($db->quoteName('user_id') . ' = :predictionAdminUserId')
            ->bind(':predictionAdminGameId', $gameId, ParameterType::INTEGER)
            ->bind(':predictionAdminUserId', $userId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return (bool) $db->loadResult();
    }

    public function scoreRuleExample(object $project, int $home, int $away, int $tipp, int $tippHome, int $tippAway, bool $joker = false): int
    {
        if ((int) ($project->mode ?? 0) !== 0) {
            $correct = ($home > $away && $tipp === 1)
                || ($home < $away && $tipp === 2)
                || ($home === $away && $tipp === 0);

            return $correct ? (int) ($project->points_tipp ?? 0) : 0;
        }

        $suffix = $joker ? '_joker' : '';
        $pointsTip = (int) ($project->{'points_tipp' . $suffix} ?? 0);
        $correctResult = (int) ($project->{'points_correct_result' . $suffix} ?? 0);
        $correctDiff = (int) ($project->{'points_correct_diff' . $suffix} ?? 0);
        $correctDraw = (int) ($project->{'points_correct_draw' . $suffix} ?? 0);
        $correctTendency = (int) ($project->{'points_correct_tendence' . $suffix} ?? 0);

        if ($home === $tippHome && $away === $tippAway) {
            return $correctResult;
        }
        if ($home === $away && ($home - $away) === ($tippHome - $tippAway)) {
            return $correctDraw;
        }
        if (($home - $away) === ($tippHome - $tippAway)) {
            return $correctDiff;
        }
        if ((($home - $away) > 0 && ($tippHome - $tippAway) > 0)
            || (($home - $away) < 0 && ($tippHome - $tippAway) < 0)) {
            return $correctTendency;
        }

        return $pointsTip;
    }

    private function loadSavedTemplateParams(string $template, int $predictionId): ?string
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_prediction_template'))
            ->where($db->quoteName('template') . ' = :predictionTemplateName')
            ->where($db->quoteName('prediction_id') . ' = :predictionTemplateId')
            ->bind(':predictionTemplateName', $template, ParameterType::STRING)
            ->bind(':predictionTemplateId', $predictionId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();

        return $value === null ? null : (string) $value;
    }

    private function loadDefaultTemplateConfig(string $template): array
    {
        $file = JPATH_SITE . '/components/com_sportsmanagement/settings/default/' . basename($template) . '.xml';
        if (!is_file($file)) {
            return [];
        }

        try {
            $xml = simplexml_load_file($file);
        } catch (\Throwable) {
            return [];
        }
        if ($xml === false) {
            return [];
        }

        $defaults = [];
        foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
            $attributes = $field->attributes();
            if (isset($attributes['default'])) {
                $defaults[(string) $attributes['name']] = (string) $attributes['default'];
            }
        }

        return $defaults;
    }

    /** Return Joomla user ids configured as administrators for one prediction game. */
    public function getPredictionGameAdminIds(int $predictionGameId): array
    {
        if ($predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('user_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_admin'))
            ->where($db->quoteName('prediction_id') . ' = :predictionAdminGameListId')
            ->bind(':predictionAdminGameListId', $predictionGameId, ParameterType::INTEGER);

        $db->setQuery($query);

        return array_map('intval', $db->loadColumn() ?: []);
    }

    /** Return the highest round id used by a project, preserving the legacy contract. */
    public function getProjectLastRoundId(int $projectId): int
    {
        if ($projectId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('MAX(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :predictionProjectRoundId')
            ->bind(':predictionProjectRoundId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /** Check whether the current Joomla user is an approved member of the active prediction game. */
    public function isCurrentUserApprovedMember(): bool
    {
        if ($this->predictionGameId <= 0) {
            return false;
        }

        $userId = (int) $this->siteApplication()->getIdentity()->id;
        if ($userId <= 0) {
            return false;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = :approvedMemberGameId')
            ->where($db->quoteName('user_id') . ' = :approvedMemberUserId')
            ->where($db->quoteName('approved') . ' = 1')
            ->bind(':approvedMemberGameId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':approvedMemberUserId', $userId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return (bool) $db->loadResult();
    }

    /**
     * Return legacy membership status: 0 approved, 1 pending, 2 not registered.
     */
    public function getCurrentUserMembershipStatus(): int
    {
        if ($this->predictionGameId <= 0) {
            return 2;
        }

        $userId = (int) $this->siteApplication()->getIdentity()->id;
        if ($userId <= 0) {
            return 2;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('approved'))
            ->from($db->quoteName('#__sportsmanagement_prediction_member'))
            ->where($db->quoteName('prediction_id') . ' = :membershipStatusGameId')
            ->where($db->quoteName('user_id') . ' = :membershipStatusUserId')
            ->bind(':membershipStatusGameId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':membershipStatusUserId', $userId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $approved = $db->loadResult();

        if ($approved === null) {
            return 2;
        }

        return (int) $approved === 1 ? 0 : 1;
    }

    /** Return one prediction project with the historical start-date fallback. */
    public function getPredictionProjectById(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = :predictionProjectById')
            ->bind(':predictionProjectById', $projectId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        if (($project->start_date ?? '') === '0000-00-00') {
            $roundQuery = $db->createQuery()
                ->select('MIN(' . $db->quoteName('round_date_first') . ')')
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = :predictionProjectStartId')
                ->bind(':predictionProjectStartId', $projectId, ParameterType::INTEGER);
            $db->setQuery($roundQuery);
            $project->start_date = $db->loadResult();
        }

        return $project;
    }

    /** Return legacy round select options for a project. */
    public function getPredictionRoundOptions(int $projectId, string $ordering = 'ASC', array $roundIds = []): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                "CONCAT_WS(':', id, alias) AS value",
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = :predictionRoundOptionsProjectId')
            ->bind(':predictionRoundOptionsProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('id') . ' ' . $direction);

        $ids = array_values(array_filter(array_map('intval', $roundIds), static fn (int $id): bool => $id > 0));
        if ($ids) {
            $query->whereIn($db->quoteName('id'), $ids, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }


    /** Return the active Joomla account for one prediction member. */
    public function getPredictionMemberEmailAddress(int $predictionMemberId): ?object
    {
        if ($predictionMemberId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('u.email'),
                $db->quoteName('u.username'),
                $db->quoteName('u.id', 'user_id'),
            ])
            ->from($db->quoteName('#__users', 'u'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_prediction_member', 'pm')
                . ' ON ' . $db->quoteName('pm.user_id') . ' = ' . $db->quoteName('u.id')
            )
            ->where($db->quoteName('pm.id') . ' = :predictionMemberEmailId')
            ->where($db->quoteName('u.block') . ' = 0')
            ->bind(':predictionMemberEmailId', $predictionMemberId, ParameterType::INTEGER)
            ->order($db->quoteName('u.email') . ' ASC');

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return email addresses for unblocked Joomla users authorised for core administration. */
    public function getSystemAdminEmailAddresses(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('u.id'),
                $db->quoteName('u.email'),
            ])
            ->from($db->quoteName('#__users', 'u'))
            ->where($db->quoteName('u.sendEmail') . ' = 1')
            ->where($db->quoteName('u.block') . ' = 0')
            ->order($db->quoteName('u.email') . ' ASC');

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $emails = [];
        $userFactory = Factory::getContainer()->get(UserFactoryInterface::class);

        foreach ($rows as $row) {
            $user = $userFactory->loadUserById((int) $row->id);

            if ($user->authorise('core.admin')) {
                $emails[] = (string) $row->email;
            }
        }

        return $emails;
    }

    /** Return email addresses for administrators assigned to the active prediction game. */
    public function getPredictionGameAdminEmailAddresses(): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('u.email'))
            ->from($db->quoteName('#__users', 'u'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_prediction_admin', 'pa')
                . ' ON ' . $db->quoteName('pa.user_id') . ' = ' . $db->quoteName('u.id')
            )
            ->where($db->quoteName('u.block') . ' = 0')
            ->where($db->quoteName('u.sendEmail') . ' = 1')
            ->where($db->quoteName('pa.prediction_id') . ' = :predictionAdminEmailGameId')
            ->bind(':predictionAdminEmailGameId', $predictionGameId, ParameterType::INTEGER)
            ->order($db->quoteName('u.email') . ' ASC');

        $db->setQuery($query);

        return array_values(array_filter(array_map('strval', $db->loadColumn() ?: [])));
    }

    /** Count stored prediction results for one Joomla user in the active prediction game. */
    public function getMemberPredictionTotalCount(int $userId): int
    {
        if ($this->predictionGameId <= 0 || $userId <= 0) {
            return 0;
        }

        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_prediction_result', 'pr'))
            ->where($db->quoteName('pr.prediction_id') . ' = :memberResultCountGameId')
            ->where($db->quoteName('pr.user_id') . ' = :memberResultCountUserId')
            ->bind(':memberResultCountGameId', $predictionGameId, ParameterType::INTEGER)
            ->bind(':memberResultCountUserId', $userId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }


    /**
     * Return a fallback round slug only when the requested legacy round is missing.
     */
    public function resolveLegacyRoundSlug(int $projectId, int $roundId): ?string
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();

        if ($roundId > 0) {
            $query = $db->createQuery()
                ->select($db->quoteName('roundcode'))
                ->from($db->quoteName('#__sportsmanagement_round'))
                ->where($db->quoteName('project_id') . ' = :roundCheckProjectId')
                ->where($db->quoteName('id') . ' = :roundCheckRoundId')
                ->bind(':roundCheckProjectId', $projectId, ParameterType::INTEGER)
                ->bind(':roundCheckRoundId', $roundId, ParameterType::INTEGER);

            $db->setQuery($query, 0, 1);

            if ($db->loadResult()) {
                return null;
            }
        }

        return $this->getProjectCurrentRoundSlug($projectId);
    }

    /** Return champion evaluation settings for the active prediction project. */
    public function getPredictionChampionSettings(): ?object
    {
        if ($this->predictionGameId <= 0 || $this->projectId <= 0) {
            return null;
        }

        $enabled = 1;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('league_champ'),
                $db->quoteName('points_tipp_champ'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = :championPredictionId')
            ->where($db->quoteName('project_id') . ' = :championProjectId')
            ->where($db->quoteName('champ') . ' = :championEnabled')
            ->bind(':championPredictionId', $this->predictionGameId, ParameterType::INTEGER)
            ->bind(':championProjectId', $this->projectId, ParameterType::INTEGER)
            ->bind(':championEnabled', $enabled, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return final-four evaluation settings for the active prediction project. */
    public function getPredictionFinal4Settings(): ?object
    {
        if ($this->predictionGameId <= 0 || $this->projectId <= 0) {
            return null;
        }

        $enabled = 1;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('league_final4'),
                $db->quoteName('points_tipp_final4'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_project'))
            ->where($db->quoteName('prediction_id') . ' = :final4PredictionId')
            ->where($db->quoteName('project_id') . ' = :final4ProjectId')
            ->where($db->quoteName('final4') . ' = :final4Enabled')
            ->bind(':final4PredictionId', $this->predictionGameId, ParameterType::INTEGER)
            ->bind(':final4ProjectId', $this->projectId, ParameterType::INTEGER)
            ->bind(':final4Enabled', $enabled, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** Return published prediction round rating overrides keyed by round id. */
    public function getPredictionRoundRatingCharts(int $predictionId): array
    {
        if ($predictionId <= 0) {
            return [];
        }

        $published = 1;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('round_id'),
                $db->quoteName('points_tipp'),
                $db->quoteName('points_correct_result'),
                $db->quoteName('points_correct_diff'),
                $db->quoteName('points_correct_draw'),
                $db->quoteName('points_correct_tendence'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround', 'ptr'))
            ->where($db->quoteName('ptr.prediction_id') . ' = :ratingPredictionId')
            ->where($db->quoteName('ptr.published') . ' = :ratingPublished')
            ->bind(':ratingPredictionId', $predictionId, ParameterType::INTEGER)
            ->bind(':ratingPublished', $published, ParameterType::INTEGER);

        $db->setQuery($query);

        return $db->loadObjectList('round_id') ?: [];
    }

    /** Return the current project round slug, falling back to the first project round. */
    public function getProjectCurrentRoundSlug(int $projectId): ?string
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select("CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ')')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.current_round') . ' = ' . $db->quoteName('r.id')
            )
            ->where($db->quoteName('p.id') . ' = :currentRoundProjectId')
            ->bind(':currentRoundProjectId', $projectId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $slug = $db->loadResult();

        if ($slug) {
            return (string) $slug;
        }

        $fallbackQuery = $db->createQuery()
            ->select("CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ')')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :fallbackRoundProjectId')
            ->bind(':fallbackRoundProjectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('r.id') . ' ASC');

        $db->setQuery($fallbackQuery, 0, 1);
        $slug = $db->loadResult();

        return $slug ? (string) $slug : null;
    }

    /** Count jokers already used by one member in the active prediction game. */
    public function getMemberPredictionJokerCount(int $userId, int $projectId = 0): int
    {
        if ($this->predictionGameId <= 0 || $userId <= 0) {
            return 0;
        }

        $joker = 1;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__sportsmanagement_prediction_result'))
            ->where($db->quoteName('prediction_id') . ' = :jokerPredictionId')
            ->where($db->quoteName('user_id') . ' = :jokerUserId')
            ->where($db->quoteName('joker') . ' = :jokerEnabled')
            ->bind(':jokerPredictionId', $this->predictionGameId, ParameterType::INTEGER)
            ->bind(':jokerUserId', $userId, ParameterType::INTEGER)
            ->bind(':jokerEnabled', $joker, ParameterType::INTEGER);

        if ($projectId > 0) {
            $query
                ->where($db->quoteName('project_id') . ' = :jokerProjectId')
                ->bind(':jokerProjectId', $projectId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return (int) $db->loadResult();
    }


    /** Return a configured team display field for one project-team id. */
    public function getProjectTeamDisplayValue(int $projectTeamId, string $field = 'name'): ?string
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $field = in_array($field, ['name', 'short_name', 'middle_name'], true) ? $field : 'name';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('t.' . $field))
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.id') . ' = :displayProjectTeamId')
            ->bind(':displayProjectTeamId', $projectTeamId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();

        return $value !== null ? (string) $value : null;
    }

    /** Return one permitted club logo field for a project-team id. */
    public function getProjectTeamClubLogo(int $projectTeamId, string $field = 'logo_big'): ?string
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $field = in_array($field, ['logo_big', 'logo_middle', 'logo_small'], true) ? $field : 'logo_big';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('c.' . $field))
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.id') . ' = :logoProjectTeamId')
            ->bind(':logoProjectTeamId', $projectTeamId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();

        return $value !== null ? (string) $value : null;
    }

    /** Return the club country for a project-team id. */
    public function getProjectTeamClubCountry(int $projectTeamId): ?string
    {
        if ($projectTeamId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('c.country'))
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.id') . ' = :countryProjectTeamId')
            ->bind(':countryProjectTeamId', $projectTeamId, ParameterType::INTEGER);

        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();

        return $value !== null ? (string) $value : null;
    }

    /** Return prediction group options for legacy select lists. */
    public function getPredictionGroupOptions(): array
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_groups'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Return prediction member options with the historical visibility filter. */
    public function getPredictionMemberOptions(bool $showFullName = false, ?int $activeUserId = null): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $nameField = $showFullName ? 'name' : 'username';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pm.id', 'value'),
                $db->quoteName('u.' . $nameField, 'text'),
                $db->quoteName('pg.id', 'pg_group_id'),
                $db->quoteName('pg.name', 'pg_group_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_member', 'pm'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('pm.user_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_prediction_groups', 'pg')
                . ' ON ' . $db->quoteName('pg.id') . ' = ' . $db->quoteName('pm.group_id')
            )
            ->where($db->quoteName('pm.prediction_id') . ' = :memberOptionsPredictionId')
            ->bind(':memberOptionsPredictionId', $this->predictionGameId, ParameterType::INTEGER);

        if ($activeUserId !== null) {
            $approved = 1;
            $showProfile = 1;
            $query
                ->where($db->quoteName('pm.approved') . ' = :memberOptionsApproved')
                ->where(
                    '(' . $db->quoteName('pm.show_profile') . ' = :memberOptionsShowProfile'
                    . ' OR ' . $db->quoteName('pm.user_id') . ' = :memberOptionsActiveUserId)'
                )
                ->bind(':memberOptionsApproved', $approved, ParameterType::INTEGER)
                ->bind(':memberOptionsShowProfile', $showProfile, ParameterType::INTEGER)
                ->bind(':memberOptionsActiveUserId', $activeUserId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Return projects assigned to one prediction game in legacy option shape. */
    public function getPredictionProjectNameOptions(int $predictionId, string $ordering = 'ASC'): array
    {
        if ($predictionId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ppj.id'),
                $db->quoteName('pj.id', 'prediction_id'),
                $db->quoteName('pj.name', 'pjName'),
                "CONCAT_WS(':', " . $db->quoteName('pj.id') . ', ' . $db->quoteName('pj.alias') . ') AS ' . $db->quoteName('slug'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'pj'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_prediction_project', 'ppj')
                . ' ON ' . $db->quoteName('ppj.project_id') . ' = ' . $db->quoteName('pj.id')
            )
            ->where($db->quoteName('ppj.prediction_id') . ' = :projectNamesPredictionId')
            ->bind(':projectNamesPredictionId', $predictionId, ParameterType::INTEGER)
            ->order($db->quoteName('ppj.id') . ' ' . $direction);

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }


    /**
     * Resolve a prediction-member avatar from one of the historically supported integrations.
     */
    public function getPredictionMemberAvatarValue(int $userId, string $source): ?string
    {
        if ($userId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery();

        switch ($source) {
            case 'prediction':
                if ($this->predictionGameId <= 0) {
                    return null;
                }

                $query
                    ->select($db->quoteName('picture'))
                    ->from($db->quoteName('#__sportsmanagement_prediction_member'))
                    ->where($db->quoteName('user_id') . ' = :avatarPredictionUserId')
                    ->where($db->quoteName('prediction_id') . ' = :avatarPredictionGameId')
                    ->bind(':avatarPredictionUserId', $userId, ParameterType::INTEGER)
                    ->bind(':avatarPredictionGameId', $this->predictionGameId, ParameterType::INTEGER);
                break;

            case 'com_cbe':
            case 'com_cbe25':
                $query
                    ->select($db->quoteName('avatar'))
                    ->from($db->quoteName('#__cbe_users'))
                    ->where($db->quoteName('userid') . ' = :avatarCbeUserId')
                    ->bind(':avatarCbeUserId', $userId, ParameterType::INTEGER);
                break;

            case 'com_kunena':
                $query
                    ->select($db->quoteName('avatar'))
                    ->from($db->quoteName('#__kunena_users'))
                    ->where($db->quoteName('userid') . ' = :avatarKunenaUserId')
                    ->bind(':avatarKunenaUserId', $userId, ParameterType::INTEGER);
                break;

            case 'com_community':
                $query
                    ->select($db->quoteName('avatar'))
                    ->from($db->quoteName('#__community_users'))
                    ->where($db->quoteName('userid') . ' = :avatarCommunityUserId')
                    ->bind(':avatarCommunityUserId', $userId, ParameterType::INTEGER);
                break;

            case 'com_comprofiler':
                $query
                    ->select($db->quoteName('avatar'))
                    ->from($db->quoteName('#__comprofiler'))
                    ->where($db->quoteName('user_id') . ' = :avatarProfilerUserId')
                    ->bind(':avatarProfilerUserId', $userId, ParameterType::INTEGER);
                break;

            default:
                return null;
        }

        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();

        return $value !== null ? (string) $value : null;
    }

    /**
     * Return published round betting rules and the first published match date per round.
     */
    public function getPredictionRoundBettingRules(int $predictionId): array
    {
        if ($predictionId <= 0) {
            return [];
        }

        $published = 1;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ptr.round_id'),
                $db->quoteName('ptr.rien_ne_va_plus'),
                'MIN(' . $db->quoteName('m.match_date') . ') AS ' . $db->quoteName('first_match_date'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_tippround', 'ptr'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('ptr.round_id')
                . ' AND ' . $db->quoteName('m.published') . ' = ' . (int) $published
            )
            ->where($db->quoteName('ptr.prediction_id') . ' = :bettingRulesPredictionId')
            ->where($db->quoteName('ptr.published') . ' = :bettingRulesPublished')
            ->bind(':bettingRulesPredictionId', $predictionId, ParameterType::INTEGER)
            ->bind(':bettingRulesPublished', $published, ParameterType::INTEGER)
            ->group([
                $db->quoteName('ptr.round_id'),
                $db->quoteName('ptr.rien_ne_va_plus'),
            ]);

        $db->setQuery($query);

        return $db->loadObjectList('round_id') ?: [];
    }

    /** Return member prediction results for a project and round range. */
    public function getPredictionMemberResults(
        int $projectId,
        int $fromRoundId,
        int $toRoundId = 0,
        int $userId = 0
    ): array {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $fromRoundId = max(1, $fromRoundId);
        $predictionGameId = $this->predictionGameId;
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id', 'matchID'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.team1_result', 'homeResult'),
                $db->quoteName('m.team2_result', 'awayResult'),
                $db->quoteName('m.team1_result_decision', 'homeDecision'),
                $db->quoteName('m.team2_result_decision', 'awayDecision'),
                $db->quoteName('m.team1_result_split', 'homeResultSplit'),
                $db->quoteName('m.team2_result_split', 'awayResultSplit'),
                $db->quoteName('m.team1_result_ot', 'homeResultOT'),
                $db->quoteName('m.team2_result_ot', 'awayResultOT'),
                $db->quoteName('m.team1_result_so', 'homeResultSO'),
                $db->quoteName('m.team2_result_so', 'awayResultSO'),
                $db->quoteName('pr.id', 'prID'),
                $db->quoteName('pr.user_id', 'prUserID'),
                $db->quoteName('pr.tipp', 'prTipp'),
                $db->quoteName('pr.tipp_home', 'prHomeTipp'),
                $db->quoteName('pr.tipp_away', 'prAwayTipp'),
                $db->quoteName('pr.joker', 'prJoker'),
                $db->quoteName('pr.points', 'prPoints'),
                $db->quoteName('pr.top', 'prTop'),
                $db->quoteName('pr.diff', 'prDiff'),
                $db->quoteName('pr.tend', 'prTend'),
                $db->quoteName('pm.id', 'pmID'),
                $db->quoteName('m.round_id', 'matchRoundId'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_prediction_result', 'pr')
                . ' ON ' . $db->quoteName('pr.match_id') . ' = ' . $db->quoteName('m.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_prediction_member', 'pm')
                . ' ON ' . $db->quoteName('pm.user_id') . ' = ' . $db->quoteName('pr.user_id')
            )
            ->where($db->quoteName('r.id') . ' >= :memberResultsFromRound')
            ->where($db->quoteName('pm.prediction_id') . ' = :memberResultsMemberGame')
            ->where($db->quoteName('pr.prediction_id') . ' = :memberResultsResultGame')
            ->where(
                '(' . $db->quoteName('m.cancel') . ' IS NULL OR '
                . $db->quoteName('m.cancel') . ' = 0)'
            )
            ->bind(':memberResultsFromRound', $fromRoundId, ParameterType::INTEGER)
            ->bind(':memberResultsMemberGame', $predictionGameId, ParameterType::INTEGER)
            ->bind(':memberResultsResultGame', $predictionGameId, ParameterType::INTEGER)
            ->order([
                $db->quoteName('pm.id') . ' ASC',
                $db->quoteName('m.match_date') . ' ASC',
                $db->quoteName('m.id') . ' ASC',
            ]);

        if ($projectId > 0) {
            $query
                ->where($db->quoteName('r.project_id') . ' = :memberResultsProjectId')
                ->bind(':memberResultsProjectId', $projectId, ParameterType::INTEGER);
        }

        if ($toRoundId > 0) {
            $query
                ->where($db->quoteName('r.id') . ' <= :memberResultsToRound')
                ->bind(':memberResultsToRound', $toRoundId, ParameterType::INTEGER);
        }

        if ($userId > 0) {
            $query
                ->where($db->quoteName('pr.user_id') . ' = :memberResultsUserId')
                ->bind(':memberResultsUserId', $userId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /** Persist the calculated result columns for one prediction result row. */
    public function updatePredictionResult(object $result): bool
    {
        $id = (int) ($result->id ?? 0);

        if ($id <= 0) {
            return false;
        }

        $row = (object) [
            'id' => $id,
            'tipp_home' => $result->tipp_home ?? null,
            'tipp_away' => $result->tipp_away ?? null,
            'tipp' => $result->tipp ?? null,
            'joker' => $result->joker ?? null,
            'points' => $result->points ?? null,
            'top' => $result->top ?? null,
            'diff' => $result->diff ?? null,
            'tend' => $result->tend ?? null,
        ];

        return (bool) $this->getDatabase()->updateObject(
            '#__sportsmanagement_prediction_result',
            $row,
            'id',
            true
        );
    }

}
