<?php
/**
 * Regression tests for saving a club through its Joomla 5/6 administrator controller.
 *
 * A minimal shared form controller and request input double verify that the
 * club editor uses Joomla's injected input, normalises club addresses, and
 * delegates to the parent save implementation without getApplication().
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class LocationHelper
    {
        public static array $response = [];
        public static array $requests = [];

        /**
         * Return controlled geocoding data without accessing an external service.
         *
         * Records addresses passed by ClubController and supplies response
         * coordinates and administrative properties for the form-save test.
         *
         * @param string $address Formatted club address submitted for lookup.
         * @return array<string,mixed> Stubbed coordinates and address fields.
         */
        public function resolveDetailed(string $address): array
        {
            self::$requests[] = $address;

            return self::$response;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController
    {
        protected object $jsmjinput;
        public array $savedValues = [];
        public array $savedArguments = [];
        public int $saveCalls = 0;

        /**
         * Emulate the shared form controller's injected Joomla request input.
         *
         * Intentionally does not define getApplication(): calling it from the
         * ClubController must reproduce the original reported fatal error.
         *
         * @param object $input Joomla request input double with a POST bag.
         */
        public function __construct(object $input)
        {
            $this->jsmjinput = $input;
        }

        /**
         * Record form values passed to parent::save without executing Joomla.
         *
         * The real shared controller validates tokens and persists the model;
         * this double verifies that the child does not skip that delegation.
         *
         * @param string|null $key Record key used by the caller.
         * @param string|null $urlVar URL record selector.
         * @return bool Successful simulated parent save operation.
         */
        public function save($key = null, $urlVar = null)
        {
            $this->savedValues = $this->jsmjinput->post->get('jform', [], 'array');
            $this->savedArguments = [$key, $urlVar];
            ++$this->saveCalls;

            return true;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\ClubController;
    use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;

    define('_JEXEC', 1);

    final class ClubSaveTestPost
    {
        /**
         * Store POST values, preserving the same mutable bag as Joomla Input.
         *
         * @param array<string,mixed> $values Club jform and extended fields.
         */
        public function __construct(public array $values) {}

        /**
         * Read a Joomla POST field without silently modifying its type.
         *
         * @param string $key POST field name.
         * @param mixed $default Default if missing.
         * @param string $filter Joomla input filter name.
         * @return mixed Current submitted value or default.
         */
        public function get(string $key, mixed $default = null, string $filter = 'cmd'): mixed
        {
            return $this->values[$key] ?? $default;
        }

        /**
         * Replace a POST field, mimicking Joomla's mutable request object.
         *
         * @param string $key Field name to update.
         * @param mixed $value Replacement submitted value.
         * @return void
         */
        public function set(string $key, mixed $value): void
        {
            $this->values[$key] = $value;
        }
    }

    final class ClubSaveTestInput
    {
        /**
         * Provide a Joomla-compatible POST data bag to the form controller.
         *
         * @param array<string,mixed> $values Submitted request parameters.
         */
        public function __construct(public ClubSaveTestPost $post = new ClubSaveTestPost([]))
        {
        }
    }

    /**
     * Assert controller results independent of php.ini zend.assertions settings.
     *
     * @param mixed $expected Value required by the migration contract.
     * @param mixed $actual Observed request or controller value.
     * @param string $message Explanation shown if the regression occurs.
     * @return void
     */
    function clubSaveAssertSame(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $message . ': expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true)
            );
        }
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ClubController.php';

    $input = new ClubSaveTestInput(new ClubSaveTestPost([
        'jform' => [
            'id' => 42,
            'name' => 'Example FC',
            'address' => 'Main Street 4',
            'zipcode' => '12345',
            'location' => 'Exampletown',
            'country' => 'DDR',
        ],
        'extended' => ['custom_field' => 'retained'],
    ]));
    LocationHelper::$response = [
        'latitude' => '51.123',
        'longitude' => '7.456',
        'address' => [
            'country_code' => 'gb',
            'county' => 'Example County',
            'state' => 'Wrong state',
            'road' => 'Main Street',
            'city' => 'Exampletown',
        ],
    ];

    $controller = new ClubController($input);

    clubSaveAssertSame(true, $controller->save('id', 'cid'), 'Club controller did not delegate save');
    clubSaveAssertSame(1, $controller->saveCalls, 'Club controller called parent save incorrectly');
    clubSaveAssertSame(['id', 'cid'], $controller->savedArguments, 'Club form save arguments changed');
    clubSaveAssertSame(
        ['Main Street 4, 12345 Exampletown, DEU'],
        LocationHelper::$requests,
        'Club address did not use the normalised country'
    );

    $form = $input->post->get('jform', [], 'array');
    clubSaveAssertSame('DEU', $form['country'] ?? null, 'Historical DDR country was not normalised');
    clubSaveAssertSame('51.123', $form['latitude'] ?? null, 'Geocoder latitude missing');
    clubSaveAssertSame('7.456', $form['longitude'] ?? null, 'Geocoder longitude missing');
    clubSaveAssertSame('Example County', $form['state'] ?? null, 'Geocoder state mapping changed');
    clubSaveAssertSame($form, $controller->savedValues, 'Parent save did not receive updated jform');
    $extended = $input->post->get('extended', [], 'array');
    clubSaveAssertSame('retained', $extended['custom_field'] ?? null, 'Existing extended club fields lost');
    clubSaveAssertSame('Main Street', $extended['COM_SPORTSMANAGEMENT_OSM_ROAD'] ?? null,
        'Resolved street was not merged into extended fields');

    // Missing geocoder data must preserve existing fields and still save.
    LocationHelper::$requests = [];
    LocationHelper::$response = [];
    $secondInput = new ClubSaveTestInput(new ClubSaveTestPost([
        'jform' => ['id' => 43, 'name' => 'Without address', 'state' => 'Original state'],
        'extended' => ['preserved' => 'yes'],
    ]));
    $second = new ClubController($secondInput);
    clubSaveAssertSame(true, $second->save(), 'Club without address could not be saved');
    clubSaveAssertSame([], LocationHelper::$requests, 'Empty address unnecessarily invoked geocoder');
    clubSaveAssertSame('Original state', $second->savedValues['state'] ?? null,
        'Saving without geocoding unexpectedly modified club state');
    clubSaveAssertSame(['preserved' => 'yes'], $secondInput->post->get('extended', [], 'array'),
        'Saving without geocoding lost extended club data');

    echo "Joomla 5/6 administrator club controller save regression OK.\n";
}
