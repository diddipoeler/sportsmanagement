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

    /**
     * Initialise the standalone XML team importer with database and geocoder services.
     * Stores Joomla's database and a supplied or new playground geocoder for resolving club and venue relationships.
     *
     * @param DatabaseInterface $database Joomla database driver.
     * @param PlaygroundGeocoder|null $geocoder Optional geocoding service.
     * @return void
     */
    public function __construct(
        private readonly DatabaseInterface $database,
        ?PlaygroundGeocoder $geocoder = null
    ) {
        $this->geocoder = $geocoder ?? new PlaygroundGeocoder($database);
    }

    /**
     * Import selected teams and their club/playground dependencies from XML.
     * Resolves imported venues and clubs first, maps external relation IDs to local values, reuses matching teams and inserts only selected new team rows with escaped messages.
     *
     * @param array<string,mixed> $post Administrator selections and team/club overrides.
     * @param array<string,mixed> $parsedData Parsed team, club and playground XML collections.
     * @return array<string,string> Club, team and venue import-status messages.
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
     * Resolve or create the local club associated with one imported team.
     * Reuses already mapped or name/country-matched clubs; otherwise imports permitted club fields, converts playground IDs and geocodes the new record.
     *
     * @param int $key Index of the imported team in the form.
     * @param object $team Original team XML record.
     * @param array<string,mixed> $post Administrator club selections and overrides.
     * @param array<string,mixed> $parsedData Source XML club records.
     * @param array<int,int> $playgroundIdMap Mapping of external venue IDs to local records.
     * @return array{0:int,1:string} Resolved local club ID and an escaped status message.
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

    /**
     * Map external playground IDs onto existing or newly selected local venues.
     * Looks up the form's explicit local venue IDs, otherwise matches venues by name, preventing foreign primary keys from being assigned directly.
     *
     * @param array<string,mixed> $post Submitted playground selection and override fields.
     * @param array<string,mixed> $parsedData Source XML playground records.
     * @return array<int,int> External venue ID to local playground ID mapping.
     */
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

    /**
     * Find the source XML club record referenced by an imported team.
     * Searches the parsed club collection for the team's original club ID without relying on destination database IDs.
     *
     * @param int $oldClubId Original club identifier from the source XML.
     * @param array<string,mixed> $parsedData Parsed source XML collections.
     * @return object|null Matching source club record, or null.
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
     * Load a club using its selected local identifier.
     * Uses a bound numeric database ID to check a club explicitly chosen by the administrator.
     *
     * @param int $id Local SportsManagement club ID.
     * @return object|null Club details including name and country, or null.
     */
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

    /**
     * Find a destination club matching both its name and country.
     * Binds club identifiers to reuse a previously imported club instead of creating duplicate local records.
     *
     * @param string $name Club name.
     * @param string $country Normalised country code.
     * @return object|null Existing matching club, or null.
     */
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

    /**
     * Load a stored team by its local database identifier.
     * Retrieves the team's identity, names, club association and information for existing-record reuse.
     *
     * @param int $id Local team database ID.
     * @return object|null Matching existing team, or null.
     */
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

    /**
     * Look up a duplicate team by name, middle name and descriptive information.
     * Uses a bound combination of fields from the import to avoid inserting a team already present under the same identity.
     *
     * @param string $name Imported full team name.
     * @param string $middleName Normalised middle team name.
     * @param string $info Additional team identity information.
     * @return object|null Matching local team, or null.
     */
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

    /**
     * Find a local playground matching its XML display name.
     * Uses a bound name query to build a safe venue-ID mapping when the administrator has not selected an explicit existing playground.
     *
     * @param string $name Imported playground name.
     * @return object|null Existing playground ID and name, or null.
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
     * Copy only supported imported team or club fields excluding external primary IDs.
     * Consults the destination table schema before accepting XML properties and converts scalar source values to strings.
     *
     * @param object $source XML team or club data object.
     * @param string $table Trusted local destination table.
     * @return object Allowed imported values excluding external row identifiers.
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
     * Strip unsupported database columns from a prepared team or club record.
     * Uses Joomla's table metadata to avoid persisting transient XML or geocoding properties that are not installed.
     *
     * @param object $source Prepared team or club row.
     * @param string $table Trusted local destination table.
     * @return object Schema-compatible row for the importer.
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
     * Convert historic numeric country references to legacy country-code strings.
     * Preserves nonnumeric country labels and resolves old database indexes using the existing country mapping.
     *
     * @param string $country Raw country value from XML or the import form.
     * @return string Normalised country code or the original value.
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

    /**
     * Format an escaped import report line for an existing club.
     * Uses the legacy orange status style while escaping the destination club name.
     *
     * @param string $name Name of the reused local club.
     * @return string HTML status line for a reused club.
     */
    private function existingClubMessage(string $name): string
    {
        return '<span style="color:orange">Using existing club data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    /**
     * Format an escaped import report line for a reused team.
     * Displays the local team's name without allowing untrusted content to render as HTML.
     *
     * @param object $team Existing team record.
     * @return string HTML status line for a reused team.
     */
    private function existingTeamMessage(object $team): string
    {
        return '<span style="color:orange">Using existing team data: </span><strong>'
            . $this->escape((string) ($team->name ?? '')) . '</strong><br />';
    }

    /**
     * Escape XML-derived team and club labels for safe administrator reports.
     * Converts HTML special characters and quotes before including imported strings in status markup.
     *
     * @param string $value Team, club or other imported text.
     * @return string HTML-escaped display text.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
