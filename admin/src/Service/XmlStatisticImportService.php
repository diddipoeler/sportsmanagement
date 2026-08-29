<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Database\DatabaseInterface;
use RuntimeException;

/**
 * Native writer for standalone statistic XML imports.
 *
 * Project imports and position/statistic relationship imports remain behind
 * the explicit legacy boundary until their ID conversion graph is migrated.
 */
final class XmlStatisticImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
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
        $statistics = array_values((array) ($parsedData['statistic'] ?? []));

        foreach ($statistics as $key => $source) {
            if (!is_object($source)) {
                continue;
            }

            $databaseStatisticId = (int) ($post['dbStatisticID_' . $key] ?? 0);

            if ($databaseStatisticId > 0) {
                $existing = $this->findById($databaseStatisticId);

                if ($existing !== null) {
                    $message .= '<span style="color:orange">Using existing statistic data: </span><strong>'
                        . $this->escape((string) $existing->name) . '</strong><br />';
                }

                continue;
            }

            // Disabled form controls are not submitted. The historical form
            // enables statisticID_* only for rows selected for creation.
            if (!array_key_exists('statisticID_' . $key, $post)) {
                continue;
            }

            $name = trim((string) ($post['statisticName_' . $key] ?? ($source->name ?? '')));

            if ($name === '') {
                continue;
            }

            $class = trim((string) ($source->class ?? ''));
            $existing = $this->findByNameAndClass($name, $class);

            if ($existing !== null) {
                $message .= '<span style="color:orange">Using existing statistic data: </span><strong>'
                    . $this->escape((string) $existing->name) . '</strong><br />';
                continue;
            }

            $row = (object) [
                'name' => $name,
                'short' => (string) ($source->short ?? ''),
                'icon' => (string) ($source->icon ?? ''),
                'class' => $class,
                'calculated' => (string) ($source->calculated ?? ''),
                'params' => (string) ($source->params ?? ''),
                'baseparams' => (string) ($source->baseparams ?? ''),
                'note' => (string) ($source->note ?? ''),
                'alias' => trim((string) ($source->alias ?? '')),
            ];

            if ($row->alias === '') {
                $row->alias = OutputFilter::stringURLSafe($name);
            }

            $row = $this->filterTableFields($row, '#__sportsmanagement_statistic');

            if (!$this->database->insertObject('#__sportsmanagement_statistic', $row)) {
                throw new RuntimeException('Unable to store imported statistic: ' . $name, 500);
            }

            $message .= '<span style="color:green">Created new statistic data: </span><strong>'
                . $this->escape($name) . '</strong><br />';
        }

        return ['Importing statistic data:' => $message];
    }

    private function findById(int $id): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('class'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_statistic'))
            ->where($this->database->quoteName('id') . ' = ' . $id);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    private function findByNameAndClass(string $name, string $class): ?object
    {
        $query = $this->database->getQuery(true)
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('class'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_statistic'))
            ->where($this->database->quoteName('name') . ' = ' . $this->database->quote($name))
            ->where($this->database->quoteName('class') . ' = ' . $this->database->quote($class));
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
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

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
