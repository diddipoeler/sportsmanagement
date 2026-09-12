<?php
/**
 * Joomla 5/6 standalone person XML import service.
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

/** Native writer for standalone person XML imports. */
final class XmlPersonImportService
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
     * @return array<string, string>
     */
    public function import(array $post, array $parsedData): array
    {
        $message = '';
        $seasonId = max(0, (int) ($post['filter_season'] ?? 0));

        foreach (array_values((array) ($parsedData['person'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $ageGroupId = max(
                0,
                (int) ($post['personAgeGroup_' . $key] ?? ($source->agegroup_id ?? 0))
            );
            $databaseId = (int) ($post['dbPersonID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findById($databaseId);

                if ($existing === null) {
                    continue;
                }

                $this->updateExistingPerson($databaseId, $source, $ageGroupId);
                $this->ensureSeasonPerson($databaseId, $seasonId);
                $message .= $this->existingMessage($existing);
                continue;
            }

            // Disabled controls are not submitted. personID_* is only enabled
            // when the administrator chose creation of this imported person.
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
                continue;
            }

            $existing = $this->findByIdentity($lastname, $firstname, $nickname, $birthday);

            if ($existing !== null) {
                $message .= $this->existingMessage($existing);
                continue;
            }

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

            // A standalone person XML file cannot safely preserve a position
            // ID from a different SportsManagement database.
            $row->position_id = 0;

            // Older JoomLeague person exports used `city`, while the current
            // SportsManagement person table stores the same value as `location`.
            if ((!isset($row->location) || trim((string) $row->location) === '')
                && isset($source->city)
            ) {
                $row->location = (string) $source->city;
            }

            $addressCountry = $this->normaliseCountry((string) ($row->address_country ?? ($source->address_country ?? '')));

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
                    'Unable to store imported person: ' . $firstname . ' ' . $lastname,
                    500
                );
            }

            $personId = (int) $this->database->insertid();
            $this->ensureSeasonPerson($personId, $seasonId);
            $message .= '<span style="color:green">Created new person data: </span><strong>'
                . $this->escape($this->personLabel($row)) . '</strong><br />';
        }

        return ['Importing person data:' => $message];
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
            throw new RuntimeException('Unable to update existing imported person.', 500);
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
            throw new RuntimeException('Unable to store imported person season relation.', 500);
        }
    }

    private function findById(int $id): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('lastname'),
                $this->database->quoteName('firstname'),
                $this->database->quoteName('nickname'),
                $this->database->quoteName('birthday'),
            ])
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
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('lastname'),
                $this->database->quoteName('firstname'),
                $this->database->quoteName('nickname'),
                $this->database->quoteName('birthday'),
            ])
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

    private function existingMessage(object $person): string
    {
        return '<span style="color:orange">Using existing person data: </span><strong>'
            . $this->escape($this->personLabel($person)) . '</strong><br />';
    }

    private function personLabel(object $person): string
    {
        $label = trim((string) ($person->lastname ?? '')) . ', '
            . trim((string) ($person->firstname ?? ''));
        $nickname = trim((string) ($person->nickname ?? ''));
        $birthday = trim((string) ($person->birthday ?? ''));

        if ($nickname !== '') {
            $label .= ' [' . $nickname . ']';
        }

        if ($birthday !== '') {
            $label .= ' - ' . $birthday;
        }

        return trim($label);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
