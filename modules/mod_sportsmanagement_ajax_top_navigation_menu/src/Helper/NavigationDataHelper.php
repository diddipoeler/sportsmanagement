<?php
/**
 * Joomla 5/6 data/link helper for the AJAX top navigation module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementAjaxTopNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class NavigationDataHelper
{
    /**
     * Validate a database or team identifier without lossy integer casts.
     *
     * Accepts nonnegative integers and decimal strings including leading
     * zeros; rejects booleans, arrays, negative or overflowing numbers and
     * partially numeric strings.
     *
     * @param mixed $value Raw Joomla selector or stored numeric identifier.
     * @return int|null Valid nonnegative integer, or null if invalid.
     */
    private static function nonNegativeSelector(mixed $value): ?int
    {
        if (!is_int($value) && (!is_string($value) || preg_match('/^[0-9]+$/D', $value) !== 1)) {
            return null;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $result = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        return $result === false ? null : $result;
    }

    /**
     * Parse a bounded list of favourite team identifiers from a project row.
     *
     * Invalid tokens must not silently become valid IDs through PHP integer
     * casts. Deduplication and a 100-ID cap bound the subsequent SQL IN clause.
     *
     * @param mixed $value Comma-separated IDs stored in the project database.
     * @return array<int,int> Up to 100 distinct positive team identifiers.
     */
    private static function favoriteTeamIds(mixed $value): array
    {
        if (!is_string($value) || strlen($value) > 8192) {
            return [];
        }

        $ids = [];

        foreach (explode(',', $value) as $part) {
            $part = trim($part);
            $id = self::nonNegativeSelector($part);

            if ($id === null || $id <= 0) {
                continue;
            }

            $ids[$id] = $id;

            if (count($ids) >= 100) {
                break;
            }
        }

        return array_values($ids);
    }

    /**
     * Resolve the active frontend application using the central resolver.
     *
     * Avoids deprecated global site application lookups.
     *
     * @return CMSApplicationInterface Active Joomla site application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Resolve the Joomla database connection for navigation queries.
     *
     * Uses an injected connection when present; otherwise retrieves DatabaseInterface from Joomla's public factory container.
     *
     * @param DatabaseInterface|null $database Optional injected database connection.
     * @param CMSApplicationInterface $application Current Joomla frontend application.
     * @return DatabaseInterface Joomla database connection.
     */
    private static function joomlaDatabase(
        ?DatabaseInterface $database,
        CMSApplicationInterface $application
    ): DatabaseInterface {
        if ($database !== null) {
            return $database;
        }

        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement AJAX navigation database service is unavailable.', 500);
        }

        return $database;
    }

    public static $_project_id = 0;
    public static $_country_fed = [];
    public static $query_getFederations = '';
    public static $query_getFederationSelect = '';
    public static $query_getAssocLeagueSelect = '';
    public static $query_getCountryAssocSelect = '';
    public static $query_getCountryFederation = '';
    public static $query_getCountrySubAssocSelect = '';
    public static $query_getCountrySubSubAssocSelect = '';
    public static $query_getLeagueAssocId = '';
    public static $query_getLeagueSelect = '';

    public $_project_slug = '';
    public $_league_slug = '';
    public $_round_slug = '';
    public $_season_slug = '';
    public $_team_slug = '';
    public $_club_slug = '';
    public $_division_slug = '';
    public $project_type = 'SIMPLE_LEAGUE';
    public $_league_id = 0;
    public $_team_id = 0;
    public $_club_id = 0;
    public $_division_id = 0;
    public $_tnid = 0;
    public $_round_id = null;

    protected Registry $_params;
    protected DatabaseInterface $_db;
    protected $_query;
    protected CMSApplicationInterface $_app;
    protected $_teamoptions = null;
    protected $_project = null;
    protected string $_user_name = '';

    /**
     * Initialise the navigation helper and select its database connection.
     *
     * Requires a Joomla site application, rejects malformed database selectors before resolution and restores legacy navigation request state for a selected project.
     *
     * @param Registry|array<string,mixed>|string $params Joomla module registry or legacy parameter data.
     * @param CMSApplicationInterface|null $app Optional injected site application.
     * @param DatabaseInterface|null $database Optional injected Joomla database.
     * @return void Initialised helper state.
     */
    public function __construct(
        $params,
        ?CMSApplicationInterface $app = null,
        ?DatabaseInterface $database = null
    ) {
        $this->_params = $params instanceof Registry ? $params : new Registry($params);

        /** @var CMSApplicationInterface $resolvedApp */
        $resolvedApp = $app ?? self::siteApplication();

        if (!$resolvedApp->isClient('site')) {
            throw new \RuntimeException('SportsManagement AJAX navigation data requires the Joomla site application.', 500);
        }

        $this->_app = $resolvedApp;

        $joomlaDatabase = self::joomlaDatabase($database, $resolvedApp);
        $input = $this->_app->getInput();
        $componentSelector = ComponentHelper::getParams('com_sportsmanagement')
            ->get('cfg_which_database', 0);
        $configuredSelector = $this->_params->get('cfg_which_database', $componentSelector);
        $requestedSelector = $input->get('cfg_which_database', null, 'RAW');
        $selector = self::nonNegativeSelector($requestedSelector ?? $configuredSelector);

        if ($selector === null) {
            throw new \InvalidArgumentException('Invalid SportsManagement navigation database selector.');
        }

        $input->set('cfg_which_database', $selector);
        $this->_db = SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);

        if (self::$_project_id) {
            $input = $this->_app->getInput();
            $input->set('jlamtopseason', $this->getSeasonId());
            $input->set('jlamtopleague', $this->getLeagueId());
            $input->set('jlamtopproject', self::$_project_id);
            $input->set('jlamtopteam', $this->_team_id);
            $input->set('jlamtopdivisionid', $this->_division_id);
        }
    }

    /**
     * Read the season identifier of the currently selected project.
     *
     * Returns zero when the navigation has no selected project.
     *
     * @return int Selected season ID or zero.
     */
    public function getSeasonId()
    {
        $project = $this->getProject();
        return $project ? (int) $project->season_id : 0;
    }

    /**
     * Load and cache the currently selected project and route slugs.
     *
     * Uses bound project or league IDs to load its season, league and round metadata, and updates the legacy navigation state.
     *
     * @param int|string $league_id Optional league selector overriding the project ID.
     * @return object|false Project metadata or false when no project exists.
     */
    public function getProject($league_id = 0)
    {
        if ($this->_project) {
            return $this->_project;
        }

        if (!self::$_project_id && !$league_id) {
            return false;
        }

        $leagueId = (int) $league_id;
        $projectId = (int) self::$_project_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select([
                'p.id',
                'p.name',
                'p.league_id',
                'p.project_type',
                "CONCAT_WS(':',p.id,p.alias) AS project_slug",
                "CONCAT_WS(':',s.id,s.alias) AS season_slug",
                "CONCAT_WS(':',l.id,l.alias) AS league_slug",
                "CONCAT_WS(':',r.id,r.alias) AS round_slug",
                'p.season_id',
                'p.current_round',
            ])
            ->from('#__sportsmanagement_project AS p')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id')
            ->join('LEFT', '#__sportsmanagement_round AS r ON p.current_round = r.id');

        if ($leagueId > 0) {
            $query->where('p.league_id = :navigationLeagueId')
                ->bind(':navigationLeagueId', $leagueId, ParameterType::INTEGER);
        } else {
            $query->where('p.id = :navigationProjectId')
                ->bind(':navigationProjectId', $projectId, ParameterType::INTEGER);
        }

        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();
        if (!$project) {
            return false;
        }

        $this->_project = $project;
        $this->project_type = (string) $project->project_type;
        self::$_project_id = (int) $project->id;
        $this->_project_slug = (string) $project->project_slug;
        $this->_season_slug = (string) $project->season_slug;
        $this->_league_slug = (string) $project->league_slug;
        $this->_round_slug = (string) ($project->round_slug ?? '');

        return $this->_project;
    }

    /**
     * Select a project and update navigation request values.
     *
     * Resets cached project/team options, loads the active project and round, and publishes IDs into Joomla's request input.
     *
     * @param int|string $project_id Selected project identifier.
     * @param int|string $team_id Selected team identifier.
     * @param int|string $division_id Selected division identifier.
     * @return void Navigation state is updated.
     */
    public function setProject($project_id, $team_id, $division_id): void
    {
        self::$_project_id = (int) $project_id;
        $this->_team_id = (int) $team_id;
        $this->_division_id = (int) $division_id;
        $this->_project = null;
        $this->_teamoptions = null;
        $this->_project = $this->getProject();
        $this->_round_id = $this->getCurrentRoundId();

        $input = $this->_app->getInput();
        $input->set('jlamtopseason', $this->getSeasonId());
        $input->set('jlamtopleague', $this->getLeagueId());
        $input->set('jlamtopproject', self::$_project_id);
        $input->set('jlamtopteam', $this->_team_id);
        $input->set('jlamtopdivisionid', $this->_division_id);
    }

    /**
     * Return the selected project's league identifier.
     *
     * Loads the project when necessary and caches the league ID.
     *
     * @return int Positive league ID or zero.
     */
    public function getLeagueId()
    {
        $project = $this->getProject();
        if (!$project) {
            return 0;
        }

        $this->_league_id = (int) $project->league_id;
        return $this->_league_id;
    }

    /**
     * Return the current round of the selected project.
     *
     * Returns zero when no published project is available.
     *
     * @return int Current round identifier or zero.
     */
    public function getCurrentRoundId()
    {
        $project = $this->getProject();
        return $project ? (int) $project->current_round : 0;
    }

    /**
     * Load federations available for navigation.
     *
     * Queries published federations to populate the top-level navigation tabs.
     *
     * @return array<int,object> Federation IDs and names.
     */
    public function getFederations(): array
    {
        $db = $this->_db;
        $query = $db->createQuery()
            ->select(['name', 'id'])
            ->from('#__sportsmanagement_federations')
            ->where('published = 1');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    /**
     * Build association choices beneath a selected subassociation.
     *
     * Loads published child associations using a bound ID and prepends the regional placeholder option.
     *
     * @param int|string $assoc_id Parent subassociation identifier.
     * @return array<int,object> Options for a lower association dropdown.
     */
    public function getCountrySubSubAssocSelect($assoc_id): array
    {
        $associationId = (int) $assoc_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_associations AS s')
            ->where('s.parent_id = :subSubAssociationId')
            ->where('s.published = 1')
            ->order('s.name')
            ->bind(':subSubAssociationId', $associationId, ParameterType::INTEGER);
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('-- Kreisverbände -- '))],
            $res
        );
    }

    /**
     * Build choices of published subassociations.
     *
     * Queries children of a parent association with a bound ID and includes the default option.
     *
     * @param int|string $assoc_id Parent association identifier.
     * @return array<int,object> Subassociation select options.
     */
    public function getCountrySubAssocSelect($assoc_id): array
    {
        $associationId = (int) $assoc_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_associations AS s')
            ->where('s.parent_id = :subAssociationId')
            ->where('s.published = 1')
            ->order('s.name')
            ->bind(':subAssociationId', $associationId, ParameterType::INTEGER);
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('-- Landesverbände -- '))],
            $res
        );
    }

    /**
     * Build country-specific regional association choices.
     *
     * Filters published top-level associations by the country code and prepends a placeholder.
     *
     * @param string|int $country Country code or legacy country value.
     * @return array<int,object> Regional association select options.
     */
    public function getCountryAssocSelect($country): array
    {
        $country = (string) $country;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_associations AS s')
            ->where('s.country = :countryAssocCountry')
            ->where('s.parent_id = 0')
            ->where('s.published = 1')
            ->order('s.name')
            ->bind(':countryAssocCountry', $country, ParameterType::STRING);
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_('-- Regionalverbände -- '))],
            $res
        );
    }

    /**
     * Build and cache country choices for a federation.
     *
     * Loads countries participating in leagues, translates their labels, sorts them and stores their federation mapping.
     *
     * @param string $federation Display name of the federation.
     * @param int|string $federationid Positive federation ID.
     * @return array<int,object> Country select options with placeholder.
     */
    public function getFederationSelect($federation = '', $federationid = 0): array
    {
        $federationId = (int) $federationid;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('s.alpha3 AS value, s.name AS text')
            ->from('#__sportsmanagement_countries AS s')
            ->join('INNER', '#__sportsmanagement_league AS l ON l.country = s.alpha3')
            ->where('s.federation = :federationId')
            ->group('s.name')
            ->order('s.name DESC')
            ->bind(':federationId', $federationId, ParameterType::INTEGER);
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        foreach ($res as $row) {
            $row->text = Text::_($row->text);
        }

        $res = ArrayHelper::sortObjects($res, 'text', 1);
        foreach ($res as $row) {
            self::$_country_fed[$row->value] = (string) $federation;
        }

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $federation))],
            $res
        );
    }

    /**
     * Look up the cached federation for a country.
     *
     * Uses the mapping populated by the federation select query.
     *
     * @param string|int $country_id Country identifier to resolve.
     * @return string|false Associated federation or false when unknown.
     */
    public function getCountryFederation($country_id)
    {
        if (!$country_id) {
            return false;
        }

        return self::$_country_fed[$country_id] ?? false;
    }

    /**
     * Collect current route parameters from Joomla request input.
     *
     * Returns the allowed component view and numeric navigation identifiers for legacy route building.
     *
     * @return array<string,int|string> Sanitised request query fields.
     */
    public function getQueryValues(): array
    {
        $input = $this->_app->getInput();

        return [
            'option' => $input->getCmd('option', ''),
            'view' => $input->getCmd('view', ''),
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->getInt('s', 0),
            'p' => $input->getInt('p', 0),
            'division' => $input->getInt('division', 0),
            'type' => $input->getInt('type', 0),
            'r' => $input->getInt('r', 0),
            'from' => $input->getInt('from', 0),
            'to' => $input->getInt('to', 0),
            'mid' => $input->getInt('mid', 0),
            'tid' => $input->getInt('tid', 0),
            'cid' => $input->getInt('cid', 0),
            'Itemid' => $input->getInt('Itemid', 0),
        ];
    }

    /**
     * Return and cache the active Joomla identity's username.
     *
     * Uses an empty name for anonymous users.
     *
     * @return string Active username or empty string.
     */
    public function getUserName(): string
    {
        $user = $this->_app->getIdentity();
        $this->_user_name = (string) ($user->username ?? '');
        return $this->_user_name;
    }

    /**
     * Look up the parent of a selected association.
     *
     * Uses a bound association ID and returns only a positive parent identifier.
     *
     * @param int|string $assoc_id Selected association ID.
     * @return int|false Parent association ID or false.
     */
    public function getAssocParentId($assoc_id)
    {
        if ((int) $assoc_id <= 0) {
            return false;
        }

        $associationId = (int) $assoc_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('parent_id')
            ->from('#__sportsmanagement_associations')
            ->where('id = :associationParentId')
            ->bind(':associationParentId', $associationId, ParameterType::INTEGER);
        $db->setQuery($query);
        $result = (int) $db->loadResult();

        return $result > 0 ? $result : false;
    }

    /**
     * Look up the current league's association.
     *
     * Uses a bound league identifier and ignores empty or zero association results.
     *
     * @return int|false Associated federation ID or false.
     */
    public function getLeagueAssocId()
    {
        if ($this->_league_id <= 0) {
            return false;
        }

        $leagueId = (int) $this->_league_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('associations')
            ->from('#__sportsmanagement_league')
            ->where('id = :leagueAssociationId')
            ->bind(':leagueAssociationId', $leagueId, ParameterType::INTEGER);
        $db->setQuery($query);
        $result = (int) $db->loadResult();

        return $result > 0 ? $result : false;
    }

    /**
     * Return the division selected by navigation.
     *
     * Reads the cached division identifier.
     *
     * @return int Selected division identifier.
     */
    public function getDivisionId()
    {
        return $this->_division_id;
    }

    /**
     * Store a selected division for navigation links.
     *
     * Replaces the cached division identifier.
     *
     * @param int|string $division_id Selected division identifier.
     * @return void Division selection updated.
     */
    public function setDivisionID($division_id): void
    {
        $this->_division_id = (int) $division_id;
    }

    /**
     * Load the valid favourite teams of a project.
     *
     * Rejects invalid project and stored IDs, deduplicates up to 100 team IDs and uses a bounded SQL IN selection.
     *
     * @param int|string $project_id Selected project identifier.
     * @return array<int,object>|false Favourite team rows or false.
     */
    public function getFavTeams($project_id)
    {
        $projectId = self::nonNegativeSelector($project_id) ?? 0;

        if ($projectId <= 0) {
            return false;
        }

        $db = $this->_db;
        $query = $db->createQuery()
            ->select('fav_team')
            ->from('#__sportsmanagement_project')
            ->where('id = :favoriteTeamsProjectId')
            ->bind(':favoriteTeamsProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query);
        $teams = $db->loadResult();
        $ids = self::favoriteTeamIds($teams);

        if (!$ids) {
            return false;
        }

        $query = $db->createQuery()
            ->select('t.id AS team_id, t.name, t.club_id')
            ->from('#__sportsmanagement_team AS t')
            ->whereIn('t.id', $ids, ParameterType::INTEGER);
        $db->setQuery($query);
        return $db->loadObjectList() ?: false;
    }

    /**
     * Resolve the season-team record for a project and club.
     *
     * Joins the project team to its underlying team with bound identifiers and caches the resulting team ID.
     *
     * @param int|string $project_id Selected project ID.
     * @param int|string $club_id Selected club ID.
     * @return int|false Season team identifier or false.
     */
    public function getTeamId($project_id, $club_id)
    {
        $projectId = (int) $project_id;
        $clubId = (int) $club_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('pt.team_id')
            ->from('#__sportsmanagement_project_team AS pt')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id')
            ->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id')
            ->where('pt.project_id = :teamProjectId')
            ->where('t.club_id = :teamClubId')
            ->bind(':teamProjectId', $projectId, ParameterType::INTEGER)
            ->bind(':teamClubId', $clubId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $result = (int) $db->loadResult();
        if ($result <= 0) {
            return false;
        }

        $this->_team_id = $result;
        return $this->_team_id;
    }

    /**
     * Store the selected team for navigation links.
     *
     * Updates the cached team identifier.
     *
     * @param int|string $team_id Selected team ID.
     * @return void Team selection updated.
     */
    public function setTeamID($team_id): void
    {
        $this->_team_id = (int) $team_id;
    }

    /**
     * Build a list of published seasons.
     *
     * Includes a configurable placeholder followed by seasons ordered by name.
     *
     * @return array<int,object> Season dropdown options.
     */
    public function getSeasonSelect(): array
    {
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('s.id AS value, s.name AS text')
            ->from('#__sportsmanagement_season AS s')
            ->where('s.published = 1')
            ->order('s.name DESC');
        $db->setQuery($query);

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('seasons_text')))],
            $db->loadObjectList() ?: []
        );
    }

    /**
     * Read a legacy module navigation setting.
     *
     * Provides the same registry lookup used by the compatibility bridge.
     *
     * @param string $name Registry key.
     * @param mixed $default Default when the setting is absent.
     * @return mixed Configured value or default.
     */
    protected function getParam($name, $default = null)
    {
        return $this->_params->get($name, $default);
    }

    /**
     * Build division options for a selected project.
     *
     * Optionally filters for subdivisions and includes the configurable default label.
     *
     * @param int|string $project_id Selected project ID.
     * @return array<int,object> Division dropdown options.
     */
    public function getDivisionSelect($project_id): array
    {
        $projectId = (int) $project_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('d.id AS value, d.name AS text')
            ->from('#__sportsmanagement_division AS d')
            ->where('d.project_id = :divisionProjectId')
            ->bind(':divisionProjectId', $projectId, ParameterType::INTEGER);

        if ((int) $this->getParam('show_only_subdivisions', 0) === 1) {
            $query->where('d.parent_id > 0');
        }

        $query->order('d.name');
        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('divisions_text')))],
            $res
        );
    }

    /**
     * Build league options for a country or association.
     *
     * Uses bound country and optional association selectors and includes a configurable placeholder.
     *
     * @param string|int $country_id Country selector.
     * @param int|string $associd Optional association identifier.
     * @return array<int,object> League dropdown options.
     */
    public function getAssocLeagueSelect($country_id, $associd): array
    {
        $countryId = (string) $country_id;
        $associationId = (int) $associd;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('l.id AS value, l.name AS text')
            ->from('#__sportsmanagement_league AS l')
            ->join('INNER', '#__sportsmanagement_project AS p ON l.id = p.league_id')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->where('l.country = :assocLeagueCountry')
            ->group('l.name')
            ->order('l.name')
            ->bind(':assocLeagueCountry', $countryId, ParameterType::STRING);

        if ($associationId > 0) {
            $query->where('l.associations = :assocLeagueAssociationId')
                ->bind(':assocLeagueAssociationId', $associationId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $res = $db->loadObjectList() ?: [];

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('leagues_text')))],
            $res
        );
    }

    /**
     * Resolve the league country of a selected project.
     *
     * Looks up the country code using a bound project identifier.
     *
     * @param int|string $project_id Selected project identifier.
     * @return string|false Country code or false.
     */
    public function getProjectCountry($project_id)
    {
        $projectId = (int) $project_id;
        if ($projectId <= 0) {
            return false;
        }

        $db = $this->_db;
        $query = $db->createQuery()
            ->select('l.country')
            ->from('#__sportsmanagement_league AS l')
            ->join('INNER', '#__sportsmanagement_project AS p ON l.id = p.league_id')
            ->where('p.id = :countryProjectId')
            ->bind(':countryProjectId', $projectId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        return $db->loadResult() ?: false;
    }

    /**
     * Build published league options for a season.
     *
     * Filters by season and publication state and includes a placeholder.
     *
     * @param int|string $season Selected season ID.
     * @return array<int,object> League dropdown options.
     */
    public function getLeagueSelect($season): array
    {
        $seasonId = (int) $season;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('l.id AS value, l.name AS text')
            ->from('#__sportsmanagement_league AS l')
            ->join('INNER', '#__sportsmanagement_project AS p ON l.id = p.league_id')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->where('s.id = :leagueSeasonId')
            ->where('s.published = 1')
            ->where('l.published = 1')
            ->group('l.name')
            ->order('l.name')
            ->bind(':leagueSeasonId', $seasonId, ParameterType::INTEGER);
        $db->setQuery($query);

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('leagues_text')))],
            $db->loadObjectList() ?: []
        );
    }

    /**
     * Build project options for a selected league.
     *
     * Loads published projects from the league with bound identifiers, ordering and a default option.
     *
     * @param int|string $league_id Selected league ID.
     * @return array<int,object> Project dropdown options.
     */
    public function getProjectSelect($league_id): array
    {
        $leagueId = (int) $league_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('p.id AS value, p.name AS text')
            ->from('#__sportsmanagement_project AS p')
            ->join('INNER', '#__sportsmanagement_season AS s ON s.id = p.season_id')
            ->join('INNER', '#__sportsmanagement_league AS l ON l.id = p.league_id')
            ->where('p.published = 1')
            ->where('p.league_id = :projectLeagueId')
            ->order('s.name DESC, p.name ASC')
            ->bind(':projectLeagueId', $leagueId, ParameterType::INTEGER);
        $db->setQuery($query);

        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('text_project_dropdown')))],
            $db->loadObjectList() ?: []
        );
    }

    /**
     * Build the team dropdown for a selected project.
     *
     * Prepends the configured placeholder to the cached team options.
     *
     * @param int|string $project_id Selected project identifier.
     * @return array<int,object> Team select options.
     */
    public function getTeamSelect($project_id): array
    {
        return array_merge(
            [HTMLHelper::_('select.option', 0, Text::_((string) $this->getParam('text_teams_dropdown')))],
            $this->getTeamsOptions($project_id)
        );
    }

    /**
     * Load and cache the selected project's participating teams.
     *
     * Queries project teams and their season-team records, reporting database failures through Joomla message handling.
     *
     * @param int|string $project_id Selected project identifier.
     * @return array<int,object> Available team options.
     */
    protected function getTeamsOptions($project_id): array
    {
        if ($this->_teamoptions !== null) {
            return $this->_teamoptions;
        }

        $projectId = (int) $project_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select('t.id AS value, t.name AS text')
            ->from('#__sportsmanagement_project_team AS pt')
            ->join('INNER', '#__sportsmanagement_season_team_id AS st ON st.id = pt.team_id')
            ->join('INNER', '#__sportsmanagement_team AS t ON t.id = st.team_id')
            ->where('pt.project_id = :teamOptionsProjectId')
            ->order('t.name ASC')
            ->bind(':teamOptionsProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            $this->_teamoptions = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->_app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );
            $this->_app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__),
                'error'
            );
            $this->_teamoptions = [];
        }

        return $this->_teamoptions;
    }

    /**
     * Create a component route for a favourite team's navigation view.
     *
     * Supports team, roster, club and statistics destinations and returns false for unsupported views.
     *
     * @param string $view Requested component view.
     * @param int|string $team_id Favourite team identifier.
     * @param int|string $club_id Favourite club identifier.
     * @return string|false Routed URL or false for unsupported views.
     */
    public function getLinkFavTeam($view, $team_id, $club_id)
    {
        $base = $this->baseRouteParameters();

        switch ($view) {
            case 'roster':
                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $team_id,
                    'ptid' => 0,
                ]);

            case 'teaminfo':
                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $team_id,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $this->_team_id,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'clubinfo':
                $this->getClubId();
                return SiteRouteHelper::view('clubinfo', $base + [
                    'cid' => $club_id,
                ]);

            case 'clubplan':
                $this->getClubId();
                return SiteRouteHelper::view('clubplan', $base + [
                    'cid' => $club_id,
                ]);

            case 'teamstats':
                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $this->_team_id,
                    'ptid' => 0,
                    'division' => 0,
                ]);
        }

        return false;
    }

    /**
     * Load the selected team's club and Joomla slugs.
     *
     * Uses a bound team ID to load the club relationship and caches club/team route slugs.
     *
     * @return int|false Club identifier or false.
     */
    public function getClubId()
    {
        if ($this->_team_id <= 0) {
            return false;
        }

        $teamId = (int) $this->_team_id;
        $db = $this->_db;
        $query = $db->createQuery()
            ->select([
                't.club_id',
                "CONCAT_WS(':',t.id,t.alias) AS team_slug",
                "CONCAT_WS(':',c.id,c.alias) AS club_slug",
            ])
            ->from('#__sportsmanagement_team AS t')
            ->join('INNER', '#__sportsmanagement_club AS c ON t.club_id = c.id')
            ->where('t.id = :clubTeamId')
            ->bind(':clubTeamId', $teamId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);
        $res = $db->loadObject();
        if (!$res) {
            return false;
        }

        $this->_club_id = (int) $res->club_id;
        $this->_club_slug = (string) $res->club_slug;
        $this->_team_slug = (string) $res->team_slug;
        return $this->_club_id;
    }

    /**
     * Generate a Joomla component link for a selected navigation view.
     *
     * Dispatches to the central site route helper for standings, fixtures, roster, club and tournament views, resolving team/club slugs when needed.
     *
     * @param string $view Selected navigation view identifier.
     * @return string|false Routed URL, or false when unavailable.
     */
    public function getLink($view)
    {
        if (!self::$_project_id) {
            return false;
        }

        $base = $this->baseRouteParameters();

        switch ($view) {
            case 'calendar':
                return SiteRouteHelper::view('calendar', $base + [
                    'tid' => $this->_team_id,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'curve':
                return SiteRouteHelper::view('curve', $base + [
                    'tid1' => $this->_team_slug,
                    'tid2' => 0,
                    'division' => $this->_division_id,
                ]);

            case 'eventsranking':
                return SiteRouteHelper::view('eventsranking', $base + [
                    'division' => $this->_division_id,
                    'tid' => $this->_team_id,
                    'evid' => 0,
                    'mid' => 0,
                ]);

            case 'matrix':
            case 'referees':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $this->_division_id,
                    'r' => 0,
                ]);

            case 'results':
            case 'allprojectrounds':
                return SiteRouteHelper::view((string) $view, $base + [
                    'r' => $this->_round_slug,
                    'division' => $this->_division_id,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ]);

            case 'resultsmatrix':
            case 'resultsranking':
                return SiteRouteHelper::view((string) $view, $base + [
                    'r' => $this->_round_slug,
                    'division' => $this->_division_id,
                    'mode' => 0,
                    'order' => 0,
                    'layout' => 0,
                ]);

            case 'rankingmatrix':
                return SiteRouteHelper::view('rankingmatrix', $base + [
                    'division' => $this->_division_id,
                    'r' => $this->_round_slug,
                ]);

            case 'rankingalltime':
                return SiteRouteHelper::view('rankingalltime', [
                    'cfg_which_database' => $base['cfg_which_database'],
                    'l' => $this->_league_id,
                    'points' => $this->getParam('show_alltimetable_points'),
                    'type' => 0,
                    'order' => 0,
                    'dir' => 0,
                    's' => 0,
                    'p' => $this->_project_slug,
                ]);

            case 'leaguechampionoverview':
                return SiteRouteHelper::view('leaguechampionoverview', [
                    'cfg_which_database' => $base['cfg_which_database'],
                    'l' => $this->_league_id,
                    's' => 0,
                    'p' => $this->_project_slug,
                ]);

            case 'resultsrankingmatrix':
                return SiteRouteHelper::view('resultsrankingmatrix', $base + [
                    'r' => $this->_round_slug,
                    'division' => $this->_division_id,
                ]);

            case 'roster':
                if (!$this->ensureTeamSlugs()) {
                    return false;
                }
                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $this->_team_slug,
                    'ptid' => 0,
                ]);

            case 'rosteralltime':
                if (!$this->ensureTeamSlugs()) {
                    return false;
                }
                return SiteRouteHelper::view('rosteralltime', $base + [
                    'tid' => $this->_team_slug,
                ]);

            case 'stats':
                return SiteRouteHelper::view('stats', $base + [
                    'division' => $this->_division_id,
                ]);

            case 'statsranking':
            case 'statsrankingteams':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $this->_division_id,
                    'tid' => $this->_team_id,
                ]);

            case 'teaminfo':
                if (!$this->ensureTeamSlugs()) {
                    return false;
                }
                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $this->_team_slug,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                if ($this->_team_id <= 0) {
                    return false;
                }
                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $this->_team_id,
                    'division' => 0,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'clubinfo':
                if (!$this->getClubId()) {
                    return false;
                }
                return SiteRouteHelper::view('clubinfo', $base + [
                    'cid' => $this->_club_slug,
                ]);

            case 'clubplan':
                if (!$this->getClubId()) {
                    return false;
                }
                return SiteRouteHelper::view('clubplan', $base + [
                    'cid' => $this->_club_slug,
                ]);

            case 'teamstats':
                if ($this->_team_id <= 0) {
                    return false;
                }
                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $this->_team_id,
                    'ptid' => 0,
                    'division' => 0,
                ]);

            case 'teams':
            case 'teamstree':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $this->_division_id,
                ]);

            case 'treetonode':
                return SiteRouteHelper::view('treetonode', $base + [
                    'tnid' => $this->_tnid,
                ]);

            case 'jltournamenttree':
                return SiteRouteHelper::view('jltournamenttree', $base + [
                    'r' => $this->_round_slug,
                ]);

            case 'tournamentbracket':
                return SiteRouteHelper::view('tournamentbracket', $base + [
                    'r' => $this->_round_slug,
                ]);

            case 'separator':
                return false;

            case 'ranking':
            default:
                return SiteRouteHelper::view((string) $view, $base + [
                    'type' => 0,
                    'r' => $this->_round_slug,
                    'from' => 0,
                    'to' => 0,
                    'division' => $this->_division_id,
                ]);
        }
    }

    /**
     * Construct common database, season and project routing parameters.
     *
     * Reads the already validated database selection and current project route slug.
     *
     * @return array<string,int|string> Base Joomla route parameters.
     */
    private function baseRouteParameters(): array
    {
        $input = $this->_app->getInput();

        return [
            'cfg_which_database' => self::nonNegativeSelector($input->get('cfg_which_database', 0, 'RAW')) ?? 0,
            's' => $input->getInt('s', 0),
            'p' => $this->_project_slug,
        ];
    }

    /**
     * Ensure valid team slugs exist for team and roster routes.
     *
     * Uses a cached team slug when available or loads the related club metadata.
     *
     * @return bool Whether the team route slugs are available.
     */
    private function ensureTeamSlugs(): bool
    {
        if ($this->_team_id <= 0) {
            return false;
        }

        if ($this->_team_slug !== '') {
            return true;
        }

        return (bool) $this->getClubId();
    }
}
