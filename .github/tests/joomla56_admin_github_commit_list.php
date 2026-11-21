<?php
/**
 * Joomla 5/6 administrator GitHub commit list untrusted API regression.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        /**
         * Simulate Joomla date formatting for supplied ISO commit dates.
         *
         * @param string $operation Requested HTML helper.
         * @param string $date Input API timestamp.
         * @param string $format Expected Joomla date format.
         * @return string Test-formatted date value.
         */
        public static function _(string $operation, string $date, string $format): string
        {
            if ($operation !== 'date' || $format !== 'd.m.Y H:i:s') {
                throw new \RuntimeException('Unexpected date formatting request.');
            }

            return 'Formatted: ' . $date;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a test translation key.
         *
         * @param string $key Joomla translation key.
         * @return string Original key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Router {
    final class Route
    {
        /**
         * Provide a static administrator route.
         *
         * @param string $url Internal Joomla route.
         * @return string Original route.
         */
        public static function _(string $url): string
        {
            return $url;
        }
    }
}

namespace {
    \define('_JEXEC', 1);

    $safeSha = str_repeat('a', 40);
    $record = static function (mixed $name, mixed $message, mixed $date, mixed $url, mixed $avatar): object {
        return (object) [
            'commit' => (object) [
                'author' => (object) ['name' => $name, 'date' => $date],
                'message' => $message,
            ],
            'html_url' => $url,
            'author' => (object) ['avatar_url' => $avatar],
        ];
    };

    $commits = [
        $record('Jane & Co.', "Fixed <test>\nNext line", '2026-11-21T10:00:00Z',
            'https://github.com/diddipoeler/sportsmanagement/commit/' . $safeSha,
            'https://avatars.githubusercontent.com/u/12345?v=4'),
        $record(['unexpected'], ['unexpected'], ['unexpected'],
            'https://github.com/diddipoeler/sportsmanagement/commit/' . $safeSha . '?evil=1',
            'https://avatars.githubusercontent.com.evil.test/u/1'),
        $record('<img src=x onerror=alert(1)>', 'Text & extra', 'not-a-date',
            'https://github.com/diddipoeler/sportsmanagement/issues/123',
            'https://user:pass@avatars.githubusercontent.com/u/2'),
        $record('Other', 'No external link', null,
            ['url is nested'], ['avatar is nested']),
        ['not a commit object'],
    ];

    $view = new class($commits) {
        /**
         * @param array $commitlist Untrusted API fixtures.
         */
        public function __construct(public array $commitlist)
        {
        }

        /**
         * Render the real Joomla GitHub commit list template.
         *
         * @return string Buffered administrator markup.
         */
        public function render(): string
        {
            ob_start();
            try {
                include dirname(__DIR__, 2) . '/admin/tmpl/github/default.php';
                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        }
    };

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $markup = $view->render();
    } finally {
        restore_error_handler();
    }

    $expectedCommit = 'https://github.com/diddipoeler/sportsmanagement/commit/' . $safeSha;
    if (substr_count($markup, 'href="' . $expectedCommit . '"') !== 1
        || substr_count($markup, '<img') !== 1
        || !str_contains($markup, 'src="https://avatars.githubusercontent.com/u/12345?v=4"')
        || !str_contains($markup, 'Jane &amp; Co.')
        || !str_contains($markup, 'Fixed &lt;test&gt;')
        || !str_contains($markup, 'Formatted: 2026-11-21T10:00:00Z')
        || !str_contains($markup, '&lt;img src=x onerror=alert(1)&gt;')
        || str_contains($markup, 'evil.test')
        || str_contains($markup, 'evil=1')
        || str_contains($markup, 'user:pass@')
        || str_contains($markup, 'Array')
    ) {
        throw new \RuntimeException('GitHub commit list rendered malformed API fields or unsafe resources.');
    }

    echo "Joomla 5/6 GitHub commit list validates untrusted API fields.\n";
}
