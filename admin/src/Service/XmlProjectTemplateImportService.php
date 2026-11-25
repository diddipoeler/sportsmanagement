<?php
/**
 * Joomla 5/6 native project-template XML import service.
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
use Joomla\Registry\Registry;
use RuntimeException;

/** Native writer for project XML import step 15. */
final class XmlProjectTemplateImportService
{
    private const SKIPPED_TEMPLATES = [
        'do_tipsl',
        'frontpage',
        'table',
        'tipranking',
        'tipresults',
        'user',
    ];

    private const SOURCE_METADATA_FIELDS = [
        'id',
        'project_id',
        'import_id',
        'checked_out',
        'checked_out_time',
        'modified',
        'modified_by',
    ];

    /**
     * Initialise the project template importer with the target database connection.
     * @param DatabaseInterface $database Target database connection.
     */
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * Apply the selected master template project or import the project's own supported templates. Reuse existing template records, merge imported parameters with local defaults and collect the resulting status report.
     * @param array<string,mixed> $post Import form selections.
     * @param array<string,mixed> $parsedData Parsed XML records.
     * @param int $projectId Target project identifier.
     * @return array{messages:array<string,string>} Template import report.
     * @throws RuntimeException If the project is missing or a required write fails.
     */
    public function import(array $post, array $parsedData, int $projectId): array
    {
        if ($projectId <= 0) {
            throw new RuntimeException('Missing project for template XML import.', 400);
        }

        $copyTemplateId = max(0, (int) ($post['copyTemplate'] ?? 0));
        $message = '';

        if ($copyTemplateId > 0) {
            $masterProjectId = $this->resolveMasterProjectId($copyTemplateId);
            $this->setProjectMasterTemplate($projectId, $masterProjectId);
            $message .= '<span style="color:orange">Using master template project: </span><strong>'
                . $masterProjectId . '</strong><br />';

            return ['messages' => ['Importing template data:' => $message]];
        }

        $this->setProjectMasterTemplate($projectId, 0);

        foreach (array_values((array) ($parsedData['template'] ?? [])) as $source) {
            if (!is_object($source)) {
                continue;
            }

            $template = trim((string) ($source->template ?? ''));

            if (!$this->isImportableTemplate($template)) {
                continue;
            }

            $existingId = $this->findTemplate($projectId, $template);

            if ($existingId > 0) {
                $message .= '<span style="color:orange">Using existing project template: </span><strong>'
                    . $this->escape($template) . '</strong><br />';
                continue;
            }

            $row = $this->filterSourceFields($source);
            $row->project_id = $projectId;
            $row->template = $template;
            $row->title = trim((string) ($source->title ?? '')) ?: $template;
            $row->params = $this->normaliseParams($template, (string) ($source->params ?? ''));

            if (!$this->database->insertObject('#__sportsmanagement_template_config', $row)) {
                throw new RuntimeException('Unable to store imported project template: ' . $template, 500);
            }

            $message .= '<span style="color:green">Created new project template: </span><strong>'
                . $this->escape($template) . '</strong><br />';
        }

        return ['messages' => ['Importing template data:' => $message]];
    }

    /**
     * Load the selected template project and return its configured master template identifier when positive, otherwise return the selected project's own identifier.
     * @param int $selectedProjectId Selected project identifier.
     * @return int Resolved master project identifier.
     * @throws RuntimeException If the selected project is absent.
     */
    private function resolveMasterProjectId(int $selectedProjectId): int
    {
        $query = $this->database->createQuery()
            ->select([
                $this->database->quoteName('id'),
                $this->database->quoteName('master_template'),
            ])
            ->from($this->database->quoteName('#__sportsmanagement_project'))
            ->where($this->database->quoteName('id') . ' = :projectId')
            ->bind(':projectId', $selectedProjectId, ParameterType::INTEGER);
        $this->database->setQuery($query, 0, 1);
        $project = $this->database->loadObject();

        if (!$project) {
            throw new RuntimeException('Selected master template project was not found.', 404);
        }

        $masterProjectId = max(0, (int) ($project->master_template ?? 0));

        return $masterProjectId > 0 ? $masterProjectId : (int) $project->id;
    }

