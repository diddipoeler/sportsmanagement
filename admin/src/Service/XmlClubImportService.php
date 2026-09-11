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

    private function existingMessage(string $name): string
    {
        return '<span style="color:orange">Using existing club data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
