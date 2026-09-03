<?php
/**
 * Native Joomla 5/6 administrator image handler controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 * @subpackage com_sportsmanagement
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
    private const MAX_REMOTE_REDIRECTS = 3;
    private const REMOTE_REQUEST_TIMEOUT = 15;

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
        $initialParts = $this->validateRemoteImageUrl($url);

        if ($initialParts === null) {
            return null;
        }

        $rawName = basename((string) ($initialParts['path'] ?? ''));
        $filename = $this->sanitiseFilename($baseDir, $rawName);

        if ($filename === null) {
            return null;
        }

        $currentUrl = $url;
        $body = null;

        try {
            // Socket follows redirects internally and cannot validate every hop, so only use transports
            // where follow_location=false is honoured.
            $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);

            for ($redirects = 0; $redirects <= self::MAX_REMOTE_REDIRECTS; $redirects++) {
                if ($this->validateRemoteImageUrl($currentUrl) === null) {
                    return null;
                }

                $response = $http->get(
                    $currentUrl,
                    ['Accept' => 'image/*,*/*;q=0.1'],
                    self::REMOTE_REQUEST_TIMEOUT
                );
                $status = $response->getStatusCode();

                if ($status >= 300 && $status < 400) {
                    if ($redirects === self::MAX_REMOTE_REDIRECTS) {
                        return null;
                    }

                    $currentUrl = $this->resolveRemoteRedirect(
                        $currentUrl,
                        trim($response->getHeaderLine('Location'))
                    );

                    if ($currentUrl === null) {
                        return null;
                    }

                    continue;
                }

                if ($status < 200 || $status >= 300) {
                    return null;
                }

                $contentLength = trim($response->getHeaderLine('Content-Length'));

                if ($contentLength !== ''
                    && ctype_digit($contentLength)
                    && (int) $contentLength > $this->maxImageBytes()) {
                    return null;
                }

                $body = (string) $response->getBody();
                break;
            }
        } catch (Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return null;
        }

        if (!is_string($body)
            || $body === ''
            || strlen($body) > $this->maxImageBytes()
            || !$this->isAllowedImageData($body)) {
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

    private function validateRemoteImageUrl(string $url): ?array
    {
        if ($url === ''
            || strlen($url) > 2048
            || str_contains($url, '\\')
            || preg_match('/[\x00-\x20\x7F]/', $url)) {
            return null;
        }

        $parts = parse_url($url);

        if (!is_array($parts)) {
            return null;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = trim((string) ($parts['host'] ?? ''), '[]');

        if (!in_array($scheme, ['http', 'https'], true)
            || $host === ''
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        $expectedPort = $scheme === 'https' ? 443 : 80;

        if (isset($parts['port']) && (int) $parts['port'] !== $expectedPort) {
            return null;
        }

        if (!$this->isPublicRemoteHost($host)) {
            return null;
        }

        $parts['scheme'] = $scheme;
        $parts['host'] = $host;

        return $parts;
    }

    private function isPublicRemoteHost(string $host): bool
    {
        $host = strtolower(rtrim(trim($host), '.'));

        if ($host === '' || strlen($host) > 253 || str_contains($host, "\0")) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $this->isPublicIpAddress($host);
        }

        foreach (['localhost', 'localdomain', 'local', 'internal', 'lan', 'home', 'home.arpa'] as $suffix) {
            if ($host === $suffix || str_ends_with($host, '.' . $suffix)) {
                return false;
            }
        }

        if (!str_contains($host, '.') || !preg_match('/^[a-z0-9.-]+$/i', $host)) {
            return false;
        }

        foreach (explode('.', $host) as $label) {
            if ($label === ''
                || strlen($label) > 63
                || str_starts_with($label, '-')
                || str_ends_with($label, '-')) {
                return false;
            }
        }

        $addresses = [];
        $ipv4 = @gethostbynamel($host);

        if (is_array($ipv4)) {
            $addresses = array_merge($addresses, $ipv4);
        }

        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);

            if (is_array($records)) {
                foreach ($records as $record) {
                    if (!empty($record['ip'])) {
                        $addresses[] = (string) $record['ip'];
                    }

                    if (!empty($record['ipv6'])) {
                        $addresses[] = (string) $record['ipv6'];
                    }
                }
            }
        }

        $addresses = array_values(array_unique($addresses));

        if ($addresses === []) {
            return false;
        }

        foreach ($addresses as $address) {
            if (!$this->isPublicIpAddress($address)) {
                return false;
            }
        }

        return true;
    }

    private function isPublicIpAddress(string $address): bool
    {
        if (filter_var(
            $address,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false) {
            return false;
        }

        if (filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            $octets = array_map('intval', explode('.', $address));

            // Carrier-grade NAT and benchmarking ranges are not valid public fetch targets.
            if (($octets[0] === 100 && $octets[1] >= 64 && $octets[1] <= 127)
                || ($octets[0] === 198 && in_array($octets[1], [18, 19], true))) {
                return false;
            }
        }

        return true;
    }

    private function resolveRemoteRedirect(string $baseUrl, string $location): ?string
    {
        $location = trim($location);

        if ($location === ''
            || str_contains($location, '\\')
            || preg_match('/[\x00-\x20\x7F]/', $location)) {
            return null;
        }

        $fragmentPosition = strpos($location, '#');

        if ($fragmentPosition !== false) {
            $location = substr($location, 0, $fragmentPosition);
        }

        if ($location === '') {
            return null;
        }

        if (preg_match('#^[a-z][a-z0-9+.-]*:#i', $location)) {
            return $location;
        }

        $base = $this->validateRemoteImageUrl($baseUrl);

        if ($base === null) {
            return null;
        }

        $scheme = (string) $base['scheme'];
        $host = (string) $base['host'];
        $hostForUrl = str_contains($host, ':') ? '[' . $host . ']' : $host;
        $port = isset($base['port']) ? ':' . (int) $base['port'] : '';
        $origin = $scheme . '://' . $hostForUrl . $port;

        if (str_starts_with($location, '//')) {
            return $scheme . ':' . $location;
        }

        $basePath = (string) ($base['path'] ?? '/');
        $basePath = $basePath !== '' ? $basePath : '/';

        if (str_starts_with($location, '?')) {
            return $origin . $basePath . $location;
        }

        if (str_starts_with($location, '/')) {
            return $origin . $location;
        }

        $slashPosition = strrpos($basePath, '/');
        $directory = $slashPosition === false ? '/' : substr($basePath, 0, $slashPosition + 1);

        return $origin . $directory . $location;
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
