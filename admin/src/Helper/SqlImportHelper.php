<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Installer\Installer;
use Joomla\Database\DatabaseInterface;

/** Execute bundled SportsManagement SQL files with Joomla's SQL splitter. */
final class SqlImportHelper
{
    public static function importFile(DatabaseInterface $db, string $sqlFile): void
    {
        if (!is_file($sqlFile) || !is_readable($sqlFile)) {
            throw new \RuntimeException('SQL import file is not readable: ' . basename($sqlFile));
        }

        try {
            $sql = file_get_contents($sqlFile);

            if ($sql === false) {
                throw new \RuntimeException('SQL import file could not be read.');
            }
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Unable to read SQL import file: ' . basename($sqlFile),
                0,
                $exception
            );
        }

        if ($sql === '') {
            return;
        }

        foreach (Installer::splitSql($sql) as $statement) {
            if (is_array($statement)) {
                $statement = $statement['query'] ?? '';
            }

            $statement = trim((string) $statement);

            if ($statement === '') {
                continue;
            }

            $db->setQuery($statement)->execute();
        }
    }
}
