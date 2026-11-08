<?php
/**
 * Native Joomla 5/6 OpenLigaDB preview service.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Http\HttpFactory;

/** Read-only OpenLigaDB preview service for the legacy extension UI. */
final class OpenLigaDbPreviewService
{
    /**
     * Create the read-only OpenLigaDB preview service for a Joomla project.
     * Uses an injected database driver to locate the configured OpenLigaDB link without changing any match records.
     *
     * @param DatabaseInterface $db Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * Retrieve the external OpenLigaDB endpoint configured for the project.
     * Reads the project's link-type backend extra-field value using bound identifiers and returns an empty string for invalid projects.
     *
     * @param int $projectId Project whose OpenLigaDB endpoint should be read.
     * @return string Saved endpoint URL, or an empty string.
     */
    public function getProjectLink(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $fieldName = 'jsmopenligadb';
        $backend = 'project';
        $fieldType = 'link';
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('ev.fieldvalue'))
            ->from($this->db->quoteName('#__sportsmanagement_user_extra_fields_values', 'ev'))
            ->join(
                'INNER',
                $this->db->quoteName('#__sportsmanagement_user_extra_fields', 'ef')
                . ' ON ' . $this->db->quoteName('ef.id') . ' = ' . $this->db->quoteName('ev.field_id')
            )
            ->where($this->db->quoteName('ev.jl_id') . ' = :projectId')
            ->where($this->db->quoteName('ef.name') . ' = :fieldName')
            ->where($this->db->quoteName('ef.template_backend') . ' = :backend')
            ->where($this->db->quoteName('ef.field_type') . ' = :fieldType')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':fieldName', $fieldName, ParameterType::STRING)
            ->bind(':backend', $backend, ParameterType::STRING)
            ->bind(':fieldType', $fieldType, ParameterType::STRING);

        $this->db->setQuery($query, 0, 1);

        return trim((string) $this->db->loadResult());
    }

    /**
     * Fetch the current OpenLigaDB match preview as a JSON list.
     * Restricts requests to approved HTTPS OpenLigaDB hosts, disables HTTP redirects, enforces a response status check and parses JSON without running arbitrary external endpoint URLs.
     *
     * @param string $url OpenLigaDB endpoint from the project's custom field.
     * @return list<array<string,mixed>> Match records returned by the service.
     */
    public function fetchMatches(string $url): array
    {
        $url = trim($url);

        if (!$this->isAllowedUrl($url)) {
            throw new \RuntimeException('Invalid OpenLigaDB preview URL.');
        }

        // Do not allow a trusted endpoint to redirect into an internal network.
        $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);
        $response = $http->get($url, ['Accept' => 'application/json'], 30);
        $status = $response->getStatusCode();
        $body = trim((string) $response->getBody());

        if ($status < 200 || $status >= 300 || $body === '') {
            throw new \RuntimeException('OpenLigaDB request failed with HTTP status ' . $status . '.');
        }

        try {
            $matches = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException('OpenLigaDB response is invalid JSON.', 0, $exception);
        }

        if (!is_array($matches)) {
            throw new \RuntimeException('OpenLigaDB response has an invalid structure.');
        }

        return array_values(array_filter($matches, 'is_array'));
    }

    /**
     * Summarise a fetched set of OpenLigaDB matches for the import preview.
     * Counts fixtures, distinct team identifiers, distinct venues and the total goal entries without modifying the stored project data.
     *
     * @param list<array<string,mixed>> $matches Parsed OpenLigaDB fixture records.
     * @return array{matches:int,teams:int,playgrounds:int,goals:int} Preview counters.
     */
    public function summarize(array $matches): array
    {
        $teams = [];
        $playgrounds = [];
        $goals = 0;

        foreach ($matches as $match) {
            foreach (['Team1', 'Team2'] as $teamKey) {
                $team = $match[$teamKey] ?? null;

                if (is_array($team) && isset($team['TeamId'])
                    && (is_int($team['TeamId']) || is_string($team['TeamId']))) {
                    $teams[(string) $team['TeamId']] = true;
                }
            }

            $location = $match['Location'] ?? null;

            if (is_array($location) && isset($location['LocationID'])
                && (is_int($location['LocationID']) || is_string($location['LocationID']))) {
                $playgrounds[(string) $location['LocationID']] = true;
            }

            if (isset($match['Goals']) && is_array($match['Goals'])) {
                $goals += count($match['Goals']);
            }
        }

        return [
            'matches' => count($matches),
            'teams' => count($teams),
            'playgrounds' => count($playgrounds),
            'goals' => $goals,
        ];
    }

    /**
     * Validate an external URL against the approved OpenLigaDB HTTPS hosts.
     * Rejects embedded credentials, alternate ports, malformed URLs, control characters and unrelated hosts to reduce server-side request forgery risks.
     *
     * @param string $url Candidate URL from a user-editable project field.
     * @return bool Whether the URL may be fetched by the preview service.
     */
    private function isAllowedUrl(string $url): bool
    {
        // The project field is editable and ultimately drives a server-side
        // HTTP request. Never allow it to contact arbitrary services or IPs.
        if ($url === ''
            || strlen($url) > 2048
            || preg_match('/[\x00-\x20\x7F\\\\]/', $url)
            || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);

        if (!is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || !in_array(strtolower((string) ($parts['host'] ?? '')), [
                'api.openligadb.de',
                'www.openligadb.de',
                'openligadb.de',
            ], true)
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)) {
            return false;
        }

        return true;
    }
}
