<?php
/**
 * Joomla 5/6 iCalendar site model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class IcalModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $divisionid = 0;
    public static int $cfg_which_database = 0;
    public static int $teamid = 0;
    public static int $projectteamid = 0;

    public ?object $team = null;
    public ?object $club = null;

    private static function joomlaDatabase(): DatabaseInterface
    {
        /** @var DatabaseInterface $database */
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        return $database;
    }

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$teamid = $input->getInt('tid', 0);
        self::$projectteamid = $input->getInt('ptid', 0);

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    /** Accept decimal IDs and legacy ID:alias slugs, rejecting partial casts. */
    private static function nonNegativeSelector(mixed $value, bool $allowAlias = false): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (!is_string($value)) {
            return null;
        }

        $pattern = $allowAlias
            ? '/^([0-9]+)(?::[\pL\pN_-]+)?$/Du'
            : '/^([0-9]+)$/D';

        if (!preg_match($pattern, $value, $matches)) {
            return null;
        }

        $digits = ltrim($matches[1], '0');

        if ($digits === '') {
            return 0;
        }

        $max = (string) PHP_INT_MAX;

        if (strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return null;
        }

        return (int) $digits;
    }

    public function getResultsPlan($projectid = 0, $teamid = 0, $divisionid = 0, $playgroundid = 0, $ordering = 'ASC', $cfg_which_database = 0)
    {
        $projectId = self::nonNegativeSelector($projectid, true);
        $teamId = self::nonNegativeSelector($teamid, true);
        $databaseSelector = self::nonNegativeSelector($cfg_which_database);
        $direction = is_string($ordering) && strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';

        // Malformed selectors must not silently expose a different project.
        if ($projectId === null || $projectId <= 0
            || $teamId === null || $databaseSelector === null) {
            return [];
        }

        $app = $this->siteApplication();

        try {
            if ($databaseSelector === self::$cfg_which_database) {
                $db = $this->getDatabase();
            } else {
                $db = SportsManagementDatabaseResolver::resolve(
                    self::joomlaDatabase(),
                    $databaseSelector
                );
            }

            $query = $db->createQuery()
                ->select([
                    $db->quoteName('m.id'),
                    $db->quoteName('m.projectteam1_id'),
                    $db->quoteName('m.projectteam2_id'),
                    $db->quoteName('m.match_date'),
                    'DATE_FORMAT(' . $db->quoteName('m.time_present') . ', "%H:%i") AS ' . $db->quoteName('time_present'),
                    $db->quoteName('playground.id', 'playground_id'),
                    $db->quoteName('playground.name', 'playground_name'),
                    $db->quoteName('playground.short_name', 'playground_short_name'),
                    $db->quoteName('playground.address', 'playground_address'),
                    $db->quoteName('playground.zipcode', 'playground_zipcode'),
                    $db->quoteName('playground.city', 'playground_city'),
                    $db->quoteName('pt1.project_id'),
                    $db->quoteName('d1.name', 'divhome'),
                    $db->quoteName('d2.name', 'divaway'),
                    'CASE WHEN CHAR_LENGTH(' . $db->quoteName('t1.alias') . ') AND CHAR_LENGTH(' . $db->quoteName('t2.alias') . ") THEN CONCAT_WS(':', " . $db->quoteName('m.id') . ", CONCAT_WS('_', " . $db->quoteName('t1.alias') . ', ' . $db->quoteName('t2.alias') . ')) ELSE ' . $db->quoteName('m.id') . ' END AS ' . $db->quoteName('slug'),
                    "CONCAT_WS(':', " . $db->quoteName('p.id') . ', ' . $db->quoteName('p.alias') . ') AS ' . $db->quoteName('project_slug'),
                    "CONCAT_WS(':', " . $db->quoteName('r.id') . ', ' . $db->quoteName('r.alias') . ') AS ' . $db->quoteName('round_slug'),
                    "CONCAT_WS(':', " . $db->quoteName('playground.id') . ', ' . $db->quoteName('playground.alias') . ') AS ' . $db->quoteName('playground_slug'),
                    $db->quoteName('t1.id', 'team1'),
                    $db->quoteName('t2.id', 'team2'),
                    $db->quoteName('p.name', 'project_name'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd1') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d1.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd2') . ' ON ' . $db->quoteName('m.division_id') . ' = ' . $db->quoteName('d2.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id'))
                ->where($db->quoteName('m.published') . ' = 1')
                ->where($db->quoteName('r.project_id') . ' = :projectId')
                ->order($db->quoteName('m.match_date') . ' ' . $direction)
                ->order($db->quoteName('m.match_number') . ' ' . $direction)
                ->bind(':projectId', $projectId, ParameterType::INTEGER);

            if ($teamId > 0) {
                $query
                    ->where('(' . $db->quoteName('t1.id') . ' = :homeTeamId OR ' . $db->quoteName('t2.id') . ' = :awayTeamId)')
                    ->bind([':homeTeamId', ':awayTeamId'], $teamId, ParameterType::INTEGER);
            }

            $db->setQuery($query);

            return $db->loadObjectList('id') ?: [];
        } catch (\Throwable $exception) {
            // SQL details and database credentials belong in server logs, not
            // in publicly accessible calendar feeds or error messages.
            Log::add(__METHOD__ . ': ' . $exception->getMessage(), Log::ERROR, 'jsmerror');
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }
    }
}
