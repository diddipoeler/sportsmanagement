<?php
/**
 * Joomla 5/6 administrator image-package import model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PicturesTable;
use Joomla\Archive\Archive;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Http\HttpFactory;

/** Native Joomla 5/6 model for installing SportsManagement image packages. */
final class SmimageimportModel extends SportsManagementAdminModel
{
    private const PACKAGE_SERVER = 'https://sportsmanagement.fussballineuropa.de/jdownloads/';

    /**
     * Resolve the native pictures table for image import operations.
     * @param string $type Requested table name.
     * @param string $prefix Legacy table namespace.
     * @param array $config Optional Joomla table configuration.
     * @return mixed Native PicturesTable or parent result.
     */
    public function getTable($type = 'Pictures', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Pictures') === 0) {
            return new PicturesTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Download and install selected image archives in the component folders.
     *
     * Strictly validates and deduplicates selected image IDs and checks
     * configured archive destinations before writing to Joomla paths.
     * Failed downloads/extractions produce generic administrator feedback;
     * exception classes are logged privately without credentials or SQL.
     *
     * @return bool Whether the selected image import succeeded.
     */
    public function import()
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $ids = self::selectedIds($post['cid'] ?? null);

        if (!$ids) {
            return false;
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';

        if (!is_dir($temporaryDirectory) || !is_writable($temporaryDirectory)) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_WRITABLE'), 'warning');
            $app->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_FILE_PERMISSIONS', Path::getPermissions($temporaryDirectory)),
                'warning'
            );

            return false;
        }

        $http = HttpFactory::getHttp([], ['curl', 'stream']);
        $db = $this->getDatabase();

        foreach ($ids as $id) {
            $name = trim(self::postedField($post, 'picture', $id));
            $folder = trim(self::postedField($post, 'folder', $id), "/\\");
            $directory = trim(self::postedField($post, 'directory', $id), "/\\");
            $submittedFile = self::postedField($post, 'file', $id);
            $file = basename($submittedFile);

            if (!$this->isSafeRelativePath($folder) || !$this->isSafeRelativePath($directory)
                || $file === '' || $file !== $submittedFile || strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'zip') {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_NO_ZIP_ERROR'), 'warning');

                return false;
            }

            $remoteUrl = rtrim(self::PACKAGE_SERVER, '/') . '/'
                . $this->encodePath($folder) . '/' . rawurlencode($file);
            $archivePath = $temporaryDirectory . '/' . $file;
            $extractDirectory = JPATH_SITE . '/images/com_sportsmanagement/database/' . $directory;

            try {
                $response = $http->get($remoteUrl);
                $status = $response->getStatusCode();
                $body = (string) $response->getBody();

                if ($status !== 200) {
                    throw new \RuntimeException('HTTP ' . $status);
                }

                if (!File::write($archivePath, $body)) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_WRITABLE'));
                }

                if (!is_dir($extractDirectory) && !Folder::create($extractDirectory)) {
                    throw new \RuntimeException(Text::_('JLIB_FILESYSTEM_ERROR_FOLDER_CREATE'));
                }

                $archive = new Archive();

                if ($archive->extract($archivePath, $extractDirectory) === false) {
                    throw new \RuntimeException(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_ERROR'));
                }

                if (File::exists($archivePath)) {
                    File::delete($archivePath);
                }

                $db->updateObject(
                    '#__sportsmanagement_pictures',
                    (object) ['id' => $id, 'published' => 1],
                    'id'
                );
                $app->enqueueMessage(
                    Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_DONE', $name),
                    'message'
                );
            } catch (\Throwable $e) {
                if (File::exists($archivePath)) {
                    File::delete($archivePath);
                }

                // Http, archive and database exceptions may contain private
                // authentication, SQL or filesystem details.
                Log::add(
                    __METHOD__ . ': image import failed (' . get_class($e) . ').',
                    Log::ERROR,
                    'com_sportsmanagement'
                );
                $this->setError(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));

                return false;
            }
        }

        return true;
    }

    /**
     * Validate user-selected picture record IDs before any import operation.
     *
     * Accepts positive integers or complete decimal strings, including
     * zero-padded Joomla form values, and removes duplicates. Rejects
     * nested values, booleans, partially numeric strings and values outside
     * PHP's integer range so malformed `cid` records cannot target picture 1.
     * Limits a single request to one hundred valid selected records.
     *
     * @param mixed $raw Raw submitted `cid` multi-selection.
     * @return array<int,int> Unique positive picture IDs in request order.
     */
    private static function selectedIds(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $selected = [];
        $max = (string) PHP_INT_MAX;

        foreach ($raw as $value) {
            if (is_int($value)) {
                if ($value <= 0) {
                    continue;
                }

                $id = $value;
            } elseif (is_string($value) && $value !== ''
                && strlen($value) <= 64 && preg_match('/^[0-9]+$/D', $value) === 1) {
                $digits = ltrim($value, '0');

                if ($digits === '' || strlen($digits) > strlen($max)
                    || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
                    continue;
                }

                $id = (int) $digits;
            } else {
                continue;
            }

            $selected[$id] = $id;

            if (count($selected) >= 100) {
                break;
            }
        }

        return array_values($selected);
    }

    /**
     * Read a bounded scalar field value from a submitted image selection.
     *
     * Administrator forms submit picture, folder, directory and archive names
     * as arrays indexed by the selected record ID. Rejects nested arrays,
     * objects and oversized values rather than converting them to the text
     * "Array" or emitting PHP 8 warnings during path construction.
     *
     * @param array<string,mixed> $post Submitted Joomla administrator form.
     * @param string $field Name of the posted picture/archive field.
     * @param int $id Validated positive picture record identifier.
     * @return string Valid text value or an empty string for invalid input.
     */
    private static function postedField(array $post, string $field, int $id): string
    {
        $values = $post[$field] ?? null;

        if (!is_array($values)) {
            return '';
        }

        $value = $values[$id] ?? null;

        if ((!is_string($value) && !is_int($value))
            || (is_string($value) && strlen($value) > 1024)) {
            return '';
        }

        return (string) $value;
    }

    /**
     * Check whether the administrator may edit an imported image record.
     * Uses record-level permission when the current identity is available
     * and otherwise falls back to the parent model ACL check.
     * @param array $data Current image record values.
     * @param string $key Identifier key.
     * @return bool Whether editing is permitted.
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        $identity = $this->administratorApplication()->getIdentity();

        return ($identity !== null && $identity->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        )) || parent::allowEdit($data, $key);
    }

    /**
     * Check an archive destination path against path traversal.
     * Rejects absolute paths, traversal segments, control characters
     * and characters outside the supported filesystem path subset.
     * @param string $path Relative archive destination path.
     * @return bool Whether the path is acceptable.
     */
    private function isSafeRelativePath(string $path): bool
    {
        return $path !== ''
            && !str_contains($path, '..')
            && !str_starts_with($path, '/')
            && !str_contains($path, "\0")
            && preg_match('#^[A-Za-z0-9._ /-]+$#', $path) === 1;
    }

    /**
     * URL-encode each segment of a validated image path.
     * @param string $path Image path separated by slashes.
     * @return string URL-safe path.
     */
    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
