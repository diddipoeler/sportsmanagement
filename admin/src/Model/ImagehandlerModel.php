<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Database\DatabaseInterface;
use Joomla\String\StringHelper;
use stdClass;
use Throwable;

/**
 * Native administrator image selector model.
 */
final class ImagehandlerModel extends BaseDatabaseModel
{
    private ?Pagination $pagination = null;

    public function __construct($config = [])
    {
        parent::__construct($config);

        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $limit  = (int) $app->getUserStateFromRequest(
            $option . '.imageselect.limit',
            'limit',
            (int) $app->get('list_limit', 20),
            'int'
        );
        $start  = max(0, (int) $app->getUserStateFromRequest(
            $option . '.imageselect.limitstart',
            'limitstart',
            0,
            'int'
        ));
        $search = trim(StringHelper::strtolower((string) $app->getUserStateFromRequest(
            $option . '.imageselect.search',
            'search',
            '',
            'string'
        )));

        $this->setState('limit', max(0, $limit));
        $this->setState('limitstart', $start);
        $this->setState('search', $search);
        $this->setState('folder', $input->getString('folder', ''));
    }

    /**
     * Use the SportsManagement database connection, including configured external DBs.
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        try {
            if (class_exists('sportsmanagementHelper', false)) {
                $sportsManagementDb = \sportsmanagementHelper::getDBConnection();

                if ($sportsManagementDb instanceof DatabaseInterface) {
                    parent::setDatabase($sportsManagementDb);
                    return;
                }
            }
        } catch (Throwable) {
        }

        parent::setDatabase($db);
    }

    public function saveimageclub(array $data): bool|string
    {
        return $this->updateImageField(
            '#__sportsmanagement_club',
            (int) ($data['club_id'] ?? 0),
            'logo_big',
            'images/com_sportsmanagement/database/clubs/large/',
            (string) ($data['picture'] ?? '')
        );
    }

    public function saveimageteamplayer(array $data): bool|string
    {
        return $this->updateImageField(
            '#__sportsmanagement_season_team_person_id',
            (int) ($data['teamplayer_id'] ?? 0),
            'picture',
            'images/com_sportsmanagement/database/teamplayers/',
            (string) ($data['picture'] ?? '')
        );
    }

    public function saveimageplayer(array $data): bool|string
    {
        return $this->updateImageField(
            '#__sportsmanagement_person',
            (int) ($data['player_id'] ?? 0),
            'picture',
            'images/com_sportsmanagement/database/persons/',
            (string) ($data['picture'] ?? '')
        );
    }

    /**
     * Return the image metadata for the current page.
     *
     * @return array<int, object>
     */
    public function getImages(): array
    {
        $list  = $this->getList();
        $total = count($list);
        $start = max(0, (int) $this->getState('limitstart', 0));
        $limit = max(0, (int) $this->getState('limit', 0));

        if ($start >= $total && $total > 0) {
            $start = $limit > 0 ? max(0, (int) (floor(($total - 1) / $limit) * $limit)) : 0;
            $this->setState('limitstart', $start);
        }

        $slice = $limit > 0 ? array_slice($list, $start, $limit) : array_slice($list, $start);
        $images = [];

        foreach ($slice as $image) {
            $image->size = $this->_parseSize((int) (@filesize($image->path) ?: 0));
            $info = @getimagesize($image->path);

            if ($info === false) {
                continue;
            }

            $image->width  = (int) $info[0];
            $image->height = (int) $info[1];

            if ($image->width > 60 || $image->height > 60) {
                [$image->width_60, $image->height_60] = $this->_imageResize(
                    $image->width,
                    $image->height,
                    60
                );
            } else {
                $image->width_60  = $image->width;
                $image->height_60 = $image->height;
            }

            $images[] = $image;
        }

        return $images;
    }

    /**
     * Return all selectable image files from the requested SportsManagement folder.
     *
     * @return array<int, object>
     */
    public function getList(): array
    {
        static $cache = [];

        $folder = $this->normaliseRelativeFolder((string) $this->getState('folder', ''));
        $search = (string) $this->getState('search', '');
        $key    = $folder . "\0" . $search;

        if (isset($cache[$key])) {
            $this->setState('total', count($cache[$key]));
            return $cache[$key];
        }

        if ($folder === null) {
            $this->setState('total', 0);
            return [];
        }

        $basePath = JPATH_SITE . '/images/com_sportsmanagement/database/' . $folder;

        if (!is_dir($basePath) || !is_readable($basePath)) {
            $this->setState('total', 0);
            return [];
        }

        $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
        $images = [];

        try {
            foreach (new DirectoryIterator($basePath) as $entry) {
                if (!$entry->isFile() || $entry->isDot()) {
                    continue;
                }

                $file = $entry->getFilename();
                $extension = strtolower($entry->getExtension());

                if (!in_array($extension, $allowedExtensions, true)) {
                    continue;
                }

                if ($search !== '' && !str_contains(StringHelper::strtolower($file), $search)) {
                    continue;
                }

                $tmp       = new stdClass();
                $tmp->name = $file;
                $tmp->path = $entry->getPathname();
                $images[]  = $tmp;
            }
        } catch (Throwable) {
            $images = [];
        }

        usort($images, static fn(object $a, object $b): int => strnatcasecmp($a->name, $b->name));

        $cache[$key] = $images;
        $this->setState('total', count($images));

        if ((int) $this->getState('limit', 0) === 0) {
            $this->setState('limit', count($images));
        }

        return $images;
    }

    public function _parseSize(int $size): string
    {
        if ($size < 1024) {
            return $size . ' bytes';
        }

        if ($size < 1024 * 1024) {
            return sprintf('%01.2f', $size / 1024.0) . ' Kb';
        }

        return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' Mb';
    }

    /**
     * @return array{0:int,1:int}
     */
    public function _imageResize(int $width, int $height, int $target): array
    {
        if ($width <= 0 || $height <= 0 || $target <= 0) {
            return [0, 0];
        }

        $percentage = $width > $height ? ($target / $width) : ($target / $height);

        return [
            (int) round($width * $percentage),
            (int) round($height * $percentage),
        ];
    }

    public function getPagination(): Pagination
    {
        if ($this->pagination === null) {
            if ($this->getState('total') === null) {
                $this->getList();
            }

            $this->pagination = new Pagination(
                (int) $this->getState('total', 0),
                (int) $this->getState('limitstart', 0),
                (int) $this->getState('limit', 0)
            );
        }

        return $this->pagination;
    }

    private function updateImageField(
        string $table,
        int $id,
        string $column,
        string $prefix,
        string $picture
    ): bool|string {
        $picture = $this->normaliseImageName($picture);

        if ($id <= 0 || $picture === null) {
            return 'Invalid image selection';
        }

        $row = new stdClass();
        $row->id = $id;
        $row->{$column} = $prefix . $picture;

        try {
            $this->getDatabase()->updateObject($table, $row, 'id');
            return true;
        } catch (Throwable $e) {
            return $e->getMessage();
        }
    }

    private function normaliseImageName(string $picture): ?string
    {
        $picture = trim(str_replace('\\', '/', $picture));
        $name = basename($picture);

        if ($name === '' || $name === '.' || $name === '..' || str_contains($name, "\0")) {
            return null;
        }

        return $name;
    }

    private function normaliseRelativeFolder(string $folder): ?string
    {
        $folder = trim(str_replace('\\', '/', $folder), '/');

        if ($folder === '' || str_contains($folder, "\0")) {
            return null;
        }

        $parts = explode('/', $folder);

        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return null;
            }
        }

        return implode('/', $parts);
    }
}
