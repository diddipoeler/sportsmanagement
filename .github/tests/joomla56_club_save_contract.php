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
