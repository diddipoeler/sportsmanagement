<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

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
     * Compatibility entry point used by legacy views such as nextmatch.
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

        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground'))
            ->where($db->quoteName('id') . ' = ' . $playgroundId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public static function updateHits(
        int $playgroundId = 0,
        int|bool $incrementHits = false,
        ?DatabaseInterface $database = null
    ): void {
        if (!$incrementHits || $playgroundId <= 0) {
            return;
        }

        $db = $database ?? self::database();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__sportsmanagement_playground'))
            ->set($db->quoteName('hits') . ' = ' . $db->quoteName('hits') . ' + 1')
            ->where($db->quoteName('id') . ' = ' . $playgroundId);
        $db->setQuery($query)->execute();
    }

    public function getPlaygroundNotic(int $playgroundId): array
    {
        if ($playgroundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_playground_details'))
            ->where($db->quoteName('playground_id') . ' = ' . $playgroundId)
            ->order($db->quoteName('date_von') . ' DESC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

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

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
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
            ->where($db->quoteName('m.playground_id') . ' = ' . $playgroundId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' ASC');

        $operator = $played ? '<' : '>';
        $query->where($db->quoteName('m.match_timestamp') . ' ' . $operator . ' ' . time());

        if ($projectId > 0 && !$allProjects) {
            $query->where($db->quoteName('p.id') . ' = ' . $projectId);
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function getCountryName(string $countryCode): string
    {
        $countryCode = strtoupper(trim($countryCode));

        if ($countryCode === '') {
            return '';
        }

        try {
            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('name'))
                ->from($db->quoteName('#__sportsmanagement_countries'))
                ->where($db->quoteName('alpha3') . ' = ' . $db->quote($countryCode));
            $db->setQuery($query, 0, 1);
            $name = trim((string) $db->loadResult());

            return $name !== '' ? Text::_($name) : '';
        } catch (\Throwable) {
            return '';
        }
    }

    private static function frontendApplication(): SiteApplication
    {
        return Factory::getContainer()->get(SiteApplication::class);
    }

    private static function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $selector = self::frontendApplication()->getInput()->getInt('cfg_which_database', self::$cfg_which_database) === 1
            ? 1
            : 0;

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
    }
}
