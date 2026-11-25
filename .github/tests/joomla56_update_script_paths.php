<?php
/**
 * Joomla 5/6 administrator update-script path and raw input regression.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {}
}
namespace {
    define('_JEXEC', 1);
    $root = sys_get_temp_dir() . '/jsm-update-path-' . bin2hex(random_bytes(8));
    define('JPATH_ADMINISTRATOR', $root . '/administrator');
    define('JPATH_SITE', $root . '/site');
    $admin = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/updates';
    $extension = JPATH_SITE . '/components/com_sportsmanagement/extensions/demo/admin/install';
    $outside = $root . '/outside';
    foreach ([$admin, $extension, $outside] as $directory) {
        if (!mkdir($directory, 0700, true)) {
            throw new \RuntimeException('Cannot initialise update path fixture.');
        }
    }

    /**
     * Remove the isolated test tree without following symbolic links.
     * @param string $path File or directory in the temporary fixture.
     * @return void
     */
    function removeUpdateFixture(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            unlink($path);
            return;
        }
        foreach (new \FilesystemIterator($path) as $entry) {
            removeUpdateFixture($entry->getPathname());
        }
        rmdir($path);
    }

    try {
        foreach ([$admin . '/update.php', $extension . '/update.php', $outside . '/other.php'] as $file) {
            file_put_contents($file, '<?php return true;');
        }
        require dirname(__DIR__, 2) . '/admin/src/Controller/UpdateController.php';
        require dirname(__DIR__, 2) . '/admin/src/Model/UpdatesModel.php';
        $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\UpdateController();
        $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\UpdatesModel();
        $normalise = new \ReflectionMethod($controller, 'normaliseFileName');
        $resolve = new \ReflectionMethod($model, 'resolveUpdateFile');

        foreach (['update.php' => $admin . '/update.php', 'demo/update.php' => $extension . '/update.php'] as $name => $path) {
            if ($normalise->invoke($controller, $name) !== $name
                || $resolve->invoke($model, $name) !== realpath($path)
                || $resolve->invoke($model, $name, $path) !== realpath($path)
                || $resolve->invoke($model, $name, $outside . '/other.php') !== null) {
                throw new \RuntimeException('Valid update or legacy path hint resolution regressed.');
            }
        }

        foreach ([null, true, ['update.php'], 7, '', '/update.php', 'demo//update.php',
            'demo/../update.php', '../update.php', "update.php\0", "update.php\n",
            ' update.php', 'demo/update.php/', 'update.txt', 'C:/update.php'] as $invalid) {
            if ($normalise->invoke($controller, $invalid) !== null
                || (is_string($invalid) && $resolve->invoke($model, $invalid) !== null)) {
                throw new \RuntimeException('Malformed update script selector accepted.');
            }
        }

        if ($resolve->invoke($model, 'update.php', "invalid\0") !== null) {
            throw new \RuntimeException('Malformed legacy path hint accepted.');
        }

        if (function_exists('symlink') && @symlink($outside . '/other.php', $admin . '/linked.php')) {
            if ($resolve->invoke($model, 'linked.php') !== null) {
                throw new \RuntimeException('External update script symlink accepted.');
            }
        }
        $linkedExtension = JPATH_SITE . '/components/com_sportsmanagement/extensions/linked';
        if (function_exists('symlink') && @symlink(dirname($extension, 2), $linkedExtension)) {
            if ($resolve->invoke($model, 'linked/update.php') !== null) {
                throw new \RuntimeException('Symlinked extension directory accepted.');
            }
        }
    } finally {
        removeUpdateFixture($root);
    }

    echo "Joomla 5/6 update script path guards passed.\n";
}
