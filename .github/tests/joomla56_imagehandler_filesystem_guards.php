<?php
/**
 * Validate Joomla 5/6 frontend image selection against filesystem escapes.
 *
 * Exercises the native ImagehandlerModel with a temporary Joomla image
 * directory, including symlinked files and folders outside that directory.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Factory {
    interface MVCFactoryInterface {}
}

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel
    {
        private array $state = [];

        /**
         * Store a model-state value for the fake Joomla image selector.
         *
         * @param string $key Joomla state key.
         * @param mixed $value Requested state value.
         * @return void
         */
        public function setState(string $key, mixed $value): void { $this->state[$key] = $value; }

        /**
         * Retrieve the selected image folder and search/pagination state.
         *
         * @param string $key Joomla state key.
         * @param mixed $default Value when missing.
         * @return mixed Current model state or default.
         */
        public function getState(string $key, mixed $default = null): mixed {
            return $this->state[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS\Pagination {
    final class Pagination
    {
        /**
         * Retain the number of validated images for the view's pager.
         *
         * @param int $total Total matched image count.
         * @param int $start Current start index.
         * @param int $limit Requested page size.
         */
        public function __construct(
            public int $total,
            public int $start,
            public int $limit
        ) {}
    }
}

namespace Joomla\Filesystem {
    final class Path
    {
        /**
         * Return the supplied canonical-looking temporary test path.
         *
         * @param string $path Local filesystem path.
         * @return string Unmodified path for the isolated file tree.
         */
        public static function clean(string $path): string { return $path; }
    }

    final class Folder
    {
        /**
         * List visible and hidden names to exercise the model's own checks.
         *
         * @param string $path Folder being inspected.
         * @return array<int,string> Directory entries.
         */
        public static function files(string $path): array {
            return array_values(array_diff(scandir($path) ?: [], ['.', '..']));
        }
    }
}

namespace Joomla\String {
    final class StringHelper
    {
        /**
         * Lowercase file names for test search matching.
         *
         * @param string $text Filename to transform.
         * @return string Lowercase value.
         */
        public static function strtolower(string $text): string { return strtolower($text); }
    }
}

namespace {
    $site = sys_get_temp_dir() . '/sm_image_selector_' . bin2hex(random_bytes(8));
    $images = $site . '/images/com_sportsmanagement/database';
    $outside = $site . '/private';
    mkdir($images . '/valid', 0777, true);
    mkdir($outside, 0777, true);

    \define('_JEXEC', 1);
    \define('JPATH_SITE', $site);

    file_put_contents($images . '/good.png', 'image');
    file_put_contents($images . '/photo.JPEG', 'image');
    file_put_contents($images . '/vector.svg', '<svg></svg>');
    file_put_contents($images . '/install.php', '<?php echo "not an image";');
    file_put_contents($images . '/malicious.jpg.php', '<?php echo "not an image";');
    file_put_contents($images . '/notes.txt', 'not an image');
    file_put_contents($images . '/.private.jpg', 'hidden');
    file_put_contents($images . '/valid/subimage.webp', 'webp');
    file_put_contents($outside . '/secrets.png', 'outside');

    $hasSymlink = function_exists('symlink')
        && @symlink($outside . '/secrets.png', $images . '/linked.png')
        && @symlink($outside, $images . '/escape');

    try {
        require_once \dirname(__DIR__, 2) . '/site/src/Model/ImagehandlerModel.php';
        $model = (new \ReflectionClass(
            \Diddipoeler\Component\SportsManagement\Site\Model\ImagehandlerModel::class
        ))->newInstanceWithoutConstructor();

        $normalise = new \ReflectionMethod($model, 'normaliseFolder');
        foreach (['..', 'valid/../private', '../private', 'foo//bar', 'foo%2Fbar', "foo\0bar"] as $bad) {
            if ($normalise->invoke($model, $bad) !== null) {
                throw new \RuntimeException('Image selector accepted an invalid folder.');
            }
        }

        $model->setFolder('');
        $list = $model->getList();
        $names = array_map(static fn (object $row): string => $row->name, $list);
        sort($names);
        if ($names !== ['good.png', 'photo.JPEG', 'vector.svg']
            || $model->getState('total') !== 3) {
            throw new \RuntimeException('Image selector included non-images or external symlink files.');
        }

        $pagination = $model->getPagination();
        if ($pagination->total !== 3) {
            throw new \RuntimeException('Image selector pagination included excluded files.');
        }

        $model->setState('search', 'photo');
        $model->setFolder('');
        $list = $model->getList();
        if (count($list) !== 1 || $list[0]->name !== 'photo.JPEG') {
            throw new \RuntimeException('Image selector search no longer matches image filenames.');
        }

        $model->setState('search', '');
        $model->setFolder('valid');
        $list = $model->getList();
        if (count($list) !== 1 || $list[0]->name !== 'subimage.webp') {
            throw new \RuntimeException('Valid nested Joomla image folders were rejected.');
        }

        if ($hasSymlink) {
            $model->setFolder('escape');
            if ($model->getList() !== [] || $model->getState('total') !== 0) {
                throw new \RuntimeException('Image selector escaped the media root through a linked folder.');
            }
        }

        $model->setFolder('../private');
        if ($model->getList() !== [] || $model->getState('total') !== 0) {
            throw new \RuntimeException('Invalid image directory was accepted.');
        }

        echo "Joomla 5/6 image selector formats, symlinks and folder guards OK\n";
    } finally {
        foreach ([
            $images . '/linked.png', $images . '/escape',
            $images . '/good.png', $images . '/photo.JPEG', $images . '/vector.svg',
            $images . '/install.php', $images . '/malicious.jpg.php',
            $images . '/notes.txt', $images . '/.private.jpg',
            $images . '/valid/subimage.webp', $outside . '/secrets.png',
        ] as $file) {
            if (is_file($file) || is_link($file)) {
                @unlink($file);
            }
        }
        @rmdir($images . '/valid');
        @rmdir($images);
        @rmdir($site . '/images/com_sportsmanagement');
        @rmdir($site . '/images');
        @rmdir($outside);
        @rmdir($site);
    }
}
