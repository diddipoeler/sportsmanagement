<?php
/**
 * Joomla 5/6 frontend image selector read model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;

/** Joomla 5/6 frontend image selector read model. */
final class ImagehandlerModel extends BaseDatabaseModel
{
    private ?Pagination $pagination = null;
    private ?array $imageList = null;
    private bool $invalidFolder = false;

    /**
     * Resolve Joomla's active frontend application through the native resolver.
     *
     * Keeps the image selection model independent from legacy global Joomla
     * factories and enforces use of the site application.
     *
     * @return CMSApplicationInterface Valid Joomla application.
     */
    private static function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Initialise the image selector from filtered Joomla request state.
     *
     * Reads the requested folder, search term and page limits, uses Joomla's
     * user-state compatibility behaviour, and normalises the requested folder
     * before any filesystem access.
     *
     * @param array $config Joomla MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional native MVC factory.
     * @return void
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $app = self::siteApplication();

        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $defaultLimit = max(1, (int) $app->get('list_limit', 20));
        $limit = max(0, (int) $app->getUserStateFromRequest(
            $option . '.imageselect.limit',
            'limit',
            $defaultLimit,
            'uint'
        ));
        $limitStart = max(0, (int) $app->getUserStateFromRequest(
            $option . '.imageselect.limitstart',
            'limitstart',
            0,
            'uint'
        ));
        $search = trim(StringHelper::strtolower((string) $app->getUserStateFromRequest(
            $option . '.search',
            'search',
            '',
            'string'
        )));

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitStart);
        $this->setState('search', $search);
        $this->setFolder($input->getString('folder', ''));
    }

    /**
     * Set and validate the image folder, invalidating cached results.
     *
     * Rejects traversal segments and unsupported folder characters without
     * ever reverting invalid input to the root folder. Resets cached image
     * and pagination results whenever a different folder is selected.
     *
     * @param string $folder Relative folder beneath the component image directory.
     * @return void
     */
    public function setFolder(string $folder): void
    {
        $normalised = $this->normaliseFolder($folder);
        $this->invalidFolder = $normalised === null;
        $this->setState('folder', $normalised ?? '');
        $this->imageList = null;
        $this->pagination = null;
    }

    /**
     * Prepare the current page of images for the Joomla selector view.
     *
     * Slices the validated file listing according to request pagination and reads
     * image dimensions and file sizes. Uses a safe preview box for SVG or other
     * supported formats without readily available dimensions.
     *
     * @return array<int,object> Image objects with file sizes and thumbnail dimensions.
     */
    public function getImages(): array
    {
        $list = $this->getList();
        $total = count($list);
        $start = max(0, (int) $this->getState('limitstart', 0));
        $limit = max(0, (int) $this->getState('limit', 0));

        if ($total > 0 && $start >= $total) {
            $start = 0;
            $this->setState('limitstart', 0);
        }

        if ($limit === 0) {
            $limit = $total;
            $this->setState('limit', $limit);
        }

        $page = $limit > 0 ? array_slice($list, $start, $limit) : [];

        foreach ($page as $image) {
            $size = @filesize($image->path);
            $image->size = $this->_parseSize($size === false ? 0 : (int) $size);
            $info = @getimagesize($image->path);

            if ($info === false) {
                // SVG and other browser-renderable formats do not always expose
                // dimensions through getimagesize(). Keep a safe preview box.
                $image->width = 60;
                $image->height = 60;
                $image->width_60 = 60;
                $image->height_60 = 60;
                continue;
            }

            $image->width = max(1, (int) ($info[0] ?? 60));
            $image->height = max(1, (int) ($info[1] ?? 60));

            if ($image->width > 60 || $image->height > 60) {
                [$image->width_60, $image->height_60] = $this->_imageResize(
                    $image->width,
                    $image->height,
                    60
                );
            } else {
                $image->width_60 = $image->width;
                $image->height_60 = $image->height;
            }
        }

        return $page;
    }

    /**
     * List only image files within the component's real filesystem root.
     *
     * Verifies both lexical and canonical directory boundaries so symlinked
     * folders cannot escape the component's image directory. Filters unsupported
     * file extensions and resolves each filename before reading metadata, while
     * maintaining the existing Joomla search and pagination state.
     *
     * @return array<int,object> Safe image filename and path records.
     */
    public function getList(): array
    {
        if ($this->imageList !== null) {
            return $this->imageList;
        }

        // Never turn a rejected folder selector into the media-root listing.
        if ($this->invalidFolder) {
            $this->setState('total', 0);
            return $this->imageList = [];
        }

        $folder = $this->normaliseFolder((string) $this->getState('folder', ''));
        $baseRoot = Path::clean(JPATH_SITE . '/images/com_sportsmanagement/database');

        if ($folder === null) {
            $this->setState('total', 0);
            return $this->imageList = [];
        }

        $basePath = $folder === '' ? $baseRoot : Path::clean($baseRoot . '/' . $folder);
        $rootPrefix = rtrim($baseRoot, '/\\') . DIRECTORY_SEPARATOR;

        if ($basePath !== $baseRoot && !str_starts_with($basePath . DIRECTORY_SEPARATOR, $rootPrefix)) {
            $this->setState('total', 0);
            return $this->imageList = [];
        }

        if (!is_dir($basePath)) {
            $this->setState('total', 0);
            return $this->imageList = [];
        }

        // Lexical path checks alone cannot detect symlinked directories.
        // Resolve the real folder and root to forbid escapes outside the
        // image selector's intended Joomla media directory.
        $realRoot = realpath($baseRoot);
        $realFolder = realpath($basePath);

        if ($realRoot === false || $realFolder === false
            || ($realFolder !== $realRoot
                && !str_starts_with($realFolder, rtrim($realRoot, '/\\') . DIRECTORY_SEPARATOR))) {
            $this->setState('total', 0);
            return $this->imageList = [];
        }

        $files = Folder::files($basePath);
        $search = (string) $this->getState('search', '');
        $images = [];

        foreach ((array) $files as $file) {
            $file = (string) $file;
            $lower = StringHelper::strtolower($file);

            if ($file === ''
                || str_starts_with($file, '.')
                || strpbrk($file, '/\\') !== false
                || preg_match('/[\\x00-\\x1F\\x7F]/', $file) === 1
                || in_array($lower, ['index.html', 'thumbs.db', 'readme.txt'], true)
                || preg_match('/\\.(?:jpe?g|jfif|png|gif|webp|svg|avif|bmp|ico)$/iD', $file) !== 1
                || ($search !== '' && !str_contains($lower, $search))) {
                continue;
            }

            $path = Path::clean($basePath . DIRECTORY_SEPARATOR . $file);
            $realFile = realpath($path);

            // Do not follow symlinked image names to private files outside
            // the configured component media directory.
            if ($realFile === false || !is_file($realFile)
                || !str_starts_with($realFile, rtrim($realRoot, '/\\') . DIRECTORY_SEPARATOR)) {
                continue;
            }

            $images[] = (object) [
                'name' => $file,
                'path' => $path,
            ];
        }

        $this->setState('total', count($images));

        if ((int) $this->getState('limit', 0) === 0) {
            $this->setState('limit', count($images));
        }

        return $this->imageList = $images;
    }

    /**
     * Return the cached Joomla pagination descriptor for the image list.
     *
     * Constructs it from the total validated image count, current page offset and
     * nonzero page size, caching the result until the folder changes.
     *
     * @return Pagination Current Joomla pagination object.
     */
    public function getPagination(): Pagination
    {
        if ($this->pagination === null) {
            $this->pagination = new Pagination(
                (int) $this->getState('total', 0),
                (int) $this->getState('limitstart', 0),
                max(1, (int) $this->getState('limit', 0))
            );
        }

        return $this->pagination;
    }

    /**
     * Format image byte counts into legacy selector size labels.
     *
     * Normalises negative and non-integer inputs to nonnegative byte counts and
     * formats larger values in KiB and MiB with two decimal places.
     *
     * @param mixed $size File size in bytes.
     * @return string Human-readable size using bytes, Kb or Mb.
     */
    public function _parseSize($size): string
    {
        $size = max(0, (int) $size);

        if ($size < 1024) {
            return $size . ' bytes';
        }

        if ($size < 1024 * 1024) {
            return sprintf('%01.2f', $size / 1024.0) . ' Kb';
        }

        return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' Mb';
    }

    /**
     * Calculate dimensions that fit a fixed thumbnail bounding box.
     *
     * Converts incoming dimensions to positive integers, scales the longest axis
     * to at most the requested maximum and preserves the aspect ratio.
     *
     * @param mixed $width Source image width.
     * @param mixed $height Source image height.
     * @param mixed $target Maximum thumbnail dimension.
     * @return array{0:int,1:int} Rescaled width and height.
     */
    public function _imageResize($width, $height, $target): array
    {
        $width = max(1, (int) $width);
        $height = max(1, (int) $height);
        $target = max(1, (int) $target);
        $percentage = $width > $height ? ($target / $width) : ($target / $height);

        return [
            max(1, (int) round($width * $percentage)),
            max(1, (int) round($height * $percentage)),
        ];
    }

    /**
     * Validate a relative image subdirectory without filesystem access.
     *
     * Converts backslash separators for compatibility, rejects NUL bytes, empty
     * path segments, dot traversal and unsupported filename characters, then
     * returns a normalised relative folder or null for invalid input.
     *
     * @param string $folder Untrusted folder selection.
     * @return string|null Normalised folder path, or null if invalid.
     */
    private function normaliseFolder(string $folder): ?string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');

        if (str_contains($folder, "\0")) {
            return null;
        }

        if ($folder === '') {
            return '';
        }

        $parts = explode('/', $folder);

        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..' || !preg_match('/^[A-Za-z0-9_-]+$/', $part)) {
                return null;
            }
        }

        return implode('/', $parts);
    }
}
