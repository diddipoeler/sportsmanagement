<?php
/**
 * Joomla 5/6 site Event view and template input validation regressions.
 *
 * Run without a Joomla installation, converting PHP warnings into failures.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return a deterministic translation marker for rendered labels.
         * @param string $key Joomla translation key.
         * @return string Same key for predictable assertions.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Router {
    final class Route
    {
        /**
         * Return the Joomla route supplied by the event template.
         * @param string $route Selected component link.
         * @return string Unmodified link for parameter validation.
         */
        public static function _(string $route): string { return $route; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    final class EventModel
    {
        /** @var array<string,mixed>|null */
        public ?array $result = null;

        /**
         * Supply a fake authorised Google Calendar event to the native view.
         * @return array<string,mixed>|null Current fake event.
         */
        public function getGCalendar(): ?array { return $this->result; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementHtmlView
    {
        public mixed $model = null;
        public object $document;
        public object $app;
        public bool $rendered = false;

        /**
         * Provide the minimum Joomla MVC services for isolated template tests.
         * @return void
         */
        public function __construct()
        {
            $this->document = new class {
                public ?string $title = null;
                /**
                 * Record an HTML title supplied by the site event view.
                 * @param string $title New document title.
                 * @return void
                 */
                public function setTitle(string $title): void { $this->title = $title; }

                /**
                 * Return a fake Joomla web asset manager.
                 * @return object Asset registry fixture.
                 */
                public function getWebAssetManager(): object
                {
                    return new class {
                        /**
                         * Accept the event CSS asset registration.
                         * @param string $name Asset name.
                         * @param string $path CSS file path.
                         * @param array $options Asset options.
                         * @return void
                         */
                        public function registerAndUseStyle(string $name, string $path, array $options): void {}
                    };
                }
            };
            $this->app = new class {
                /**
                 * Return intentionally malformed legacy date formats.
                 * @return object Joomla-like component configuration.
                 */
                public function getParams(): object
                {
                    return new class {
                        /**
                         * Return an invalid date setting to check safe fallbacks.
                         * @param string $name Configuration name.
                         * @param mixed $default Default when unavailable.
                         * @return mixed Configured value.
                         */
                        public function get(string $name, mixed $default = null): mixed
                        {
                            return $name === 'description_date_format' ? ['invalid'] : $default;
                        }
                    };
                }

                /**
                 * Simulate an invalid timezone from an older component.
                 * @param string $key Joomla application parameter.
                 * @param mixed $default Default value.
                 * @return mixed Configured value.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return $key === 'offset' ? ['invalid'] : $default;
                }
            };
        }

        /**
         * Return the model attached to the view by this standalone fixture.
         * @return mixed Current MVC model fixture.
         */
        public function getModel(): mixed { return $this->model; }

        /**
         * Return a fake Joomla document and its web asset manager.
         * @return object Document fixture.
         */
        public function getDocument(): object { return $this->document; }

        /**
         * Record that the event view completed rendering.
         * @param string|null $tpl Optional Joomla template selector.
         * @return void
         */
        public function display($tpl = null) { $this->rendered = true; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/View/Event/HtmlView.php';
    $eventView = \Diddipoeler\Component\SportsManagement\Site\View\Event\HtmlView::class;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\EventModel();
    $view = new $eventView();
    $view->model = $model;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $model->result = ['title' => ['bad']];
        $view->display();

        if (!$view->rendered || $view->document->title !== null) {
            throw new \RuntimeException('Invalid event title reached the browser document.');
        }

        $model->result = ['title' => "Sport\nFixture"];
        $view->display();
        if ($view->document->title !== null) {
            throw new \RuntimeException('Control-character title reached the document.');
        }

        $model->result = ['title' => '  League Final  '];
        $view->display();
        if ($view->document->title !== 'League Final') {
            throw new \RuntimeException('Valid event title was not preserved.');
        }

        // The passive layout must never produce PHP 8 array-to-string notices.
        // It must also avoid route slugs derived from invalid numeric IDs.
        $renderer = new class {
            public ?array $event = null;
            public object $app;
            public object $document;

            /**
             * Prepare the test view with already-created Joomla facade services.
             * @return void
             */
            public function __construct()
            {
                $fixture = new \Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView();
                $this->app = $fixture->app;
                $this->document = $fixture->document;
            }

            /**
             * Return the web asset manager for the passive template.
             * @return object Fake document.
             */
            public function getDocument(): object { return $this->document; }

            /**
             * Execute the real event template within a fixture view scope.
             * @return string Captured HTML response.
             */
            public function render(): string
            {
                ob_start();

                try {
                    require dirname(__DIR__, 2) . '/site/tmpl/event/default.php';
                    return (string) ob_get_clean();
                } catch (\Throwable $error) {
                    ob_end_clean();
                    throw $error;
                }
            }
        };

        $renderer->event = [
            'title' => ['invalid'], 'calendarName' => 'Match Calendar',
            'description' => ['nested'], 'location' => new \stdClass(),
            'start' => ['invalid'], 'end' => true, 'allDay' => ['invalid'],
            'gcid' => '10junk', 'color' => ['red'], 'htmlLink' => ['url'],
            'id' => new \stdClass(),
        ];
        $html = $renderer->render();

        if (!str_contains($html, 'Match Calendar')
            || str_contains($html, 'gcids=')
            || str_contains($html, '>Array<')
            || str_contains($html, 'Google Calendar</a>')
            || !str_contains($html, '#135CAE')) {
            throw new \RuntimeException('Malformed Event template data generated invalid markup.');
        }

        $renderer->event = [
            'title' => 'Match & Results', 'calendarName' => 'Club',
            'description' => 'Match info', 'gcid' => '00042',
            'location' => 'Berlin', 'allDay' => true,
            'start' => '2026-11-15', 'end' => '2026-11-16',
            'color' => '#102030', 'htmlLink' => 'https://example.org/match',
        ];
        $html = $renderer->render();

        if (!str_contains($html, 'Match &amp; Results')
            || !str_contains($html, 'gcids=42')
            || !str_contains($html, '15.11.2026')
            || !str_contains($html, 'https://example.org/match')
            || !str_contains($html, '#102030')) {
            throw new \RuntimeException('Valid Event template content was lost.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 event view and passive layout regressions passed.\n";
}
