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

    /**
     * Process a project-team image upload in the administrator.
     * Checks the CSRF token and edit permission, resolves the destination and supplied file, and reports the stored image back to the project-team selector.
     * @return void
     */
    public function uploadprojectteams(): void
    {
        $this->requireToken();
        $this->requirePermission('core.edit');

        $input     = $this->app->getInput();
        $data      = $input->post->getArray();
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

    /**
     * Save the selected picture for an existing player.
     * Reads the player ID and image choice from POST and delegates the authorised change to the shared image-selection persistence handler.
     * @return void
     */
    public function saveimageplayer(): void
    {
        $input = $this->app->getInput()->post;
        $this->saveImageSelection(
            'saveimageplayer',
            [
                'player_id' => $input->getInt('player_id'),
                'picture'   => $input->getString('picture'),
            ]
        );
    }

    /**
     * Assign an image to a club record.
     * Reads the selected club and picture from POST and delegates validation and persistence to the image-selection model operation.
     * @return void
     */
    public function saveimageclub(): void
    {
        $input = $this->app->getInput()->post;
        $this->saveImageSelection(
            'saveimageclub',
            [
                'club_id' => $input->getInt('club_id'),
                'picture' => $input->getString('picture'),
            ]
        );
    }

    /**
     * Persist an image selection for a team-player relation.
     * Extracts relation ID and picture from the submitted form and uses the shared authorised image-selection save path.
     * @return void
     */
    public function saveimageteamplayer(): void
    {
        $input = $this->app->getInput()->post;
        $this->saveImageSelection(
            'saveimageteamplayer',
            [
                'teamplayer_id' => $input->getInt('teamplayer_id'),
                'picture'       => $input->getString('picture'),
            ]
        );
    }

    /**
     * Upload or import a SportsManagement image through the administrator.
     * Checks POST and create permission, validates the image category and local or remote source, stores an allowed image and returns its basename to the Joomla modal.
     * @return void
     */
    public function upload(): void
    {
        $this->requireToken();
        $this->requirePermission('core.create');

        $input     = $this->app->getInput();
        $data      = $input->post->getArray();
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

    /**
     * Delete selected component images through a protected administrator task.
     * Verifies the POST token and delete permission, rejects non-string
     * submissions before normalising paths, removes eligible images and
     * thumbnails and redirects to the image manager. File operation logs
     * include the exception type, not potentially sensitive disk paths.
     * @return void
     */
    public function delete(): void
    {
        $this->requireToken();
        $this->requirePermission('core.delete');

        $input  = $this->app->getInput();
        $images = $input->post->get('rm', [], 'array');
        $type   = $input->post->getCmd('type');
        $folder = ImageSelectHelper::getFolder($type);
        $baseDir = $this->imageBaseDirectory($folder, false);

        if ($baseDir !== null) {
            foreach ($images as $image) {
                $relativeImage = is_string($image)
                    ? $this->normaliseRelativeImagePath($image)
                    : null;

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
                    Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
                }
            }
        }

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=imagehandler&type=' . rawurlencode($type) . '&tmpl=component'
        );
    }

    /**
     * Retrieve the native image-handler model without discarding caller settings.
     * Forwards the requested name, prefix and MVC configuration to the parent factory, adding ignore_request only when the caller did not set it.
     * @param string $name Requested model name.
     * @param string $prefix Model namespace prefix.
     * @param array $config Optional Joomla MVC model configuration.
     * @return mixed Model instance returned by the Joomla controller factory.
     */
    public function getModel($name = 'Imagehandler', $prefix = 'Administrator', $config = [])
    {
        // Preserve caller-specific MVC options instead of discarding them.
        return parent::getModel($name, $prefix, $config + ['ignore_request' => true]);
    }

    /**
     * Validate and persist one of the supported image-selection actions.
     * Checks the POST CSRF token and component edit permission, requires the image-handler model, invokes the named save operation with posted data and handles its result.
     * @param string $method Permitted model method for the selected entity type.
     * @param array $data Entity identifier and chosen picture from POST.
     * @return void
     */
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

    /**
     * Reject administrator image mutations without a valid POST CSRF token.
     * Uses Joomla's session token guard and returns an HTTP 403 error before any file or database action can run.
     * @return void
     * @throws \RuntimeException When the submitted token is invalid.
     */
    private function requireToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
    }

    /**
     * Require an authenticated identity with the specified component permission.
     * Checks the current Joomla identity and permits the requested SportsManagement action or component administration; rejects guests and unauthorised users.
     * @param string $action Joomla component ACL action required by the mutation.
     * @return void
     * @throws \RuntimeException When the identity lacks access.
     */
    private function requirePermission(string $action): void
    {
        $identity = $this->app->getIdentity();

        if ($identity === null || (int) ($identity->id ?? 0) <= 0
            || (!$identity->authorise($action, 'com_sportsmanagement')
                && !$identity->authorise('core.admin', 'com_sportsmanagement'))) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Locate a safe local component image directory for administrator uploads.
     * Normalises the category folder, rejects traversal and invalid segments, optionally creates a missing directory through Joomla's filesystem API and returns an absolute path.
     * @param string $folder Component image category beneath the fixed image root.
     * @param bool $create Create the category directory when missing.
     * @return string|null Absolute directory ending in a separator, or null.
     */
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
                Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
                return null;
            }
        }

        if (!is_dir($directory)) {
            return null;
        }

        return rtrim($directory, '/\\') . '/';
    }

    /**
     * Move a bounded temporary upload into the validated image directory.
     * Checks source existence and configured size limits, calls Joomla's filesystem upload method and logs unexpected filesystem failures.
     * @param string $source Temporary upload file path.
     * @param string $target Local image destination path.
     * @return bool True when the uploaded file was stored.
     */
    private function uploadFile(string $source, string $target): bool
    {
        if (!is_file($source) || filesize($source) > $this->maxImageBytes()) {
            return false;
        }

        try {
            return (bool) File::upload($source, $target);
        } catch (Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            return false;
        }
    }

    /**
     * Fetch and save a permitted remote image for the administrator selector.
     * Validates the initial URL, filename and each redirect destination, restricts network and payload sizes, verifies the image contents and writes only approved data.
     * @param string $url Remote image URL supplied by the administrator.
     * @param string $baseDir Already validated local image directory.
     * @return string|null Stored basename or null on validation or download failure.
     */
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
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
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
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            return null;
        }

        return $filename;
    }

    /**
     * Validate an outbound image URL before any server-side HTTP request.
     * Rejects malformed schemes, credentials, unsafe hosts, ports and control bytes to keep remote image downloads away from internal services.
     * @param string $url Proposed HTTP or HTTPS image location.
     * @return array|null Parsed URL components or null when unsafe.
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

        $parts['scheme'] = $scheme;
        $parts['host'] = $host;

        return $parts;
    }

    /**
     * Check a remote image host and its DNS answers for public reachability.
     * Validates host syntax, rejects internal hostname suffixes, resolves A and AAAA records and requires every resulting address to pass public-IP checks.
     * @param string $host Normalised URL hostname or IP literal.
     * @return bool Whether the host resolves exclusively to public addresses.
     */
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

    /**
     * Reject unroutable, reserved and internal IP addresses for remote fetches.
     * Applies PHP's private and reserved range filters and blocks extra IPv4 carrier-grade NAT and benchmarking networks.
     * @param string $address IPv4 or IPv6 candidate address.
     * @return bool Whether server-side access to this address is permitted.
     */
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

    /**
     * Resolve one HTTP redirect against the current remote image URL.
     * Normalises a Location header while rejecting control characters and invalid targets, allowing the subsequent URL validator to check the next fetch destination.
     * @param string $baseUrl URL of the response issuing the redirect.
     * @param string $location Raw HTTP Location header value.
     * @return string|null Resolved next URL or null for invalid redirect syntax.
     */
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

    /**
     * Derive a safe supported image basename for local storage.
     * Strips path components and rejects invalid basenames or extensions before applying the component filename sanitiser.
     * @param string $baseDir Validated destination directory.
     * @param string $filename Suggested image filename.
     * @return string|null Approved basename or null when invalid.
     */
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

    /**
     * Verify a local upload is a supported image of permitted size.
     * Checks file existence and length, inspects its actual image metadata and accepts only the MIME types permitted by this controller.
     * @param string $path Temporary local image file path.
     * @return bool Whether the file is allowed for upload.
     */
    private function isAllowedImageFile(string $path): bool
    {
        if (!is_file($path) || filesize($path) > $this->maxImageBytes()) {
            return false;
        }

        $info = @getimagesize($path);

        return $info !== false && $this->isAllowedMime((string) ($info['mime'] ?? ''));
    }

    /**
     * Validate image bytes received from a remote server.
     * Decodes image metadata from the response body and accepts only content belonging to the supported MIME list.
     * @param string $body Downloaded binary response body.
     * @return bool Whether the response contains a supported image.
     */
    private function isAllowedImageData(string $body): bool
    {
        $info = @getimagesizefromstring($body);

        return $info !== false && $this->isAllowedMime((string) ($info['mime'] ?? ''));
    }

    /**
     * Check decoded image metadata against the supported MIME allowlist.
     * Normalises the MIME type and limits writes to GIF, JPEG, PNG and WebP image formats.
     * @param string $mime MIME value detected from image contents.
     * @return bool Whether the MIME type is accepted.
     */
    private function isAllowedMime(string $mime): bool
    {
        return in_array(strtolower($mime), ['image/gif', 'image/jpeg', 'image/png', 'image/webp'], true);
    }

    /**
     * Read the configured maximum image size as a byte limit.
     * Clamps the component's kilobyte setting to a positive value and converts it for upload and network payload validation.
     * @return int Maximum image size in bytes.
     */
    private function maxImageBytes(): int
    {
        $kilobytes = max(1, (int) ComponentHelper::getParams('com_sportsmanagement')->get('image_max_size', 120));

        return $kilobytes * 1024;
    }

    /**
     * Normalise an image category path without allowing directory traversal.
     * Unifies path separators, rejects null bytes and dot segments and keeps the folder relative to the fixed SportsManagement image directory.
     * @param string $folder Relative category path from component configuration.
     * @return string|null Safe relative directory or null.
     */
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

    /**
     * Validate a selected image path before filesystem deletion.
     * Canonicalises separators, compares the value with Joomla's path filter and rejects empty, dot or unsafe path components.
     * @param string $image Image path submitted by the administrator.
     * @return string|null Safe relative image path or null.
     */
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

    /**
     * Return the completed image selection to the parent Joomla modal.
     * Encodes callback parameters for safe JavaScript embedding, invokes the matching image selector when needed and closes the modal or refreshes the parent page.
     * @param string $type Image category defining the parent callback.
     * @param string $filename Uploaded image basename.
     * @param string $field Target form field identifier.
     * @param string $fieldId Target entity or field record identifier.
     * @param bool $select Whether the modal must call the image selection handler.
     * @return void
     */
    private function closeUploadModal(
        string $type,
        string $filename,
        string $field,
        string $fieldId,
        bool $select
    ): void {
        // Never let user-controlled selector values terminate the script element.
        $flags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
            | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR;
        $typeJson     = json_encode($type, $flags);
        $filenameJson = json_encode($filename, $flags);
        $fieldJson    = json_encode($field, $flags);
        $fieldIdJson  = json_encode($fieldId, $flags);

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

    /**
     * Show a safe image-upload error without exposing unescaped script content.
     * JSON-encodes the public error message using HTML-safe flags, displays a browser alert and navigates back to the previous page.
     * @param string $message Translated error message for the administrator.
     * @return void
     */
    private function showUploadError(string $message): void
    {
        echo '<script>alert(' . json_encode($message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR)
            . ');window.history.go(-1);</script>';
    }
}
