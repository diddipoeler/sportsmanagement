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
    /**
     * Select distinct playground records for the frontend ticker.
     *
     * Validate project, database and result-limit parameters, query the
     * selected database, discard malformed records and return a bounded random
     * selection. Invalid project settings never broaden the database query.
     * Caps the database result set at 1000 records and limits the final
     * random sample to at most 100 playgrounds.
     *
     * @param Registry $params Module settings and filters.
     * @param CMSApplicationInterface $app Active application.
     * @param DatabaseInterface $fallbackDatabase Default Joomla database.
     * @return array<int, object> Playground records, possibly empty.
     */
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

        $rawLimit = $params->get('limit', 1);
        // Prevent malformed module settings (including arrays) from becoming
        // PHP warnings or unexpectedly huge random selections.
        $limit = self::boundedLimit($rawLimit);
        $whichDatabase = $this->databaseSelector($params, $app);

        // Reject malformed configuration rather than silently using DB zero.
        if ($whichDatabase === null) {
            return [];
        }

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

        // Bound the rows fetched as well as the final random sample.
        // A project with thousands of playgrounds must not allocate an
        // unlimited result set for a small front-end ticker.
        $db->setQuery($query, 0, 1000);
        $rows = $db->loadObjectList() ?: [];
        $rows = is_array($rows) ? $rows : [];
        $unique = [];

        foreach ($rows as $row) {
            // Database drivers should return objects, but do not access fields
            // on malformed rows or merge unrelated records under ID zero.
            if (!is_object($row) || !isset($row->id_playground)
                || (!is_int($row->id_playground) && !is_string($row->id_playground))
                || !ctype_digit((string) $row->id_playground)
                || self::projectId($row->id_playground) <= 0
            ) {
                continue;
            }

            $unique[self::projectId($row->id_playground)] = $row;
        }

        $playgrounds = array_values($unique);

        if (count($playgrounds) <= $limit) {
            return $playgrounds;
        }

        $keys = array_rand($playgrounds, $limit);
        $keys = is_array($keys) ? $keys : [$keys];

        return array_values(array_map(static fn (int $key): object => $playgrounds[$key], $keys));
    }

    /**
     * Parse the configured maximum number of playgrounds to display.
     *
     * Accepts integers and zero-padded decimal strings, rejecting structured
     * input and overflows before clamping the result between 1 and 100.
     * Oversized valid decimal values are capped without an integer cast.
     *
     * @param mixed $raw Raw Joomla module limit option.
     * @return int Number of playgrounds to show, between 1 and 100.
     */
    private static function boundedLimit(mixed $raw): int
    {
        if (is_int($raw)) {
            return max(1, min(100, $raw));
        }

        if (!is_string($raw) || strlen($raw) > 4096 || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 1;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return 1;
        }

        if (strlen($digits) > 3 || (strlen($digits) === 3 && strcmp($digits, '100') > 0)) {
            return 100;
        }

        return max(1, (int) $digits);
    }

    /**
     * Parse a positive project identifier, optionally in Joomla ID:alias form.
     *
     * Rejects unsupported types, invalid aliases, zero IDs and integer
     * overflows so an invalid project cannot change the database scope.
     *
     * @param mixed $value Raw project selector from module settings.
     * @return int Positive project ID, or zero when invalid.
     */
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

    /**
     * Check whether the module explicitly requests playgrounds from all projects.
     *
     * Only missing, empty or zero-padded zero values mean unrestricted
     * selection; an invalid positive identifier must not be treated as zero.
     *
     * @param mixed $value Raw configured project selector.
     * @return bool Whether the all-projects option was explicitly selected.
     */
    private static function isUnfilteredProject(mixed $value): bool
    {
        return $value === null
            || $value === ''
            || $value === 0
            || $value === '0'
            || $value === '000'
            || (is_string($value) && preg_match('/^0+$/D', trim($value)) === 1);
    }

    /**
     * Resolve a safe base URL for images of playgrounds.
     *
     * Returns the configured external HTTP(S) server only when external data
     * is selected and the URL has no credentials, query or fragment. Falls
     * back to the local Joomla root for missing or invalid configuration.
     *
     * @param Registry $params Module settings controlling database selection.
     * @param CMSApplicationInterface $app Active Joomla application.
     * @return string Valid image base URL, or an empty string for an invalid selector.
     */
    public function getPictureServer(Registry $params, CMSApplicationInterface $app): string
    {
        $selector = $this->databaseSelector($params, $app);

        if ($selector === null) {
            return '';
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        // Use the same validated selector as getData(): a component-wide
        // setting must not change this module's image host independently.
        if ($selector === 1) {
            $configuredServer = $componentParams->get('cfg_which_database_server', '');
            $server = is_string($configuredServer) ? trim($configuredServer) : '';

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

    /**
     * Determine which database the legacy playground ticker should use.
     *
     * Reads the module setting before the request fallback and accepts only
     * database IDs zero and one, including zero-padded decimal strings.
     * Invalid selections return null rather than silently selecting database zero.
     *
     * @param Registry $params Module database configuration.
     * @param CMSApplicationInterface $app Application providing fallback input.
     * @return int|null Database index 0 or 1, or null if invalid.
     */
    private function databaseSelector(Registry $params, CMSApplicationInterface $app): ?int
    {
        $raw = $params->get(
            'cfg_which_database',
            $app->getInput()->getInt('cfg_which_database', 0)
        );

        if (is_int($raw)) {
            return $raw === 0 || $raw === 1 ? $raw : null;
        }

        if (!is_string($raw) || !preg_match('/^0*(?:0|1)$/D', $raw)) {
            return null;
        }

        return str_ends_with($raw, '1') ? 1 : 0;
    }
}
