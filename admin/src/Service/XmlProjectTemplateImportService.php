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

    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string, mixed> $post
     * @param array<string, mixed> $parsedData
     * @return array{messages:array<string,string>}
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

    private function normaliseParams(string $template, string $source): string
    {
        $current = $this->decodeParams($source);
        $defaults = $this->loadTemplateDefaults($template);
        $params = array_replace($defaults, $current);
        $encoded = json_encode($params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '{}' : $encoded;
    }

    /** @return array<string,mixed> */
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
            $registry->loadString(str_replace('\\n', "\n", $source));

            return $registry->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<string,mixed> */
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

    private function filterSourceFields(object $source): object
    {
        $columns = $this->database->getTableColumns('#__sportsmanagement_template_config');
        $row = new \stdClass();

        foreach ($source as $field => $value) {
            $field = (string) $field;

            if (in_array($field, ['id', 'project_id'], true) || !array_key_exists($field, $columns)) {
                continue;
            }

            $row->{$field} = is_scalar($value) ? (string) $value : $value;
        }

        if (array_key_exists('checked_out', $columns)) {
            $row->checked_out = 0;
        }

        return $row;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
