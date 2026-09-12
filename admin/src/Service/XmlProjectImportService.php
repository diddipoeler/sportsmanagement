<?php
/**
 * Joomla 5/6 native project XML import orchestrator.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use RuntimeException;

/** Coordinates the native project XML import steps in dependency order. */
final class XmlProjectImportService
{
    public function __construct(private readonly DatabaseInterface $database)
    {
    }

    /**
     * @param array<string,mixed> $post
     * @param array<string,mixed> $parsedData
     * @return array<string,mixed>
     */
    public function import(
        array $post,
        array $parsedData,
        string $targetStep,
        int $importProjectId,
        string $importVersion
    ): array {
        $prepared = (new XmlProjectReferenceImportService($this->database))->prepare(
            $post,
            $parsedData,
            $targetStep,
            $importProjectId
        );
        $post = $prepared['post'];
        $messages = $this->referenceMessages((array) ($prepared['messages'] ?? []));
        $projectId = max(0, (int) ($prepared['projectId'] ?? 0));

        if (version_compare($targetStep, '15', 'ge')) {
            if ($projectId <= 0) {
                throw new RuntimeException('Project step 15 requires a prepared project.', 500);
            }

            $templateResult = (new XmlProjectTemplateImportService($this->database))->import(
                $post,
                $parsedData,
                $projectId
            );
            $messages = array_replace($messages, $templateResult['messages']);
        }

        $maps = [];

        if (version_compare($targetStep, '16', 'ge')) {
            if ($projectId <= 0) {
                throw new RuntimeException('Project structure import requires a prepared project.', 500);
            }

            $structureResult = (new XmlProjectStructureImportService($this->database))->import(
                $post,
                $parsedData,
                $projectId,
                max(0, (int) ($post['season'] ?? 0)),
                !empty($post['admin']) ? (int) $post['admin'] : 62,
                $importVersion,
                $targetStep
            );
            $maps = $structureResult['maps'];
            $messages = array_replace($messages, $structureResult['messages']);
        }

        if (version_compare($targetStep, '21', 'ge')) {
            $messages = (new XmlProjectContinuationService($this->database))->import(
                $post,
                $parsedData,
                $maps,
                $messages,
                $targetStep,
                $projectId,
                max(0, (int) ($post['season'] ?? 0)),
                $importVersion
            );
        }

        return $messages;
    }

    /**
     * @param list<string> $messages
     * @return array<string,string>
     */
    private function referenceMessages(array $messages): array
    {
        $keys = [
            'Importing sports-type data:',
            'Importing league data:',
            'Importing season data:',
        ];
        $result = [];

        foreach (array_values($messages) as $index => $message) {
            $key = $keys[$index] ?? ('Importing project reference data ' . ($index + 1) . ':');
            $result[$key] = (string) $message;
        }

        return $result;
    }
}
