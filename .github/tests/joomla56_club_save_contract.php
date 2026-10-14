<?php
/**
 * Standalone Joomla 5/6 regression tests for frontend club date and save handling.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class AdminModel
    {
        public ?string $error = null;

        protected function getDatabase(): mixed
        {
            return null;
        }

        public function setError($error): void
        {
            $this->error = (string) $error;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    class ClubTable
    {
        public static string $mode = 'ok';
        public static array $bound = [];

        public function __construct(mixed $database)
        {
        }

        public function bind($data): bool
        {
            self::$bound = (array) $data;

            return self::$mode !== 'bind';
        }

        public function check(): bool
        {
            return self::$mode !== 'check';
        }

        public function store(): bool
        {
            return self::$mode !== 'store';
        }

        public function getError(): string
        {
            return 'Controlled table failure';
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Model/EditclubModel.php';

    $model = (new ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Site\Model\EditclubModel::class
    ))->newInstanceWithoutConstructor();
    $normalize = new ReflectionMethod($model, 'normaliseFrontendDate');

    $dates = [
        '31-12-2020' => '2020-12-31',
        '31.12.2020' => '2020-12-31',
        '31122020' => '2020-12-31',
        '2020-12-31' => '2020-12-31',
        '31-02-2020' => '',
        '2020-13-01' => '',
    ];

    foreach ($dates as $provided => $expected) {
        $result = $normalize->invoke($model, $provided);

        if ($result !== $expected) {
            throw new RuntimeException("Club date $provided normalized to $result, expected $expected");
        }
    }

    $input = [
        'id' => 25,
        'founded' => '31-12-2020',
        'dissolved' => '2024-06-05',
        'name' => 'Club test',
    ];

    \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$mode = 'ok';

    if ($model->updItem($input) !== true) {
        throw new RuntimeException('Successful club store must return true.');
    }

    $bound = \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound;

    if ($bound['founded'] !== '2020-12-31' || $bound['dissolved'] !== '2024-06-05') {
        throw new RuntimeException('Club fields were not normalized before binding.');
    }

    $originalBound = $bound;

    foreach (['founded', 'dissolved'] as $field) {
        $invalid = $input;
        $invalid[$field] = '31-02-2020';

        if ($model->updItem($invalid) !== false
            || \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound !== $originalBound
        ) {
            throw new RuntimeException('Invalid club ' . $field . ' date must not be persisted or silently cleared.');
        }
    }

    $nested = $input;
    $nested['request'] = ['founded' => '31-02-2020'];

    if ($model->updItem($nested) !== false
        || \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound !== $originalBound
    ) {
        throw new RuntimeException('Nested request must not bypass club date validation.');
    }

    $cleared = $input;
    $cleared['founded'] = '';
    $cleared['dissolved'] = '';
    $cleared['founded_year'] = 2020;
    $cleared['dissolved_year'] = 2024;
    $cleared['founded_timestamp'] = 1609372800;
    $cleared['dissolved_timestamp'] = 1717545600;
    $cleared['request'] = ['id' => 99, 'name' => 'Updated'];

    if ($model->updItem($cleared) !== true) {
        throw new RuntimeException('Clearing club dates must remain possible.');
    }

    $clearedBound = \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound;

    if ($clearedBound['id'] !== 25
        || $clearedBound['name'] !== 'Updated'
        || $clearedBound['founded'] !== '0000-00-00'
        || $clearedBound['dissolved'] !== '0000-00-00'
        || $clearedBound['founded_year'] !== 'kein'
        || $clearedBound['dissolved_year'] !== 0
        || $clearedBound['founded_timestamp'] !== 0
        || $clearedBound['dissolved_timestamp'] !== 0
        || array_key_exists('request', $clearedBound)
    ) {
        throw new RuntimeException('Club cleared dates or record identity were not preserved.');
    }

    // Joomla 5/6 jform fields override legacy request fields, never the
    // top-level record ID. A partial edit must leave both dates untouched.
    if (!$model->updItem([
        'id' => '0025',
        'name' => 'Old name',
        'request' => ['id' => 80, 'name' => 'Legacy', 'published' => 0],
        'jform' => ['id' => 99, 'name' => 'Native', 'picture' => '', 'active' => false],
    ])) {
        throw new RuntimeException('Native jform club partial update failed.');
    }

    $partial = \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound;

    if ($partial['id'] !== 25
        || $partial['name'] !== 'Native'
        || $partial['picture'] !== ''
        || $partial['active'] !== false
        || $partial['published'] !== 0
        || array_key_exists('founded', $partial)
        || array_key_exists('dissolved', $partial)
        || array_key_exists('jform', $partial)
    ) {
        throw new RuntimeException('Club partial update cleared dates or accepted nested IDs.');
    }

    foreach ([0, -4, true, '25garbage', '999999999999999999999999', []] as $invalidId) {
        if ($model->updItem(['id' => $invalidId, 'name' => 'Bad ID']) !== false
            || \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound !== $partial) {
            throw new RuntimeException('Invalid club record ID reached the Joomla table.');
        }
    }

    foreach ([
        ['id' => 25, 'jform' => ['founded' => '2025-02-29']],
        ['id' => 25, 'jform' => ['dissolved' => '31-02-2026']],
        ['id' => 25, 'jform' => ['founded' => ['2025-01-01']]],
    ] as $badDate) {
        if ($model->updItem($badDate) !== false
            || \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$bound !== $partial) {
            throw new RuntimeException('Invalid nested club date was not rejected.');
        }
    }

    foreach (['bind', 'check', 'store'] as $mode) {
        \Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable::$mode = $mode;

        if ($model->updItem($input) !== false) {
            throw new RuntimeException("Club $mode failure must return false.");
        }
    }

    $controller = file_get_contents(dirname(__DIR__, 2) . '/site/src/Controller/EditclubController.php');

    if (!is_string($controller)
        || !str_contains($controller, '$saved = $this->editClubModel()->updItem($post);')
        || !str_contains($controller, 'if (!$saved) {')
    ) {
        throw new RuntimeException('Club controller must handle a failed store.');
    }

    echo "Joomla 5/6 frontend club date and save contract: OK\n";
}
