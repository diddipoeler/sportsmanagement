<?php
/**
 * Joomla 5/6 compact calendar JSON feed bounds and ID regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver
    {
        /**
         * Provide a minimal Joomla site application for the JSON template.
         *
         * The dummy application exposes only the document MIME-type setter.
         *
         * @return object Site application stand-in.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Return a document stub used to verify JSON rendering.
                 *
                 * @return object Document accepting the JSON MIME type.
                 */
                public function getDocument(): object
                {
                    return new class {
                        /**
                         * Accept the MIME type selected by the native layout.
                         *
                         * @param string $type MIME type for calendar data.
                         * @return void
                         */
                        public function setMimeEncoding(string $type): void {}
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Router {
    class Route
    {
        /**
         * Return a synthetic Joomla route without performing routing.
         *
         * @param string $route Raw component URL.
         * @param bool $xhtml Whether Joomla would escape ampersands.
         * @return string Unchanged URL for assertions.
         */
        public static function _(string $route, bool $xhtml = true): string
        {
            return $route;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    $view = new class {
        public array $events = [];

        /**
         * Execute the real compact calendar template and decode its JSON.
         *
         * Captures generated output and ensures any rendering error is
         * rethrown after restoring output buffering.
         *
         * @param string $path Path to the module JSON template.
         * @return array Decoded events grouped by calendar day.
         */
        public function render(string $path): array
        {
            ob_start();
            try {
                include $path;
                $json = (string) ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }

            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }
    };

    $view->events = [
        [
            'id' => 'long', 'start' => '2026-01-01', 'end' => '2036-01-01',
            'gcid' => 9, 'title' => 'Meeting <script>alert(1)</script>',
            'allDay' => true, 'color' => '#135CAE',
        ],
        [
            'id' => 'invalid-id', 'start' => '2026-01-01', 'end' => '2026-01-02',
            'gcid' => -7, 'title' => 'Invalid calendar', 'allDay' => true,
            'color' => '#135CAE',
        ],
        ['id' => 'bad-date', 'start' => 'not-a-date', 'end' => '2026-01-02', 'gcid' => 2],
    ];

    $data = $view->render(dirname(__DIR__, 2) . '/site/tmpl/jsonfeed/module.php');

    if (count($data) !== 370) {
        throw new \RuntimeException('Long-running calendar event expanded without a 370-day bound.');
    }

    $last = (new \DateTimeImmutable('2026-01-01'))->modify('+369 days')->format('Y-m-d');
    if (($data[0]['id'] ?? null) !== '2026-01-01'
        || ($data[369]['id'] ?? null) !== $last) {
        throw new \RuntimeException('Calendar JSON day ordering or inclusive range changed.');
    }

    $firstUrl = $data[0]['url'] ?? '';
    if (!str_contains($firstUrl, '&gcids=9')
        || str_contains($firstUrl, '-7')
        || !str_contains($data[0]['description'] ?? '', '&lt;script&gt;')
        || str_contains($data[0]['description'] ?? '', '<script>')) {
        throw new \RuntimeException('Compact calendar exposed invalid IDs or unsafe event titles.');
    }

    echo "Joomla 5/6 compact JSON calendar range and ID checks OK\n";
}
