<?php
/**
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class PlaygroundTickerHelper
{
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $fallbackDatabase
    ): array {
        $configuredProject = $params->get('p', 0);
        $projectId = self::projectId($configuredProject);

        // Project 0 means "all playgrounds"; an invalid configured value
        // must not silently become 0 and expose unrelated projects.
        if ($projectId <= 0 && !self::isUnfilteredProject($configuredProject)) {
            return [];
        }

        $limit = max(1, (int) $params->get('limit', 1));
        $whichDatabase = $this->databaseSelector($params, $app);
        $db = SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            $whichDatabase
        );
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pg.id', 'id_playground'),
                $db->quoteName('pg.name', 'playground_name'),
                $db->quoteName('pg.name'),
                $db->quoteName('pg.address'),
                $db->quoteName('pg.zipcode'),
                $db->quoteName('pg.city'),
                $db->quoteName('pg.country'),
                $db->quoteName('pg.club_id'),
                $db->quoteName('pg.extended'),
                $db->quoteName('pg.latitude'),
                $db->quoteName('pg.longitude'),
                $db->quoteName('pg.state'),
                $db->quoteName('pg.picture'),
                $db->quoteName('pg.website'),
                $db->quoteName('pg.max_visitors'),
                $db->quoteName('cl.name', 'club_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'pg'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'cl')
                . ' ON ' . $db->quoteName('cl.id') . ' = ' . $db->quoteName('pg.club_id')
            );

        if ($projectId > 0) {
            $query->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.standard_playground') . ' = ' . $db->quoteName('pg.id')
            )
                ->where($db->quoteName('pt.project_id') . ' = :projectId')
                ->bind(':projectId', $projectId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $unique = [];

        foreach ($rows as $row) {
            $unique[(int) $row->id_playground] = $row;
        }

        $playgrounds = array_values($unique);

        if (count($playgrounds) <= $limit) {
            return $playgrounds;
        }

        $keys = array_rand($playgrounds, $limit);
        $keys = is_array($keys) ? $keys : [$keys];

        return array_values(array_map(static fn (int $key): object => $playgrounds[$key], $keys));
    }

    /** Parse a single selected project, optionally in Joomla ID:alias form. */
    private static function projectId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $value = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $value, $parts)) {
            return 0;
        }

        $digits = ltrim($parts[1], '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    /** Only a missing or explicitly zero project selection requests all. */
    private static function isUnfilteredProject(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || $value === 0
            || $value === '0'
            || $value === '000'
            || (is_string($value) && preg_match('/^0+$/D', trim($value)) === 1);
    }

    public function getPictureServer(Registry $params, CMSApplicationInterface $app): string
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $useExternal = (bool) $componentParams->get('cfg_which_database', false)
            || $this->databaseSelector($params, $app) === 1;

        if ($useExternal) {
            $server = trim((string) $componentParams->get('cfg_which_database_server', ''));

            if ($server !== ''
                && !preg_match('/[\\x00-\\x20\\x7F]/', $server)
                && !str_contains($server, '\\\\')
                && filter_var($server, FILTER_VALIDATE_URL) !== false
            ) {
                $parts = parse_url($server);
                if (is_array($parts)
                    && in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)
                    && !empty($parts['host'])
                    && !isset($parts['user'])
                    && !isset($parts['pass'])
                    && !isset($parts['query'])
                    && !isset($parts['fragment'])
                ) {
                    return rtrim($server, '/') . '/';
                }
            }
        }

        return Uri::root();
    }

    private function databaseSelector(Registry $params, CMSApplicationInterface $app): int
    {
        return (int) $params->get(
            'cfg_which_database',
            $app->getInput()->getInt('cfg_which_database', 0)
        ) === 1 ? 1 : 0;
    }
}
