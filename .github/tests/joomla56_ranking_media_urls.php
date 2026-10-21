<?php
/**
 * Joomla 5/6 Ranking module logo and flag URL security regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://site.example.test/joomla/';
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_ranking/src/Helper/RankingHelper.php';

    $helper = new \Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper();
    $mediaUrl = new \ReflectionMethod($helper, 'mediaUrl');

    foreach ([
        ['images/club logo.png', 'https://site.example.test/joomla/images/club%20logo.png'],
        ['/images/logo.png', 'https://site.example.test/joomla/images/logo.png'],
        ['https://cdn.example.test/logo.png', 'https://cdn.example.test/logo.png'],
        ['http://cdn.example.test/logo.png', 'http://cdn.example.test/logo.png'],
        ['', ''],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,<svg>', ''],
        ['//cdn.example.test/logo.png', ''],
        ['file:///etc/passwd', ''],
        ['images/../secret.png', ''],
        ['images/./logo.png', ''],
        ['images/%2e%2e/secret.png', ''],
        ['images\wrong.png', ''],
        ['https://user:secret@cdn.example.test/logo.png', ''],
        ["https://cdn.example.test/\nlogo.png", ''],
        ['images/logo.png?redirect=evil', ''],
    ] as [$raw, $expected]) {
        if ($mediaUrl->invoke($helper, $raw) !== $expected) {
            throw new \RuntimeException('Ranking helper accepted unsafe image URL: ' . json_encode($raw));
        }
    }

    echo "Joomla 5/6 Ranking media URL regressions passed\n";
}
