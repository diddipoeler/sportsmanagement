<?php
/**
 * Joomla 5/6 LiveScore connector for the SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
}

final class LivescoreConnector extends JSMCalendar
{
    /**
     * Resolve the Joomla site application for the LiveScore connector.
     * Uses the shared frontend application resolver before inspecting calendar preferences or executing the provider query.
     *
     * @return CMSApplicationInterface Active Joomla frontend application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Resolve and validate Joomla's database service for LiveScore lookups.
     * Obtains DatabaseInterface from the Joomla dependency injection container.
     * Converts missing container services into a controlled runtime exception
     * and keeps the original cause attached for diagnostic logging.
     *
     * @throws \RuntimeException When the database service cannot be obtained.
     *
     * @return DatabaseInterface Valid Joomla database driver.
     */
    private static function joomlaDatabase(): DatabaseInterface
    {
        try {
            $database = Factory::getContainer()->get(DatabaseInterface::class);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('SportsManagement LiveScore database service could not be resolved.', 500, $exception);
        }

        if (!$database instanceof DatabaseInterface) {
            throw new \RuntimeException('SportsManagement LiveScore database service is unavailable.', 500);
        }

        return $database;
    }

    private Registry $xparams;
    private string $connectorPrefix = '';

    /**
     * Append LiveScore fixtures inside the requested calendar date window.
     * Validates the optional database table prefix before constructing any
     * table name, loads matching games and converts them into rows compatible
     * with the calendar's shared event list. Invalid prefixes safely fall
     * back to the Joomla default database table prefix.
     *
     * @param array $caldates Date boundaries and optional connector parameters.
     * @param Registry $params Module settings including the configured table prefix.
     * @param array $matches Shared calendar rows populated by reference.
     * @return array<int,array> New LiveScore calendar rows.
     */
    public function appendMatches(array &$caldates, Registry &$params, array &$matches): array
    {
        $this->xparams = $params;
        $rawPrefix = $params->get('prefix', '');
        $this->connectorPrefix = is_string($rawPrefix)
            && strlen($rawPrefix) <= 64
            && ($rawPrefix === '' || preg_match('/^[A-Za-z0-9_]+$/D', $rawPrefix) === 1)
                ? $rawPrefix
                : '';

        return $this->formatRows($this->getRows($caldates), $matches);
    }

    /**
     * Query games from the configured LiveScore table during the requested dates.
     * Checks the Joomla frontend context, quotes the selected table and date boundaries, and normalises the sort direction before loading the games.
     *
     * @param array $caldates Requested calendar start and end timestamps.
     * @param string $ordering ASC or DESC date order.
     * @return array<int,object> Matching LiveScore game records.
     */
    private function getRows(array $caldates, string $ordering = 'ASC'): array
    {
        $app = self::siteApplication();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Calendar LiveScore requires the Joomla site application.', 500);
        }

        $db = self::joomlaDatabase();
        $table = $this->connectorPrefix !== ''
            ? str_replace('#__', $this->connectorPrefix, '#__livescore_games')
            : '#__livescore_games';
        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName($table));

        if (!empty($caldates['start'])) {
            $query->where($db->quoteName('mdate') . ' >= ' . $db->quote((string) $caldates['start']));
        }
        if (!empty($caldates['end'])) {
            $query->where($db->quoteName('mdate') . ' <= ' . $db->quote((string) $caldates['end']));
        }

        $query->order($db->quoteName('mdate') . ' ' . $direction);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    /**
     * Convert LiveScore fixtures into a common calendar entry structure.
     * Maps fixture dates, competing teams and season references to the shared calendar representation, adding a live result label.
     *
     * @param array<int,object> $rows Raw provider game records.
     * @param array $matches Calendar entries to extend in-place.
     * @return array<int,array> Formatted LiveScore events.
     */
    private function formatRows(array $rows, array &$matches): array
    {
        $newRows = [];

        foreach ($rows as $key => $row) {
            $formatted = [
                'type' => 'ls',
                'date' => (string) ($row->mdate ?? ''),
                'result' => 'LIVE!',
                'headingtitle' => parent::jl_utf8_convert('LiveScore'),
                'homename' => parent::jl_utf8_convert((string) ($row->heim ?? '')),
                'homepic' => '',
                'awaypic' => '',
                'awayname' => parent::jl_utf8_convert((string) ($row->gast ?? '')),
                'matchcode' => (string) ($row->saison ?? ''),
                'project_id' => 'LIVE!',
            ];

            $newRows[$key] = $formatted;
            $matches[] = $formatted;
        }

        return $newRows;
    }
}
