<?php
/**
 * Guard Joomla 5/6 event IDs, native MVC view resolution and page titles.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class GoogleCalendarReadService
    {
        public static array $requests = [];
        public static ?array $event = ['title' => 'Calendar event'];

        /**
         * Stand in for the Joomla-injected read service.
         *
         * @param object $db Joomla database fixture.
         * @param object $app Joomla site application fixture.
         */
        public function __construct(object $db, object $app) {}

        /**
         * Record the only calendar IDs permitted to reach the service.
         *
         * @param int $calendarId Selected calendar row.
         * @param string $eventId Opaque validated event identifier.
         * @return array|null Configured fixture event.
         */
        public function getEvent(int $calendarId, string $eventId): ?array
        {
            self::$requests[] = [$calendarId, $eventId];
            return self::$event;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel
    {
        public static ?object $application = null;
        public static array $state = [];

        /**
         * Provide a stand-in Joomla site application to the event model.
         *
         * @return object Site application fixture.
         */
        protected function siteApplication(): object
        {
            return self::$application;
        }

        /**
         * Provide the mock Joomla database to the calendar read service.
         *
         * @return object Database service fixture.
         */
        protected function getDatabase(): object
        {
            return new \stdClass();
        }

        /**
         * Preserve the populated Joomla model state for later assertions.
         *
         * @param string $key Joomla model state key.
         * @param mixed $value Request-specific state value.
         * @return void
         */
        protected function setState(string $key, mixed $value): void
        {
            self::$state[$key] = $value;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\View {
    class SportsManagementHtmlView
    {
        public ?object $model = null;
        public bool $rendered = false;
        public object $document;

        /**
         * Construct a view with a mutable document title fixture.
         */
        public function __construct()
        {
            $this->document = new class {
                public string $title = '';

                /**
                 * Capture the Joomla document title for the event view.
                 *
                 * @param string $title Sanitised event title.
                 * @return void
                 */
                public function setTitle(string $title): void
                {
                    $this->title = $title;
                }
            };
        }

        /**
         * Return the model attached to the native MVC view.
         *
         * @return object|null Configured event model, or no model.
         */
        public function getModel(): ?object { return $this->model; }

        /**
         * Return the Joomla HTML document stand-in.
         *
         * @return object Fake document with recorded title.
         */
        public function getDocument(): object { return $this->document; }

        /**
         * Record delegation to Joomla's normal site view rendering.
         *
         * @param string|null $tpl Optional view template suffix.
         * @return void
         */
        public function display($tpl = null) { $this->rendered = true; }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Model/EventModel.php';
    require_once dirname(__DIR__, 2) . '/site/src/View/Event/HtmlView.php';

    $input = new class {
        public int $calendarId = 5;
        public mixed $eventId = 'event-x';

        /**
         * Return the selected calendar ID from the Joomla request.
         *
         * @param string $key Request key for the selected calendar.
         * @param int $default Fallback calendar ID.
         * @return int Configured calendar ID.
         */
        public function getInt(string $key, int $default = 0): int
        {
            return $key === 'gcid' ? $this->calendarId : $default;
        }

        /**
         * Return a raw eventID value including malformed request arrays.
         *
         * @param string $key Joomla request parameter.
         * @param mixed $default Default parameter value.
         * @param string $filter Input filtering strategy.
         * @return mixed Raw request value.
         */
        public function get(string $key, mixed $default = '', string $filter = 'raw'): mixed
        {
            return $key === 'eventID' ? $this->eventId : $default;
        }
    };

    $app = new class($input) {
        /**
         * Keep the current Joomla request input for the frontend model.
         *
         * @param object $input Simulated Joomla Input instance.
         */
        public function __construct(private object $input) {}

        /**
         * Return the current frontend request parameters.
         *
         * @return object Request input object.
         */
        public function getInput(): object { return $this->input; }

        /**
         * Return component parameters used to initialise the event view.
         *
         * @return object Empty Joomla component registry stand-in.
         */
        public function getParams(): object { return new \stdClass(); }
    };

    $base = \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementModel::class;
    $service = \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService::class;
    $base::$application = $app;
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\EventModel();
    $viewClass = \Diddipoeler\Component\SportsManagement\Site\View\Event\HtmlView::class;

    foreach ([[0, 'event-x'], [5, ['event-x']], [5, new \stdClass()],
        [5, 123], [5, ''], [5, '   '], [5, "event\nnext"],
        [5, "event\0invalid"], [5, str_repeat('x', 513)]] as [$calendarId, $eventId]) {
        $input->calendarId = $calendarId;
        $input->eventId = $eventId;
        if ($model->getGCalendar() !== null || $service::$requests !== []) {
            throw new \RuntimeException('Invalid site event ID reached Google Calendar.');
        }
    }

    $input->calendarId = 5;
    $input->eventId = 'event-x';
    $invalidView = new $viewClass();
    try {
        $invalidView->display();
        throw new \RuntimeException('Joomla event view rendered with a missing MVC model.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 500) {
            throw $error;
        }
    }

    $view = new $viewClass();
    $view->model = $model;
    $view->display();
    if ($service::$requests !== [[5, 'event-x']]
        || $view->event !== ['title' => 'Calendar event']
        || $view->document->title !== 'Calendar event'
        || !$view->rendered) {
        throw new \RuntimeException('Native Joomla event view dispatch or title regressed.');
    }

    $service::$event = null;
    $emptyView = new $viewClass();
    $emptyView->model = $model;
    $emptyView->display();
    if (!$emptyView->rendered || $emptyView->event !== null || $emptyView->document->title !== '') {
        throw new \RuntimeException('Unavailable calendar events could not render the empty view.');
    }

    echo "Joomla 5/6 event MVC model and raw ID validation passed.\n";
}
