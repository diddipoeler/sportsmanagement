<?php
/**
 * Joomla 5/6 standalone team XML import service.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Native writer for standalone team XML imports.
 *
 * Team exports can contain dependent playground and club records. Those are
 * resolved first so foreign IDs from the source database are never copied
 * directly into the local team or club rows.
 */
final class XmlTeamImportService
{
    private readonly PlaygroundGeocoder $geocoder;

    /** @var array<int, int> */
    private array $clubIdMap = [];

    public function __construct(
        private readonly DatabaseInterface $database,
        ?PlaygroundGeocoder $geocoder = null
    ) {
        $this->geocoder = $geocoder ?? new PlaygroundGeocoder($database);
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     *
     * @return array<string, string>
     */
    public function import(array $post, array $parsedData): array
    {
        $messages = [];

        if (!empty($parsedData['playground'])) {
            $messages += (new XmlPlaygroundImportService($this->database, $this->geocoder))
                ->import($post, $parsedData);
        }

        $playgroundIdMap = $this->buildPlaygroundIdMap($post, $parsedData);
        $clubMessage = '';
        $teamMessage = '';

        foreach (array_values((array) ($parsedData['team'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldClubId = (int) ($source->club_id ?? 0);
            $databaseClubId = (int) ($post['dbClubID_' . $key] ?? 0);

            if ($databaseClubId > 0) {
                $clubId = $databaseClubId;
                $existingClub = $this->findClubById($clubId);

                if ($existingClub !== null) {
                    $clubMessage .= $this->existingClubMessage((string) $existingClub->name);
                }
            } else {
                [$clubId, $resolvedClubMessage] = $this->resolveImportedClub(
                    $key,
                    $source,
                    $post,
                    $parsedData,
                    $playgroundIdMap
                );
                $clubMessage .= $resolvedClubMessage;
            }

            if ($oldClubId > 0 && $clubId > 0) {
                $this->clubIdMap[$oldClubId] = $clubId;
            }

            $databaseTeamId = (int) ($post['dbTeamID_' . $key] ?? 0);

            if ($databaseTeamId > 0) {
                $existingTeam = $this->findTeamById($databaseTeamId);

                if ($existingTeam !== null) {
                    $teamMessage .= $this->existingTeamMessage($existingTeam);
                }

                continue;
            }

            // teamID_* is disabled whenever the administrator selected an
            // existing team. Its presence therefore marks the create path.
            if (!array_key_exists('teamID_' . $key, $post)) {
                continue;
            }

            $name = substr(
                trim((string) ($post['teamName_' . $key] ?? ($source->name ?? ''))),
                0,
                75
            );

            if ($name === '') {
                continue;
            }

            $info = (string) ($post['teamInfo_' . $key] ?? ($source->info ?? ''));
            $middleName = substr($name, 0, 24);
            $existingTeam = $this->findTeamByIdentity($name, $middleName, $info);

            if ($existingTeam !== null) {
                $teamMessage .= $this->existingTeamMessage($existingTeam);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_team');
            $row->name = $name;
            $row->short_name = substr($name, 0, 14);
            $row->middle_name = $middleName;
            $row->info = $info;
            $row->club_id = $clubId > 0 ? $clubId : 0;
            $row->sports_type_id = max(0, (int) ($post['sportstype'] ?? 0));
            $row->agegroup_id = max(0, (int) ($post['agegroup_id'] ?? 0));
            $row->alias = OutputFilter::stringURLSafe($name);

            $oldPlaygroundId = (int) ($source->standard_playground ?? 0);
            $row->standard_playground = $oldPlaygroundId > 0
                ? (int) ($playgroundIdMap[$oldPlaygroundId] ?? 0)
                : 0;

            $row = $this->filterTableFields($row, '#__sportsmanagement_team');

            if (!$this->database->insertObject('#__sportsmanagement_team', $row)) {
                throw new RuntimeException('Unable to store imported team: ' . $name, 500);
            }

            $teamMessage .= '<span style="color:green">Created new team data: </span><strong>'
                . $this->escape($name) . '</strong>';

            if ($row->club_id > 0) {
                $teamMessage .= ' - club_id [<strong>' . (int) $row->club_id . '</strong>]';
            }

            $teamMessage .= '<br />';
        }

        if ($clubMessage !== '') {
            $messages['Importing club data:'] = $clubMessage;
        }

        $messages['Importing team data:'] = $teamMessage;

        return $messages;
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     * @param array<int, int> $playgroundIdMap
     *
     * @return array{0:int,1:string}
     */
    private function resolveImportedClub(
        int $key,
        object $team,
        array $post,
        array $parsedData,
        array $playgroundIdMap
    ): array {
        $oldClubId = (int) ($post['clubID_' . $key] ?? ($team->club_id ?? 0));

        if ($oldClubId > 0 && isset($this->clubIdMap[$oldClubId])) {
            return [$this->clubIdMap[$oldClubId], ''];
        }

        $sourceClub = $this->findSourceClub($oldClubId, $parsedData);
        $name = trim((string) ($post['clubName_' . $key] ?? ($sourceClub->name ?? '')));
        $name = substr($name, 0, 200);

        if ($name === '') {
            return [0, ''];
        }

        $country = trim((string) ($post['clubCountry_' . $key] ?? ($sourceClub->country ?? '')));
        $country = $this->normaliseCountry($country);
        $existing = $this->findClubByNameAndCountry($name, $country);

        if ($existing !== null) {
            $clubId = (int) $existing->id;

            if ($oldClubId > 0) {
                $this->clubIdMap[$oldClubId] = $clubId;
            }

            return [$clubId, $this->existingClubMessage((string) $existing->name)];
        }

        $row = $sourceClub !== null
            ? $this->filterSourceFields($sourceClub, '#__sportsmanagement_club')
            : new \stdClass();
        $row->name = $name;
        $row->country = $country;
        $row->alias = OutputFilter::stringURLSafe($name);
        $row->admin = (int) ($post['admin'] ?? 62);

        $oldPlaygroundId = (int) ($sourceClub->standard_playground ?? 0);
        $row->standard_playground = $oldPlaygroundId > 0
            ? (int) ($playgroundIdMap[$oldPlaygroundId] ?? 0)
            : 0;

        $geo = $this->geocoder->geocode($row);

        if ($geo !== null) {
            if ($geo['latitude'] !== null) {
                $row->latitude = $geo['latitude'];
            }

            if ($geo['longitude'] !== null) {
                $row->longitude = $geo['longitude'];
            }

            if ($geo['state'] !== '') {
                $row->state = $geo['state'];
            }
        }

        $row = $this->filterTableFields($row, '#__sportsmanagement_club');

        if (!$this->database->insertObject('#__sportsmanagement_club', $row)) {
            throw new RuntimeException('Unable to store imported club: ' . $name, 500);
        }

        $clubId = (int) $this->database->insertid();

        if ($oldClubId > 0) {
            $this->clubIdMap[$oldClubId] = $clubId;
        }

        $message = '<span style="color:green">Created new club data: </span><strong>'
            . $this->escape($name) . '</strong>';

        if ($country !== '') {
            $message .= ' (<strong>' . $this->escape($country) . '</strong>)';
        }

        return [$clubId, $message . '<br />'];
    }

    /** @return array<int, int> */
    private function buildPlaygroundIdMap(array $post, array $parsedData): array
    {
        $map = [];

        foreach (array_values((array) ($parsedData['playground'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);

            if ($oldId <= 0) {
                continue;
            }

            $databaseId = (int) ($post['dbPlaygroundID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $map[$oldId] = $databaseId;
                continue;
            }

            if (!array_key_exists('playgroundID_' . $key, $post)) {
                continue;
            }

            $name = substr(
                trim((string) ($post['playgroundName_' . $key] ?? ($source->name ?? ''))),
                0,
                74
            );

            if ($name === '') {
                continue;
            }

            $existing = $this->findPlaygroundByName($name);

            if ($existing !== null) {
                $map[$oldId] = (int) $existing->id;
            }
        }

        return $map;
    }

    private function findSourceClub(int $oldClubId, array $parsedData): ?object
    {
        if ($oldClubId <= 0) {
            return null;
        }

        foreach ((array) ($parsedData['club'] ?? []) as $club) {
            if (is_object($club) && (int) ($club->id ?? 0) === $oldClubId) {
                return $club;
            }
        }

        return null;
    }

    private function findClubById(int $id): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('country'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_club'))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findClubByNameAndCountry(string $name, string $country): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('country'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_club'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->where($this->database->quoteName('country') . ' = :country')
            ->bind(':name', $name, ParameterType::STRING)
            ->bind(':country', $country, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findTeamById(int $id): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('short_name'),
                $this->database->quoteName('middle_name'),
                $this->database->quoteName('info'),
                $this->database->quoteName('club_id'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_team'))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findTeamByIdentity(string $name, string $middleName, string $info): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('short_name'),
                $this->database->quoteName('middle_name'),
                $this->database->quoteName('info'),
                $this->database->quoteName('club_id'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_team'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->where($this->database->quoteName('middle_name') . ' = :middleName')
            ->where($this->database->quoteName('info') . ' = :info')
            ->bind(':name', $name, ParameterType::STRING)
            ->bind(':middleName', $middleName, ParameterType::STRING)
            ->bind(':info', $info, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findPlaygroundByName(string $name): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_playground'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function filterSourceFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            $field = (string) $field;

            if ($field === 'id' || !array_key_exists($field, $columns)) {
                continue;
            }

            $row->{$field} = is_scalar($value) ? (string) $value : $value;
        }

        return $row;
    }

    private function filterTableFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            if (array_key_exists((string) $field, $columns)) {
                $row->{$field} = $value;
            }
        }

        return $row;
    }

    private function normaliseCountry(string $country): string
    {
        $country = trim($country);

        if ($country === '' || !ctype_digit($country)) {
            return $country;
        }

        $countries = explode(
            ',',
            ',AFG,ALB,DZA,ASM,AND,AGO,AIA,ATA,ATG,ARG,ARM,ABW,AUS,AUT,AZE,BHS,BHR,BGD,BRB,BLR,BEL,BLZ,BEN,BMU,BTN,BOL,BIH,BWA,BVT,BRA,IOT,BRN,BGR,BFA,BDI,KHM,CMR,CAN,CPV,CYM,CAF,TCD,CHL,CHN,CXR,CCK,COL,COM,COG,COK,CRI,CIV,HRV,CUB,CYP,CZE,DNK,DJI,DMA,DOM,TMP,ECU,EGY,SLV,GNQ,ERI,EST,ETH,FLK,FRO,FJI,FIN,FRA,FXX,GUF,PYF,ATF,GAB,GMB,GEO,DEU,GHA,GIB,GRC,GRL,GRD,GLP,GUM,GTM,GIN,GNB,GUY,HTI,HMD,HND,HKG,HUN,ISL,IND,IDN,IRN,IRQ,IRL,ISR,ITA,JAM,JPN,JOR,KAZ,KEN,KIR,PRK,KOR,KWT,KGZ,LAO,LVA,LBN,LSO,LBR,LBY,LIE,LTU,LUX,MAC,MKD,MDG,MWI,MYS,MDV,MLI,MLT,MHL,MTQ,MRT,MUS,MYT,MEX,FSM,MDA,MCO,MNG,MSR,MAR,MOZ,MMR,NAM,NRU,NPL,NLD,ANT,NCL,NZL,NIC,NER,NGA,NIU,NFK,MNP,NOR,OMN,PAK,PLW,PAN,PNG,PRY,PER,PHL,PCN,POL,PRT,PRI,QAT,REU,ROM,RUS,RWA,KNA,LCA,VCT,WSM,SMR,STP,SAU,SEN,SYC,SLE,SGP,SVK,SVN,SLB,SOM,ZAF,SGS,ESP,LKA,SHN,SPM,SDN,SUR,SJM,SWZ,SWE,CHE,SYR,TWN,TJK,TZA,THA,TGO,TKL,TON,TTO,TUN,TUR,TKM,TCA,TUV,UGA,UKR,ARE,GBR,USA,UMI,URY,UZB,VUT,VAT,VEN,VNM,VGB,VIR,WLF,ESH,YEM'
        );
        $countries[238] = 'ZMB';
        $countries[239] = 'ZWE';
        $countries[240] = 'ENG';
        $countries[241] = 'SCO';
        $countries[242] = 'WAL';
        $countries[243] = 'ALA';
        $countries[244] = 'NEI';
        $countries[245] = 'MNE';
        $countries[246] = 'SRB';

        return $countries[(int) $country] ?? $country;
    }

    private function existingClubMessage(string $name): string
    {
        return '<span style="color:orange">Using existing club data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    private function existingTeamMessage(object $team): string
    {
        return '<span style="color:orange">Using existing team data: </span><strong>'
            . $this->escape((string) ($team->name ?? '')) . '</strong><br />';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
