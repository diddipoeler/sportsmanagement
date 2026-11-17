<?php
/**
 * Regression checks for raw image control bytes in site and quote module URLs.
 *
 * Run: php .github/tests/joomla56-raw-picture-url-controls.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Uri {
    /** Minimal site root stand-in for standalone URL resolution tests. */
    final class Uri
    {
        /**
         * Return a fixed site URL for asserting valid picture paths.
         *
         * @return string Test installation root.
         */
        public static function root(): string
        {
            return 'https://example.test/';
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Site\Helper\PersonImageHelper;
    use Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/PersonImageHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

    foreach ([
        "images/player.png\n",
        "\timages/player.png",
        "images/player.png\0",
        "https://example.test/picture.jpg\r",
    ] as $rawPicture) {
        if (PersonImageHelper::url($rawPicture) !== '') {
            throw new \RuntimeException('Site accepted raw control bytes in picture URL.');
        }
    }

    if (PersonImageHelper::url('images/player.png') !== 'https://example.test/images/player.png') {
        throw new \RuntimeException('Site rejected safe relative picture URL.');
    }

    foreach ([
        "https://cdn.example.test/images/\n",
        "\thttps://cdn.example.test/images/",
        "https://cdn.example.test/images/\0",
    ] as $remoteBase) {
        if (RquotesHelper::pictureServer(1, $remoteBase, 'https://example.test/') !== '') {
            throw new \RuntimeException('Quote module accepted unsafe remote image server.');
        }
    }

    if (RquotesHelper::pictureServer(1, 'https://cdn.example.test/images', 'https://example.test/')
        !== 'https://cdn.example.test/images/') {
        throw new \RuntimeException('Quote module rejected valid remote image server.');
    }

    if (RquotesHelper::pictureServer(0, null, 'https://example.test/') !== 'https://example.test/') {
        throw new \RuntimeException('Local quote images lost Joomla root fallback.');
    }

    echo "Joomla 5/6 raw picture URL guards passed.\n";
}
