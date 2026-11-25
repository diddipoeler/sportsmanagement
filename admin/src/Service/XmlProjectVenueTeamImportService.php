<?php
/**
 * Joomla 5/6 native project XML importer for venues, clubs and teams.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;

/**
 * Prepares project XML import steps 9-12 while preserving the legacy ID graph.
 *
 * The native writer stores playgrounds, clubs and teams and then promotes their
 * real database IDs into the historical db*ID_* fields. The remaining legacy
 * importer can therefore build its conversion maps without inserting the same
 * rows again. Step 11 is applied natively before the legacy pass so its update
 * becomes a no-op there.
 */
final class XmlProjectVenueTeamImportService
{
    private readonly PlaygroundGeocoder $geocoder;

    /** @var array<int, int> */
    private array $clubIdMap = [];

    /**
     * Initialise the XML venue and team importer with the target database and an optional geocoder. Create the default playground geocoder when none is supplied.
     * @param DatabaseInterface $database Target database connection.
     * @param PlaygroundGeocoder|null $geocoder Optional playground geocoder.
     */
    public function __construct(
        private readonly DatabaseInterface $database,
        ?PlaygroundGeocoder $geocoder = null
    ) {
        $this->geocoder = $geocoder ?? new PlaygroundGeocoder($database);
    }

    /**
     * Execute the venue, club, venue-club mapping and team preparation stages reached by the supplied import step, carrying the updated selection data forward.
     * @param array<string,mixed> $post Current import form selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param string $step Current import step.
     * @return array<string,mixed> Updated form selections.
     */
    public function prepare(array $post, array $parsedData, string $step): array
    {
        if (version_compare($step, '9', 'ge')) {
            $post = $this->preparePlaygrounds($post, $parsedData);
        }

        if (version_compare($step, '10', 'ge')) {
            $post = $this->prepareClubs($post, $parsedData);
        }

        if (version_compare($step, '11', 'ge')) {
            $this->convertPlaygroundClubIds($post, $parsedData);
        }

        if (version_compare($step, '12', 'ge')) {
            $post = $this->prepareTeams($post, $parsedData);
        }

        return $post;
    }

