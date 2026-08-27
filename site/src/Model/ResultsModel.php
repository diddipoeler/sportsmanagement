<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\MatchTable;
use Diddipoeler\Component\SportsManagement\Site\Pagination\JSMSportsmanagementPagination;
use Joomla\CMS\Factory;
use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 list model for the regular results view.
 *
 * Public compatibility fields are retained while legacy views and third-party
 * callers are migrated. Database access itself is delegated to native models.
 */
final class ResultsModel extends SportsManagementListModel
{
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $roundid = 0;
    public static int $mode = 0;
    public static int $order = 0;
    public static int $cfg_which_database = 0;
    public static string $layout = '';
    public static int $limitstart = 0;
    public static int $limit = 0;

    public array $rounds = [0];
    public array $config = [];
    public ?object $project = null;
    public ?array $matches = null;
    public string $_identifier = 'results';

    private ResultsDataModel $dataModel;
    private ResultsAccessModel $accessModel;
    private ResultsEditModel $editModel;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [];
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        self::$divisionid = max(0, $input->getInt('division', 0));
        self::$mode = max(0, $input->getInt('mode', 0));
        self::$order = max(0, $input->getInt('order', 0));
        self::$projectid = max(0, $input->getInt('p', 0));
        self::$layout = $input->getCmd('layout', '');
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        $this->setDatabaseSelector(self::$cfg_which_database);
        $this->dataModel = $this->createDataModel();
        $this->accessModel = $this->createAccessModel();
        $this->editModel = $this->createEditModel();

        $requestedRound = max(0, $input->getInt('r', 0));
        self::$roundid = $requestedRound > 0
            ? $requestedRound
            : $this->dataModel->getCurrentRound();

        if (class_exists('sportsmanagementHelperHtml')) {
            \sportsmanagementHelperHtml::$roundid = self::$roundid;
        }

