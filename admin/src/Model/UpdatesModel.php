<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 model for local SportsManagement update scripts and history. */
final class UpdatesModel extends BaseDatabaseModel
{
    public function loadUpdateFile($myfilename, $file): string
    {
        $fileName = trim((string) $file);
        $resolved = $this->resolveUpdateFile($fileName, (string) $myfilename);

        if ($resolved === null) {
            $this->administratorApplication()->enqueueMessage(Text::_('Update file not found!'), 'error');

            return '';
        }

        $version = null;
        $major = null;
        $minor = null;
        $build = null;
        $revision = null;

        include $resolved;

        if ($fileName === 'jl_upgrade-0_93b_to_1_5.php') {
            return '';
        }

        $db = $this->sportsDatabase();
        $existing = $this->getVersionRow($db, $fileName);
        $baseVersion = $this->getVersionRow($db, 'sportsmanagement');
        $record = (object) [
            'id' => (int) ($existing->id ?? 0),
            'count' => (int) ($existing->count ?? 0) + 1,
            'file' => $fileName,
            'version' => $this->metadataValue($version, $baseVersion->version ?? ''),
            'major' => (int) $this->metadataValue($major, $baseVersion->major ?? 0),
            'minor' => (int) $this->metadataValue($minor, $baseVersion->minor ?? 0),
            'build' => (int) $this->metadataValue($build, $baseVersion->build ?? 0),
            'revision' => (string) $this->metadataValue($revision, $baseVersion->revision ?? ''),
        ];

        try {
            if ($record->id > 0) {
                $db->updateObject('#__sportsmanagement_version', $record, 'id');
            } else {
                unset($record->id);
                $db->insertObject('#__sportsmanagement_version', $record);
            }
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'notice'
            );
        }

