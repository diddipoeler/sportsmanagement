<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ImageSelectHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Http\HttpFactory;

/** Joomla 5/6 frontend image upload/delete controller. */
final class ImagehandlerController extends BaseController
{
    public function upload(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $app = $this->getApplication();
        $input = $app->getInput();
        $type = $input->getCmd('type', '');
        $field = $input->getCmd('field', '');
        $fieldId = $input->getCmd('fieldid', '');
        $linkAddress = trim($input->getString('linkaddress', ''));
        $file = $input->files->get('userfile', [], 'array');
        $folder = ImageSelectHelper::getFolder($type);
        $baseDir = $this->imageBaseDirectory($folder);

        if ($baseDir === null) {
            $this->showError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        if ($linkAddress !== '') {
            $filename = $this->downloadRemoteImage($linkAddress, $baseDir);

            if ($filename === null) {
                $this->showError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED'));
                return;
            }

            $this->closeModal($type, $filename, $field, $fieldId);
            return;
        }

        if (empty($file['name']) || empty($file['tmp_name'])) {
            $this->showError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY'));
            return;
        }

        if (!ImageSelectHelper::check($file)) {
            $this->showError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $filename = basename(ImageSelectHelper::sanitize($baseDir, (string) $file['name']));

        if ($filename === '' || !$this->uploadFile((string) $file['tmp_name'], $baseDir . $filename)) {
            $this->showError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $this->closeModal($type, $filename, $field, $fieldId);
    }

    public function delete(): void
    {
        if (!Session::checkToken('get')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $app = $this->getApplication();
        $input = $app->getInput();
        $type = $input->getCmd('type', '');
        $folder = ImageSelectHelper::getFolder($type);
        $baseDir = $this->imageBaseDirectory($folder, false);
        $images = $input->get('rm', [], 'array');

        if ($baseDir !== null) {
            foreach ((array) $images as $image) {
                $image = $this->normaliseImageName((string) $image);

                if ($image === null) {
                    Log::add(
                        Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE'),
                        Log::WARNING,
                        'jsmerror'
                    );
                    continue;
                }

                try {
                    $fullPath = $baseDir . $image;
                    $thumbPath = $baseDir . 'small/' . $image;

                    if (is_file($fullPath)) {
                        File::delete($fullPath);
                    }

                    if (is_file($thumbPath)) {
                        File::delete($thumbPath);
                    }
                } catch (\Throwable $e) {
                    Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
                }
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=imagehandler&type=' . rawurlencode($type) . '&tmpl=component'
        );
    }

    private function imageBaseDirectory(string $folder, bool $create = true): ?string
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

        $directory = JPATH_SITE . '/images/com_sportsmanagement/database/' . $folder;

        try {
            if ($create && !is_dir($directory)) {
                Folder::create($directory);
            }
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return null;
        }

        return is_dir($directory) ? rtrim($directory, '/\\') . '/' : null;
    }

    private function uploadFile(string $source, string $target): bool
    {
        if (!is_file($source) || filesize($source) > $this->maxImageBytes()) {
            return false;
        }

        try {
            return (bool) File::upload($source, $target);
        } catch (\Throwable $e) {
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
        $extension = strtolower((string) pathinfo($rawName, PATHINFO_EXTENSION));

        if (!in_array($extension, ['gif', 'jpg', 'jpeg', 'png', 'bmp', 'webp'], true)) {
            return null;
        }

        $filename = basename(ImageSelectHelper::sanitize($baseDir, $rawName));

        if ($filename === '') {
            return null;
        }

        try {
            $response = (new HttpFactory())->getHttp()->get($url);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return null;
        }

        if ($status < 200 || $status >= 300 || $body === '' || strlen($body) > $this->maxImageBytes()) {
            return null;
        }

        $imageInfo = @getimagesizefromstring($body);
        $mime = strtolower((string) ($imageInfo['mime'] ?? ''));

        if ($imageInfo === false || !in_array($mime, ['image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/webp'], true)) {
            return null;
        }

        try {
            return File::write($baseDir . $filename, $body) ? $filename : null;
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return null;
        }
    }

    private function maxImageBytes(): int
    {
        return max(
            1,
            (int) ComponentHelper::getParams('com_sportsmanagement')->get('image_max_size', 120)
        ) * 1024;
    }

    private function normaliseImageName(string $image): ?string
    {
        $image = trim(str_replace('\\', '/', $image), '/');

        if ($image === '' || str_contains($image, "\0") || $image !== InputFilter::clean($image, 'path')) {
            return null;
        }

        if (basename($image) !== $image || in_array($image, ['.', '..'], true)) {
            return null;
        }

        return $image;
    }

    private function closeModal(string $type, string $filename, string $field, string $fieldId): void
    {
        $typeJson = json_encode($type, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filenameJson = json_encode($filename, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fieldJson = json_encode($field, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fieldIdJson = json_encode($fieldId, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo '<script>'
            . 'if (window.parent && typeof window.parent["selectImage_" + ' . $typeJson . '] === "function") {'
            . 'window.parent["selectImage_" + ' . $typeJson . ']('
            . $filenameJson . ',' . $filenameJson . ',' . $fieldJson . ',' . $fieldIdJson . ');}'
            . 'if (window.parent && window.parent.Joomla && window.parent.Joomla.Modal) {'
            . 'window.parent.Joomla.Modal.getCurrent().close();}'
            . '</script>';
    }

    private function showError(string $message): void
    {
        echo '<script>alert(' . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . ');window.history.go(-1);</script>';
    }
}
