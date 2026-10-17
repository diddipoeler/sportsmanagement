<?php
/**
 * Joomla 5/6 secure single-source ZIP staging for administrator imports.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const NOTICE = 200;
        public const WARNING = 300;
        public static function add(string $message, int $severity, string $category): void {}
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        protected object $app;
        protected object $input;
        public function __construct($config = [], $factory = null, $app = null, $input = null) {}
        public function setTestApp(object $app): void {
            $this->app = $app;
            $this->input = $app->getInput();
        }
        protected function registerTask(string $name, string $handler): void {}
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {}
        protected function checkToken(): void {}
    }
}
namespace Joomla\Filesystem {
    class File {
        public static array $uploads = [];
        public static function makeSafe(string $name): string { return basename($name); }
        public static function getExt(string $name): string { return pathinfo($name, PATHINFO_EXTENSION); }
        public static function exists(string $path): bool { return is_file($path); }
        public static function delete(string $path): bool {
            return !file_exists($path) || unlink($path);
        }
        public static function upload(string $from, string $to): bool {
            self::$uploads[] = $to;
            return copy($from, $to);
        }
    }
}
namespace Joomla\Http {
    class HttpFactory {}
}
namespace Joomla\Registry {
    class Registry {}
}
namespace {
    define('_JEXEC', 1);
    if (!class_exists(\ZipArchive::class)) {
        throw new \RuntimeException('zip PHP extension is required by importer ZIP regression.');
    }

    $testBase = sys_get_temp_dir() . '/jsm-archive-guard-' . bin2hex(random_bytes(7));
    if (!mkdir($testBase . '/tmp', 0700, true)) {
        throw new \RuntimeException('Cannot create ZIP regression fixture.');
    }
    define('JPATH_SITE', $testBase);

    register_shutdown_function(static function () use ($testBase): void {
        foreach (glob($testBase . '/tmp/*') ?: [] as $file) {
            if (is_file($file) || is_link($file)) {
                @unlink($file);
            }
        }
        @rmdir($testBase . '/tmp');
        @rmdir($testBase);
    });

    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Service/LegacyImportZipService.php';

    foreach ([
        'JlextdbbimportController',
        'JlextdfbnetplayerimportController',
        'JlextlmoimportsController',
        'JlextprofleagimportController',
        'JlxmlimportController',
    ] as $controllerName) {
        require_once $root . '/admin/src/Controller/' . $controllerName . '.php';
    }

    $makeZip = static function (array $entries) use ($testBase): string {
        $path = $testBase . '/tmp/test-' . bin2hex(random_bytes(8)) . '.zip';
        $archive = new \ZipArchive();

        if ($archive->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Cannot create test ZIP.');
        }

        foreach ($entries as $name => $value) {
            if (!$archive->addFromString((string) $name, $value)) {
                throw new \RuntimeException('Cannot add ZIP entry: ' . $name);
            }
        }

        if (!$archive->close()) {
            throw new \RuntimeException('Cannot close test ZIP.');
        }

        return $path;
    };

    $service = \Diddipoeler\Component\SportsManagement\Administrator\Service\LegacyImportZipService::class;
    $target = $testBase . '/tmp/sportsmanagement_import.csv';
    $existing = $testBase . '/tmp/unrelated.csv';
    file_put_contents($existing, 'old unrelated data');

    $good = $makeZip(['readme.txt' => 'metadata', 'actual.csv' => 'this import only']);
    if (!$service::stage($good, $target, ['csv', 'ics'])
        || file_get_contents($target) !== 'this import only'
        || file_get_contents($existing) !== 'old unrelated data') {
        throw new \RuntimeException('ZIP staging selected stale/shared data instead of the current ZIP.');
    }

    foreach ([
        ['actual.csv' => 'one', 'second.ics' => 'two'],
        ['nested/data.csv' => 'no recursive match'],
        ['notes.txt' => 'no acceptable extension'],
        ['actual.csv' => ''],
        ['../outside.csv' => 'malicious traversal'],
        ['/absolute.csv' => 'absolute path'],
        ['data.csv' => str_repeat('x', 25 * 1024 * 1024 + 1)],
        array_fill_keys(array_map(static fn(int $i): string => 'file-' . $i . '.txt', range(1, 101)), 'x'),
    ] as $entries) {
        $archive = $makeZip($entries);
        if ($service::stage($archive, $target, ['csv', 'ics']) !== false
            || file_get_contents($target) !== 'this import only') {
            throw new \RuntimeException('Invalid, oversized or ambiguous ZIP altered staged import.');
        }
    }

    $symlink = $testBase . '/tmp/sportsmanagement_import_link.csv';
    if (function_exists('symlink') && @symlink($target, $symlink)) {
        $archive = $makeZip(['actual.csv' => 'wrong']);
        if ($service::stage($archive, $symlink, ['csv']) !== false
            || file_get_contents($target) !== 'this import only') {
            throw new \RuntimeException('ZIP staging wrote through a target symlink.');
        }
    }

    $app = new class {
        public array $messages = [];
        public function getInput(): object { return new \stdClass(); }
        public function enqueueMessage(string $message, string $type): void {
            $this->messages[] = [$message, $type];
        }
    };

    $controllers = [
        [\Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdbbimportController::class, 'csv', 'import.csv', ['csv', 'ics']],
        [\Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbnetplayerimportController::class, 'csv', 'import.ics', ['csv', 'ics']],
        [\Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextlmoimportsController::class, 'l98', 'import.l98', ['l98']],
        [\Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextprofleagimportController::class, 'xml', 'import.xml', ['xml']],
        [\Diddipoeler\Component\SportsManagement\Administrator\Controller\JlxmlimportController::class, 'jlg', 'import.jlg', ['jlg', 'xml']],
    ];
    foreach ($controllers as [$controllerType, $ext, $name, $allowed]) {
        $controller = new $controllerType();
        $controller->setTestApp($app);
        $prepare = new \ReflectionMethod($controllerType, 'prepareImportFile');
        $source = tempnam($testBase . '/tmp', 'uploaded-');
        file_put_contents($source, 'raw ' . $ext);
        $upload = [
            'name' => $name,
            'tmp_name' => $source,
            'error' => UPLOAD_ERR_OK,
        ];
        $target = $testBase . '/tmp/sportsmanagement_import.' . $ext;
        $args = str_contains($controllerType, 'profleagimport')
            ? [$upload, $target]
            : (str_contains($controllerType, 'dfbnetplayerimport')
                ? [$upload, 'com_sportsmanagement']
                : [$upload]);

        if ($prepare->invokeArgs($controller, $args) !== true
            || file_get_contents($target) !== 'raw ' . $ext) {
            throw new \RuntimeException($controllerType . ' broke direct import staging.');
        }
        if (!str_contains((string) end(\Joomla\Filesystem\File::$uploads), '/jsm-upload-')
            || file_exists($testBase . '/tmp/' . $name)) {
            throw new \RuntimeException('Controller used an attacker-controlled upload destination.');
        }

        // Error codes and invalid extensions must not erase valid staging.
        foreach ([
            ['error' => UPLOAD_ERR_PARTIAL],
            ['name' => 'malicious.exe'],
            ['name' => $name, 'tmp_name' => '/file/not/available'],
        ] as $invalid) {
            if ($prepare->invokeArgs($controller, array_replace($args, [0 => array_replace($upload, $invalid)])) !== false
                || file_get_contents($target) !== 'raw ' . $ext) {
                throw new \RuntimeException($controllerType . ' accepted a broken upload.');
            }
        }

        $archive = $makeZip(['fresh.' . $ext => 'archive payload']);
        $zipUpload = ['name' => 'test.zip', 'tmp_name' => $archive, 'error' => UPLOAD_ERR_OK];
        $zipArgs = $args;
        $zipArgs[0] = $zipUpload;

        if ($prepare->invokeArgs($controller, $zipArgs) !== true
            || file_get_contents($target) !== 'archive payload') {
            throw new \RuntimeException($controllerType . ' failed ZIP import staging.');
        }

        $badZip = $makeZip(['a.' . $ext => 'first', 'b.' . $ext => 'second']);
        $zipArgs[0]['tmp_name'] = $badZip;

        if ($prepare->invokeArgs($controller, $zipArgs) !== false
            || file_get_contents($target) !== 'archive payload') {
            throw new \RuntimeException($controllerType . ' accepted ambiguous ZIP entries.');
        }

        @unlink($source);
    }

    echo "Joomla 5/6 single-source ZIP archive and upload staging guards OK\n";
}
