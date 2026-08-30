<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\PicturesTable;
use Joomla\Archive\Archive;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Http\HttpFactory;

/** Native Joomla 5/6 model for installing SportsManagement image packages. */
final class SmimageimportModel extends SportsManagementAdminModel
{
    private const PACKAGE_SERVER = 'https://sportsmanagement.fussballineuropa.de/jdownloads/';

    public function getTable($type = 'Pictures', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'Pictures') === 0) {
            return new PicturesTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /** Download and install the selected image archives. */
    public function import()
    {
        $app = $this->administratorApplication();
        $post = $app->getInput()->post->getArray();
        $ids = array_values(array_unique(array_filter(
            array_map('intval', (array) ($post['cid'] ?? [])),
            static fn (int $id): bool => $id > 0
        )));

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
            $name = trim((string) ($post['picture'][$id] ?? ''));
            $folder = trim((string) ($post['folder'][$id] ?? ''), "/\\");
            $directory = trim((string) ($post['directory'][$id] ?? ''), "/\\");
            $submittedFile = (string) ($post['file'][$id] ?? '');
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

                $this->setError($e->getMessage());
                $app->enqueueMessage($e->getMessage(), 'error');

                return false;
            }
        }

        return true;
    }

    protected function allowEdit($data = [], $key = 'id')
    {
        $id = (int) ($data[$key] ?? 0);

        return $this->administratorApplication()->getIdentity()->authorise(
            'core.edit',
            'com_sportsmanagement.message.' . $id
        ) || parent::allowEdit($data, $key);
    }

    private function isSafeRelativePath(string $path): bool
    {
        return $path !== ''
            && !str_contains($path, '..')
            && !str_starts_with($path, '/')
            && !str_contains($path, "\0")
            && preg_match('#^[A-Za-z0-9._ /-]+$#', $path) === 1;
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }
}
