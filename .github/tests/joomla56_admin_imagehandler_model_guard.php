<?php
/**
 * Joomla 5/6 backend image selector security and safe failure regression.
 *
 * Covers direct model ACL denial, strict numeric record selection, safe
 * image extensions, symlink boundaries and database exception privacy.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the message key used by Joomla's safe error facade.
         *
         * @param string $key Translation key.
         * @return string Untranslated key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Record sanitized image-selection write errors.
         *
         * @param string $message Safe log message.
         * @param int $level Error priority.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Application {
    class AdministratorApplication
    {
        public ?object $identity = null;

        /**
         * Verify that the image model is executing in the administrator.
         *
         * @param string $client Client name.
         * @return bool True for administrator.
         */
        public function isClient(string $client): bool { return $client === 'administrator'; }

        /**
         * Return the current test identity or an anonymous identity.
         *
         * @return object|null Current editor.
         */
        public function getIdentity(): ?object { return $this->identity; }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static object $app;

        /**
         * Create a container that supplies the administrator application.
         *
         * @return object Joomla service container double.
         */
        public static function getContainer(): object {
            return new class {
                /**
                 * Return the configured administrator application service.
                 *
                 * @param string $class Requested service.
                 * @return object Current application.
                 */
                public function get(string $class): object { return \Joomla\CMS\Factory::$app; }
            };
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel
    {
        public static ?object $db = null;
        private array $state = [];

        /**
         * Expose an in-memory database writer for direct model tests.
         *
         * @return object Database double.
         */
        protected function getDatabase(): object { return self::$db; }

        /**
         * Set Joomla's selected media folder and image search text.
         *
         * @param string $name State key.
         * @param mixed $value State value.
         * @return void
         */
        public function setState(string $name, mixed $value): void { $this->state[$name] = $value; }

        /**
         * Return image browser state values.
         *
         * @param string $name State key.
         * @param mixed $default Value when absent.
         * @return mixed Current state.
         */
        public function getState(string $name, mixed $default = null): mixed {
            return $this->state[$name] ?? $default;
        }
    }
}

namespace Joomla\CMS\Pagination {
    final class Pagination
    {
        /**
         * Build the pagination descriptor for image view fixtures.
         *
         * @param int $total Image count.
         * @param int $start Page offset.
         * @param int $limit Page size.
         * @return void
         */
        public function __construct(int $total, int $start, int $limit) {}
    }
}

namespace Joomla\String {
    final class StringHelper
    {
        /**
         * Lowercase a filename for Joomla image searching.
         *
         * @param string $text Source filename.
         * @return string Lowercase search string.
         */
        public static function strtolower(string $text): string { return strtolower($text); }
    }
}

namespace {
    $site = sys_get_temp_dir() . '/sm_admin_selector_' . bin2hex(random_bytes(6));
    $root = $site . '/images/com_sportsmanagement/database';
    mkdir($root . '/persons', 0777, true);
    mkdir($site . '/private', 0777, true);

    \define('_JEXEC', 1);
    \define('JPATH_SITE', $site);

    file_put_contents($root . '/persons/regular.jpg', 'test image');
    file_put_contents($root . '/persons/script.php', '<?php exit;');
    file_put_contents($site . '/private/secret.png', 'private file');
    $links = function_exists('symlink')
        && @symlink($site . '/private', $root . '/escape')
        && @symlink($site . '/private/secret.png', $root . '/persons/private.png');

