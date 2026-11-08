<?php
/**
 * Test Joomla 5/6 packaged random quotes and legacy root TXT file fallback.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store fake module settings for the standalone Joomla regression.
         *
         * @param array $values Module parameter values used by the reader.
         */
        public function __construct(private array $values = []) {}

        /**
         * Read one saved module option, returning its default if missing.
         *
         * @param string $name Parameter key.
         * @param mixed $default Default for absent keys.
         * @return mixed Stored option or its default.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

    $root = dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes';
    $packaged = $root . '/mod_sportsmanagement_rquotes';
    $filename = 'sm-audit-' . bin2hex(random_bytes(6)) . '.txt';
    $packagedPath = $packaged . '/' . $filename;
    $legacyPath = $root . '/' . $filename;
    $app = new class implements \Joomla\CMS\Application\CMSApplicationInterface {};
    $helper = new \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper();
    $read = new \ReflectionMethod($helper, 'textLine');
    $config = new \Joomla\Registry\Registry(['filename' => $filename, 'randomtext' => 0]);

    if (!is_file($packaged . '/rquotes.txt')) {
        throw new \RuntimeException('The module must package its default rquotes.txt in the text folder.');
    }

    try {
        file_put_contents($packagedPath, 'packaged quote');
        file_put_contents($legacyPath, 'legacy quote');

        if ($read->invoke($helper, $config, $app) !== 'packaged quote') {
            throw new \RuntimeException('Packaged quote text was not preferred over a legacy root file.');
        }

        unlink($packagedPath);

        if ($read->invoke($helper, $config, $app) !== 'legacy quote') {
            throw new \RuntimeException('Existing root-level custom quote files stopped working.');
        }

        foreach (['../secret.txt', 'file.php', 'bad..txt', ['array']] as $unsafe) {
            $bad = new \Joomla\Registry\Registry(['filename' => $unsafe]);
            if ($read->invoke($helper, $bad, $app) !== '') {
                throw new \RuntimeException('An unsafe quote-text filename was accepted.');
            }
        }

        if (function_exists('symlink')) {
            @symlink($legacyPath, $packagedPath);
            if (is_link($packagedPath)) {
                // The valid legacy file may still be used, but the symlink
                // itself must never be opened by the packaged-path lookup.
                if ($read->invoke($helper, $config, $app) !== 'legacy quote') {
                    throw new \RuntimeException('Quote lookup followed an unsafe symlink.');
                }
            }
        }
    } finally {
        if (is_file($packagedPath) || is_link($packagedPath)) {
            unlink($packagedPath);
        }
        if (is_file($legacyPath)) {
            unlink($legacyPath);
        }
    }

    echo "Joomla 5/6 packaged and legacy quote text files passed.\n";
}
