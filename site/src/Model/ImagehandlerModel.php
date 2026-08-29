<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
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

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        /** @var SiteApplication $app */
        $app = Factory::getContainer()->get(SiteApplication::class);
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

    public function setFolder(string $folder): void
    {
        $this->setState('folder', $this->normaliseFolder($folder) ?? '');
        $this->imageList = null;
        $this->pagination = null;
    }

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

    public function getList(): array
    {
        if ($this->imageList !== null) {
            return $this->imageList;
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

        $files = Folder::files($basePath);
        $search = (string) $this->getState('search', '');
        $images = [];

        foreach ((array) $files as $file) {
            $file = (string) $file;
            $lower = StringHelper::strtolower($file);

            if ($file === ''
                || str_starts_with($file, '.')
                || in_array($lower, ['index.html', 'thumbs.db', 'readme.txt'], true)
                || ($search !== '' && !str_contains($lower, $search))) {
                continue;
            }

            $path = Path::clean($basePath . DIRECTORY_SEPARATOR . $file);

            if (!is_file($path)) {
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
