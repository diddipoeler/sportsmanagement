<?php

namespace Diddipoeler\Plugin\Finder\Sportsmanagement\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;

\defined('_JEXEC') or die;

final class Sportsmanagement extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    protected $context = 'Sportsmanagement';
    protected $extension = 'com_sportsmanagement';
    protected $layout = 'sportsmanagement';
    protected $type_title = 'SportsManagement';
    protected $table = '#__sportsmanagement_project';
    protected $state_field = 'published';
    protected $autoloadLanguage = true;

    private const ENTITY_PARAMS = [
        'club' => 'search_clubs',
        'team' => 'search_teams',
        'player' => 'search_players',
        'staff' => 'search_staffs',
        'referee' => 'search_referees',
        'playground' => 'search_playgrounds',
        'project' => 'search_projects',
    ];

    private const ENTITY_LABELS = [
        'club' => 'Club',
        'team' => 'Team',
        'player' => 'Player',
        'staff' => 'Staff',
        'referee' => 'Referee',
        'playground' => 'Playground',
        'project' => 'Project',
    ];

    public static function getSubscribedEvents(): array
    {
        return parent::getSubscribedEvents();
    }

    public function onFinderGarbageCollection()
    {
        // This adapter spans several SportsManagement tables. The parent
        // garbage collector assumes one source table, so stale entries are
        // handled by a normal Smart Search rebuild instead.
        return 0;
    }

    protected function setup()
    {
        return ComponentHelper::isEnabled($this->extension);
    }

    protected function getContentCount()
    {
        $total = 0;

        foreach ($this->getEnabledEntities() as $entity) {
            $total += $this->getEntityCount($entity);
        }

        return $total;
    }

    protected function getItems($offset, $limit, $query = null)
    {
        $offset = max(0, (int) $offset);
        $remaining = max(0, (int) $limit);
        $items = [];

        if ($remaining === 0) {
            return $items;
        }

        foreach ($this->getEnabledEntities() as $entity) {
            $count = $this->getEntityCount($entity);

            if ($offset >= $count) {
                $offset -= $count;
                continue;
            }

            $take = min($remaining, $count - $offset);
            $rows = $this->getEntityRows($entity, $offset, $take);

            foreach ($rows as $row) {
                $items[] = $this->createResult($entity, $row);
            }

            $remaining -= count($rows);
            $offset = 0;

            if ($remaining <= 0) {
                break;
            }
        }

        return $items;
    }

    protected function index(Result $item)
    {
        if (!ComponentHelper::isEnabled($this->extension)) {
            return;
        }

        $entity = (string) $item->getElement('sportsmanagement_entity');
        $country = (string) $item->getElement('sportsmanagement_country');

        $item->setLanguage();
        $item->addTaxonomy('Type', 'SportsManagement');
        $item->addTaxonomy('SportsManagement Type', self::ENTITY_LABELS[$entity] ?? 'SportsManagement');

        if ($country !== '') {
            $item->addTaxonomy('Country', $country);
        }

        $this->indexer->index($item);
    }

    private function getEnabledEntities(): array
    {
        $entities = [];

        foreach (self::ENTITY_PARAMS as $entity => $parameter) {
            if ((int) $this->params->get($parameter, 1) === 1) {
                $entities[] = $entity;
            }
        }

        return $entities;
    }

    private function getEntityCount(string $entity): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        switch ($entity) {
            case 'club':
                $query->select('COUNT(DISTINCT c.id)')
                    ->from('#__sportsmanagement_club AS c')
                    ->join('INNER', '#__sportsmanagement_team AS t ON t.club_id = c.id')
                    ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id')
                    ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
                    ->where('c.published = 1');
                break;

            case 'team':
                $query->select('COUNT(DISTINCT t.id)')
                    ->from('#__sportsmanagement_team AS t')
                    ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id')
                    ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
                    ->where('t.published = 1');
                break;

            case 'player':
            case 'staff':
                $personType = $entity === 'player' ? 1 : 2;
                $query->select('COUNT(DISTINCT pe.id)')
                    ->from('#__sportsmanagement_person AS pe')
                    ->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pe.id')
                    ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
                    ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
                    ->where('pe.published = 1')
                    ->where('tp.persontype = ' . $personType);
                break;

            case 'referee':
                $query->select('COUNT(DISTINCT pe.id)')
                    ->from('#__sportsmanagement_person AS pe')
                    ->join('INNER', '#__sportsmanagement_season_person_id AS sp ON sp.person_id = pe.id')
                    ->join('INNER', '#__sportsmanagement_project_referee AS pr ON pr.person_id = sp.id')
                    ->where('pe.published = 1');
                break;

            case 'playground':
                $query->select('COUNT(DISTINCT pl.id)')
                    ->from('#__sportsmanagement_playground AS pl')
                    ->where('pl.published = 1');
                break;

            case 'project':
                $query->select('COUNT(DISTINCT pro.id)')
                    ->from('#__sportsmanagement_project AS pro')
                    ->where('pro.published = 1');
                break;

            default:
                return 0;
        }

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function getEntityRows(string $entity, int $offset, int $limit): array
    {
        $query = $this->getEntityQuery($entity);

        if (!$query instanceof QueryInterface) {
            return [];
        }

        $db = $this->getDatabase();
        $db->setQuery($query, $offset, $limit);

        return $db->loadObjectList() ?: [];
    }

    private function getEntityQuery(string $entity): ?QueryInterface
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        switch ($entity) {
            case 'club':
                return $query
                    ->select('c.id, c.name AS title, c.alias, c.founded AS start_date, c.country')
                    ->select('c.logo_big AS image, c.published AS state')
                    ->select("CONCAT_WS(' ', c.address, c.zipcode, c.location, c.phone, c.fax, c.email, c.unique_id, c.notes) AS summary")
                    ->select('MIN(pt.project_id) AS project_id, 0 AS team_id')
                    ->from('#__sportsmanagement_club AS c')
                    ->join('INNER', '#__sportsmanagement_team AS t ON t.club_id = c.id')
                    ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id')
                    ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
                    ->where('c.published = 1')
                    ->group('c.id')
                    ->order('c.name ASC');

            case 'team':
                return $query
                    ->select('t.id, t.name AS title, t.alias, t.checked_out_time AS start_date, c.country')
                    ->select('st.picture AS image, t.published AS state')
                    ->select("CONCAT_WS(' ', t.info, t.notes) AS summary")
                    ->select('MIN(pt.project_id) AS project_id, t.id AS team_id')
                    ->from('#__sportsmanagement_team AS t')
                    ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = t.id')
                    ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
                    ->join('LEFT', '#__sportsmanagement_club AS c ON c.id = t.club_id')
                    ->where('t.published = 1')
                    ->group('t.id')
                    ->order('t.name ASC');

            case 'player':
            case 'staff':
                $personType = $entity === 'player' ? 1 : 2;

                return $query
                    ->select('pe.id, pe.firstname, pe.lastname, pe.nickname, pe.alias, pe.birthday AS start_date, pe.country')
                    ->select('pe.picture AS image, pe.published AS state, pe.notes AS summary')
                    ->select('MIN(pt.project_id) AS project_id, MIN(t.id) AS team_id')
                    ->from('#__sportsmanagement_person AS pe')
                    ->join('INNER', '#__sportsmanagement_season_team_person_id AS tp ON tp.person_id = pe.id')
                    ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.team_id = tp.team_id AND st.season_id = tp.season_id')
                    ->join('INNER', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id')
                    ->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id')
                    ->where('pe.published = 1')
                    ->where('tp.persontype = ' . $personType)
                    ->group('pe.id')
                    ->order('pe.lastname ASC, pe.firstname ASC');

            case 'referee':
                return $query
                    ->select('pe.id, pe.firstname, pe.lastname, pe.nickname, pe.alias, pe.birthday AS start_date, pe.country')
                    ->select('pe.picture AS image, pe.published AS state, pe.notes AS summary')
                    ->select('MIN(pr.project_id) AS project_id, 0 AS team_id')
                    ->from('#__sportsmanagement_person AS pe')
                    ->join('INNER', '#__sportsmanagement_season_person_id AS sp ON sp.person_id = pe.id')
                    ->join('INNER', '#__sportsmanagement_project_referee AS pr ON pr.person_id = sp.id')
                    ->where('pe.published = 1')
                    ->group('pe.id')
                    ->order('pe.lastname ASC, pe.firstname ASC');

            case 'playground':
                return $query
                    ->select('pl.id, pl.name AS title, pl.alias, pl.checked_out_time AS start_date, pl.country')
                    ->select('pl.picture AS image, pl.published AS state, pl.notes AS summary')
                    ->select('MIN(r.project_id) AS project_id, 0 AS team_id')
                    ->from('#__sportsmanagement_playground AS pl')
                    ->join('LEFT', '#__sportsmanagement_match AS m ON m.playground_id = pl.id')
                    ->join('LEFT', '#__sportsmanagement_round AS r ON r.id = m.round_id')
                    ->where('pl.published = 1')
                    ->group('pl.id')
                    ->order('pl.name ASC');

            case 'project':
                return $query
                    ->select('pro.id, pro.name AS title, pro.alias, pro.checked_out_time AS start_date, l.country')
                    ->select('pro.picture AS image, pro.published AS state')
                    ->select("CONCAT_WS(' ', pro.name, pro.staffel_id) AS summary")
                    ->select('pro.id AS project_id, 0 AS team_id')
                    ->from('#__sportsmanagement_project AS pro')
                    ->join('LEFT', '#__sportsmanagement_league AS l ON l.id = pro.league_id')
                    ->where('pro.published = 1')
                    ->order('pro.name ASC');
        }

        return null;
    }

    private function createResult(string $entity, object $row): Result
    {
        $item = new Result();
        $item->id = $entity . ':' . (int) $row->id;
        $item->title = $this->getTitle($entity, $row);
        $item->summary = trim((string) ($row->summary ?? ''));
        $item->start_date = $this->normaliseDate((string) ($row->start_date ?? ''));
        $item->state = (int) ($row->state ?? 1);
        $item->access = 1;
        $item->language = '*';
        $item->url = $this->buildUrl($entity, $row);
        $item->route = $item->url;
        $item->type_id = $this->type_id;
        $item->layout = $this->layout;
        $item->setElement('sportsmanagement_entity', $entity);
        $item->setElement('sportsmanagement_country', trim((string) ($row->country ?? '')));

        return $item;
    }

    private function getTitle(string $entity, object $row): string
    {
        if (!in_array($entity, ['player', 'staff', 'referee'], true)) {
            return trim((string) ($row->title ?? ''));
        }

        $nickname = trim((string) ($row->nickname ?? ''));
        $parts = [trim((string) ($row->firstname ?? ''))];

        if ($nickname !== '') {
            $parts[] = "'" . $nickname . "'";
        }

        $parts[] = trim((string) ($row->lastname ?? ''));

        return trim(implode(' ', array_filter($parts, static fn ($part) => $part !== '')));
    }

    private function buildUrl(string $entity, object $row): string
    {
        $projectId = (int) ($row->project_id ?? 0);
        $id = $this->slug((int) $row->id, (string) ($row->alias ?? ''));
        $teamId = (int) ($row->team_id ?? 0);
        $base = [
            'option' => 'com_sportsmanagement',
            'cfg_which_database' => 0,
            's' => 0,
        ];

        switch ($entity) {
            case 'club':
                $base += ['view' => 'clubinfo', 'p' => $projectId, 'cid' => $id];
                break;
            case 'team':
                $base += ['view' => 'teaminfo', 'p' => $projectId, 'tid' => $id];
                break;
            case 'player':
            case 'staff':
                $base += [
                    'view' => 'player',
                    'p' => $projectId,
                    'tid' => $teamId,
                    'pid' => $id,
                ];
                break;
            case 'referee':
                $base += ['view' => 'referee', 'p' => $projectId, 'pid' => $id];
                break;
            case 'playground':
                $base += ['view' => 'playground', 'p' => $projectId, 'pgid' => $id];
                break;
            case 'project':
                $base += ['view' => 'ranking', 'p' => $id, 'type' => 0];
                break;
        }

        return 'index.php?' . http_build_query($base, '', '&', PHP_QUERY_RFC3986);
    }

    private function slug(int $id, string $alias): string
    {
        $alias = trim($alias);

        return $alias === '' ? (string) $id : $id . ':' . $alias;
    }

    private function normaliseDate(string $date): ?string
    {
        $date = trim($date);

        if ($date === '' || str_starts_with($date, '0000-00-00')) {
            return null;
        }

        return $date;
    }
}
