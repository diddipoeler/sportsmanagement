<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\ImagehandlerModel;
use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;
use Throwable;

/**
 * Native Joomla 5/6 controller for the administrator image browser.
 */
final class ImagehandlerController extends SportsManagementAdminController
{
    public function uploadprojectteams(): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');

        $input     = $this->app->getInput();
        $data      = $input->getArray();
        $file      = $input->files->get('userfile', [], 'array');
        $type      = (string) ($data['type'] ?? '');
        $field     = (string) ($data['field'] ?? '');
        $fieldId   = (string) ($data['fieldid'] ?? '');
        $imageList = !empty($data['imagelist']);
        $folder    = ImageSelectHelper::getFolder($type);

        if (empty($file['name']) || empty($file['tmp_name'])) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY'));
            return;
        }

        $baseDir = $this->imageBaseDirectory($folder);

        if ($baseDir === null || !$this->isAllowedImageFile((string) $file['tmp_name'])) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $filename = $this->sanitiseFilename($baseDir, (string) $file['name']);

        if ($filename === null || !$this->uploadFile((string) $file['tmp_name'], $baseDir . $filename)) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'), 'message');
        $this->closeUploadModal($type, $filename, $field, $fieldId, !$imageList);
    }

    public function saveimageplayer(): void
    {
        $input = $this->app->getInput();
        $this->saveImageSelection(
            'saveimageplayer',
            [
                'player_id' => $input->getInt('player_id'),
                'picture'   => $input->getString('picture'),
            ]
        );
    }

    public function saveimageclub(): void
    {
        $input = $this->app->getInput();
        $this->saveImageSelection(
            'saveimageclub',
            [
                'club_id' => $input->getInt('club_id'),
                'picture' => $input->getString('picture'),
            ]
        );
    }

    public function saveimageteamplayer(): void
    {
        $input = $this->app->getInput();
        $this->saveImageSelection(
            'saveimageteamplayer',
            [
                'teamplayer_id' => $input->getInt('teamplayer_id'),
                'picture'       => $input->getString('picture'),
            ]
        );
    }

    public function upload(): void
    {
        $this->requireToken();
        $this->requirePermission('core.create');

        $input     = $this->app->getInput();
        $data      = $input->getArray();
        $file      = $input->files->get('userfile', [], 'array');
        $type      = (string) ($data['type'] ?? '');
        $field     = (string) ($data['field'] ?? '');
        $fieldId   = (string) ($data['fieldid'] ?? '');
        $link      = trim((string) ($data['linkaddress'] ?? ''));
        $pid       = max(0, (int) ($data['pid'] ?? 0));
        $mid       = max(0, (int) ($data['mid'] ?? 0));
        $imageList = !empty($data['imagelist']);
        $folder    = ImageSelectHelper::getFolder($type);

        if ($type === 'projectimages' && $pid > 0) {
            $folder .= '/' . $pid;
            $imageList = true;
        } elseif ($type === 'matchreport' && $mid > 0) {
            $folder .= '/' . $mid;
            $imageList = true;
        }

        $baseDir = $this->imageBaseDirectory($folder);

        if ($baseDir === null) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        if ($link !== '') {
            $filename = $this->downloadRemoteImage($link, $baseDir);

            if ($filename === null) {
                $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED'));
                return;
            }

            $this->closeUploadModal($type, $filename, $field, $fieldId, !$imageList);
            return;
        }

        if (empty($file['name']) || empty($file['tmp_name'])) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY'));
            return;
        }

        if (!$this->isAllowedImageFile((string) $file['tmp_name'])) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $filename = $this->sanitiseFilename($baseDir, (string) $file['name']);

        if ($filename === null || !$this->uploadFile((string) $file['tmp_name'], $baseDir . $filename)) {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $this->app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'), 'message');
        $this->closeUploadModal($type, $filename, $field, $fieldId, !$imageList);
    }

    public function delete(): void
    {
        $this->requireToken();
        $this->requirePermission('core.delete');

        $input  = $this->app->getInput();
        $images = $input->get('rm', [], 'array');
        $type   = $input->getCmd('type');
        $folder = ImageSelectHelper::getFolder($type);
        $baseDir = $this->imageBaseDirectory($folder, false);

        if ($baseDir !== null) {
            foreach ($images as $image) {
                $relativeImage = $this->normaliseRelativeImagePath((string) $image);

                if ($relativeImage === null) {
                    Log::add(
                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE'),
                        Log::WARNING,
                        'jsmerror'
                    );
                    continue;
                }

                $fullPath  = $baseDir . $relativeImage;
                $thumbPath = $baseDir . 'small/' . basename($relativeImage);

                try {
                    if (is_file($fullPath)) {
                        File::delete($fullPath);
                    }

                    if (is_file($thumbPath)) {
                        File::delete($thumbPath);
                    }
                } catch (Throwable $e) {
                    Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
                }
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=imagehandler&type=' . rawurlencode($type) . '&tmpl=component'
        );
    }

    public function getModel($name = 'Imagehandler', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }

    private function saveImageSelection(string $method, array $data): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');

        $model = $this->getModel();

        if (!$model instanceof ImagehandlerModel) {
            throw new \RuntimeException('ImagehandlerModel is unavailable.', 500);
        }

        $resultUpdate = $model->{$method}($data);
        $result = $resultUpdate === true
            ? 'Nachricht&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE')
            : '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE') . ': ' . $resultUpdate;

        $this->app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->app->close();
    }

    private function requireToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    private function requirePermission(string $action): void
    {
        $identity = $this->app->getIdentity();

        if (!$identity->authorise($action, 'com_sportsmanagement')
            && !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    private function imageBaseDirectory(string $folder, bool $create = true): ?string
    {
        $folder = $this->normaliseRelativeFolder($folder);

        if ($folder === null) {
            return null;
        }

        $directory = JPATH_SITE . '/images/com_sportsmanagement/database/' . $folder;

        if ($create && !is_dir($directory)) {
            try {
                Folder::create($directory);
            } catch (Throwable $e) {
                Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
                return null;
            }
        }

        if (!is_dir($directory)) {
            return null;
        }

        return rtrim($directory, '/\\') . '/';
    }

    private function uploadFile(string $source, string $target): bool
    {
        if (!is_file($source) || filesize($source) > $this->maxImageBytes()) {
            return false;
        }

        try {
            return (bool) File::upload($source, $target);
        } catch (Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return false;
        }
    }

    private function downloadRemoteImage(string $url, string $baseDir): ?string
    {
        $parts = parse_url($url);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if (!in_array($scheme, ['http', 'https'], true) || empty($parts['host'])) {
            return null;
        }

        $rawName = basename((string) ($parts['path'] ?? ''));
        $filename = $this->sanitiseFilename($baseDir, $rawName);

        if ($filename === null) {
            return null;
        }

        try {
            $response = (new HttpFactory())->getHttp()->get($url);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
        } catch (Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return null;
        }

        if ($status < 200 || $status >= 300) {
            return null;
        }

        if ($body === '' || strlen($body) > $this->maxImageBytes() || !$this->isAllowedImageData($body)) {
            return null;
        }

        try {
            if (!File::write($baseDir . $filename, $body)) {
                return null;
            }
        } catch (Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return null;
        }

        return $filename;
    }

    private function sanitiseFilename(string $baseDir, string $filename): ?string
    {
        $filename = basename(trim(str_replace('\\', '/', $filename)));

        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $safe = ImageSelectHelper::sanitize($baseDir, $filename);
        $safe = basename(str_replace('\\', '/', $safe));

        return $safe !== '' ? $safe : null;
    }

    private function isAllowedImageFile(string $path): bool
    {
        if (!is_file($path) || filesize($path) > $this->maxImageBytes()) {
            return false;
        }

        $info = @getimagesize($path);

        return $info !== false && $this->isAllowedMime((string) ($info['mime'] ?? ''));
    }

    private function isAllowedImageData(string $body): bool
    {
        $info = @getimagesizefromstring($body);

        return $info !== false && $this->isAllowedMime((string) ($info['mime'] ?? ''));
    }

    private function isAllowedMime(string $mime): bool
    {
        return in_array(strtolower($mime), ['image/gif', 'image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function maxImageBytes(): int
    {
        $kilobytes = max(1, (int) ComponentHelper::getParams('com_sportsmanagement')->get('image_max_size', 120));

        return $kilobytes * 1024;
    }

    private function normaliseRelativeFolder(string $folder): ?string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');

        if ($folder === '' || str_contains($folder, "\0")) {
            return null;
        }

        foreach (explode('/', $folder) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return null;
            }
        }

        return $folder;
    }

    private function normaliseRelativeImagePath(string $image): ?string
    {
        $image = trim(str_replace('\\', '/', $image), '/');

        if ($image === '' || str_contains($image, "\0") || $image !== InputFilter::clean($image, 'path')) {
            return null;
        }

        foreach (explode('/', $image) as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return null;
            }
        }

        return $image;
    }

    private function closeUploadModal(
        string $type,
        string $filename,
        string $field,
        string $fieldId,
        bool $select
    ): void {
        $typeJson     = json_encode($type, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filenameJson = json_encode($filename, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fieldJson    = json_encode($field, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fieldIdJson  = json_encode($fieldId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo '<script>';

        if ($select) {
            echo 'if (window.parent && typeof window.parent["selectImage_" + ' . $typeJson . '] === "function") {'
                . 'window.parent["selectImage_" + ' . $typeJson . ']('
                . $filenameJson . ',' . $filenameJson . ',' . $fieldJson . ',' . $fieldIdJson . ');}' ;
        }

        echo 'if (window.parent && window.parent.Joomla && window.parent.Joomla.Modal) {'
            . 'window.parent.Joomla.Modal.getCurrent().close();}'
            . ($select ? '' : 'if (window.parent) { window.parent.location.reload(); }')
            . '</script>';
    }

    private function showUploadError(string $message): void
    {
        echo '<script>alert(' . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . ');window.history.go(-1);</script>';
    }
}
