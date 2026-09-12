<?php
/**
 * Joomla 5/6 native project XML person importer.
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
 * Prepares project XML import step 13 and promotes local person IDs.
 */
final class XmlProjectPersonImportService
{
    private readonly PlaygroundGeocoder $geocoder;

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
     * @return array<string, mixed>
     */
    public function prepare(array $post, array $parsedData): array
    {
        $seasonId = max(0, (int) ($post['season'] ?? ($post['filter_season'] ?? 0)));
        $positionIdMap = $this->buildPositionIdMap($post, $parsedData);

        foreach (array_values((array) ($parsedData['person'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $ageGroupId = max(
                0,
                (int) ($post['personAgeGroup_' . $key] ?? ($source->agegroup_id ?? 0))
            );
            $databaseId = max(0, (int) ($post['dbPersonID_' . $key] ?? 0));

            if ($databaseId > 0) {
                if ($this->findById($databaseId) === null) {
                    throw new RuntimeException('Selected person was not found.', 404);
                }

                $this->updateExistingPerson($databaseId, $source, $ageGroupId);
                $this->ensureSeasonPerson($databaseId, $seasonId);
                unset($post['personID_' . $key]);
                continue;
            }

            if (!array_key_exists('personID_' . $key, $post)) {
                continue;
            }

            $lastname = substr(
                trim((string) ($post['personLastname_' . $key] ?? ($source->lastname ?? ''))),
                0,
                45
            );
            $firstname = substr(
                trim((string) ($post['personFirstname_' . $key] ?? ($source->firstname ?? ''))),
                0,
                45
            );
            $nickname = substr(
                trim((string) ($post['personNickname_' . $key] ?? ($source->nickname ?? ''))),
                0,
                45
            );
            $birthday = substr(
                trim((string) ($post['personBirthday_' . $key] ?? ($source->birthday ?? ''))),
                0,
                10
            );

            if ($lastname === '' || $firstname === '') {
                throw new RuntimeException('Missing person name for project XML import.', 400);
            }

            $existing = $this->findByIdentity($lastname, $firstname, $nickname, $birthday);

            if ($existing !== null) {
                $databaseId = (int) $existing->id;
                $this->updateExistingPerson($databaseId, $source, $ageGroupId);
                $this->ensureSeasonPerson($databaseId, $seasonId);
            } else {
                $row = $this->filterSourceFields($source, '#__sportsmanagement_person');
                $row->lastname = $lastname;
                $row->firstname = $firstname;
                $row->nickname = $nickname;
                $row->birthday = $birthday;
                $row->info = array_key_exists('personInfo_' . $key, $post)
                    ? substr((string) $post['personInfo_' . $key], 0, 50)
                    : (string) ($source->info ?? '');
                $row->knvbnr = array_key_exists('personKnvbnr_' . $key, $post)
                    ? substr((string) $post['personKnvbnr_' . $key], 0, 10)
                    : (string) ($source->knvbnr ?? '');
                $row->agegroup_id = $ageGroupId;
                $row->published = 1;
                $row->alias = OutputFilter::stringURLSafe(trim($firstname . ' ' . $lastname));

                $oldPositionId = (int) ($source->position_id ?? 0);
                $row->position_id = $oldPositionId > 0
                    ? (int) ($positionIdMap[$oldPositionId] ?? 0)
                    : 0;

                if ((!isset($row->location) || trim((string) $row->location) === '')
                    && isset($source->city)
                ) {
                    $row->location = (string) $source->city;
                }

                $addressCountry = $this->normaliseCountry(
                    (string) ($row->address_country ?? ($source->address_country ?? ''))
                );

                if ($addressCountry !== '') {
                    $row->address_country = $addressCountry;
                }

                $location = (object) [
                    'address' => (string) ($source->address ?? ($row->address ?? '')),
                    'city' => (string) ($source->city ?? ($source->location ?? ($row->location ?? ''))),
                    'zipcode' => (string) ($source->zipcode ?? ($row->zipcode ?? '')),
                    'country' => $addressCountry,
                ];
                $geo = $this->geocoder->geocode($location);

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

                $row = $this->filterTableFields($row, '#__sportsmanagement_person');

                if (!$this->database->insertObject('#__sportsmanagement_person', $row)) {
                    throw new RuntimeException(
                        'Unable to store project person: ' . $firstname . ' ' . $lastname,
                        500
                    );
                }

                $databaseId = (int) $this->database->insertid();
                $this->ensureSeasonPerson($databaseId, $seasonId);
            }

            if ($databaseId <= 0) {
                throw new RuntimeException('Prepared project person has no database ID.', 500);
            }

            $post['dbPersonID_' . $key] = $databaseId;
            unset($post['personID_' . $key]);
        }

        return $post;
    }

    /** @return array<int, int> */
    private function buildPositionIdMap(array $post, array $parsedData): array
    {
        $map = [];

        foreach (array_values((array) ($parsedData['parentposition'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = max(0, (int) ($post['dbParentPositionID_' . $key] ?? 0));

            if ($oldId > 0 && $databaseId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        foreach (array_values((array) ($parsedData['position'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = max(0, (int) ($post['dbPositionID_' . $key] ?? 0));

            if ($oldId > 0 && $databaseId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        return $map;
    }

    private function updateExistingPerson(int $personId, object $source, int $ageGroupId): void
    {
        $columns = $this->database->getTableColumns('#__sportsmanagement_person');
        $row = (object) ['id' => $personId];

        if (array_key_exists('info', $columns)) {
            $row->info = (string) ($source->info ?? '');
        }

        if (array_key_exists('agegroup_id', $columns)) {
            $row->agegroup_id = $ageGroupId;
        }

        if (count(get_object_vars($row)) === 1) {
            return;
        }

        if (!$this->database->updateObject('#__sportsmanagement_person', $row, 'id')) {
            throw new RuntimeException('Unable to update existing project person.', 500);
        }
    }

    private function ensureSeasonPerson(int $personId, int $seasonId): void
    {
        if ($personId <= 0 || $seasonId <= 0) {
            return;
        }

        $personType = 1;
        $teamId = 0;
        $query = $this->database->createQuery()
            ->select('COUNT(*)')
            ->from($this->database->quoteName('#__sportsmanagement_season_person_id'))
            ->where($this->database->quoteName('person_id') . ' = :personId')
            ->where($this->database->quoteName('season_id') . ' = :seasonId')
            ->where($this->database->quoteName('team_id') . ' = :teamId')
            ->where($this->database->quoteName('persontype') . ' = :personType')
            ->bind(':personId', $personId, ParameterType::INTEGER)
            ->bind(':seasonId', $seasonId, ParameterType::INTEGER)
            ->bind(':teamId', $teamId, ParameterType::INTEGER)
            ->bind(':personType', $personType, ParameterType::INTEGER);
        $this->database->setQuery($query);

        if ((int) $this->database->loadResult() > 0) {
            return;
        }

        $relation = (object) [
            'person_id' => $personId,
            'season_id' => $seasonId,
            'team_id' => $teamId,
            'persontype' => $personType,
        ];

        if (!$this->database->insertObject('#__sportsmanagement_season_person_id', $relation)) {
            throw new RuntimeException('Unable to store project person season relation.', 500);
        }
    }

    private function findById(int $id): ?object
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_person'))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findByIdentity(
        string $lastname,
        string $firstname,
        string $nickname,
        string $birthday
    ): ?object {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_person'))
            ->where($this->database->quoteName('lastname') . ' = :lastname')
            ->where($this->database->quoteName('firstname') . ' = :firstname')
            ->where($this->database->quoteName('nickname') . ' = :nickname')
            ->where($this->database->quoteName('birthday') . ' = :birthday')
            ->bind(':lastname', $lastname, ParameterType::STRING)
            ->bind(':firstname', $firstname, ParameterType::STRING)
            ->bind(':nickname', $nickname, ParameterType::STRING)
            ->bind(':birthday', $birthday, ParameterType::STRING);
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
}
