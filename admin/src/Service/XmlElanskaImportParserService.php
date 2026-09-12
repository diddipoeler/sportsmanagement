<?php
/**
 * Joomla 5/6 native parser for the historical Èlanska/MNZ Maribor XML source.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/** Convert the special Slovenian league feed into the normal project-import collections. */
final class XmlElanskaImportParserService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function parse(\SimpleXMLElement $xml, string $country, int $ageGroupId, int $seasonId): array
    {
        if ($seasonId <= 0) {
            throw new RuntimeException('Missing season for Èlanska XML import.', 400);
        }

        $seasonName = $this->seasonName($seasonId);
        $rows = $this->seasonRows($xml);

        if ($rows === []) {
            throw new RuntimeException('Èlanska XML import contains no season rows.', 400);
        }

        $country = strtoupper(trim($country));
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $data = [
            'sportstype' => (object) [
                'id' => 1,
                'name' => 'COM_SPORTSMANAGEMENT_ST_SOCCER',
            ],
            'season' => (object) [
                'id' => $seasonId,
                'name' => $seasonName,
            ],
            'club' => [],
            'team' => [],
            'projectteam' => [],
            'round' => [],
            'match' => [],
            'exportversion' => (object) [
                'version' => '2.4.00',
                'exportRoutine' => '2010-09-23 15:00:00',
                'exportDate' => '2010-09-23',
                'exportTime' => '2010-09-23',
                'exportSystem' => '1. Èlanska liga MNZ Maribor',
            ],
        ];

        $matched = false;

        foreach ($rows as $row) {
            if (trim($this->value($row, 'season')) !== $seasonName) {
                continue;
            }

            if (!$matched) {
                $leagueId = max(0, (int) $this->value($row, 'league_id'));
                $leagueName = trim($this->value($row, 'league'));

                if ($leagueId <= 0 || $leagueName === '') {
                    throw new RuntimeException('Èlanska XML import has incomplete league data.', 400);
                }

                $data['league'] = (object) [
                    'id' => $leagueId,
                    'name' => $leagueName,
                    'country' => $country,
                    'agegroup_id' => max(0, $ageGroupId),
                    'short_name' => $leagueName,
                    'middle_name' => $leagueName,
                ];
                $data['project'] = (object) [
                    'id' => 1,
                    'name' => trim($leagueName . ' ' . $seasonName),
                    'agegroup_id' => max(0, $ageGroupId),
                    'master_template' => 0,
                    'league_id' => $leagueId,
                    'season_id' => $seasonId,
                    'sports_type_id' => 1,
                ];
                $matched = true;
            }

            $this->addSide(
                $data,
                max(0, (int) $this->value($row, 'home_id')),
                trim($this->value($row, 'home')),
                $country,
                $ageGroupId,
                (string) $params->get('ph_logo_big', ''),
                (string) $params->get('ph_logo_medium', ''),
                (string) $params->get('ph_logo_small', ''),
                (string) $params->get('ph_team', '')
            );
            $this->addSide(
                $data,
                max(0, (int) $this->value($row, 'away_id')),
                trim($this->value($row, 'away')),
                $country,
                $ageGroupId,
                (string) $params->get('ph_logo_big', ''),
                (string) $params->get('ph_logo_medium', ''),
                (string) $params->get('ph_logo_small', ''),
                (string) $params->get('ph_team', '')
            );

            $roundId = max(0, (int) $this->value($row, 'round'));

            if ($roundId > 0 && !isset($data['round'][$roundId])) {
                $data['round'][$roundId] = (object) [
                    'id' => $roundId,
                    'project_id' => 1,
                    'roundcode' => $roundId,
                    'name' => $roundId . '.Krog',
                ];
            }

            $matchId = max(0, (int) $this->value($row, 'match_id'));

            if ($matchId <= 0) {
                continue;
            }

            $data['match'][$matchId] = (object) [
                'id' => $matchId,
                'round_id' => $roundId,
                'match_number' => $this->value($row, 'ID'),
                'projectteam1_id' => max(0, (int) $this->value($row, 'home_id')),
                'projectteam2_id' => max(0, (int) $this->value($row, 'away_id')),
                'team1_result' => $this->nullableResult($this->value($row, 'home_goals')),
                'team2_result' => $this->nullableResult($this->value($row, 'away_goals')),
                'crowd' => max(0, (int) $this->value($row, 'spectators')),
                'playground_id' => 0,
                'match_date' => $this->normaliseLegacyMatchDate($this->value($row, 'match_date')),
            ];
        }

        if (!$matched || !isset($data['project'], $data['league'])) {
            throw new RuntimeException(
                'Èlanska XML import contains no matches for selected season: ' . $seasonName,
                404
            );
        }

        foreach (['club', 'team', 'projectteam', 'round', 'match'] as $collection) {
            $data[$collection] = array_values($data[$collection]);
        }

        return $data;
    }

    /** @return list<\SimpleXMLElement> */
    private function seasonRows(\SimpleXMLElement $xml): array
    {
        $root = isset($xml->dataroot) ? $xml->dataroot : $xml;
        $rows = [];

        foreach ($root->all_seasons as $row) {
            $rows[] = $row;
        }

        return $rows;
    }

    private function seasonName(int $seasonId): string
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('name'))
            ->from($this->database->quoteName('#__sportsmanagement_season'))
            ->where($this->database->quoteName('id') . ' = :seasonId')
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $name = trim((string) ($this->database->loadResult() ?? ''));

        if ($name === '') {
            throw new RuntimeException('Selected Èlanska season was not found.', 404);
        }

        return $name;
    }

    /**
     * @param array<string,mixed> $data
     */
    private function addSide(
        array &$data,
        int $id,
        string $name,
        string $country,
        int $ageGroupId,
        string $logoBig,
        string $logoMiddle,
        string $logoSmall,
        string $teamPicture
    ): void {
        if ($id <= 0 || $name === '') {
            return;
        }

        if (!isset($data['club'][$id])) {
            $data['club'][$id] = (object) [
                'id' => $id,
                'name' => $name,
                'country' => $country,
                'standard_playground' => 0,
                'extended' => '',
                'logo_big' => $logoBig,
                'logo_middle' => $logoMiddle,
                'logo_small' => $logoSmall,
            ];
        }

        if (!isset($data['team'][$id])) {
            $data['team'][$id] = (object) [
                'id' => $id,
                'club_id' => $id,
                'name' => $name,
                'short_name' => $name,
                'middle_name' => $name,
                'info' => '',
                'agegroup_id' => max(0, $ageGroupId),
                'extended' => '',
                'picture' => $teamPicture,
            ];
        }

        if (!isset($data['projectteam'][$id])) {
            $data['projectteam'][$id] = (object) [
                'id' => $id,
                'project_id' => 1,
                'team_id' => $id,
                'picture' => $teamPicture,
            ];
        }
    }

    private function value(\SimpleXMLElement $row, string $field): string
    {
        if (isset($row->{$field})) {
            return trim((string) $row->{$field});
        }

        $attributes = $row->attributes();

        return isset($attributes[$field]) ? trim((string) $attributes[$field]) : '';
    }

    private function nullableResult(string $value): int|null
    {
        $value = trim($value);

        return $value === '' ? null : (int) $value;
    }

    private function normaliseLegacyMatchDate(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '0000-00-00 00:00:00';
        }

        $parts = explode('T', $value, 2);

        return trim($parts[0]);
    }
}
