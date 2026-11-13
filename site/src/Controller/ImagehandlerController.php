<?php
/**
 * Joomla 5/6 frontend image upload/delete controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
    /**
     * Handle an authorised frontend image upload or remote-image import.
     * Checks the POST CSRF token and component upload permissions, selects the configured image directory, validates file or remote URL input, stores the permitted image and reports the filename to the parent Joomla modal.
     * @return void
     * @throws \RuntimeException When the request is forged or the user lacks access.
     */
    public function upload(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $this->assertImagePermission('upload');

        $app = $this->app;
        $input = $this->input;
        $type = $input->post->getCmd('type', '');
        $field = $input->post->getCmd('field', '');
        $fieldId = $input->post->getCmd('fieldid', '');
        $linkAddress = trim($input->post->getString('linkaddress', ''));
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

    /**
     * Delete selected SportsManagement images from an authorised frontend request.
     * Verifies the CSRF token and delete permission, rejects non-string file
     * selections without PHP 8 coercion warnings, validates each basename
     * before touching disk, removes existing originals and thumbnails and
     * redirects back to the image manager. Filesystem exception details are
     * not written to the shared Joomla log.
     * @return void
     * @throws \RuntimeException When the request fails CSRF or authorisation.
     */
    public function delete(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $this->assertImagePermission('delete');

        $input = $this->input;
        $type = $input->post->getCmd('type', '');
        $folder = ImageSelectHelper::getFolder($type);
        $baseDir = $this->imageBaseDirectory($folder, false);
        $images = $input->post->get('rm', [], 'array');

        if ($baseDir !== null) {
            foreach ((array) $images as $image) {
                $image = is_string($image)
                    ? $this->normaliseImageName($image)
                    : null;

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
                    Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
                }
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=imagehandler&type=' . rawurlencode($type) . '&tmpl=component'
        );
    }

    /**
     * CSRF protection is not authorization. Only users with component editing
     * rights may upload images, and removing files requires delete rights.
     */
    private function assertImagePermission(string $action): void
    {
        $identity = $this->app->getIdentity();

        if (!$identity || (int) ($identity->id ?? 0) <= 0) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $asset = 'com_sportsmanagement';
        $allowed = $action === 'delete'
            ? $identity->authorise('core.delete', $asset)
            : ($action === 'upload' && (
                $identity->authorise('core.create', $asset)
                || $identity->authorise('core.edit', $asset)
            ));

        if (!$allowed) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Resolve a permitted SportsManagement image directory below the site root.
     * Normalises configured directory separators, rejects empty or traversal path elements, optionally creates the directory through Joomla's filesystem API, and returns null when unavailable.
     * @param string $folder Image category subdirectory relative to the component image root.
     * @param bool $create Whether a missing category directory may be created.
     * @return string|null Absolute directory path with trailing separator, or null.
     */
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
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            return null;
        }

        return is_dir($directory) ? rtrim($directory, '/\\') . '/' : null;
    }

    /**
     * Store an uploaded temporary file in the selected image directory.
     * Rejects nonexistent or oversized sources, delegates the move to Joomla's filesystem upload helper, and logs storage failures instead of exposing filesystem details.
     * @param string $source Server-side temporary upload path.
     * @param string $target Validated destination path within the image directory.
     * @return bool Whether Joomla successfully stored the uploaded file.
     */
    private function uploadFile(string $source, string $target): bool
    {
        if (!is_file($source) || filesize($source) > $this->maxImageBytes()) {
            return false;
        }

        try {
            return (bool) File::upload($source, $target);
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            return false;
        }
    }

    /**
     * Download a validated public image into the SportsManagement image directory.
     * Checks URL scheme, host and extension, disallows HTTP redirects, applies request and size limits, verifies the decoded image MIME type, and writes the image only when all checks succeed.
     * @param string $url Requested remote image URL.
     * @param string $baseDir Validated local destination directory.
     * @return string|null Stored image basename, or null when rejected or unavailable.
     */
    private function downloadRemoteImage(string $url, string $baseDir): ?string
    {
        $parts = $this->validateRemoteImageUrl($url);

        if ($parts === null) {
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
            // Prevent a public URL from redirecting the server into a private
            // network. The Socket transport cannot guarantee this option.
            $http = (new HttpFactory())->getHttp(['follow_location' => false], ['Curl', 'Stream']);
            $response = $http->get($url, ['Accept' => 'image/*'], 10);
            $status = $response->getStatusCode();
            $body = (string) $response->getBody();
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
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
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            return null;
        }
    }

    /**
     * Remote images must come from publicly routable HTTP(S) hosts. URL
     * credentials, alternate ports, DNS-local hostnames and control bytes
     * are not suitable destinations for a server-side fetch.
     */
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

        return $parts;
    }

    /**
     * Validate a remote image hostname against private-network destinations.
     * Checks IP literals directly or validates DNS labels and all resolved A/AAAA addresses, rejecting local, private and otherwise unroutable hosts before the HTTP request.
     * @param string $host Hostname or IP literal from a parsed image URL.
     * @return bool True only when every discovered address is publicly routable.
     */
    private function isPublicRemoteHost(string $host): bool
    {
        $host = strtolower(rtrim(trim($host), '.'));

        if ($host === '' || strlen($host) > 253 || str_contains($host, "\0")) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return $this->isPublicRemoteIp($host);
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

        if ($addresses === []) {
            return false;
        }

        foreach (array_unique($addresses) as $address) {
            if (!$this->isPublicRemoteIp((string) $address)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether an IP address is suitable for a public image fetch.
     * Uses PHP's public-IP validation and additionally excludes IPv4 carrier-grade NAT and benchmarking address blocks that must not be reached server-side.
     * @param string $address IPv4 or IPv6 address returned by DNS or URL parsing.
     * @return bool Whether the address is allowed as a remote image host.
     */
    private function isPublicRemoteIp(string $address): bool
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

            // Carrier-grade NAT and benchmarking subnets are not public hosts.
            if (($octets[0] === 100 && $octets[1] >= 64 && $octets[1] <= 127)
                || ($octets[0] === 198 && in_array($octets[1], [18, 19], true))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compute the component's maximum permitted image payload size.
     * Reads the configured size in kibibytes, enforces a positive minimum and converts it to bytes for both uploaded and downloaded image checks.
     * @return int Maximum accepted image payload size in bytes.
     */
    private function maxImageBytes(): int
    {
        return max(
            1,
            (int) ComponentHelper::getParams('com_sportsmanagement')->get('image_max_size', 120)
        ) * 1024;
    }

    /**
     * Normalise a selected image filename before deletion.
     * Rejects null bytes, path separators, dot traversal and filenames changed by Joomla's path filter so deletion remains confined to the selected image directory.
     * @param string $image Filename submitted by the image manager.
     * @return string|null Safe image basename, or null when invalid.
     */
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

    /**
     * Notify the parent image selector and close the Joomla modal.
     * JSON-encodes each callback argument with HTML-safe flags, calls the matching parent selector when available and closes the active Joomla modal without interpolating raw input into JavaScript.
     * @param string $type Image category used to select the callback.
     * @param string $filename Newly stored image basename.
     * @param string $field Target field identifier in the parent form.
     * @param string $fieldId Target field record identifier.
     * @return void
     */
    private function closeModal(string $type, string $filename, string $field, string $fieldId): void
    {
        // Values are embedded inside a <script> element. Escape HTML delimiters
        // as well as JavaScript strings to prevent an early </script> terminator.
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;
        $typeJson = json_encode($type, $flags);
        $filenameJson = json_encode($filename, $flags);
        $fieldJson = json_encode($field, $flags);
        $fieldIdJson = json_encode($fieldId, $flags);

        echo '<script>'
            . 'if (window.parent && typeof window.parent["selectImage_" + ' . $typeJson . '] === "function") {'
            . 'window.parent["selectImage_" + ' . $typeJson . ']('
            . $filenameJson . ',' . $filenameJson . ',' . $fieldJson . ',' . $fieldIdJson . ');}'
            . 'if (window.parent && window.parent.Joomla && window.parent.Joomla.Modal) {'
            . 'window.parent.Joomla.Modal.getCurrent().close();}'
            . '</script>';
    }

    /**
     * Display a safe browser alert when image processing fails.
     * Encodes the translated message as a script-safe JSON string before showing an alert and returning the user to the previous browser page.
     * @param string $message Public-facing, translated error message.
     * @return void
     */
    private function showError(string $message): void
    {
        echo '<script>alert(' . json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)
            . ');window.history.go(-1);</script>';
    }
}