    /**
     * Validate selected existing playgrounds or prepare new ones from XML records. Reuse matching names, apply the configured picture fallback and record the target identifiers without retaining foreign club references.
     * @param array<string,mixed> $post Current import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<string,mixed> Updated playground selections.
     */
    private function preparePlaygrounds(array $post, array $parsedData): array
    {
        $placeholder = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_stadium', '');

        foreach (array_values((array) ($parsedData['playground'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = max(0, (int) ($post['dbPlaygroundID_' . $key] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_playground', $databaseId, 'playground');
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
                throw new RuntimeException('Missing playground name for project XML import.', 400);
            }

            $existing = $this->findPlaygroundByName($name);

            if ($existing !== null) {
                $databaseId = (int) $existing->id;
            } else {
                $row = $this->filterSourceFields($source, '#__sportsmanagement_playground');
                $row->name = $name;
                $row->short_name = substr($name, 0, 14);
                $row->alias = substr(OutputFilter::stringURLSafe($name), 0, 74);
                $row->country = trim((string) ($source->country ?? '')) ?: 'DEU';
                $row->picture = trim((string) ($source->picture ?? '')) ?: $placeholder;

                // Do not copy a foreign club primary key. Step 11 restores the
                // relationship after the project clubs have real local IDs.
                $row->club_id = 0;

                $geo = $this->geocoder->geocode($row);

                if ($geo !== null) {
                    if ($geo['latitude'] !== null) {
                        $row->latitude = $geo['latitude'];
                    }

                    if ($geo['longitude'] !== null) {
                        $row->longitude = $geo['longitude'];
                    }

                    if ($geo['state'] !== '' && property_exists($row, 'state')) {
                        $row->state = $geo['state'];
                    }
                }

                $row = $this->filterTableFields($row, '#__sportsmanagement_playground');

                if (!$this->database->insertObject('#__sportsmanagement_playground', $row)) {
                    throw new RuntimeException('Unable to store project playground: ' . $name, 500);
                }

                $databaseId = (int) $this->database->insertid();
            }

            if ($databaseId <= 0) {
                throw new RuntimeException('Prepared project playground has no database ID: ' . $name, 500);
            }

            $post['dbPlaygroundID_' . $key] = $databaseId;
            unset($post['playgroundID_' . $key]);
        }

        return $post;
    }

    /**
     * Resolve selected or newly imported clubs for the XML teams. Reuse existing club mappings and matching names and countries, and use prepared playground mappings when importing club data.
     * @param array<string,mixed> $post Current import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<string,mixed> Updated club selections.
     */
    private function prepareClubs(array $post, array $parsedData): array
    {
        $playgroundIdMap = $this->buildPlaygroundIdMap($post, $parsedData);
        $this->clubIdMap = [];

        foreach (array_values((array) ($parsedData['team'] ?? [])) as $key => $team) {
            if (!is_object($team)) {
                continue;
            }

            $oldClubId = (int) ($team->club_id ?? 0);
            $databaseId = max(0, (int) ($post['dbClubID_' . $key] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_club', $databaseId, 'club');

                if ($oldClubId > 0) {
                    $this->clubIdMap[$oldClubId] = $databaseId;
                }

                $post['clubName_' . $key] = '';
                unset($post['clubID_' . $key]);
                continue;
            }

            if (!array_key_exists('clubID_' . $key, $post)) {
                continue;
            }

            if ($oldClubId > 0 && isset($this->clubIdMap[$oldClubId])) {
                $databaseId = $this->clubIdMap[$oldClubId];
            } else {
                $sourceClub = $this->findSourceClub($oldClubId, $parsedData);
                $name = substr(
                    trim((string) ($post['clubName_' . $key] ?? ($sourceClub->name ?? ''))),
                    0,
                    100
                );

                if ($name === '') {
                    throw new RuntimeException('Missing club name for project XML import.', 400);
                }

                $country = trim((string) ($post['clubCountry_' . $key] ?? ($sourceClub->country ?? '')));
                $country = $this->normaliseCountry($country);
                $existing = $this->findClubByNameAndCountry($name, $country);

                if ($existing !== null) {
                    $databaseId = (int) $existing->id;
                } else {
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
                        throw new RuntimeException('Unable to store project club: ' . $name, 500);
                    }

                    $databaseId = (int) $this->database->insertid();
                }
            }

            if ($databaseId <= 0) {
                throw new RuntimeException('Prepared project club has no database ID.', 500);
            }

            if ($oldClubId > 0) {
                $this->clubIdMap[$oldClubId] = $databaseId;
            }

            $post['dbClubID_' . $key] = $databaseId;
            // The legacy mapper only treats dbClubID_* as an existing choice
            // when the corresponding submitted club name is empty.
            $post['clubName_' . $key] = '';
            unset($post['clubID_' . $key]);
        }

        return $post;
    }

    /**
     * Translate source playground and club identifiers to target database identifiers and update the prepared playground's club relation when it differs.
     * @param array<string,mixed> $post Current import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return void
     * @throws RuntimeException If updating a prepared playground fails.
     */
    private function convertPlaygroundClubIds(array $post, array $parsedData): void
    {
        $playgroundIdMap = $this->buildPlaygroundIdMap($post, $parsedData);
        $clubIdMap = $this->buildClubIdMap($post, $parsedData);

        foreach (array_values((array) ($parsedData['playground'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldPlaygroundId = (int) ($source->id ?? 0);

            if ($oldPlaygroundId <= 0 || !isset($playgroundIdMap[$oldPlaygroundId])) {
                continue;
            }

            $databasePlaygroundId = (int) $playgroundIdMap[$oldPlaygroundId];
            $oldClubId = (int) ($source->club_id ?? 0);
            $databaseClubId = $oldClubId > 0 ? (int) ($clubIdMap[$oldClubId] ?? 0) : 0;
            $playground = $this->requirePlaygroundById($databasePlaygroundId);

            if ((int) ($playground->club_id ?? 0) === $databaseClubId) {
                continue;
            }

            $update = (object) [
                'id' => $databasePlaygroundId,
                'club_id' => $databaseClubId,
            ];

            if (!$this->database->updateObject('#__sportsmanagement_playground', $update, 'id')) {
                throw new RuntimeException(
                    'Unable to update imported playground club relation for ID ' . $databasePlaygroundId,
                    500
                );
            }
        }
    }

    /**
     * Validate selected existing teams or reuse matching team identities. Prepare new teams with resolved club and playground references and update the import selections.
     * @param array<string,mixed> $post Current import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<string,mixed> Updated team selections.
     */
    private function prepareTeams(array $post, array $parsedData): array
    {
        $playgroundIdMap = $this->buildPlaygroundIdMap($post, $parsedData);
        $clubIdMap = $this->buildClubIdMap($post, $parsedData);

        foreach (array_values((array) ($parsedData['team'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = max(0, (int) ($post['dbTeamID_' . $key] ?? 0));

            if ($databaseId > 0) {
                $this->requireById('#__sportsmanagement_team', $databaseId, 'team');
                $post['teamName_' . $key] = '';
                unset($post['teamID_' . $key]);
                continue;
            }

            if (!array_key_exists('teamID_' . $key, $post)) {
                continue;
            }

            $name = substr(
                trim((string) ($post['teamName_' . $key] ?? ($source->name ?? ''))),
                0,
                75
            );

            if ($name === '') {
                throw new RuntimeException('Missing team name for project XML import.', 400);
            }

            $info = (string) ($post['teamInfo_' . $key] ?? ($source->info ?? ''));
            $middleName = substr($name, 0, 24);
            $existing = $this->findTeamByIdentity($name, $middleName, $info);

            if ($existing !== null) {
                $databaseId = (int) $existing->id;
            } else {
                $oldClubId = (int) ($source->club_id ?? 0);
                $databaseClubId = max(0, (int) ($post['dbClubID_' . $key] ?? 0));

                if ($databaseClubId <= 0 && $oldClubId > 0) {
                    $databaseClubId = (int) ($clubIdMap[$oldClubId] ?? 0);
                }

                $row = $this->filterSourceFields($source, '#__sportsmanagement_team');
                $row->name = $name;
                $row->short_name = substr($name, 0, 14);
                $row->middle_name = $middleName;
                $row->info = $info;
                $row->club_id = $databaseClubId;
                $row->sports_type_id = max(0, (int) ($post['sportstype'] ?? 0));
                $row->agegroup_id = max(0, (int) ($post['agegroup_id'] ?? 0));
                $row->alias = OutputFilter::stringURLSafe($name);

                $oldPlaygroundId = (int) ($source->standard_playground ?? 0);
                $row->standard_playground = $oldPlaygroundId > 0
                    ? (int) ($playgroundIdMap[$oldPlaygroundId] ?? 0)
                    : 0;

                $row = $this->filterTableFields($row, '#__sportsmanagement_team');

                if (!$this->database->insertObject('#__sportsmanagement_team', $row)) {
                    throw new RuntimeException('Unable to store project team: ' . $name, 500);
                }

                $databaseId = (int) $this->database->insertid();
            }

            if ($databaseId <= 0) {
                throw new RuntimeException('Prepared project team has no database ID: ' . $name, 500);
            }

            $post['dbTeamID_' . $key] = $databaseId;
            // The legacy team mapper mirrors the club mapper and requires the
            // submitted new-team name to be empty on the existing-ID path.
            $post['teamName_' . $key] = '';
            unset($post['teamID_' . $key]);
        }

        return $post;
    }

    /**
     * Build a source-to-target playground identifier map using positive identifiers from parsed XML records and the corresponding import form selections.
     * @param array<string,mixed> $post Current import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<int,int> Source playground IDs mapped to target IDs.
     */
    private function buildPlaygroundIdMap(array $post, array $parsedData): array
    {
        $map = [];

        foreach (array_values((array) ($parsedData['playground'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $oldId = (int) ($source->id ?? 0);
            $databaseId = max(0, (int) ($post['dbPlaygroundID_' . $key] ?? 0));

            if ($oldId > 0 && $databaseId > 0) {
                $map[$oldId] = $databaseId;
            }
        }

        return $map;
    }

    /**
     * Build the source-to-target club identifier map from XML team records and submitted club selections. Later selections replace earlier mappings for duplicate source clubs.
     * @param array<string,mixed> $post Current import selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<int,int> Source club IDs mapped to target IDs.
     */
    private function buildClubIdMap(array $post, array $parsedData): array
    {
        $map = [];

        foreach (array_values((array) ($parsedData['team'] ?? [])) as $key => $team) {
            if (!is_object($team)) {
                continue;
            }

            $oldClubId = (int) ($team->club_id ?? 0);
            $databaseId = max(0, (int) ($post['dbClubID_' . $key] ?? 0));

            if ($oldClubId > 0 && $databaseId > 0) {
                // Legacy conversion arrays are assigned in iteration order, so
                // later choices intentionally replace earlier duplicates.
                $map[$oldClubId] = $databaseId;
            }
        }

        return $map;
    }

    /**
     * Find the XML club record matching a positive source identifier.
     * @param int $oldClubId Source club identifier.
     * @param array $parsedData Parsed XML records.
     * @return object|null Source club or null.
     */
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

    /**
     * Read the selected target record's identifier and name using a bound identifier. Reject a selection that does not exist.
     * @param string $table Target table name.
     * @param int $id Selected record identifier.
     * @param string $label Record type used in the error message.
     * @return object Selected record.
     * @throws RuntimeException If the record is absent.
     */
    private function requireById(string $table, int $id, string $label): object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName($table))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $row = $this->database->loadObject();

        if (!$row) {
            throw new RuntimeException('Selected ' . $label . ' was not found.', 404);
        }

        return $row;
    }

    /**
     * Load a prepared playground with its current club reference for relationship conversion.
     * @param int $id Target playground identifier.
     * @return object Prepared playground.
     * @throws RuntimeException If the playground is absent.
     */
    private function requirePlaygroundById(int $id): object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('club_id'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_playground'))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $row = $this->database->loadObject();

        if (!$row) {
            throw new RuntimeException('Prepared project playground was not found.', 404);
        }

        return $row;
    }

    /**
     * Look up an existing playground by its exact name using a bound query parameter.
     * @param string $name Playground name.
     * @return object|null Matching playground or null.
     */
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

    /**
     * Look up an existing club by its exact name and country code using bound parameters.
     * @param string $name Club name.
     * @param string $country Country code.
     * @return object|null Matching club or null.
     */
    private function findClubByNameAndCountry(string $name, string $country): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_club'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->where($this->database->quoteName('country') . ' = :country')
            ->bind(':name', $name, ParameterType::STRING)
            ->bind(':country', $country, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Look up an existing team by its name, middle name and information field to avoid creating duplicate import records.
     * @param string $name Team name.
     * @param string $middleName Intermediate team name.
     * @param string $info Team information.
     * @return object|null Matching team or null.
     */
    private function findTeamByIdentity(string $name, string $middleName, string $info): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
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

    /**
     * Copy XML source properties that exist in the target schema, excluding the foreign primary key. Convert scalar source values to strings before preparing the insert record.
     * @param object $source XML record.
     * @param string $table Target table name.
     * @return object Filtered insert record.
     */
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

    /**
     * Retain only properties supported by the target database schema, preserving their original values.
     * @param object $source Proposed record.
     * @param string $table Target table name.
     * @return object Schema-compatible record.
     */
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

    /**
     * Trim the imported country value and translate numeric legacy country identifiers using the historical country-code mapping. Preserve nonnumeric or unmapped values.
     * @param string $country Source country value.
     * @return string Country code or unchanged unmapped value.
     */
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
