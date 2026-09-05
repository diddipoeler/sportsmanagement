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

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
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

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->select("CONCAT_WS(':', id, alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('published') . ' = 1');
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

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('pm.prediction_id') . ' = ' . $this->predictionGameId);

        if ($this->predictionMemberId > 0) {
            $query->where($db->quoteName('pm.id') . ' = ' . $this->predictionMemberId);
        } else {
            $userId = (int) $this->siteApplication()->getIdentity()->id;
            if ($userId <= 0) {
                return $empty;
            }
            $query->where($db->quoteName('pm.user_id') . ' = ' . $userId);
        }

        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: $empty;
    }

    public function getPredictionProjects(): array
    {
        if ($this->predictionGameId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('pp.prediction_id') . ' = ' . $this->predictionGameId)
            ->where($db->quoteName('pp.published') . ' = 1');
        $db->setQuery($query);
        $projects = $db->loadObjectList() ?: [];

        foreach ($projects as $project) {
            if (($project->start_date ?? '') === '0000-00-00') {
                $roundQuery = $db->getQuery(true)
                    ->select('MIN(' . $db->quoteName('round_date_first') . ')')
                    ->from($db->quoteName('#__sportsmanagement_round'))
                    ->where($db->quoteName('project_id') . ' = ' . (int) $project->project_id);
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

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('user_id'))
            ->from($db->quoteName('#__sportsmanagement_prediction_admin'))
            ->where($db->quoteName('prediction_id') . ' = ' . (int) $game->id)
            ->where($db->quoteName('user_id') . ' = ' . (int) $user->id);
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
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_prediction_template'))
            ->where($db->quoteName('template') . ' = ' . $db->quote($template))
            ->where($db->quoteName('prediction_id') . ' = ' . $predictionId);
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
}