    try {
        require_once \dirname(__DIR__, 2) . '/admin/src/Model/ImagehandlerModel.php';

        $type = \Diddipoeler\Component\SportsManagement\Administrator\Model\ImagehandlerModel::class;
        $model = (new \ReflectionClass($type))->newInstanceWithoutConstructor();
        $normalise = new \ReflectionMethod($model, 'normaliseImageName');
        $positive = new \ReflectionMethod($model, 'positiveRecordId');

        foreach ([
            ['images/com_sportsmanagement/database/clubs/large/Club Image.JPG', 'Club Image.JPG'],
            ['regular.png', 'regular.png'], ['foo.php', null], ['image.svg', null],
            ['.env.png', null], ['bad.png?x=1', null], ["bad\rname.jpg", null],
            ['x.png#evil', null], ['bad.css', null],
        ] as [$image, $expected]) {
            if ($normalise->invoke($model, $image) !== $expected) {
                throw new \RuntimeException('Backend image selection accepted a dangerous filename.');
            }
        }

        foreach ([
            ['0007', 7], [7, 7], [' 7 ', 0], ['7junk', 0], [true, 0],
            [['7'], 0], [7.5, 0], ['99999999999999999999999999', 0],
        ] as [$raw, $expected]) {
            if ($positive->invoke(null, $raw) !== $expected) {
                throw new \RuntimeException('Backend image selector coerced an invalid record ID.');
            }
        }

        $db = new class {
            public int $writes = 0;
            public bool $fail = false;
            public ?object $latestRow = null;

            /**
             * Update a selected person image, or simulate a private SQL error.
             *
             * @param string $table Component-controlled target table.
             * @param object $row Row with selected image path.
             * @param string $key Primary key.
             * @return void
             */
            public function updateObject(string $table, object $row, string $key): void {
                ++$this->writes;
                $this->latestRow = $row;
                if ($this->fail) {
                    throw new \RuntimeException('SQLSTATE username=root password=PRIVATE_IMAGE_KEY');
                }
            }
        };
        \Joomla\CMS\MVC\Model\BaseDatabaseModel::$db = $db;
        $app = new \Joomla\CMS\Application\AdministratorApplication();
        \Joomla\CMS\Factory::$app = $app;

        $operations = [
            'saveimageclub' => ['club_id' => '7', 'picture' => 'regular.png'],
            'saveimageplayer' => ['player_id' => '7', 'picture' => 'regular.png'],
            'saveimageteamplayer' => ['teamplayer_id' => '7', 'picture' => 'regular.png'],
        ];

        foreach ($operations as $method => $posted) {
            if ($model->{$method}($posted) !== 'JERROR_ALERTNOAUTHOR') {
                throw new \RuntimeException('Unprotected image model operation: ' . $method);
            }
        }

        if ($db->writes !== 0) {
            throw new \RuntimeException('Anonymous image model call updated an image field.');
        }

        $app->identity = new class {
            public int $id = 19;
            public bool $edit = true;

            /**
             * Simulate a Joomla component edit permission.
             *
             * @param string $action Requested ACL action.
             * @param string $asset Component asset.
             * @return bool Editor is allowed when the edit flag is set.
             */
            public function authorise(string $action, string $asset): bool {
                return $asset === 'com_sportsmanagement' && $action === 'core.edit' && $this->edit;
            }
        };

        foreach ([
            ['player_id' => '7junk', 'picture' => 'regular.png'],
            ['player_id' => 7, 'picture' => 'evil.php'],
        ] as $posted) {
            if ($model->saveimageplayer($posted) !== 'COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE') {
                throw new \RuntimeException('Invalid image update was not rejected.');
            }
        }
        if ($db->writes !== 0) {
            throw new \RuntimeException('Invalid image selector executed a write.');
        }

        if ($model->saveimageplayer(['player_id' => '0007', 'picture' => 'regular.png']) !== true
            || $db->writes !== 1 || $db->latestRow->picture
                !== 'images/com_sportsmanagement/database/persons/regular.png') {
            throw new \RuntimeException('Approved administrator image changes were rejected.');
        }

        $db->fail = true;
        if ($model->saveimageplayer(['player_id' => 7, 'picture' => 'regular.png'])
            !== 'JERROR_AN_ERROR_HAS_OCCURRED') {
            throw new \RuntimeException('Image model disclosed a database failure.');
        }
        $entries = \Joomla\CMS\Log\Log::$entries;
        if (count($entries) !== 1 || !str_contains($entries[0][0], 'RuntimeException')
            || str_contains($entries[0][0], 'PRIVATE_IMAGE_KEY')
            || $entries[0][2] !== 'com_sportsmanagement') {
            throw new \RuntimeException('Image model logged private SQL exception content.');
        }

        $model->setState('folder', 'persons');
        $model->setState('search', '');
        $names = array_map(static fn (object $image): string => $image->name, $model->getList());
        if ($names !== ['regular.jpg']) {
            throw new \RuntimeException('Backend image list exposed an external linked file.');
        }

        if ($links) {
            $model->setState('folder', 'escape');
            if ($model->getList() !== []) {
                throw new \RuntimeException('Backend image list traversed a symlinked directory.');
            }
        }

        echo "Joomla 5/6 admin image selection ACL, file bounds and error privacy OK\n";
    } finally {
        foreach ([
            $root . '/escape', $root . '/persons/private.png',
            $root . '/persons/regular.jpg', $root . '/persons/script.php',
            $site . '/private/secret.png',
        ] as $file) {
            if (is_file($file) || is_link($file)) {
                @unlink($file);
            }
        }
        @rmdir($root . '/persons');
        @rmdir($root);
        @rmdir($site . '/images/com_sportsmanagement');
        @rmdir($site . '/images');
        @rmdir($site . '/private');
        @rmdir($site);
    }
}
