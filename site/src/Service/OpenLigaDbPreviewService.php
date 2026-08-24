<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Http\HttpFactory;

/** Read-only OpenLigaDB preview service for the legacy extension UI. */
final class OpenLigaDbPreviewService
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

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

    /** @return list<array<string,mixed>> */
    public function fetchMatches(string $url): array
    {
        $url = trim($url);

        if (!$this->isAllowedUrl($url)) {
            throw new \RuntimeException('Invalid OpenLigaDB preview URL.');
        }

        $http = (new HttpFactory())->getHttp();
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

    /** @param list<array<string,mixed>> $matches
     *  @return array{matches:int,teams:int,playgrounds:int,goals:int}
     */
    public function summarize(array $matches): array
    {
        $teams = [];
        $playgrounds = [];
        $goals = 0;

        foreach ($matches as $match) {
            foreach (['Team1', 'Team2'] as $teamKey) {
                $team = $match[$teamKey] ?? null;

                if (is_array($team) && isset($team['TeamId'])) {
                    $teams[(string) $team['TeamId']] = true;
                }
            }

            $location = $match['Location'] ?? null;

            if (is_array($location) && isset($location['LocationID'])) {
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

    private function isAllowedUrl(string $url): bool
    {
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
