<?php
/**
 * Joomla 5/6 frontend JSON calendar selector precedence regression.
 *
 * An invalid explicitly supplied gcids parameter must never be replaced with
 * the older gcid parameter or unexpectedly trigger an external API call.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class GoogleCalendarReadService
    {
        public static array $calls = [];

        /**
         * Initialize the calendar read service without network dependencies.
         *
         * @param object $db Fake Joomla database service.
         * @param object $app Fake Joomla frontend application.
         */
        public function __construct(object $db, object $app)
        {
        }

        /**
         * Capture the requested calendar IDs and optional time window.
         *
         * @param array<int,int> $ids Normalized calendar identifiers.
         * @param int|null $start Start timestamp.
         * @param int|null $end End timestamp.
         * @return array<int,array<string,mixed>> Minimal response fixture.
         */
        public function getEvents(array $ids, ?int $start, ?int $end): array
        {
            self::$calls[] = ['ids' => $ids, 'start' => $start, 'end' => $end];

            return [['ids' => $ids]];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel
    {
        public static ?object $application = null;
        public static ?object $database = null;

        /**
         * Return the active fake Joomla site application to the native model.
         *
         * @return object Test application with controlled request input.
         */
        protected function siteApplication(): object
        {
            return self::$application;
        }

        /**
         * Return the isolated fake Joomla database dependency.
         *
         * @return object Fake database service.
         */
        protected function getDatabase(): object
        {
            return self::$database;
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Model/JsonfeedModel.php';

    $input = new class {
        public array $values = [];

        /**
         * Return raw frontend input, keeping absent parameters distinct.
         *
         * @param string $key Query parameter.
         * @param mixed $default Default for an absent key.
         * @param string $filter Joomla raw input filter selector.
         * @return mixed Configured request value or default.
         */
        public function get(string $key, mixed $default = null, string $filter = 'raw'): mixed
        {
            return array_key_exists($key, $this->values) ? $this->values[$key] : $default;
        }

        /**
         * Supply date-range bounds without changing calendar selection.
         *
         * @param string $key Time-window input key.
         * @param int $default Default integer.
         * @return int Configured positive integer or the default.
         */
        public function getInt(string $key, int $default = 0): int
        {
            return isset($this->values[$key]) && is_int($this->values[$key])
                ? $this->values[$key]
                : $default;
        }
    };

    $app = new class($input) {
        /**
         * Inject the controlled Joomla input into the application fixture.
         *
         * @param object $input Input provider for calendar selectors.
         */
        public function __construct(private object $input)
        {
        }

        /**
         * Expose the request input expected by the native JSON feed model.
         *
         * @return object Input provider with get/getInt APIs.
         */
        public function getInput(): object
        {
            return $this->input;
        }
    };

    $base = \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementModel::class;
    $base::$application = $app;
    $base::$database = new \stdClass();

    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel();
    $service = \Diddipoeler\Component\SportsManagement\Site\Service\GoogleCalendarReadService::class;

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach ([
            [['gcid' => '7'], [7]],
            [['gcids' => '3,4', 'gcid' => '7'], [3, 4]],
            [['gcids' => [10, 12], 'gcid' => '7'], [10, 12]],
        ] as [$values, $expectedIds]) {
            $input->values = $values;
            $before = count($service::$calls);
            $result = $model->getGoogleCalendarFeeds();

            if (count($service::$calls) !== $before + 1
                || $service::$calls[$before]['ids'] !== $expectedIds
                || $result[0]['ids'] !== $expectedIds) {
                throw new \RuntimeException('Valid modern/legacy JSON calendar selectors were changed.');
            }
        }

        foreach ([
            ['gcids' => '', 'gcid' => '7'],
            ['gcids' => 'abc', 'gcid' => '7'],
            ['gcids' => ['invalid'], 'gcid' => '7'],
            ['gcids' => [], 'gcid' => '7'],
            ['gcids' => new \stdClass(), 'gcid' => '7'],
            ['gcids' => false, 'gcid' => '7'],
            [],
        ] as $values) {
            $input->values = $values;
            $before = count($service::$calls);

            if ($model->getGoogleCalendarFeeds() !== []
                || count($service::$calls) !== $before) {
                throw new \RuntimeException('Invalid explicit gcids selector fell back to gcid.');
            }
        }

        $input->values = ['gcids' => '9', 'start' => 1000, 'end' => 2000];
        $model->getGoogleCalendarFeeds();

        if (end($service::$calls) !== ['ids' => [9], 'start' => 1000, 'end' => 2000]) {
            throw new \RuntimeException('Native calendar feed timestamp forwarding changed.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 JSON calendar selection precedence passed.\n";
}
