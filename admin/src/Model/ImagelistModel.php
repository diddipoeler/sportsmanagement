<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use stdClass;
use Throwable;

/**
 * File-backed image browser used by the administrator image selector modal.
 */
final class ImagelistModel extends ListModel
{
    public static array $filesOutput = [];

    public int $limitstart = 0;

    public int $limit = 0;

    /** @var array<int, object> */
    public array $items = [];

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->limitstart = Factory::getApplication()->getInput()->getInt('limitstart', 0);
    }

    /**
     * Build the requested image file list.
     *
     * @param string $path      Relative image directory.
     * @param mixed  $scopeName Retained legacy scope parameter.
     * @param array  $post      Filter data.
     *
     * @return array<int, object>
     */
    public function getFiles($path, $scopeName = '', $post = []): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $relativePath = $this->normaliseRelativePath((string) $path);

        if ($relativePath === null) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE'),
                Log::WARNING,
                'jsmerror'
            );
            return $this->resetFiles();
        }

        $baseRoot = Path::clean(JPATH_ROOT . '/images/com_sportsmanagement/database');
        $directory = Path::clean($baseRoot . '/' . $relativePath);
        $rootPrefix = rtrim($baseRoot, '/\\') . DIRECTORY_SEPARATOR;

        if ($directory !== $baseRoot && !str_starts_with($directory . DIRECTORY_SEPARATOR, $rootPrefix)) {
            Log::add(
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE'),
                Log::WARNING,
                'jsmerror'
            );
            return $this->resetFiles();
        }

        if (!is_dir($directory)) {
            try {
                if (!Folder::create($directory) && !is_dir($directory)) {
                    throw new \RuntimeException('Unable to create image directory');
                }
                Log::add(Text::_('COM_SPORTSMANAGEMENT_ADMIN_CREATE_FOLDER'), Log::NOTICE, 'jsmerror');
            } catch (Throwable $e) {
                Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
                return $this->resetFiles();
            }
        }

        if (!is_readable($directory)) {
            return $this->resetFiles();
        }

        $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
        $search = mb_strtolower(trim((string) ($post['filter_search'] ?? '')));
        self::$filesOutput = [];

        try {
            foreach (new DirectoryIterator($directory) as $entry) {
                if (!$entry->isFile() || $entry->isDot()) {
                    continue;
                }

                $file = $entry->getFilename();
                $extension = strtolower($entry->getExtension());

                if (!in_array($extension, $allowedExtensions, true)) {
                    continue;
                }

                if ($search !== '' && !str_contains(mb_strtolower($file), $search)) {
                    continue;
                }

                $fileDate = $entry->getMTime();
                $fileMeta = new stdClass();
                $fileMeta->size = $entry->getSize();
                $fileMeta->is_writable = (int) $entry->isWritable();
                $fileMeta->name = pathinfo($file, PATHINFO_FILENAME);
                $fileMeta->exs = $extension;
                $fileMeta->file = $file;
                $fileMeta->fileP = '';
                $fileMeta->path_relative = $relativePath;
                $fileMeta->width_60 = '60';
                $fileMeta->height_60 = '60';
                $fileMeta->dateC = $fileDate;
                $fileMeta->dateM = $fileDate;
                self::$filesOutput[] = $fileMeta;
            }
        } catch (Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return $this->resetFiles();
        }

        usort(
            self::$filesOutput,
            static fn(object $a, object $b): int => strnatcasecmp($a->file, $b->file)
        );

        $limit = max(0, (int) $this->getUserStateFromRequest(
            $this->context . '.limit',
            'limit',
            (int) $app->get('list_limit', 20),
            'int'
        ));
        $start = max(0, $input->getInt('limitstart', 0));

        $this->setState('list.limit', $limit);
        $this->setState('list.start', $start);
        $this->limitstart = $start;
        $this->limit = $limit;
        $this->items = $limit > 0
            ? array_slice(self::$filesOutput, $start, $limit)
            : array_slice(self::$filesOutput, $start);

        return $this->items;
    }

    public function getPagination(): Pagination
    {
        $store = $this->getStoreId('getPagination');
        $limit = max(0, (int) $this->getState('list.limit'));
        $this->cache[$store] = new Pagination($this->getTotal(), $this->getStart(), $limit);

        return $this->cache[$store];
    }

    public function getTotal(): int
    {
        $store = $this->getStoreId('getTotal');
        $this->cache[$store] = count(self::$filesOutput);

        return $this->cache[$store];
    }

    public function getStart(): int
    {
        $store = $this->getStoreId('getstart');
        $start = max(0, (int) $this->getState('list.start', $this->limitstart));
        $limit = max(0, (int) $this->getState('list.limit', $this->limit));
        $total = $this->getTotal();

        if ($limit > 0 && $start >= $total && $total > 0) {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        } elseif ($limit === 0) {
            $start = 0;
        }

        $this->setState('list.start', $start);
        $this->cache[$store] = $start;

        return $this->cache[$store];
    }

    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();

        $value = max(0, (int) $this->getUserStateFromRequest(
            $this->context . '.limit',
            'limit',
            (int) $app->get('list_limit', 20),
            'int'
        ));
        $this->setState('list.limit', $value);
        $this->setState('list.start', max(0, $input->getInt('limitstart', 0)));

        $published = $this->getUserStateFromRequest(
            $this->context . '.filter.state',
            'filter_published',
            '',
            'string'
        );
        $this->setState('filter.state', $published);

        $filterOrder = $this->getUserStateFromRequest(
            $this->context . '.filter_order',
            'filter_order',
            '',
            'string'
        );
        $filterOrderDir = strtoupper((string) $this->getUserStateFromRequest(
            $this->context . '.filter_order_Dir',
            'filter_order_Dir',
            '',
            'cmd'
        ));

        if (!in_array($filterOrderDir, ['ASC', 'DESC', ''], true)) {
            $filterOrderDir = 'ASC';
        }

        $this->setState('filter_order', $filterOrder);
        $this->setState('filter_order_Dir', $filterOrderDir);
    }

    /**
     * File-backed model; no database list query is used.
     */
    protected function getListQuery()
    {
        return null;
    }

    /**
     * @return array<int, object>
     */
    private function resetFiles(): array
    {
        self::$filesOutput = [];
        $this->items = [];
        $this->setState('list.start', 0);
        return $this->items;
    }

    private function normaliseRelativePath(string $path): ?string
    {
        $path = trim(str_replace('\\', '/', $path), '/');

        if ($path === '' || str_contains($path, "\0")) {
            return null;
        }

        $parts = explode('/', $path);

        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return null;
            }
        }

        return implode('/', $parts);
    }
}
