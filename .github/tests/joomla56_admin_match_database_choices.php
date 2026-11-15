<?php
/**
 * Joomla 5/6 administrator match copy and calendar database-mode regression.
 *
 * Simulates the request and saved administrator preference without a live
 * Joomla connection; malformed input must never select an external database.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController
    {
        protected object $app;
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\MatchController;

    define('_JEXEC', 1);

    final class MatchSelectorTestInput
    {
        public int $rawReads = 0;

        /**
         * Store raw request inputs, retaining malformed payload types.
         *
         * @param array<string,mixed> $values Simulated Joomla request values.
         */
        public function __construct(public array $values = []) {}

        /**
         * Expose raw submitted selectors without integer coercion.
         *
         * @param string $name Requested input name.
         * @param mixed $default Missing field fallback.
         * @param string $filter Joomla input filter.
         * @return mixed Raw value, or the supplied default.
         */
        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            if ($filter !== 'raw') {
                throw new \RuntimeException('Match database selector was read with a lossy filter.');
            }

            ++$this->rawReads;
            return array_key_exists($name, $this->values) ? $this->values[$name] : $default;
        }
    }

    final class MatchSelectorTestApplication
    {
        /**
         * Provide the request and stored administrator database preference.
         *
         * @param MatchSelectorTestInput $input Request parameters.
         * @param mixed $stored Previous database mode in Joomla user state.
         */
        public function __construct(
            public MatchSelectorTestInput $input,
            public mixed $stored = 0
        ) {}

        /**
         * Return the injected Joomla input object.
         *
         * @return MatchSelectorTestInput Submitted request.
         */
        public function getInput(): MatchSelectorTestInput
        {
            return $this->input;
        }

        /**
         * Read the user's saved database selection when the request omits it.
         *
         * @param string $name User-state key.
         * @param mixed $default Fallback when no saved value exists.
         * @return mixed Stored database mode.
         */
        public function getUserState(string $name, mixed $default = null): mixed
        {
            if ($name !== 'com_sportsmanagement.cfg_which_database') {
                throw new \RuntimeException('Wrong administrator database selection state key.');
            }

            return $this->stored ?? $default;
        }
    }

    /**
     * Require strict equality regardless of PHP assertion configuration.
     *
     * @param mixed $expected Expected database choice or call count.
     * @param mixed $actual Observed parser result.
     * @param string $label Description of the scenario under test.
     * @return void
     */
    function matchSelectorAssert(mixed $expected, mixed $actual, string $label): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $label . ': expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true)
            );
        }
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/MatchController.php';

    $controller = (new \ReflectionClass(MatchController::class))->newInstanceWithoutConstructor();
    $appProperty = new \ReflectionProperty($controller, 'app');
    $selectorMethod = new \ReflectionMethod($controller, 'selectedMatchDatabaseSelector');

    foreach ([
        [0, 0], [1, 1], ['0', 0], ['1', 1],
        ['0000', 0], ['0001', 1],
    ] as [$raw, $expected]) {
        $input = new MatchSelectorTestInput(['cfg_which_database' => $raw]);
        $appProperty->setValue($controller, new MatchSelectorTestApplication($input, 0));

        matchSelectorAssert($expected, $selectorMethod->invoke($controller),
            'Valid match database selector');
        matchSelectorAssert(1, $input->rawReads, 'Match selector was not read raw');
    }

    foreach ([false, true, [], ['1'], (object) ['id' => 1],
        '', 2, '2', 7, '0007', '1junk', '1.0', ' 1', '1 ',
        '-1', '+1', PHP_INT_MAX . '0', str_repeat('0', 65) . '1',
    ] as $raw) {
        $input = new MatchSelectorTestInput(['cfg_which_database' => $raw]);
        $appProperty->setValue($controller, new MatchSelectorTestApplication($input, 0));

        try {
            $selectorMethod->invoke($controller);
            throw new \RuntimeException('Malformed match database mode was accepted.');
        } catch (\InvalidArgumentException $exception) {
            matchSelectorAssert(400, $exception->getCode(),
                'Invalid match database mode HTTP status');
        }

        matchSelectorAssert(1, $input->rawReads,
            'Malformed selector bypassed raw input validation');
    }

    // Missing request input uses the saved Joomla administrator selection.
    foreach ([['0001', 1], [1, 1], ['0000', 0], [0, 0]] as [$state, $expected]) {
        $input = new MatchSelectorTestInput();
        $appProperty->setValue($controller, new MatchSelectorTestApplication($input, $state));

        matchSelectorAssert($expected, $selectorMethod->invoke($controller),
            'Stored database preference was ignored');
    }

    foreach (['1junk', ['1'], 2, true] as $state) {
        $appProperty->setValue($controller,
            new MatchSelectorTestApplication(new MatchSelectorTestInput(), $state));

        try {
            $selectorMethod->invoke($controller);
            throw new \RuntimeException('Invalid stored match preference was accepted.');
        } catch (\InvalidArgumentException $exception) {
            matchSelectorAssert(400, $exception->getCode(),
                'Invalid stored match preference HTTP status');
        }
    }

    // Keep both privileged actions attached to the same strict parser.
    $source = file_get_contents(
        dirname(__DIR__, 2) . '/admin/src/Controller/MatchController.php'
    );
    if (!is_string($source)
        || substr_count($source, '$this->selectedMatchDatabaseSelector()') !== 2
        || str_contains($source, '$input->getInt(' . "\n" . "                'cfg_which_database'")) {
        throw new \RuntimeException('Match copy and calendar actions bypass strict database modes.');
    }

    echo "Joomla 5/6 administrator match database choices: OK\n";
}
