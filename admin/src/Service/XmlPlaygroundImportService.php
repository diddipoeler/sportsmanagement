<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/** Native writer for standalone playground XML imports. */
final class XmlPlaygroundImportService
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

    private function findById(int $id): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_playground'))
            ->where($this->database->quoteName('id') . ' = ' . $id);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findByName(string $name): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_playground'))
            ->where($this->database->quoteName('name') . ' = ' . $this->database->quote($name));
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function filterSourceFields(object $source, string $table): object
    {
        $columns = $this->database->getTableColumns($table);
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            $field = (string) $field;

            if (array_key_exists($field, $columns)) {
                $row->{$field} = is_scalar($value) ? (string) $value : $value;
            }
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

    private function existingMessage(string $name): string
    {
        return '<span style="color:orange">Using existing playground data: </span><strong>'
            . $this->escape($name) . '</strong><br />';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
