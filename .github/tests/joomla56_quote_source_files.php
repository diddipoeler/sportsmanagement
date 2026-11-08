<?php
/**
 * Verify that the Joomla 5/6 backend selects supported quote text files.
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

namespace {
    $root = sys_get_temp_dir() . '/sportsmanagement-quotes-' . bin2hex(random_bytes(6));
    define('JPATH_SITE', $root);
    define('_JEXEC', 1);
    $directory = $root . '/modules/mod_sportsmanagement_rquotes';

    if (!mkdir($directory, 0700, true)) {
        throw new \RuntimeException('Cannot create temporary quote test directory.');
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Model/SmquotestxtModel.php';

    try {
        foreach (['q10.txt', 'q2.txt', 'notes.TXT', 'script.php',
            'bad..txt', 'quoted name.txt', '.hidden.txt'] as $filename) {
            file_put_contents($directory . '/' . $filename, 'quote');
        }

        if (function_exists('symlink')) {
            @symlink($directory . '/q2.txt', $directory . '/linked.txt');
        }

        $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\SmquotestxtModel();
        $listed = $model->getTXTFiles();

        if ($listed !== ['notes.TXT', 'q2.txt', 'q10.txt']) {
            throw new \RuntimeException('Unsafe or invalid quote sources included: ' . json_encode($listed));
        }
    } finally {
        foreach (new \DirectoryIterator($directory) as $file) {
            if (!$file->isDot()) {
                unlink($file->getPathname());
            }
        }
        rmdir($directory);
        rmdir(dirname($directory));
        rmdir($root);
    }

    echo "Joomla 5/6 quote TXT source filtering passed.\n";
}
