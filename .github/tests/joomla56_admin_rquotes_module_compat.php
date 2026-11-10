<?php
/**
 * Joomla 5/6 administrator quote picker and frontend rotation compatibility.
 *
 * Exercises the native administrator file chooser and random quote module
 * timezone conversion without starting a full Joomla application.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel
    {
    }
}

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace {
    $root = sys_get_temp_dir() . '/jsm56-quote-picker-' . bin2hex(random_bytes(6));
    $directory = $root . '/modules/mod_sportsmanagement_rquotes/mod_sportsmanagement_rquotes';
    $moduleRoot = dirname($directory);
    $outside = $root . '/outside.txt';

    define('JPATH_SITE', $root);
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Model/SmquotestxtModel.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

    if (!mkdir($directory, 0700, true)) {
        throw new \RuntimeException('Cannot create isolated Joomla quote module folder.');
    }

    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotestxtModel();
    $helper = new \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper();
    $now = new \ReflectionMethod($helper, 'now');

    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public mixed $offset = 'UTC';

        /**
         * Return the simulated legacy Joomla application timezone setting.
         *
         * @param string $key Configuration key being requested.
         * @param mixed $default Value when the option is not present.
         * @return mixed Raw configured timezone, including malformed values.
         */
        public function get(string $key, mixed $default = null): mixed
        {
            return $key === 'offset' ? $this->offset : $default;
        }
    };

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach (['quotes2.txt', 'quotes10.txt', 'a.txt', 'file.php', '.hidden.txt', 'bad..txt'] as $file) {
            file_put_contents($directory . '/' . $file, 'sample');
        }
        file_put_contents($outside, 'private file');

        if (function_exists('symlink')) {
            @symlink($outside, $directory . '/outside.txt');
        }

        if ($model->getTXTFiles() !== ['a.txt', 'quotes2.txt', 'quotes10.txt']) {
            throw new \RuntimeException('Administrator quote picker exposed unsafe files or lost natural sorting.');
        }

        // A missing module folder is optional and should never abort form rendering.
        $alias = $moduleRoot . '/mod_sportsmanagement_rquotes';
        rename($directory, $alias . '-offline');

        if ($model->getTXTFiles() !== []) {
            throw new \RuntimeException('Missing quote module directory was not handled safely.');
        }

        if (function_exists('symlink')) {
            symlink($alias . '-offline', $alias);

            if ($model->getTXTFiles() !== []) {
                throw new \RuntimeException('Administrator followed a symbolic link for the quote file list.');
            }
            unlink($alias);
        }

        rename($alias . '-offline', $directory);

        // Old module settings were sometimes saved as arrays by custom forms.
        // The native helper must never cast those into a DateTimeZone name.
        foreach ([
            ['Europe/Berlin', 'Europe/Berlin'],
            ['  Europe/Berlin  ', 'Europe/Berlin'],
            ['+02:00', '+02:00'],
            ['UTC', 'UTC'],
            [[], 'UTC'],
            [new \stdClass(), 'UTC'],
            [false, 'UTC'],
            [null, 'UTC'],
            ['Invalid/TimeZone', 'UTC'],
            ["Europe/Berlin\0bad", 'UTC'],
            [str_repeat('X', 129), 'UTC'],
        ] as [$configured, $expectedZone]) {
            $app->offset = $configured;
            $date = $now->invoke($helper, $app);

            if (!$date instanceof \DateTimeImmutable
                || $date->getTimezone()->getName() !== $expectedZone) {
                throw new \RuntimeException('Random quotes accepted a malformed timezone setting.');
            }
        }
    } finally {
        restore_error_handler();

        if (is_link($directory . '/outside.txt')) {
            unlink($directory . '/outside.txt');
        }

        foreach (['quotes2.txt', 'quotes10.txt', 'a.txt', 'file.php', '.hidden.txt', 'bad..txt'] as $file) {
            @unlink($directory . '/' . $file);
        }
        @unlink($outside);
        @rmdir($directory);
        @rmdir($moduleRoot);
        @rmdir(dirname($moduleRoot));
        @rmdir($root . '/modules');
        @rmdir($root);
    }

    echo "Joomla 5/6 administrator quote picker and module timezone passed.\n";
}
