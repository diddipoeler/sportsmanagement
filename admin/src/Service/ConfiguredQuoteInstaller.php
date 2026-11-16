<?php
/**
 * Install selected SportsManagement quote collections from bundled XML.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Idempotent importer for the five quote categories in admin/config.xml.
 *
 * cfg_quotes stores literal "type,daily_number" pairs. Only the component's
 * fixed list of bundled XML sources is accepted; submitted values never become
 * filesystem paths, SQL fragments or arbitrary category identifiers.
 */
final class ConfiguredQuoteInstaller
{
    private const QUOTE_SOURCES = [
        'player,1' => ['player', 1],
        'staff,2' => ['staff', 2],
        'functionaries,3' => ['functionaries', 3],
        'reporter,4' => ['reporter', 4],
        'others,5' => ['others', 5],
    ];

    /**
     * Create an installer using Joomla's current component database.
     *
     * @param DatabaseInterface $database Database containing the quotes table.
     */
    public function __construct(private DatabaseInterface $database)
    {
    }

    /**
     * Import only selected collections which have no stored quotes yet.
     *
     * Accepts Joomla's array-valued multiple selection and older single
     * strings. Each category is checked independently via daily_number.
     * Existing quotes, including user-edited records, are never touched.
     * Bundled XML is parsed without network entities, validated for its
     * category number, and imported with the original text, author and notes.
     *
     * @param mixed $selected Saved cfg_quotes component option value.
     * @return array<string,int> Newly installed quote counts per option value.
     * @throws \RuntimeException If the bundled XML cannot be parsed or stored.
     */
    public function installMissing(mixed $selected): array
    {
        if (is_string($selected)) {
            $selected = [$selected];
        }

        if (!is_array($selected) || count($selected) > 100) {
            return [];
        }

        $installed = [];

        foreach (array_unique(array_filter($selected, 'is_string')) as $choice) {
            if (!isset(self::QUOTE_SOURCES[$choice])) {
                continue;
            }

            [$type, $dailyNumber] = self::QUOTE_SOURCES[$choice];

            // Only bootstrap a completely empty category. User-managed data,
            // including any records imported by the older installer, wins.
            $query = $this->database->createQuery()
                ->select('COUNT(*)')
                ->from($this->database->quoteName('#__sportsmanagement_rquote'))
                ->where($this->database->quoteName('daily_number') . ' = :quoteDailyNumber')
                ->bind(':quoteDailyNumber', $dailyNumber, ParameterType::INTEGER);
            $this->database->setQuery($query);

            if ((int) $this->database->loadResult() > 0) {
                continue;
            }

            $file = JPATH_ADMINISTRATOR
                . '/components/com_sportsmanagement/helpers/xml_files/quote_' . $type . '.xml';

            if (!is_file($file) || !is_readable($file)) {
                throw new \RuntimeException('Bundled SportsManagement quote source is unavailable: ' . $type);
            }

            $document = @simplexml_load_file($file, \SimpleXMLElement::class, LIBXML_NONET);

            if (!$document instanceof \SimpleXMLElement) {
                throw new \RuntimeException('Bundled SportsManagement quote XML is invalid: ' . $type);
            }

            // Validate the whole source before inserting any of its records.
            // A mismatched category is a damaged package, not a valid config.
            $records = [];

            foreach ($document->quotes as $entry) {
                if (!isset($entry->quote)) {
                    continue;
                }

                $attributes = $entry->quote->attributes();
                $number = (string) ($attributes['daily_number'] ?? '');

                if ($number !== (string) $dailyNumber) {
                    throw new \RuntimeException('Bundled SportsManagement quote category does not match: ' . $type);
                }

                $quote = trim((string) $entry->quote);

                if ($quote === '') {
                    continue;
                }

                $records[] = (object) [
                    'daily_number' => $dailyNumber,
                    'quote' => $quote,
                    'author' => (string) ($attributes['author'] ?? ''),
                    'notes' => (string) ($attributes['notes'] ?? ''),
                ];
            }

            if ($records === []) {
                throw new \RuntimeException('Bundled SportsManagement quote source is empty: ' . $type);
            }

            foreach ($records as $record) {
                if ($this->database->insertObject('#__sportsmanagement_rquote', $record) === false) {
                    throw new \RuntimeException('SportsManagement quote import failed: ' . $type);
                }
            }

            $installed[$choice] = count($records);
        }

        return $installed;
    }
}
