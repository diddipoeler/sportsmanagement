<?php
/**
 * Joomla 5/6 standalone playground XML import service.
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

/** Native writer for standalone playground XML imports. */
final class XmlPlaygroundImportService
{
    private readonly PlaygroundGeocoder $geocoder;

    /**
     * Initialise the standalone venue importer with the target database and an optional geocoder, constructing the default geocoder when none is supplied.
     * @param DatabaseInterface $database Target database connection.
     * @param PlaygroundGeocoder|null $geocoder Optional venue geocoder.
     */
    public function __construct(
        private readonly DatabaseInterface $database,
        ?PlaygroundGeocoder $geocoder = null
    ) {
        $this->geocoder = $geocoder ?? new PlaygroundGeocoder($database);
    }

    /**
     * Process venue records selected in the XML import form. Reuse existing identifiers or name matches, prepare new venue names, aliases and fallback images, clear foreign club identifiers, enrich records with available geocoding results and insert schema-compatible rows.
     * @param array<string,mixed> $post Import form selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @return array<string,string> HTML venue import report.
     * @throws RuntimeException If a venue insertion fails.
     */
    public function import(array $post, array $parsedData): array
    {
        $message = '';
        $placeholder = (string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_stadium', '');

        foreach (array_values((array) ($parsedData['playground'] ?? [])) as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseId = (int) ($post['dbPlaygroundID_' . $key] ?? 0);

            if ($databaseId > 0) {
                $existing = $this->findById($databaseId);

                if ($existing !== null) {
                    $message .= $this->existingMessage((string) $existing->name);
                }

                continue;
            }

            // Disabled form controls are not posted. playgroundID_* is only
            // enabled when the administrator selected creation of this row.
            if (!array_key_exists('playgroundID_' . $key, $post)) {
                continue;
            }

            $name = substr(trim((string) ($post['playgroundName_' . $key] ?? ($source->name ?? ''))), 0, 74);

            if ($name === '') {
                continue;
            }

            $existing = $this->findByName($name);

            if ($existing !== null) {
                $message .= $this->existingMessage((string) $existing->name);
                continue;
            }

            $row = $this->filterSourceFields($source, '#__sportsmanagement_playground');
            $row->name = $name;
            $row->short_name = substr($name, 0, 14);
            $row->alias = substr(OutputFilter::stringURLSafe($name), 0, 74);
            $row->country = trim((string) ($source->country ?? '')) ?: 'DEU';
            $row->picture = trim((string) ($source->picture ?? '')) ?: $placeholder;

            // A standalone playground import cannot safely preserve a club ID
            // from another database. Project imports keep their legacy ID
            // conversion path until clubs/playgrounds are migrated together.
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
                throw new RuntimeException('Unable to store imported playground: ' . $name, 500);
            }

            $message .= '<span style="color:green">Created new playground data: </span><strong>'
                . $this->escape($name) . '</strong>';

            if ($row->country !== '') {
                $message .= ' (<strong>' . $this->escape((string) $row->country) . '</strong>)';
            }

            $message .= '<br />';
        }

        return ['Importing playground data:' => $message];
    }

    /**
     * Find an existing venue by its bound primary key and return its identifier and name for reuse reporting.
     * @param int $id Venue identifier.
     * @return object|null Matching venue or null.
     */
    private function findById(int $id): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_playground'))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Find the first venue matching the supplied name using a bound query parameter.
     * @param string $name Venue name.
     * @return object|null Matching venue or null.
     */
    private function findByName(string $name): ?object
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
     * Copy source properties supported by the target table, excluding the foreign primary key and converting scalar values to strings.
     * @param object $source Imported XML record.
     * @param string $table Target table name.
     * @return object Filtered source record.
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
     * Retain only fields available in the installed target table while preserving values prepared during import and geocoding.
     * @param object $source Prepared venue record.
     * @param string $table Target table name.
     * @return object Schema-compatible insert record.
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
     * Build an informational HTML report entry for a reused venue, escaping its display name.
     * @param string $name Existing venue name.
     * @return string HTML report entry.
     */
    private function existingMessage(string $name): string
    {
        return '<span style="color:orange">Using existing playground data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    /**
     * Escape venue report values for HTML output using UTF-8 and both quote types.
     * @param string $value Raw report value.
     * @return string HTML-escaped value.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
