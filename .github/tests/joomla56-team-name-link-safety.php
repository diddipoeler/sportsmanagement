<?php
/**
 * Standalone Joomla 5/6 team name link safety regression coverage.
 *
 * Run with PHP 8.2–8.4 without a Joomla bootstrap.
 *
 * Usage: php .github/tests/joomla56-team-name-link-safety.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Build a deterministic link for the standalone safety fixture.
         *
         * @param string $url Link destination.
         * @param string $label Escaped team display name.
         * @return string Simulated Joomla link markup.
         */
        public static function link(string $url, string $label): string
        {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>';
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/NamePresentationHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\NamePresentationHelper::class;
    $team = (object) ['name' => 'North & <South>', 'short_name' => 'N&S'];

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach ([
            'index.php?option=com_sportsmanagement&view=teaminfo' =>
                '<a href="index.php?option=com_sportsmanagement&amp;view=teaminfo">North &amp; &lt;South&gt;</a>',
            '/teams/north' => '<a href="/teams/north">North &amp; &lt;South&gt;</a>',
            '/teams/north%20city' => '<a href="/teams/north%20city">North &amp; &lt;South&gt;</a>',
            'https://sports.example/team?id=1&active=1' =>
                '<a href="https://sports.example/team?id=1&amp;active=1">North &amp; &lt;South&gt;</a>',
        ] as $destination => $expected) {
            if ($helper::team($team, 2, $destination) !== $expected) {
                throw new \RuntimeException('Allowed team link changed: ' . $destination);
            }
        }

        foreach ([
            'javascript:alert(1)', 'data:text/html,x', 'vbscript:msgbox(1)',
            '//evil.example/payload', 'https://user:password@host.example/team',
            'https://bad host.example/team', 'https://host.example\\bad',
            "index.php\nondanger=1", ' https://sports.example', '#unexpected', '?unsafe',
            '%6aavascript%3aalert(1)', '%256aavascript%253aalert(1)',
            '%2f%2fevil.example/team', '%252f%252fevil.example/team',
            '%5c%5cevil.example/team', '%0aevil.example/team',
            'javascript%253Aalert(1)',
        ] as $destination) {
            if ($helper::team($team, 2, $destination) !== 'North &amp; &lt;South&gt;') {
                throw new \RuntimeException('Unsafe team destination generated a link: ' . json_encode($destination));
            }
        }

        if ($helper::team($team, 0, 'index.php?view=teaminfo')
            !== '<a href="index.php?view=teaminfo">N&amp;S</a>') {
            throw new \RuntimeException('Valid team short name or link escaped incorrectly.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 team name link safety passed.\n";
}
