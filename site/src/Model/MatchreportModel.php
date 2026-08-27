<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;
use stdClass;
use Throwable;

/**
 * Native Joomla 5/6 model for the match report view.
 */
final class MatchreportModel extends SportsManagementProjectModel
{
    private int $matchId = 0;
    private int $databaseSelector = 0;
    private ?object $match = null;
    private ?array $playersEvents = null;
    private ?array $playersBasicStats = null;
    private ?array $staffBasicStats = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        $this->matchId = max(0, $input->getInt('mid', 0));
        $this->databaseSelector = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;
    }

    public function getMatchId(): int
    {
        return $this->matchId;
    }

    public function getDatabaseSelector(): int
    {
        return $this->databaseSelector;
    }

    public function getMatchData(): ?object
    {
        return $this->loadMatch();
    }

    public function getbillardplayer(
        string $positionName = 'COM_SPORTSMANAGEMENT_GOLF_BILLARD_P_CAPTAIN',
        int $projectTeamId = 0,
        int $matchId = 0
    ): array {
        if ($projectTeamId <= 0 || $matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.id'),
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('pp.id', 'pro_position'),
                $db->quoteName('pp.position_id'),
                $db->quoteName('pos.name'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.picture', 'ppic'),
                $db->quoteName('p.knvbnr'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'pp') . ' ON ' . $db->quoteName('pp.id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('pp.position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('p.id'))
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('pos.name') . ' = ' . $db->quote($positionName));

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return [];
        }
    }

    public function checkMatchPlayerProjectPositionID(): void
    {
        if ($this->matchId <= 0 || $this->projectId <= 0) {
            return;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('teamplayer_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player'))
            ->where($db->quoteName('match_id') . ' = ' . $this->matchId)
            ->where($db->quoteName('project_position_id') . ' = 0');
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $row) {
            $positionQuery = $db->getQuery(true)
                ->select($db->quoteName('ppp.project_position_id'))
                ->from($db->quoteName('#__sportsmanagement_person_project_position', 'ppp'))
                ->join(
                    'INNER',
                    $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                    . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('ppp.person_id')
                )
                ->where($db->quoteName('ppp.project_id') . ' = ' . $this->projectId)
                ->where($db->quoteName('tp.id') . ' = ' . (int) $row->teamplayer_id);
            $db->setQuery($positionQuery, 0, 1);
            $positionId = (int) $db->loadResult();

            if ($positionId <= 0) {
                continue;
            }

            $update = (object) [
                'id' => (int) $row->id,
                'project_position_id' => $positionId,
            ];
            $db->updateObject('#__sportsmanagement_match_player', $update, 'id');
        }
    }

    public function getClubinfo(int $clubId): ?object
    {
        if ($clubId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_club'))
            ->where($db->quoteName('id') . ' = ' . $clubId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getRound(): ?object
    {
        $match = $this->loadMatch();
        if ($match === null || (int) ($match->round_id ?? 0) <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . (int) $match->round_id);
        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();

        if (!$round) {
            return null;
        }

        if (trim((string) ($round->name ?? '')) === '') {
            $round->name = Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_GAMEDAY_NB', $round->id);
        }

        return $round;
    }

    public function getMatchPictures(string $folder): array
    {
        $folder = trim($folder, '/\\');
        if ($folder === '') {
            return [];
        }

        $basePath = JPATH_SITE . '/images/com_sportsmanagement/database/' . $folder;
        if (!Folder::exists($basePath)) {
            return [];
        }

        $sitePath = 'images/com_sportsmanagement/database/' . $folder;
        $images = [];

        foreach (Folder::files($basePath) ?: [] as $file) {
            if (
                !is_file($basePath . '/' . $file)
                || str_starts_with($file, '.')
                || in_array(strtolower($file), ['index.html', 'thumbs.db', 'readme.txt'], true)
            ) {
                continue;
            }

            $image = new stdClass();
            $image->name = $file;
            $image->sitepath = $sitePath;
            $image->path = Path::clean($basePath . '/' . $file);
            $images[] = $image;
        }

        return $images;
    }

    public function getMatchPositions(string $which = 'player'): array
    {
        $table = match ($which) {
            'staff' => '#__sportsmanagement_match_staff',
            'referee' => '#__sportsmanagement_match_referee',
            default => '#__sportsmanagement_match_player',
        };

        if ($this->matchId <= 0 || $this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pos.id'),
                $db->quoteName('pos.name'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('ppos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->join('INNER', $db->quoteName($table, 'mp') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->where($db->quoteName('mp.match_id') . ' = ' . $this->matchId)
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->group([
                $db->quoteName('pos.id'),
                $db->quoteName('pos.name'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('ppos.id'),
                $db->quoteName('pos.ordering'),
            ])
            ->order($db->quoteName('pos.ordering') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return [];
        }
    }

    public function getMatchPersons(string $which = 'player'): array
    {
        if ($this->matchId <= 0 || $this->projectId <= 0 || !in_array($which, ['player', 'staff'], true)) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'DISTINCT ' . $db->quoteName('pt.id'),
                $db->quoteName('pt.id', 'ptid'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('p.picture', 'ppic'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('st.team_id'),
                $db->quoteName('st.id', 'stid'),
                $db->quoteName('tp.person_id'),
                $db->quoteName('tp.picture'),
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
            ]);

        if ($which === 'player') {
            $query
                ->select([
                    $db->quoteName('mp.trikot_number'),
                    $db->quoteName('mp.teamplayer_id'),
                    $db->quoteName('mp.captain'),
                    $db->quoteName('mp.project_position_id', 'position_id'),
                    $db->quoteName('tp.jerseynumber'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
                ->where($db->quoteName('mp.came_in') . ' = 0')
                ->order([
                    $db->quoteName('mp.ordering'),
                    $db->quoteName('tp.jerseynumber'),
                    $db->quoteName('p.lastname'),
                ]);
        } else {
            $query
                ->select([
                    $db->quoteName('mp.team_staff_id'),
                    $db->quoteName('mp.project_position_id', 'position_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_staff', 'mp'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('mp.team_staff_id'));
        }

        $query
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('p.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('mp.match_id') . ' = ' . $this->matchId)
            ->where($db->quoteName('p.published') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return [];
        }
    }

    public function getEventTypes(): array
    {
        if ($this->matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_eventtype', 'pet') . ' ON ' . $db->quoteName('pet.eventtype_id') . ' = ' . $db->quoteName('et.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_event', 'me') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id'))
            ->where($db->quoteName('me.match_id') . ' = ' . $this->matchId)
            ->group([
                $db->quoteName('pet.ordering'),
                $db->quoteName('et.id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->order($db->quoteName('pet.ordering'));

        try {
            $db->setQuery($query);
            return array_values(array_unique($db->loadObjectList() ?: [], SORT_REGULAR));
        } catch (Throwable $e) {
            Factory::getApplication()->enqueueMessage(Text::_(__METHOD__ . ' ' . $e->getMessage()), 'error');
            return [];
        }
    }

    public function getMatchArticle(int $articleId = 0, int $matchId = 0, int $categoryId = 0): ?object
    {
        $component = (string) ComponentHelper::getParams('com_sportsmanagement')->get('which_article_component', 'com_content');
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id'),
                $db->quoteName('c.title'),
                $db->quoteName('c.introtext'),
            ]);

        if ($component === 'com_k2') {
            $query->from($db->quoteName('#__k2_items', 'c'));
            if ($articleId > 0) {
                $query->where($db->quoteName('c.id') . ' = ' . $articleId);
            }
        } else {
            $query->from($db->quoteName('#__content', 'c'));

            if ($articleId > 0) {
                $query->where($db->quoteName('c.id') . ' = ' . $articleId);
            } elseif ($matchId > 0) {
                $query
                    ->join('INNER', $db->quoteName('#__fields_values', 'fv') . ' ON ' . $db->quoteName('fv.item_id') . ' = ' . $db->quoteName('c.id'))
                    ->join('INNER', $db->quoteName('#__fields', 'f') . ' ON ' . $db->quoteName('f.id') . ' = ' . $db->quoteName('fv.field_id'))
                    ->where($db->quoteName('f.title') . ' = ' . $db->quote('jsmmatchid'))
                    ->where($db->quoteName('fv.value') . ' = ' . $db->quote((string) $matchId));
            }

            if ($categoryId > 0) {
                $query->where($db->quoteName('c.catid') . ' = ' . $categoryId);
            }
        }

        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    public function getMatchStats(): array
    {
        $match = $this->loadMatch();
        if ($match === null) {
            return [];
        }

        $stats = [
            (int) $match->projectteam1_id => [],
            (int) $match->projectteam2_id => [],
        ];

        foreach ($this->loadMatchStatisticRows() as $stat) {
            $stats[(int) $stat->projectteam_id][(int) $stat->teamplayer_id][(int) $stat->statistic_id] = $stat->value;
        }

        return $stats;
    }

    public function getPlayersStats(): array
    {
        if ($this->playersBasicStats !== null) {
            return $this->playersBasicStats;
        }

        $stats = [];
        foreach ($this->loadMatchStatisticRows() as $stat) {
            $stats[(int) $stat->teamplayer_id][(int) $stat->statistic_id] = $stat->value;
        }

        return $this->playersBasicStats = $stats;
    }

    public function getPlayersEvents(): array
    {
        if ($this->playersEvents !== null) {
            return $this->playersEvents;
        }

        if ($this->matchId <= 0) {
            return $this->playersEvents = [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_event'))
            ->where($db->quoteName('match_id') . ' = ' . $this->matchId);
        $db->setQuery($query);

        $events = [];
        foreach ($db->loadObjectList() ?: [] as $event) {
            $playerId = (int) ($event->teamplayer_id ?? 0);
            $eventTypeId = (int) ($event->event_type_id ?? 0);
            if ($playerId <= 0 || $eventTypeId <= 0) {
                continue;
            }

            $events[$playerId][$eventTypeId] = ($events[$playerId][$eventTypeId] ?? 0) + (float) ($event->event_sum ?? 0);
        }

        return $this->playersEvents = $events;
    }

    public function getMatchStaffStats(): array
    {
        if ($this->staffBasicStats !== null) {
            return $this->staffBasicStats;
        }

        if ($this->matchId <= 0) {
            return $this->staffBasicStats = [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_staff_statistic'))
            ->where($db->quoteName('match_id') . ' = ' . $this->matchId);
        $db->setQuery($query);

        $stats = [];
        foreach ($db->loadObjectList() ?: [] as $stat) {
            $staffId = (int) ($stat->team_staff_id ?? 0);
            $statisticId = (int) ($stat->statistic_id ?? 0);
            if ($staffId <= 0 || $statisticId <= 0) {
                continue;
            }
            $stats[$staffId][$statisticId] = $stat->value;
        }

        return $this->staffBasicStats = $stats;
    }

    public function getPlaygroundSchema($schema, $which): array
    {
        $schema = trim((string) $schema);
        if ($schema === '') {
            return [];
        }

        $shortName = $which === 'gast' ? 'AWAY_POS' : 'HOME_POS';
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extended'))
            ->from($db->quoteName('#__sportsmanagement_rosterposition'))
            ->where($db->quoteName('name') . ' = ' . $db->quote($schema))
            ->where($db->quoteName('short_name') . ' = ' . $db->quote($shortName));
        $db->setQuery($query, 0, 1);
        $extended = $db->loadResult();

        if (!$extended) {
            return [];
        }

        $registry = new Registry();
        $registry->loadString((string) $extended);
        $positions = [];

        for ($index = 0, $position = 1; $index < 11; $index++, $position++) {
            $positions[$schema][$index][$which]['oben'] = $registry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_' . $position . '_TOP');
            $positions[$schema][$index][$which]['links'] = $registry->get('COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_' . $position . '_LEFT');
        }

        return $positions;
    }

    private function loadMatch(): ?object
    {
        if ($this->match !== null) {
            return $this->match;
        }

        if ($this->matchId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match'))
            ->where($db->quoteName('id') . ' = ' . $this->matchId);
        $db->setQuery($query, 0, 1);
        $this->match = $db->loadObject() ?: null;

        return $this->match;
    }

    private function loadMatchStatisticRows(): array
    {
        if ($this->matchId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_match_statistic'))
            ->where($db->quoteName('match_id') . ' = ' . $this->matchId);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }
}
