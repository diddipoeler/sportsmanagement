<?php
/**
 * Joomla 5/6 standalone club XML import service.
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

/** Native writer for standalone club XML imports. */
final class XmlClubImportService
{
    private readonly PlaygroundGeocoder $geocoder;

    /**
     * Initialise standalone club XML import with Joomla's database and a geocoder.
     * Uses the injected playground geocoder if available, otherwise creates a native geocoder to enrich imported club locations.
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
     * Import standalone club records from a parsed XML document.
     * Matches explicitly selected or existing clubs, validates names and countries, filters unknown database columns, resets foreign playground references and records created clubs with escaped status messages.
     *
     * @param array<string,mixed> $post Administrator form selections and club overrides.
     * @param array<string,mixed> $parsedData Parsed XML club data objects.
     * @return array<string,string> Escaped HTML messages describing reused or newly created clubs.
     */
    public function import(array $post, array $parsedData): array
    {
        $message = '';

        foreach (array_values((array) ($parsedData['club'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = (int) ($post['dbClubID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findById($databaseId);

                if ($existing !== null) {
                    $message .= $this->existingMessage((string) $existing->name);
                }

                continue;
            }

            // The historical standalone-club form reuses the combined
            // club/team controls and may leave clubName_* empty. Prefer an
            // explicitly submitted value, but fall back to the XML record.
            $name = trim((string) ($post['clubName_' . $key] ?? ''));

            if ($name === '') {
                $name = trim((string) ($source->name ?? ''));
            }

            $name = substr($name, 0, 100);

            if ($name === '') {
                continue;
            }

            $country = trim((string) ($post['clubCountry_' . $key] ?? ''));

            if ($country === '') {
                $country = trim((string) ($source->country ?? ''));
            }

            $country = $this->normaliseCountry($country);
            $existing = $this->findByNameAndCountry($name, $country);

            if ($existing !== null) {
                $message .= $this->existingMessage((string) $existing->name);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_club');
            $row->name = $name;
            $row->country = $country;
            $row->alias = OutputFilter::stringURLSafe($name);

            // Foreign playground IDs are not valid for a standalone club
            // import. Project imports keep their explicit ID-conversion path.
            $row->standard_playground = 0;
            $row->admin = (int) ($post['admin'] ?? 62);

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

            $message .= '<span style="color:green">Created new club data: </span><strong>'
                . $this->escape($name) . '</strong>';

            if ($country !== '') {
                $message .= ' (<strong>' . $this->escape($country) . '</strong>)';
            }

            $message .= '<br />';
        }

        return ['Importing club data:' => $message];
    }

    /**
     * Look up an existing club with the specified database record identifier.
     * Uses a bound numeric ID and a single-record query to avoid inadvertently selecting a different club.
     *
     * @param int $id Existing club identifier.
     * @return object|null Club record with name and country, or null.
     */
    private function findById(int $id): ?object
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
     * Look up a club matching both its submitted name and country code.
     * Binds both match fields to the Joomla database query to detect duplicates before creating new records.
     *
     * @param string $name Club name from XML or the administrator form.
     * @param string $country Normalised country code.
     * @return object|null Matching existing club row, or null.
     */
    private function findByNameAndCountry(string $name, string $country): ?object
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
     * Retain source XML values that map to writable club table columns.
     * Uses Joomla's table schema to strip unknown XML fields and the original record ID, preventing imported IDs from replacing local primary keys.
     *
     * @param object $source Parsed external club row.
     * @param string $table Trusted destination club table name.
     * @return object Column-compatible values excluding source IDs.
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
     * Restrict the final club object to actual destination table columns.
     * Removes transient or geocoder-produced properties that are not part of the installed club table schema.
     *
     * @param object $source Prepared local club record.
     * @param string $table Trusted destination database table.
     * @return object Club object containing supported table columns only.
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
     * Convert a historical numeric country selection to its legacy ISO-like code.
     * Preserves nonnumeric codes and resolves numeric legacy selectors through the original country index mapping, including late-added countries.
     *
     * @param string $country Raw XML or form country value.
     * @return string Normalised country code, or the original unmapped value.
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
     * Format an escaped import status message for a reused club.
     * Preserves the legacy administrator HTML message format while escaping the club name to prevent output markup injection.
     *
     * @param string $name Name of the reused existing club.
     * @return string HTML status line indicating that local club data was reused.
     */
    private function existingMessage(string $name): string
    {
        return '<span style="color:orange">Using existing club data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    /**
     * Encode imported strings safely for administrator import-result markup.
     * Escapes HTML quotes and special characters before embedding external club values in the import report.
     *
     * @param string $value Club name, country or other import summary value.
     * @return string HTML-escaped display text.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
