<?php
/**
 * Install missing SportsManagement country associations from component options.
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

/**
 * Non-destructive country-association bootstrap from bundled XML.
 *
 * Configured country values are restricted to ISO-style three-letter tokens
 * and can reference only the component's own associations_XXX.xml packages.
 * Existing countries are never deleted, updated or reseeded on option saves.
 */
final class ConfiguredAssociationInstaller
{
    /** @var string[] Country codes selected without a bundled XML source. */
    private array $missingSources = [];

    /**
     * Inject the SportsManagement component's Joomla database connection.
     *
     * @param DatabaseInterface $database Database containing association rows.
     */
    public function __construct(private DatabaseInterface $database)
    {
    }

    /**
     * Import complete association trees for selected, currently empty countries.
     *
     * Accepts Joomla's multi-select array and the older single-country string.
     * Malformed values are rejected before any database query or filesystem
     * access. For each valid country, checks whether any association already
     * exists and skips it to preserve edits. XML is parsed without network
     * access and validated fully before any rows are inserted. Parent IDs are
     * linked to IDs generated during this country's installation. Repeated
     * names within a single source reuse the first record to respect the
     * legacy unique country/name index; association
     * names, short/middle names, aliases, website, icons and flags are
     * retained. Parent associations can appear after their children in XML;
     * the import orders dependencies before writing rows.
     *
     * @param mixed $selected Saved cfg_country_associations setting.
     * @return array<string,int> Number of installed associations by country.
     * @throws \RuntimeException When a bundled association XML is malformed,
     *     its hierarchy is inconsistent or a database insert fails.
     */
    public function installMissing(mixed $selected): array
    {
        $this->missingSources = [];

        if (is_string($selected)) {
            $selected = [$selected];
        }

        if (!is_array($selected) || count($selected) > 300) {
            return [];
        }

        $countries = [];

        foreach ($selected as $value) {
            if (!is_string($value) || strlen($value) !== 3
                || preg_match('/^[A-Za-z]{3}$/D', $value) !== 1) {
                continue;
            }

            $countries[strtoupper($value)] = true;
        }

        $installed = [];

        foreach (array_keys($countries) as $country) {
            $query = $this->database->createQuery()
                ->select('COUNT(*)')
                ->from($this->database->quoteName('#__sportsmanagement_associations'))
                ->where($this->database->quoteName('country') . ' = :configuredAssociationCountry')
                ->bind(':configuredAssociationCountry', $country, ParameterType::STRING);
            $this->database->setQuery($query);

            // Never overwrite, delete or expand a country already managed by
            // an administrator or populated by an older import.
            if ((int) $this->database->loadResult() > 0) {
                continue;
            }

            $file = JPATH_ADMINISTRATOR
                . '/components/com_sportsmanagement/helpers/xml_files/associations_' . $country . '.xml';

            if (!is_file($file) || !is_readable($file)) {
                $this->missingSources[] = $country;
                continue;
            }

            $xml = @simplexml_load_file($file, \SimpleXMLElement::class, LIBXML_NONET);

            if (!$xml instanceof \SimpleXMLElement || $xml->getName() !== 'structur') {
                throw new \RuntimeException('SportsManagement association XML is invalid for country ' . $country);
            }

            $records = [];
            $seen = [];

            foreach ($xml->associations as $association) {
                if (!isset($association->assocname)) {
                    continue;
                }

                $item = $association->assocname;
                $attrs = $item->attributes();
                $name = trim((string) $item);
                $itemCountry = strtoupper((string) ($attrs['country'] ?? ''));
                $main = (string) ($attrs['main'] ?? '');
                $parentMain = (string) ($attrs['parentmain'] ?? '0');

                if ($name === '' || $itemCountry !== $country
                    || preg_match('/^[0-9]{1,9}$/D', $main) !== 1
                    || preg_match('/^[0-9]{1,9}$/D', $parentMain) !== 1) {
                    throw new \RuntimeException('SportsManagement association XML has invalid hierarchy for ' . $country);
                }

                $seen[$main] = true;
                $short = trim((string) ($attrs['shortname'] ?? ''));

                $records[] = [
                    'main' => $main,
                    'parentMain' => $parentMain,
                    'country' => $country,
                    'name' => $name,
                    'short_name' => $short !== '' ? $short : $name,
                    'middle_name' => $name,
                    'website' => (string) ($attrs['website'] ?? ''),
                    'picture' => 'images/com_sportsmanagement/database/associations/'
                        . basename((string) ($attrs['icon'] ?? '')),
                    'assocflag' => (string) ($attrs['flag'] ?? ''),
                    'alias' => OutputFilter::stringURLSafe($name),
                ];
            }

            if ($records === []) {
                throw new \RuntimeException('SportsManagement association XML has no records for ' . $country);
            }

            // Validate and topologically order parent/child references before
            // writing any rows. Some bundled countries (notably FRA) list a
            // child before its parent, so source order is not always usable.
            // Missing parent keys and dependency cycles fail before SQL writes.
            $pending = $records;
            $ordered = [];
            $resolved = ['0' => true];

            while ($pending !== []) {
                $deferred = [];
                $progress = false;

                foreach ($pending as $values) {
                    if (!isset($resolved[$values['parentMain']])) {
                        $deferred[] = $values;
                        continue;
                    }

                    $ordered[] = $values;
                    $resolved[$values['main']] = true;
                    $progress = true;
                }

                if (!$progress) {
                    throw new \RuntimeException(
                        'SportsManagement association XML has missing or cyclic parents for ' . $country
                    );
                }

                $pending = $deferred;
            }

            $records = $ordered;

            $ids = [];
            $nameIds = [];
            $count = 0;

            foreach ($records as $values) {
                // The legacy schema has a UNIQUE (name,country) index.
                // Some bundled XML files reuse the same name in different
                // branches (e.g. several German Kreise). Preserve the first
                // occurrence and link later XML main IDs to that record.
                // Without this the import would stop on a duplicate key
                // after having already inserted hundreds of rows.
                if (isset($nameIds[$values['name']])) {
                    $ids[$values['main']] = $nameIds[$values['name']];
                    continue;
                }

                $parentId = $values['parentMain'] === '0'
                    ? 0 : (int) ($ids[$values['parentMain']] ?? 0);

                if ($values['parentMain'] !== '0' && $parentId <= 0) {
                    throw new \RuntimeException('SportsManagement association parent ID is unavailable for ' . $country);
                }

                $record = (object) [
                    'country' => $values['country'],
                    'name' => $values['name'],
                    'alias' => $values['alias'],
                    'short_name' => $values['short_name'],
                    'middle_name' => $values['middle_name'],
                    'website' => $values['website'],
                    'picture' => $values['picture'],
                    'assocflag' => $values['assocflag'],
                    'parent_id' => $parentId,
                ];

                if ($this->database->insertObject('#__sportsmanagement_associations', $record) === false) {
                    throw new \RuntimeException('SportsManagement association insert failed for ' . $country);
                }

                $id = (int) ($record->id ?? $this->database->insertid());

                if ($id <= 0) {
                    throw new \RuntimeException('SportsManagement association row ID is unavailable for ' . $country);
                }

                $ids[$values['main']] = $id;
                $nameIds[$values['name']] = $id;
                $count++;
            }

            $installed[$country] = $count;
        }

        return $installed;
    }

    /**
     * Report selected countries for which no bundled association XML exists.
     *
     * The caller can give an administrator a localised warning containing
     * only validated ISO-style country codes, never a filesystem path.
     *
     * @return string[] Selected country identifiers without local source XML.
     */
    public function getMissingSources(): array
    {
        return $this->missingSources;
    }
}
