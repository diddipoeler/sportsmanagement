<?php
/**
 * Temporary adapter from the historical project parser to the native importer.
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

/**
 * Keeps only the legacy public getData() parser behind the migration boundary.
 */
final class LegacyProjectContinuationService
{
    /**
     * @param array<string, mixed> $post
     * @param array<string, array<int, int>> $maps
     * @param array<string, string> $messages
     *
     * @return array<string, mixed>
     */
    public function continue(
        object $legacy,
        array $post,
        array $maps,
        array $messages,
        string $targetStep
    ): array {
        $parsedData = $legacy->getData($post);

        if (!is_array($parsedData)) {
            throw new RuntimeException('Unable to load XML data for the project import continuation.', 500);
        }

        $database = $legacy->getDbo();

        if (!$database instanceof DatabaseInterface) {
            throw new RuntimeException('XML continuation database is unavailable.', 500);
        }

        return (new XmlProjectContinuationService($database))->import(
            $post,
            $parsedData,
            $maps,
            $messages,
            $targetStep,
            max(0, (int) ($legacy->_project_id ?? 0)),
            max(0, (int) ($post['season'] ?? ($post['filter_season'] ?? 0))),
            (string) ($legacy->import_version ?? '')
        );
    }
}
