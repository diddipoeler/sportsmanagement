<?php
/**
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\LegacyModelDatabaseHelper;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Native Joomla 5/6 model for local SportsManagement update scripts and history. */
final class UpdatesModel extends BaseDatabaseModel
{
    /**
     * Execute one allowlisted, local SportsManagement update script and record its version.
     * Resolves the requested filename inside trusted update directories, includes the script, and updates its execution count and version metadata in the component version table.
     * Non-string request values and symlinked scripts are rejected before execution.
     * Validates the raw administrator database choice before including scripts
     * that may alter installed component data.
     * @param mixed $myfilename Optional update directory or fully qualified legacy script path.
     * @param mixed $file Name of the update script requested by the administrator.
     * @return string Script status message, or an empty string when unavailable.
     */
    public function loadUpdateFile($myfilename, $file): string
    {
        $fileName = is_string($file) ? $file : '';
        $resolved = is_string($myfilename) ? $this->resolveUpdateFile($fileName, $myfilename) : null;

        if ($resolved === null) {
            $this->administratorApplication()->enqueueMessage(Text::_('Update file not found!'), 'error');

            return '';
        }

        LegacyModelDatabaseHelper::selector();

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

    /**
     * Load installed SportsManagement component versions in descending date order.
     * Queries the component version table through the configured SportsManagement database connection and tolerates database errors for the administrator view.
     * @return array<int,object> Stored version records, or an empty list.
     */
    public function getVersions(): array
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
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

    /**
     * Sort update file records from newest to oldest by their update date.
     * Parses each updateFileDate field and treats invalid dates as the earliest possible date.
     * @param array $a First update metadata record.
     * @param array $b Second update metadata record.
     * @return int Comparison result for a descending date sort.
     */
    public function _cmpDate($a, $b): int
    {
        $left = strtotime((string) ($a['updateFileDate'] ?? '')) ?: 0;
        $right = strtotime((string) ($b['updateFileDate'] ?? '')) ?: 0;

        return $right <=> $left;
    }

    /**
     * Sort update metadata by filename without case sensitivity.
     * Compares the file_name field of two update records, replacing missing names with empty text.
     * @param array $a First update metadata record.
     * @param array $b Second update metadata record.
     * @return int Case-insensitive filename comparison result.
     */
    public function _cmpName($a, $b): int
    {
        return strcasecmp((string) ($a['file_name'] ?? ''), (string) ($b['file_name'] ?? ''));
    }

    /**
     * Sort update metadata by the recorded last-version label.
     * Performs a case-insensitive string comparison of legacy last_version fields.
     * @param array $a First version metadata record.
     * @param array $b Second version metadata record.
     * @return int Case-insensitive version-label comparison result.
     */
    public function _cmpVersion($a, $b): int
    {
        return strcasecmp((string) ($a['last_version'] ?? ''), (string) ($b['last_version'] ?? ''));
    }

    /**
     * Fetch the history of executed SportsManagement component updates.
     * Reads all rows from the version-history table in descending date order and reports database errors through Joomla notifications.
     * @return array<int,object> Installed update history records, or an empty list.
     */
    public function getVersionHistory(): array
    {
        $db = $this->sportsDatabase();
        $query = $db->createQuery()
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

    /**
     * Assemble metadata for update scripts available in trusted component directories.
     * Discovers available PHP scripts, reads declared update metadata and merges each script with matching database version records for the administrator list.
     * @return array<int,array<string,mixed>> Available update files and their version status.
     */
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

    /**
     * Enumerate candidate update scripts across component and extension directories.
     * Searches the installed component update directory and supported extension locations while retaining only local PHP filenames for later validation.
     * @return array<int,string> Relative names of discovered update scripts.
     */
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

    /**
     * List PHP files immediately contained in a local update directory.
     * Skips missing directories and non-file entries and sorts matching filenames using natural case-insensitive ordering.
     * @param string $directory Trusted directory to scan for PHP update scripts.
     * @return array<int,string> Sorted PHP filenames.
     */
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

    /**
     * Resolve an update filename to a trusted local PHP script path.
     *
     * Accepts one component filename or an extension name and filename.
     * Rejects malformed path segments, control characters, non-PHP files
     * and symlinked entries that resolve outside the expected installed
     * directory structure. A legacy absolute path hint must resolve to
     * exactly the same verified script and is never used as an alternative.
     *
     * @param string $fileName Filename or supported relative update path.
     * @param string $providedPath Optional legacy path hint from the caller.
     * @return string|null Canonical trusted script path, or null when rejected.
     */
    private function resolveUpdateFile(string $fileName, string $providedPath = ''): ?string
    {
        if ($fileName === '' || strlen($fileName) > 512
            || preg_match('/[\x00-\x1F\x7F]/', $fileName) === 1
            || strlen($providedPath) > 4096
            || preg_match('/[\x00-\x1F\x7F]/', $providedPath) === 1) {
            return null;
        }

        $normalised = str_replace('\\', '/', $fileName);
        $parts = explode('/', $normalised);

        if (count($parts) > 2) {
            return null;
        }

        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..'
                || trim($part) !== $part || str_contains($part, ':')) {
                return null;
            }
        }

        if (strtolower(pathinfo($parts[array_key_last($parts)], PATHINFO_EXTENSION)) !== 'php') {
            return null;
        }

        if (count($parts) === 1) {
            $root = realpath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement');
            $relative = 'assets' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $parts[0];
        } else {
            $root = realpath(JPATH_SITE . '/components/com_sportsmanagement');
            $relative = 'extensions' . DIRECTORY_SEPARATOR . $parts[0]
                . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'install'
                . DIRECTORY_SEPARATOR . $parts[1];
        }

        if ($root === false || !is_dir($root)) {
            return null;
        }

        $candidate = $root . DIRECTORY_SEPARATOR . $relative;
        $real = realpath($candidate);

        // Exact canonical equality also refuses symlinked parent directories.
        if ($real === false || $real !== $candidate || is_link($candidate)
            || !is_file($real) || !is_readable($real)) {
            return null;
        }

        if ($providedPath !== '' && realpath($providedPath) !== $real) {
            return null;
        }

        return $real;
    }

