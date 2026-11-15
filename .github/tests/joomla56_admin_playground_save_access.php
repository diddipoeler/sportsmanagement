<?php
/**
 * Joomla 5/6 backend playground save token/ACL and geocoding regression.
 *
 * Simulates Joomla's injected input and parent save without network or SQL
 * dependencies, verifying that geocoding occurs only for permitted edits.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    final class PlaygroundGeocoder
    {
        public static int $calls = 0;
        public static bool $fail = false;
        public static ?array $result = null;

        /**
         * Initialise the geocoder double without a real database.
         *
         * @param mixed $database Controller-provided connection stub.
         */
        public function __construct(mixed $database) {}

        /**
         * Return controlled geocoding data or simulate a network exception.
         *
         * @param object $data Playground address values to enrich.
         * @return array|null Simulated geocoder result.
         */
        public function geocode(object $data): ?array
        {
            ++self::$calls;

            if (self::$fail) {
                throw new \RuntimeException('External geocoder unavailable');
            }

            return self::$result;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController
    {
        protected object $input;
        protected object $jsmdb;
        public bool $allowed = true;
        public int $checks = 0;
        public int $saves = 0;
        public array $savedForm = [];

        /**
         * Inject a Joomla-like input and empty database into the form double.
         *
         * @param object $input Request data with a mutable POST bag.
         */
        public function __construct(object $input)
        {
            $this->input = $input;
            $this->jsmdb = new \stdClass();
        }

        /**
         * Deny unauthorised form saves before any geocoder side effects.
         *
         * @param array<string,mixed> $data Unmodified Joomla form data.
         * @param string|null $key Form record ID parameter.
         * @return void
         * @throws \RuntimeException When the request must be denied.
         */
        protected function assertFormSaveAccess(array $data, $key = null): void
        {
            ++$this->checks;

            if (!$this->allowed) {
                throw new \RuntimeException('JERROR_ALERTNOAUTHOR', 403);
            }
        }

        /**
         * Record the final form submitted to the shared parent saver.
         *
         * @param string|null $key Primary record key.
         * @param string|null $urlVar Record URL parameter.
         * @return bool Simulated successful persistence.
         */
        public function save($key = null, $urlVar = null)
        {
            $this->savedForm = $this->input->post->get('jform', [], 'array');
            ++$this->saves;

            return true;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\PlaygroundController;
    use Diddipoeler\Component\SportsManagement\Administrator\Service\PlaygroundGeocoder;

    define('_JEXEC', 1);

    final class PlaygroundSaveTestPost
    {
        /**
         * Store mutable Joomla POST form fields.
         *
         * @param array<string,mixed> $data Submitted request values.
         */
        public function __construct(public array $data) {}

        /**
         * Read the requested POST field.
         *
         * @param string $name Field name.
         * @param mixed $default Fallback for missing values.
         * @param string $filter Joomla input filtering mode.
         * @return mixed Field value or default.
         */
        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            return $this->data[$name] ?? $default;
        }

        /**
         * Update a submitted form field.
         *
         * @param string $name Joomla POST field name.
         * @param mixed $value Replacement value.
         * @return void
         */
        public function set(string $name, mixed $value): void
        {
            $this->data[$name] = $value;
        }
    }

    final class PlaygroundSaveTestInput
    {
        /**
         * Make a POST bag available to the controller.
         *
         * @param PlaygroundSaveTestPost $post Mutable Joomla POST input.
         */
        public function __construct(public PlaygroundSaveTestPost $post) {}
    }

    /**
     * Require strict equality for playground save assertions.
     *
     * @param mixed $expected Expected value.
     * @param mixed $actual Observed value.
     * @param string $message Failure context.
     * @return void
     * @throws \RuntimeException On failed assertions.
     */
    function playgroundSaveAssert(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($message . ': ' . var_export($actual, true));
        }
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/PlaygroundController.php';

    $form = ['id' => 3, 'name' => 'Example Playground', 'state' => 'Original'];
    $input = new PlaygroundSaveTestInput(new PlaygroundSaveTestPost(['jform' => $form]));
    PlaygroundGeocoder::$result = [
        'state' => 'Updated State', 'latitude' => '51.123', 'longitude' => '7.456',
    ];
    $controller = new PlaygroundController($input);

    playgroundSaveAssert(true, $controller->save(), 'Valid playground save failed');
    playgroundSaveAssert(1, $controller->checks, 'Access was not checked before geocoding');
    playgroundSaveAssert(1, PlaygroundGeocoder::$calls, 'Valid playground geocoding missing');
    playgroundSaveAssert('Updated State', $controller->savedForm['state'] ?? null,
        'Geocoded state was not passed to parent save');
    playgroundSaveAssert('51.123', $controller->savedForm['latitude'] ?? null,
        'Geocoded latitude was lost');

    PlaygroundGeocoder::$calls = 0;
    $deniedInput = new PlaygroundSaveTestInput(new PlaygroundSaveTestPost(['jform' => $form]));
    $denied = new PlaygroundController($deniedInput);
    $denied->allowed = false;

    try {
        $denied->save();
        throw new \RuntimeException('Denied playground save unexpectedly succeeded.');
    } catch (\RuntimeException $e) {
        playgroundSaveAssert(403, $e->getCode(), 'Denied playground status was incorrect');
    }

    playgroundSaveAssert(1, $denied->checks, 'Access was never checked');
    playgroundSaveAssert(0, $denied->saves, 'Denied playground reached parent save');
    playgroundSaveAssert(0, PlaygroundGeocoder::$calls, 'Denied playground initiated geocoding');
    playgroundSaveAssert(['jform' => $form], $deniedInput->post->data,
        'Denied playground mutated the submitted form');

    PlaygroundGeocoder::$fail = true;
    $unavailable = new PlaygroundController(
        new PlaygroundSaveTestInput(new PlaygroundSaveTestPost(['jform' => $form]))
    );
    playgroundSaveAssert(true, $unavailable->save(), 'Geocoder failure blocked permitted save');
    playgroundSaveAssert($form, $unavailable->savedForm,
        'Failed geocoder unexpectedly modified playground data');

    echo "Joomla 5/6 administrator playground save access: OK\n";
}
