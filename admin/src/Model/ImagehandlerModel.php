<?php
/**
 * Joomla 5/6 administrator image selector model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use DirectoryIterator;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
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

    /**
     * Initialise the image browser with request-backed pagination and filters.
     * @param array $config Joomla image handler model configuration.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $app    = self::resolveAdministratorApplication();
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
     * Bind the image model to the active SportsManagement database.
     *
     * Reuses the legacy external database resolver when it returns an
     * implementation of Joomla DatabaseInterface; otherwise keeps the
     * explicitly injected Joomla connection as a safe fallback.
     *
     * @param DatabaseInterface $db Joomla database service.
     * @return void
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

    /**
     * Assign an approved club image after validating its record ID.
     *
     * A direct model invocation still requires an authenticated editor.
     *
     * @param array $data Club ID and selected image file.
     * @return bool|string True on success or a failure message.
     */
    public function saveimageclub(array $data): bool|string
    {
        return $this->updateImageField(
            '#__sportsmanagement_club',
            self::positiveRecordId($data['club_id'] ?? null),
            'logo_big',
            'images/com_sportsmanagement/database/clubs/large/',
            (string) ($data['picture'] ?? '')
        );
    }

    /**
     * Assign an approved image to a season team-player relation.
     *
     * Rejects malformed IDs and delegates component ACL checks to the
     * shared image-field writer before updating a database record.
     *
     * @param array $data Team player ID and selected picture.
     * @return bool|string True on success or a failure message.
     */
    public function saveimageteamplayer(array $data): bool|string
    {
        return $this->updateImageField(
            '#__sportsmanagement_season_team_person_id',
            self::positiveRecordId($data['teamplayer_id'] ?? null),
            'picture',
            'images/com_sportsmanagement/database/teamplayers/',
            (string) ($data['picture'] ?? '')
        );
    }

    /**
     * Assign a previously selected image to an existing person.
     *
     * Parses the record ID without lossy integer casts and validates the
     * filename and editor permission through the shared update method.
     *
     * @param array $data Player ID and selected picture.
     * @return bool|string True on success or a failure message.
     */
    public function saveimageplayer(array $data): bool|string
    {
        return $this->updateImageField(
            '#__sportsmanagement_person',
            self::positiveRecordId($data['player_id'] ?? null),
            'picture',
            'images/com_sportsmanagement/database/persons/',
            (string) ($data['picture'] ?? '')
        );
    }

    /**
     * Read image metadata for the active page of the image browser.
     *
     * Uses validated readable files, extracts dimensions and scales the
     * image preview to the configured maximum size.
     *
     * @return array<int,object> Images with size and dimensions.
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
     * Read image files only from the actual component media root.
     *
     * Resolves both the selected directory and each file to a canonical
     * path so symlinks cannot expose private data outside the image tree.
     * Retains the legacy search and pagination listing contract.
     *
     * @return array<int, object> Filenames and safe local paths.
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

        $realRoot = realpath(JPATH_SITE . '/images/com_sportsmanagement/database');
        $realFolder = realpath($basePath);

        if ($realRoot === false || $realFolder === false
            || ($realFolder !== $realRoot
                && !str_starts_with($realFolder, rtrim($realRoot, '/\\') . DIRECTORY_SEPARATOR))) {
            $this->setState('total', 0);
            return [];
        }

        $realRootPrefix = rtrim($realRoot, '/\\') . DIRECTORY_SEPARATOR;
        $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
        $images = [];

        try {
            foreach (new DirectoryIterator($basePath) as $entry) {
                if (!$entry->isFile() || $entry->isDot()) {
                    continue;
                }

                $file = $entry->getFilename();
                $extension = strtolower($entry->getExtension());
                $realFile = $entry->getRealPath();

                if ($file === '' || str_starts_with($file, '.')
                    || $realFile === false || !str_starts_with($realFile, $realRootPrefix)
                    || !in_array($extension, $allowedExtensions, true)) {
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

    /**
     * Format a byte count as a human-readable size.
     * @param int $size File size in bytes.
     * @return string Formatted size in bytes, kilobytes or megabytes.
     */
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
     * Calculate proportional image dimensions for a preview target.
     *
     * @param int $width Source image width.
     * @param int $height Source image height.
     * @param int $target Target longest-side length.
     * @return array{0:int,1:int} Scaled width and height.
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

    /**
     * Construct or reuse Joomla pagination for the image browser.
     * @return Pagination Current image list pagination.
     */
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

    /**
     * Save a selected image only for an authenticated component editor.
     *
     * Uses trusted, component-defined tables, columns and media prefixes,
     * validates a safe image basename and suppresses sensitive database
     * exception messages from both the JSON response and server log.
     *
     * @param string $table Database table to update.
     * @param int $id Target row identifier.
     * @param string $column Image column name.
     * @param string $prefix Component image directory prefix.
     * @param string $picture Selected image filename.
     * @return bool|string True or a translated/failure string.
     */
    private function updateImageField(
        string $table,
        int $id,
        string $column,
        string $prefix,
        string $picture
    ): bool|string {
        if (!self::canEditImages()) {
            return Text::_('JERROR_ALERTNOAUTHOR');
        }

        $picture = $this->normaliseImageName($picture);

        if ($id <= 0 || $picture === null) {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE');
        }

        $row = new stdClass();
        $row->id = $id;
        $row->{$column} = $prefix . $picture;

        try {
            $this->getDatabase()->updateObject($table, $row, 'id');
            return true;
        } catch (Throwable $e) {
            Log::add(
                __METHOD__ . ': image selection update failed (' . get_class($e) . ').',
                Log::ERROR,
                'com_sportsmanagement'
            );

            return Text::_('JERROR_AN_ERROR_HAS_OCCURRED');
        }
    }

    /**
     * Validate a selected image basename for use in a local media URL.
     *
     * Accepts historical component-relative filenames, but never stores
     * file extensions other than the supported raster-image formats or
     * URL-control characters that could corrupt image markup.
     *
     * @param string $picture User-selected filename or component image path.
     * @return string|null Safe basename, or null for malformed input.
     */
    private function normaliseImageName(string $picture): ?string
    {
        $picture = trim(str_replace('\\', '/', $picture));
        $name = basename($picture);

        if (strlen($name) > 255
            || preg_match('/^[\\pL\\pN][\\pL\\pN _().+-]{0,240}\\.(?:gif|jpe?g|png|webp)$/iuD', $name) !== 1) {
            return null;
        }

        return $name;
    }

    /**
     * Validate an image browser directory relative to its trusted root.
     * @param string $folder Image folder requested by the browser.
     * @return string|null Valid relative path or null if invalid.
     */
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
    /**
     * Convert a posted record selector into a positive database ID.
     *
     * Only positive integers and decimal strings are accepted, including
     * legacy zero-padded values. Booleans, nested values, floats, malformed
     * strings and integer overflows can never select another record.
     *
     * @param mixed $value Raw identifier from the image modal form.
     * @return int Positive database ID, or zero when invalid.
     */
    private static function positiveRecordId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        if (is_string($value)) {
            if (strlen($value) > 64 || preg_match('/^[0-9]+$/D', $value) !== 1) {
                return 0;
            }

            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Require a logged-in Joomla administrator with component edit rights.
     *
     * This model-level permission guard supplements the image controller's
     * POST-token validation when a caller invokes saveimage* directly.
     * Joomla component administrators retain their normal override.
     *
     * @return bool Whether the current identity may edit component records.
     */
    private static function canEditImages(): bool
    {
        $identity = self::resolveAdministratorApplication()->getIdentity();

        return is_object($identity)
            && (int) ($identity->id ?? 0) > 0
            && method_exists($identity, 'authorise')
            && ($identity->authorise('core.edit', 'com_sportsmanagement')
                || $identity->authorise('core.admin', 'com_sportsmanagement'));
    }

    /**
     * Resolve and verify the Joomla administrator application service.
     * @return AdministratorApplication Valid administrator application.
     * @throws \RuntimeException If the application is unavailable.
     */
    private static function resolveAdministratorApplication(): AdministratorApplication
    {
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app instanceof AdministratorApplication || !$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement image handler model requires the Joomla administrator application.', 500);
        }

        return $app;
    }

}