    /**
     * Extract one quoted metadata assignment from a PHP update script.
     * Matches an expected assignment of the requested variable to a string literal without executing the script.
     * @param string $content PHP update script source text.
     * @param string $variable Metadata variable name to locate.
     * @return string Unquoted and trimmed metadata value, or an empty string.
     */
    private function extractMetadataValue(string $content, string $variable): string
    {
        $pattern = '/\\$' . preg_quote($variable, '/') . '\\s*=\\s*([\'\"])(.*?)\\1\\s*;/s';

        if (!preg_match($pattern, $content, $matches)) {
            return '';
        }

        return trim((string) $matches[2]);
    }

    /**
     * Load the stored version status for a single update script.
     * Queries the component version table by a parameter-bound filename and handles database failures without terminating the administrator list.
     * @param DatabaseInterface $db Active SportsManagement database connection.
     * @param string $file Stored update script identifier.
     * @return object|null Matching version record, or null if unavailable.
     */
    private function getVersionRow(DatabaseInterface $db, string $file): ?object
    {
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_version'))
            ->where($db->quoteName('file') . ' = :versionFile')
            ->bind(':versionFile', $file, ParameterType::STRING);

        try {
            $db->setQuery($query, 0, 1);

            return $db->loadObject() ?: null;
        } catch (\Throwable $e) {
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'notice');

            return null;
        }
    }

    /**
     * Choose the value supplied by an update script or its installed fallback.
     * Retains false and zero as explicit metadata values and uses the fallback only for null or empty-string data.
     * @param mixed $candidate Value declared by the update script.
     * @param mixed $fallback Previously installed metadata value.
     * @return mixed Selected metadata value.
     */
    private function metadataValue($candidate, $fallback)
    {
        return $candidate !== null && $candidate !== '' ? $candidate : $fallback;
    }

    /**
     * Resolve the configured SportsManagement database for update operations.
     * Reuses the shared native administrator bridge for request/session
     * validation and entitlement checks while retaining the MVC model's
     * injected Joomla database as the fallback connection.
     * @return DatabaseInterface Connection used for version reads and writes.
     */
    private function sportsDatabase(): DatabaseInterface
    {
        return LegacyModelDatabaseHelper::resolve($this->getDatabase());
    }

    /**
     * Retrieve and validate Joomla's administrator application service.
     * Uses the dependency injection container and refuses frontend application contexts before performing update operations.
     * @return AdministratorApplication Validated backend application.
     * @throws \RuntimeException When executed outside the administrator client.
     */
    private function administratorApplication(): AdministratorApplication
    {
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app instanceof AdministratorApplication || !$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement requires the Joomla administrator application.');
        }

        return $app;
    }
}