    /**
     * Update the target project's master template reference, clamping a negative reference to zero.
     * @param int $projectId Target project identifier.
     * @param int $masterProjectId Master template project identifier.
     * @return void
     * @throws RuntimeException If the update fails.
     */
    private function setProjectMasterTemplate(int $projectId, int $masterProjectId): void
    {
        $row = (object) [
            'id' => $projectId,
            'master_template' => max(0, $masterProjectId),
        ];

        if (!$this->database->updateObject('#__sportsmanagement_project', $row, 'id')) {
            throw new RuntimeException('Unable to update imported project master template.', 500);
        }
    }

    /**
     * Find an existing template configuration by the exact project identifier and template name.
     * @param int $projectId Target project identifier.
     * @param string $template Template name.
     * @return int Template record identifier or zero.
     */
    private function findTemplate(int $projectId, string $template): int
    {
        $query = $this->database->createQuery()
            ->select($this->database->quoteName('id'))
            ->from($this->database->quoteName('#__sportsmanagement_template_config'))
            ->where($this->database->quoteName('project_id') . ' = :projectId')
            ->where($this->database->quoteName('template') . ' = :template')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':template', $template, ParameterType::STRING);
        $this->database->setQuery($query, 0, 1);

        return (int) ($this->database->loadResult() ?? 0);
    }

    /**
     * Reject empty names, names containing directory components, prediction templates and the explicitly excluded historical template names.
     * @param string $template Candidate template name.
     * @return bool Whether the name passes the import filters.
     */
    private function isImportableTemplate(string $template): bool
    {
        if ($template === '' || basename($template) !== $template) {
            return false;
        }

        if (str_starts_with(strtolower($template), 'prediction')) {
            return false;
        }

        return !in_array(strtolower($template), self::SKIPPED_TEMPLATES, true);
    }

    /**
     * Decode the imported template parameters, overlay them on the locally defined default settings and serialize the merged configuration as JSON.
     * @param string $template Template name.
     * @param string $source Imported parameter string.
     * @return string JSON configuration or an empty JSON object on encoding failure.
     */
    private function normaliseParams(string $template, string $source): string
    {
        $current = $this->decodeParams($source);
        $defaults = $this->loadTemplateDefaults($template);
        $params = array_replace($defaults, $current);
        $encoded = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '{}' : $encoded;
    }

    /**
     * Decode a nonempty parameter string as a JSON array first, then attempt legacy INI parsing through Joomla Registry, restoring escaped newline sequences.
     * @param string $source Imported parameter string.
     * @return array<string,mixed> Decoded configuration or an empty array.
     */
    private function decodeParams(string $source): array
    {
        $source = trim($source);

        if ($source === '') {
            return [];
        }

        $decoded = json_decode($source, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        try {
            $registry = new Registry();
            $registry->loadString(str_replace('\\n', "\n", $source), 'INI');

            return $registry->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * Read the local XML settings file with network access disabled and collect named field defaults, excluding spacer and message fields.
     * @param string $template Template name.
     * @return array<string,mixed> Available template field defaults.
     */
    private function loadTemplateDefaults(string $template): array
    {
        $path = JPATH_COMPONENT_SITE . '/settings/default/' . $template . '.xml';

        if (!is_file($path) || !function_exists('simplexml_load_file')) {
            return [];
        }

        $xml = @simplexml_load_file($path, \SimpleXMLElement::class, LIBXML_NONET);

        if (!$xml) {
            return [];
        }

        $fields = $xml->xpath('//field[@name]') ?: [];
        $defaults = [];

        foreach ($fields as $field) {
            $name = trim((string) $field['name']);
            $type = strtolower(trim((string) $field['type']));

            if ($name === '' || in_array($type, ['spacer', 'jsmmessage'], true)) {
                continue;
            }

            $defaults[$name] = (string) $field['default'];
        }

        return $defaults;
    }

    /**
     * Copy schema-supported template fields excluding source identifiers and audit metadata, convert scalar values to strings and reset checkout state when supported.
     * @param object $source Imported template record.
     * @return object Filtered insert record.
     */
    private function filterSourceFields(object $source): object
    {
        $columns = $this->database->getTableColumns('#__sportsmanagement_template_config');
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            $field = (string) $field;

            if (in_array($field, self::SOURCE_METADATA_FIELDS, true)
                || !array_key_exists($field, $columns)
            ) {
                continue;
            }

            $row->{$field} = is_scalar($value) ? (string) $value : $value;
        }

        if (array_key_exists('checked_out', $columns)) {
            $row->checked_out = 0;
        }

        return $row;
    }

    /**
     * Escape a template name for HTML import reports using UTF-8 and both quote types.
     * @param string $value Raw report value.
     * @return string HTML-escaped value.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
