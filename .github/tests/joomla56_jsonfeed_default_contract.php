<?php
/**
 * Verify safe default calendar JSON output through Joomla 5/6 site services.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementSiteApplicationResolver
    {
        /**
         * Provide Joomla's document and filtered frontend request stub.
         *
         * @return object Application-like object for default feed rendering.
         */
        public static function resolve(): object
        {
            return new class {
                /** Return a document accepting the JSON MIME type. */
                public function getDocument(): object
                {
                    return new class {
                        /** Record the MIME type requested by the JSON template. */
                        public function setMimeEncoding(string $type): void
                        {
                            if ($type !== 'application/json') {
                                throw new \RuntimeException('Calendar feed used an invalid MIME type.');
                            }
                        }
                    };
                }

                /** Provide filtered Joomla input for menu-item routing. */
                public function getInput(): object
                {
                    return new class {
                        /** Return the selected Joomla menu item ID. */
                        public function getInt(string $key, int $default = 0): int
                        {
                            return $key === 'Itemid' ? 99 : $default;
                        }
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
         * Preserve URLs so the test can verify encoded event identifiers.
         *
         * @param string $url Calendar event route.
         * @param bool $xhtml Whether XHTML escaping is requested.
         * @return string Raw route for comparison.
         */
        public static function _(string $url, bool $xhtml = true): string
        {
            return $url;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    $view = new class {
        public array $events = [];
        public int $compactMode = 0;

        /**
         * Render the actual site calendar feed and decode its JSON response.
         *
         * @param string $file Absolute path to the native layout.
         * @return array Parsed events from the frontend feed.
         */
        public function render(string $file): array
        {
            ob_start();
            try {
                include $file;
                $json = (string) ob_get_clean();
            } catch (\Throwable $exception) {
                ob_end_clean();
                throw $exception;
            }

            if (str_contains($json, '<script>')) {
                throw new \RuntimeException('Unsafe markup leaked to JSON output.');
            }

            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        }
    };

    $view->events = [
        [
            'id' => 'a&b', 'gcid' => 5, 'start' => '2026-11-08',
            'title' => '<script>alert(1)</script>',
            'description' => 'Test <img onerror=alert(1)>',
            'location' => 'North & South',
            'allDay' => true, 'color' => 'not-a-color',
        ],
        ['id' => 'skip', 'gcid' => 0, 'start' => '2026-11-08'],
    ];
    $data = $view->render(dirname(__DIR__, 2) . '/site/tmpl/jsonfeed/default.php');

    if (count($data) !== 1
        || ($data[0]['id'] ?? null) !== 'a&b'
        || ($data[0]['title'] ?? null) !== '<script>alert(1)</script>'
        || !str_contains((string) ($data[0]['description'] ?? ''), '&lt;img')
        || !str_contains((string) ($data[0]['description'] ?? ''), 'North &amp; South')
        || !str_contains((string) ($data[0]['url'] ?? ''), 'eventID=a%26b')
        || !str_contains((string) ($data[0]['url'] ?? ''), '&Itemid=99')
        || empty($data[0]['allDay'])) {
        throw new \RuntimeException('Default JSON feed safety or event routing contract regressed.');
    }

    echo "Joomla 5/6 default JSON calendar feed passed.\n";
}
