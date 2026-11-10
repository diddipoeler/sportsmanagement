<?php
/**
 * Joomla 5/6 tournament tree JSON and legacy score-output regressions.
 *
 * Exercises the native payload converter and the remaining score compatibility
 * method without requiring Joomla, a database or the bracket JS library.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        /**
         * Return a minimal Joomla configuration registry for debug requests.
         *
         * @param string $component Joomla component extension name.
         * @return object Registry-like object with an always-disabled debug flag.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Return false to avoid emitting public debug information.
                 *
                 * @param string $name Registry value.
                 * @param mixed $default Default for absent settings.
                 * @return bool Disabled flag.
                 */
                public function get(string $name, mixed $default = null): bool
                {
                    return false;
                }
            };
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 400;
        public const INFO = 200;
        public static array $entries = [];

        /**
         * Record internal model diagnostics for exposure checks.
         *
         * @param string $message Confidential driver message or aggregate diagnostic.
         * @param int $level Joomla log severity.
         * @param string $category Log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return the Joomla language key instead of needing translations.
         *
         * @param string $key Error message language key.
         * @return string Deterministic untranslated language key.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementProjectModel
    {
        public static ?object $siteApp = null;

        /**
         * Supply a simulated Joomla frontend application for legacy errors.
         *
         * @return object Application with enqueueMessage() support.
         */
        protected function siteApplication(): object
        {
            return self::$siteApp;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/TournamentTreePayload.php';
    require_once $root . '/site/src/Model/JltournamenttreeModel.php';

    $builder = \Diddipoeler\Component\SportsManagement\Site\Helper\TournamentTreePayload::class;
    $rounds = [
        (object) ['roundcode' => 20, 'name' => 'Final <script>'],
        (object) ['roundcode' => 10, 'name' => 'Semifinal "quotes"'],
        (object) ['roundcode' => 20, 'name' => 'Final <script>'],
        (object) ['roundcode' => 0, 'name' => 'Ignored'],
        ['roundcode' => 5, 'name' => 'Not an object'],
    ];

    $bracket = [
        10 => [
            (object) [
                'firstname' => '<img src=x onerror=alert(1)>',
                'secondname' => 'FC Köln',
                'team1_result' => '03',
                'team2_result' => '1</script>',
            ],
            (object) [
                'firstname' => 'Rivers & Lakes',
                'secondname' => 'München',
                'team1_result' => 2,
                'team2_result' => false,
            ],
            'not-a-match',
        ],
        20 => [
            (object) ['team1_result' => 4, 'team2_result' => '5'],
        ],
    ];

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        $payload = $builder::build($rounds, $bracket);

        if (count($payload['rounds']) !== 2
            || ($payload['rounds'][0]['roundname'] ?? null) !== 'Semifinal "quotes"'
            || count($payload['teams']) !== 2
            || $payload['teams'][0][0] !== '<img src=x onerror=alert(1)>'
            || $payload['teams'][0][1] !== 'FC Köln'
            || $payload['results'] !== [
                [[3, null], [2, null]],
                [[4, 5]],
            ]) {
            throw new \RuntimeException('Native tournament rounds, teams or scores were not normalized.');
        }

        $json = json_encode(
            $payload,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
                | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
        );

        if (str_contains($json, '<script') || str_contains($json, '<img')
            || !str_contains($json, '\\u003Cimg')) {
            throw new \RuntimeException('Tournament names could escape a JSON script data element.');
        }

        foreach ([
            [null, 'null'],
            ['', 'null'],
            [true, 'null'],
            [[], 'null'],
            ['8;alert(1)', 'null'],
            ['1e3', 'null'],
            [1.5, 'null'],
            [-5, 'null'],
            ['0002', '2'],
            [9, '9'],
        ] as [$raw, $expected]) {
            $model = (new \ReflectionClass(
                \Diddipoeler\Component\SportsManagement\Site\Model\JltournamenttreeModel::class
            ))->newInstanceWithoutConstructor();
            $method = new \ReflectionMethod($model, 'safeScoreLiteral');

            if ($method->invoke(null, $raw) !== $expected) {
                throw new \RuntimeException('Legacy bracket accepted an executable result score.');
            }
        }

        $model->bracket = [
            10 => [(object) ['team1_result' => '1;alert(1)', 'team2_result' => 2]],
        ];
        $fragment = $model->getTournamentResults([(object) ['roundcode' => 10]]);

        if ($fragment !== '[[null,2]]') {
            throw new \RuntimeException('Legacy bracket score fragment was not made numeric-only.');
        }

        $app = new class {
            public array $messages = [];

            /**
             * Record public Joomla messages for confidential-error assertions.
             *
             * @param string $message Translated visitor-facing feedback.
             * @param string $type Joomla message type.
             * @return void
             */
            public function enqueueMessage(string $message, string $type = 'message'): void
            {
                $this->messages[] = [$message, $type];
            }
        };

        \Diddipoeler\Component\SportsManagement\Site\Model\SportsManagementProjectModel::$siteApp = $app;
        $report = new \ReflectionMethod($model, 'reportDatabaseError');
        $report->invoke($model, new \RuntimeException('database-password=secret-sports'));

        if ($app->messages !== [['JERROR_AN_ERROR_HAS_OCCURRED', 'error']]
            || count(\Joomla\CMS\Log\Log::$entries) !== 1
            || !str_contains(\Joomla\CMS\Log\Log::$entries[0][0], 'database-password=secret-sports')) {
            throw new \RuntimeException('Tournament model leaked a database error to frontend visitors.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 native tournament JSON and private legacy diagnostics OK\n";
}
