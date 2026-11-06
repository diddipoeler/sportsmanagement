<?php
/**
 * Native Joomla 5/6 frontend model for SportsManagement playgrounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

final class PlaygroundModel extends SportsManagementProjectModel
{
    /**
     * Legacy public static state retained for existing views/extensions.
     */
    public static int $playgroundid = 0;
    public static int $projectid = 0;
    public static int $cfg_which_database = 0;

    /**
     * Legacy public property retained for compatibility.
     */
    public $playground = null;

    /**
     * Initialise playground, project and component database context.
     * Preserves compatibility with historical project model readers.
     * @param array $config Joomla site model options.
     * @param MVCFactoryInterface|null $factory Optional MVC factory.
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$playgroundid = $input->getInt('pgid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0) === 1 ? 1 : 0;

        if (class_exists('sportsmanagementModelProject')) {
            \sportsmanagementModelProject::$projectid = self::$projectid;
        }
    }

    /**
     * Read the selected playground and optionally increment its hit counter.
     *
     * Supports the historical static entry point used by nextmatch.
     *
     * @param int $playgroundId Playground identifier or current request ID.
     * @param int|bool $incrementHits Whether to increment the hit counter.
     * @return object|null Playground record or null when unavailable.
     */
    public static function getPlayground(int $playgroundId = 0, int|bool $incrementHits = false): ?object
    {
        if ($playgroundId <= 0) {
            $playgroundId = self::frontendApplication()->getInput()->getInt('pgid', self::$playgroundid);
        }

        if ($playgroundId <= 0) {
            return null;
        }

        $db = self::database();

        if ((bool) $incrementHits) {
            self::updateHits($playgroundId, true, $db);
        }

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = :playgroundId')
            ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /**
     * Increment the selected playground's visit counter.
     * @param int $playgroundId Playground identifier.
     * @param int|bool $incrementHits Whether to increment the hit count.
     * @param DatabaseInterface|null $database Optional database override.
     * @return void
     */
    public static function updateHits(
        int $playgroundId = 0,
        int|bool $incrementHits = false,
        ?DatabaseInterface $database = null
    ): void {
        if (!$incrementHits || $playgroundId <= 0) {
            return;
        }

        $db = $database ?? self::database();
        $query = $db->createQuery()
            ->update($db->quoteName('#__sportsmanagement_playground'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = :playgroundId')
            ->bind(':playgroundId', $playgroundId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    /**
     * List historical notices associated with a playground.
     * @param int $playgroundId Playground identifier.
     * @return array<int,object> Notices ordered from newest to oldest.
     */
    public function getPlaygroundNotic(int $playgroundId): array
    {
        if ($playgroundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground_details'))
            ->where($db->quoteName('playground_id') . ' = :noticePlaygroundId')
            ->order($db->quoteName('date_von') . ' DESC')
            ->bind(':noticePlaygroundId', $playgroundId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Format the playground address from configured location fields.
     * @param object|null $playground Optional playground record.
     * @return string Formatted street, place and country.
     */
    public function getAddressString(?object $playground = null): string
    {
        $playground ??= self::getPlayground(self::$playgroundid);

        if (!$playground) {
            return '';
        }

        $parts = [];

        foreach (['address', 'state'] as $property) {
            $value = trim((string) ($playground->{$property} ?? ''));
            if ($value !== '') {
                $parts[] = $value;
            }
        }

        $location = trim((string) ($playground->location ?? $playground->city ?? ''));
        $zipCode = trim((string) ($playground->zipcode ?? ''));
        if ($location !== '') {
            $parts[] = trim($zipCode . ' ' . $location);
        } elseif ($zipCode !== '') {
            $parts[] = $zipCode;
        }

        $countryCode = trim((string) ($playground->country ?? ''));
        if ($countryCode !== '') {
            $countryName = $this->getCountryName($countryCode);
            $parts[] = $countryName !== '' ? $countryName : $countryCode;
        }

        return implode(', ', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }

    /**
     * List matching past or upcoming games at the selected playground.
     * @param int $projectId Optional project filter.
     * @param int $playgroundId Playground or current playground ID.
     * @param int|bool $played Whether to use the played-matches filter.
     * @param int|bool $allProjects Whether to include other projects.
     * @return array<int,object> Playground match records.
     */
    public function getNextGames(
        int $projectId = 0,
        int $playgroundId = 0,
        int|bool $played = false,
        int|bool $allProjects = false
    ): array {
        if ($playgroundId <= 0) {
            $playgroundId = self::$playgroundid;
        }

        if ($playgroundId <= 0) {
            return [];
        }

        $timestamp = time();
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.time_present'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('st1.team_id', 'team1'),
                $db->quoteName('st2.team_id', 'team2'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt1')
                . ' ON ' . $db->quoteName('pt1.id') . ' = ' . $db->quoteName('m.projectteam1_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt2')
                . ' ON ' . $db->quoteName('pt2.id') . ' = ' . $db->quoteName('m.projectteam2_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt1.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st1')
                . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id')
            )
            ->where($db->quoteName('m.playground_id') . ' = :nextGamesPlaygroundId')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' ASC')
            ->bind(':nextGamesPlaygroundId', $playgroundId, ParameterType::INTEGER);

        $operator = $played ? '<' : '>';
        $query
            ->where($db->quoteName('m.match_timestamp') . ' ' . $operator . ' :matchTimestamp')
            ->bind(':matchTimestamp', $timestamp, ParameterType::INTEGER);

        if ($projectId > 0 && !$allProjects) {
            $query
                ->where($db->quoteName('p.id') . ' = :nextGamesProjectId')
                ->bind(':nextGamesProjectId', $projectId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Translate a three-letter country code into its display name.
     * @param string $countryCode Country alpha-3 code.
     * @return string Localised country name or empty string.
     */
    private function getCountryName(string $countryCode): string
    {
        $countryCode = strtoupper(trim($countryCode));

        if ($countryCode === '') {
            return '';
        }

        try {
            $db = $this->getDatabase();
            $query = $db->createQuery()
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = :countryCode')
                ->bind(':countryCode', $countryCode);
            $db->setQuery($query, 0, 1);
            $name = trim((string) $db->loadResult());

            return $name !== '' ? Text::_($name) : '';
        } catch (\Throwable) {
            return '';
        }
    }

    /**
     * Resolve the current Joomla site application.
     * @return CMSApplicationInterface Joomla frontend application.
     */
    private static function frontendApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Resolve the configured SportsManagement database connection.
     * @return DatabaseInterface Component database connection.
     */
    private static function database(): DatabaseInterface
    {
        $selector = self::frontendApplication()->getInput()->getInt('cfg_which_database', self::$cfg_which_database) === 1
            ? 1
            : 0;

        return SportsManagementDatabaseResolver::resolve(self::joomlaDatabase(), $selector);
    }

    /**
     * Resolve and validate Joomla's primary database service.
     * @return DatabaseInterface Joomla database connection.
     * @throws \RuntimeException If the service is missing.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        $database = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement playground database service is unavailable.', 500);
        }

        return $database;
    }
}