        return '';
    }

    public function getVersions(): array
    {
        $db = $this->sportsDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('version'),
                $db->quoteName('date'),
            ])
            ->from($db->quoteName('#__sportsmanagement_version'))
            ->order($db->quoteName('date') . ' DESC');

        try {
            $db->setQuery($query);
            $rows = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'notice');

            return [];
        }

        foreach ($rows as $row) {
            if (!empty($row->date)) {
                $row->date = substr((string) $row->date, 0, 16);
            }
        }

        return $rows;
    }

    public function _cmpDate($a, $b): int
    {
        $left = strtotime((string) ($a['updateFileDate'] ?? '')) ?: 0;
        $right = strtotime((string) ($b['updateFileDate'] ?? '')) ?: 0;

        return $right <=> $left;
    }

    public function _cmpName($a, $b): int
    {
        return strcasecmp((string) ($a['file_name'] ?? ''), (string) ($b['file_name'] ?? ''));
    }

    public function _cmpVersion($a, $b): int
    {
        return strcasecmp((string) ($a['last_version'] ?? ''), (string) ($b['last_version'] ?? ''));
    }

    public function getVersionHistory(): array
    {
        $db = $this->sportsDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_version_history'))
            ->order($db->quoteName('date') . ' DESC');

        try {
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'notice');

            return [];
        }
    }

    public function loadUpdateFiles(): array
    {
        $updateFiles = [];
        $fileNames = $this->discoverUpdateFiles();
        $db = $this->sportsDatabase();
        $app = $this->administratorApplication();

        foreach ($fileNames as $fileName) {
            $resolved = $this->resolveUpdateFile($fileName);

            if ($resolved === null) {
                continue;
            }

            $content = @file_get_contents($resolved);

            if ($content === false) {
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', $resolved, __LINE__),
                    'notice'
                );
                continue;
            }

            if (strtolower($this->extractMetadataValue($content, 'excludeFile')) === 'true') {
                continue;
            }

            $stored = $this->getVersionRow($db, $fileName);
            $updateFiles[] = [
                'id' => count($updateFiles),
                'file_name' => $fileName,
                'version' => $this->extractMetadataValue($content, 'version'),
                'last_version' => $this->extractMetadataValue($content, 'lastVersion'),
                'updateFileDate' => $this->extractMetadataValue($content, 'updateFileDate'),
                'updateFileTime' => $this->extractMetadataValue($content, 'updateFileTime'),
                'updateTime' => '0000-00-00 00:00:00',
                'updateDescription' => $this->extractMetadataValue($content, 'updateDescription'),
                'date' => (string) ($stored->date ?? ''),
                'count' => (int) ($stored->count ?? 0),
            ];
        }

        $option = 'com_sportsmanagement';
        $filterOrder = (string) $app->getUserState($option . 'updates_filter_order', 'dates');
        $filterDirection = strtoupper((string) $app->getUserState($option . 'updates_filter_order_Dir', ''));
        $compareMethod = match ($filterOrder) {
            'name' => '_cmpName',
            'version' => '_cmpVersion',
            'date', 'dates' => '_cmpDate',
            default => '_cmpDate',
        };

        usort($updateFiles, [$this, $compareMethod]);

        if ($filterDirection === 'ASC') {
            $updateFiles = array_reverse($updateFiles);
        }

        return $updateFiles;
    }

    private function discoverUpdateFiles(): array
    {
        $files = [];
        $adminUpdates = JPATH_ADMINISTRATOR
            . DIRECTORY_SEPARATOR . 'components'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'assets'
            . DIRECTORY_SEPARATOR . 'updates';

        foreach ($this->phpFiles($adminUpdates) as $file) {
            $files[] = $file;
        }

        $extensionsRoot = JPATH_SITE
            . DIRECTORY_SEPARATOR . 'components'
            . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
            . DIRECTORY_SEPARATOR . 'extensions';

        if (is_dir($extensionsRoot)) {
            foreach (new \DirectoryIterator($extensionsRoot) as $extension) {
                if ($extension->isDot() || !$extension->isDir()) {
                    continue;
                }

                $extensionName = $extension->getFilename();
                $installDir = $extension->getPathname()
                    . DIRECTORY_SEPARATOR . 'admin'
                    . DIRECTORY_SEPARATOR . 'install';

                foreach ($this->phpFiles($installDir) as $file) {
                    $files[] = $extensionName . '/' . $file;
                }
            }
        }

        $files = array_values(array_unique($files));
        natcasesort($files);

        return array_values($files);
    }

    private function phpFiles(string $directory): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];

        foreach (new \DirectoryIterator($directory) as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }

            if (strtolower($file->getExtension()) === 'php') {
                $files[] = $file->getFilename();
            }
        }

        natcasesort($files);

        return array_values($files);
    }

    private function resolveUpdateFile(string $fileName, string $providedPath = ''): ?string
    {
        $normalised = str_replace('\\', '/', trim($fileName));
        $parts = array_values(array_filter(explode('/', $normalised), static fn (string $part): bool => $part !== ''));

        if (!$parts || count($parts) > 2) {
            return null;
        }

        foreach ($parts as $part) {
            if ($part === '.' || $part === '..' || basename($part) !== $part) {
                return null;
            }
        }

        if (count($parts) === 1) {
            $candidate = JPATH_ADMINISTRATOR
                . DIRECTORY_SEPARATOR . 'components'
                . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
                . DIRECTORY_SEPARATOR . 'assets'
                . DIRECTORY_SEPARATOR . 'updates'
                . DIRECTORY_SEPARATOR . $parts[0];
        } else {
            $candidate = JPATH_SITE
                . DIRECTORY_SEPARATOR . 'components'
                . DIRECTORY_SEPARATOR . 'com_sportsmanagement'
                . DIRECTORY_SEPARATOR . 'extensions'
                . DIRECTORY_SEPARATOR . $parts[0]
                . DIRECTORY_SEPARATOR . 'admin'
                . DIRECTORY_SEPARATOR . 'install'
                . DIRECTORY_SEPARATOR . $parts[1];
        }

        $real = realpath($candidate);

        if ($real === false || !is_file($real) || strtolower(pathinfo($real, PATHINFO_EXTENSION)) !== 'php') {
            return null;
        }

        if ($providedPath !== '') {
            $providedReal = realpath($providedPath);

            if ($providedReal === false || $providedReal !== $real) {
                return null;
            }
        }

        return $real;
    }

    private function extractMetadataValue(string $content, string $variable): string
    {
        $pattern = '/\\$' . preg_quote($variable, '/') . '\\s*=\\s*([\'\"])(.*?)\\1\\s*;/s';

        if (!preg_match($pattern, $content, $matches)) {
            return '';
        }

        return trim((string) $matches[2]);
    }

    private function getVersionRow(DatabaseInterface $db, string $file): ?object
    {
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_version'))
            ->where($db->quoteName('file') . ' = ' . $db->quote($file));

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
    }

    private function metadataValue($candidate, $fallback)
    {
        return $candidate !== null && $candidate !== '' ? $candidate : $fallback;
    }

    private function sportsDatabase(): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register(
                'sportsmanagementHelper',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
            );
        }

        return \sportsmanagementHelper::getDBConnection();
    }

    private function administratorApplication(): AdministratorApplication
    {
        return Factory::getContainer()->get(AdministratorApplication::class);
    }
}