        $this->config = $this->dataModel->getTemplateConfig('results');
    }

    public function getPagination()
    {
        $store = $this->getStoreId('getPagination');
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        $limit = max(0, (int) $this->getState('list.limit') - (int) $this->getState('list.links'));
        $this->cache[$store] = new JSMSportsmanagementPagination(
            $this->getTotal(),
            $this->getStart(),
            $limit
        );

        return $this->cache[$store];
    }

    /**
     * Legacy-compatible static match reader.
     *
     * When $pagination is true the query object is returned so old callers can
     * apply their own list limits. Otherwise rows remain keyed by match id.
     */
    public static function getResultsRows(
        $round,
        $division,
        &$config,
        $params = null,
        $cfg_which_database = 0,
        $team = 0,
        $pagination = false
    ) {
        $model = new ResultsDataModel();
        $model->setDatabaseSelector((int) $cfg_which_database);

        $projectId = self::$projectid;
        if ($projectId <= 0) {
            $projectId = Factory::getApplication()->getInput()->getInt('p', 0);
        }
        if ($projectId <= 0 && class_exists('sportsmanagementModelProject')) {
            $projectId = (int) (\sportsmanagementModelProject::$projectid ?? 0);
        }

        $model->setProjectId($projectId);
        $model->setDivisionId((int) $division);

        if ($pagination) {
            return $model->getResultsQuery((int) $round, (int) $division, $params, (int) $team);
        }

        return $model->getResultsRows(
            (int) $round,
            (int) $division,
            $params,
            (int) $team,
            self::$limitstart,
            self::$limit
        );
    }

    public function getLimit(): int
    {
        return (int) $this->getState('list.limit');
    }

    public function getLimitStart(): int
    {
        return (int) $this->getState('list.start');
    }

    public function getDivisionID($cfg_which_database = 0): int
    {
        return self::$divisionid;
    }

    public function getDivision($cfg_which_database = 0): ?object
    {
        return self::$divisionid > 0 ? $this->dataModel->getDivision(self::$divisionid) : null;
    }

    /** Native project object used by the results view and templates. */
    public function getProject(): ?object
    {
        return $this->dataModel->getProject();
    }

    public function getTemplateConfig(string $template = 'results'): array
    {
        return $this->dataModel->getTemplateConfig($template);
    }

    public function getOverallConfig(): array
    {
        return $this->dataModel->getOverallConfig();
    }

    public function getRoundCode(int $roundId = 0): string
    {
        return $this->dataModel->getRoundCode($roundId > 0 ? $roundId : self::$roundid);
    }

    public function getRoundOptions(string $ordering = 'ASC'): array
    {
        return $this->dataModel->getRoundOptions($ordering);
    }

    public function getRounds(string $ordering = 'ASC', bool $slug = true): array
    {
        return $this->dataModel->getRounds($ordering, $slug);
    }

    public function getProjectTeamsIndexed(int $divisionId = 0): array
    {
        return $this->dataModel->getProjectTeamsIndexed($divisionId);
    }

    public function getFavTeams(): array
    {
        return $this->dataModel->getFavTeams();
    }

    public function getProjectEvents(int $positionId = 0): array
    {
        return $this->dataModel->getProjectEvents($positionId);
    }

    public function getProjectPositionsOptions(int $positionId = 0, int $personType = 1): array
    {
        return $this->dataModel->getProjectPositionsOptions($positionId, $personType, self::$projectid);
    }

    public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'match') === 0) {
            if (!class_exists(MatchTable::class)) {
                require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/SportsManagementTable.php';
                require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Table/MatchTable.php';
            }

            return new MatchTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    public function limitText($text, $wordcount): string
    {
        $text = (string) $text;
        $wordcount = (int) $wordcount;
        if ($wordcount <= 0) {
            return $text;
        }

        $words = explode(' ', $text);
        if (count($words) <= $wordcount) {
            return $text;
        }

        return implode(' ', array_slice($words, 0, $wordcount)) . '...';
    }

    public function getRssFeeds($rssfeedlink, $rssitems)
    {
        $urls = array_values(array_filter(array_map('trim', explode(',', (string) $rssfeedlink))));
        if (!$urls) {
            return [];
        }

        foreach ($urls as $url) {
            try {
                $factory = new FeedFactory();
                return $factory->getFeed($url);
            } catch (Throwable) {
                Factory::getApplication()->enqueueMessage(
                    Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'),
                    'notice'
                );
            }
        }

        return [];
    }

    public function getData()
    {
        try {
            $items = $this->getItems();
        } catch (Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            return false;
        }

        $project = $this->dataModel->getProject();
        if (!$project) {
            return $items;
        }

        $user = Factory::getApplication()->getIdentity();
        $projectAllowed = $this->accessModel->isAllowed((int) ($project->editorgroup ?? 0));
        $contentIds = $this->getContentIds(array_map(
            static fn ($match): int => (int) ($match->id ?? 0),
            $items
        ));

        foreach ($items as $match) {
            $matchId = (int) ($match->id ?? 0);
            $match->allowed = false;

            if (((int) ($match->checked_out ?? 0) === 0 || (int) $match->checked_out === (int) $user->id)
                && ($projectAllowed || $this->accessModel->isMatchAdmin($matchId, (int) $user->id))) {
                $match->allowed = true;
            }

            $match->content_id = (int) ($contentIds[$matchId] ?? 0);
        }

        return $items;
    }

    public function isAllowed($cfg_which_database = 0, $editorgroup = 0): bool
    {
        if ((int) $cfg_which_database !== self::$cfg_which_database) {
            $this->accessModel->setDatabaseSelector((int) $cfg_which_database);
        }

        return $this->accessModel->isAllowed((int) $editorgroup);
    }

    public function isMatchAdmin($matchid = 0, $userid = 0, $cfg_which_database = 0): bool
    {
        if ((int) $cfg_which_database !== self::$cfg_which_database) {
            $this->accessModel->setDatabaseSelector((int) $cfg_which_database);
        }

        return $this->accessModel->isMatchAdmin((int) $matchid, (int) $userid);
    }

    public function getMatches($cfg_which_database = 0, $editorgroup = 0, $cat_id = 0): array
    {
        if ($this->matches === null) {
            $this->matches = $this->dataModel->getResultsRows(
                self::$roundid,
                self::$divisionid,
                null,
                0,
                self::$limitstart,
                self::$limit
            );
        }

        $allowed = $this->isAllowed($cfg_which_database, $editorgroup);
        $user = Factory::getApplication()->getIdentity();
        $contentIds = $this->getContentIds(array_keys($this->matches), (int) $cat_id);

        foreach ($this->matches as $key => $match) {
            $match->allowed = false;
            if (((int) ($match->checked_out ?? 0) === 0 || (int) $match->checked_out === (int) $user->id)
                && ($allowed || $this->isMatchAdmin((int) $match->id, (int) $user->id, $cfg_which_database))) {
                $match->allowed = true;
            }
            $match->content_id = (int) ($contentIds[(int) $match->id] ?? 0);
            $this->matches[$key] = $match;
        }

        return $this->matches;
    }

    public static function getMatchRefereeTeams($match_id = 0, $cfg_which_database = 0): array
    {
        $model = new ResultsDataModel();
        $model->setDatabaseSelector((int) $cfg_which_database);
        $db = $model->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mr.project_referee_id', 'value'),
                $db->quoteName('t.name', 'teamname'),
                $db->quoteName('pos.name', 'position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('mr.project_position_id') . ' = ' . $db->quoteName('pos.id'))
            ->where($db->quoteName('mr.match_id') . ' = ' . (int) $match_id)
            ->order([
                $db->quoteName('pos.name') . ' ASC',
                $db->quoteName('mr.ordering') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('value') ?: [];
        } catch (Throwable) {
            return [];
        }
    }

    public function getShowEditIcon($editorgroup = 0): bool
    {
        $user = Factory::getApplication()->getIdentity();
        if ((int) $user->id <= 0) {
            return false;
        }

        if ($this->accessModel->isAllowed((int) $editorgroup)) {
            return true;
        }

        Factory::getApplication()->enqueueMessage(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_CHANGE_NOTALLOWED'),
            'notice'
        );
        return false;
    }

    public function isTeamEditor($userid = 0, $cfg_which_database = 0): bool
    {
        if ((int) $cfg_which_database !== self::$cfg_which_database) {
            $this->accessModel->setDatabaseSelector((int) $cfg_which_database);
        }

        return $this->accessModel->isTeamEditor((int) $userid);
    }

    public function saveshort($cfg_which_database = 0): bool
    {
        $this->editModel->setDatabaseSelector((int) $cfg_which_database);
        return $this->editModel->saveShort();
    }

    protected function getListQuery()
    {
        $query = $this->dataModel->getResultsQuery(self::$roundid, self::$divisionid);
        if ($query) {
            return $query;
        }

        $db = $this->getDatabase();
        return $db->getQuery(true)
            ->select($db->quoteName('m.id'))
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->where('1 = 0');
    }

    protected function populateState($ordering = 'm.match_date', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $limit = $app->getUserStateFromRequest(
            $this->context . '.list.limit',
            'limit',
            (int) $app->get('list_limit', 20),
            'uint'
        );
        $start = $input->getUInt('limitstart', 0);

        $this->setState('list.limit', $limit);
        $this->setState('list.start', $start);
        self::$limit = (int) $limit;
        self::$limitstart = (int) $start;
    }

    private function createDataModel(): ResultsDataModel
    {
        $model = new ResultsDataModel();
        $model->setDatabaseSelector(self::$cfg_which_database);
        $model->setProjectId(self::$projectid);
        $model->setDivisionId(self::$divisionid);
        return $model;
    }

    private function createAccessModel(): ResultsAccessModel
    {
        $model = new ResultsAccessModel();
        $model->setDatabaseSelector(self::$cfg_which_database);
        $model->setProjectId(self::$projectid);
        return $model;
    }

    private function createEditModel(): ResultsEditModel
    {
        $model = new ResultsEditModel();
        $model->setDatabaseSelector(self::$cfg_which_database);
        return $model;
    }

    /** @return array<int,int> */
    private function getContentIds(array $matchIds, int $categoryId = 0): array
    {
        $matchIds = array_values(array_unique(array_filter(array_map('intval', $matchIds))));
        if (!$matchIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('fv.value', 'match_id'),
                $db->quoteName('c.id', 'content_id'),
            ])
            ->from($db->quoteName('#__content', 'c'))
            ->join('INNER', $db->quoteName('#__fields_values', 'fv') . ' ON ' . $db->quoteName('fv.item_id') . ' = ' . $db->quoteName('c.id'))
            ->join('INNER', $db->quoteName('#__fields', 'f') . ' ON ' . $db->quoteName('f.id') . ' = ' . $db->quoteName('fv.field_id'))
            ->where($db->quoteName('f.title') . ' = ' . $db->quote('jsmmatchid'))
            ->where($db->quoteName('fv.value') . ' IN (' . implode(',', $matchIds) . ')')
            ->where($db->quoteName('c.catid') . ' = ' . $categoryId);

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (Throwable) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $matchId = (int) ($row->match_id ?? 0);
            if ($matchId > 0) {
                $result[$matchId] = (int) ($row->content_id ?? 0);
            }
        }
        return $result;
    }
}
