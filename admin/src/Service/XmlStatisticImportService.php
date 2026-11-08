<?php
/**
 * Joomla 5/6 statistic XML import service.
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
 * Native writer for Statistic XML rows.
 *
 * It is used directly by standalone imports and by the project pre-resolution
 * layer. Position/statistic and match/statistic relationship graphs remain in
 * the explicit legacy project writer for now.
 */
final class XmlStatisticImportService
{
    /**
     * Create the statistic XML import writer using Joomla's database driver.
     * Stores the database connection used to match or insert statistic definitions from legacy XML documents.
     *
     * @param DatabaseInterface $database Joomla database driver.
     * @return void
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Import selected statistic definitions from a legacy SportsManagement XML file.
     * Reuses explicitly selected local statistics, matches existing entries by name and class, applies database-column filtering and inserts new definitions with escaped result messages.
     *
     * @param array<string,mixed> $post Administrator selections and field overrides.
     * @param array<string,mixed> $parsedData Parsed statistic definition objects.
     * @return array<string,string> HTML status messages for matched or created statistics.
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

    /**
     * Load a statistic definition using its existing database identifier.
     * Uses a bound identifier to check whether a statistic chosen in the administrator import form still exists.
     *
     * @param int $id Local statistic record identifier.
     * @return object|null Statistic ID, name and class, or null.
     */
    private function findById(int $id): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('class'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_statistic'))
            ->where($this->database->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Find a statistic that already has the imported name and implementation class.
     * Binds both fields to avoid inserting duplicate statistic definitions and to preserve previously configured local values.
     *
     * @param string $name Display name of the statistic.
     * @param string $class Statistic calculation class or category.
     * @return object|null Matching existing statistic definition, or null.
     */
    private function findByNameAndClass(string $name, string $class): ?object
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('name'),
                $this->database->quoteName('class'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_statistic'))
            ->where($this->database->quoteName('name') . ' = :name')
            ->where($this->database->quoteName('class') . ' = :class')
            ->bind(':name', $name, ParameterType::STRING)
            ->bind(':class', $class, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return $this->database->loadObject() ?: null;
    }

    /**
     * Keep only statistic properties supported by the current Joomla database schema.
     * Copies allowed columns from the imported statistic object and ignores values for columns not present in the installed table.
     *
     * @param object $source Prepared imported statistic definition.
     * @param string $table Trusted statistic destination table name.
     * @return object Record containing valid statistic table columns.
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
     * Escape imported statistic labels for safe administrator status markup.
     * Encodes HTML control characters and quotes when reporting imported or reused definitions.
     *
     * @param string $value Imported statistic display value.
     * @return string HTML-escaped text.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
