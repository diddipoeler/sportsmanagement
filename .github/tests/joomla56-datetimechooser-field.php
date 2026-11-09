<?php
/**
 * Standalone Joomla 5/6 datetime chooser validation regressions.
 *
 * Run: php .github/tests/joomla56-datetimechooser-field.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form {
    /** Test form base exposing the date chooser's saved state. */
    class FormField
    {
        /** @var array<string,string> Synthetic XML attributes. */
        protected array $element = [];

        /** @var mixed Raw saved value. */
        protected mixed $value = '';

        protected string $name = 'matchdate';
        protected string $id = 'matchdate';

        /**
         * Load the isolated calendar state into the fake Joomla form.
         *
         * @param mixed $value Saved field input.
         * @param array<string,string> $attributes XML field attributes.
         */
        public function __construct(mixed $value = '', array $attributes = [])
        {
            $this->value = $value;
            $this->element = $attributes;
        }
    }
}

namespace Joomla\CMS\HTML {
    /** Capture the value passed to Joomla's native calendar HTML helper. */
    final class HTMLHelper
    {
        /**
         * Return the chosen date without rendering HTML.
         *
         * @param string $method Native helper name.
         * @param mixed $value Prepared calendar value.
         * @param string $name Input name.
         * @param string $id Input ID.
         * @param string $format Display format.
         * @param array<string,mixed> $attributes HTML input attributes.
         * @return string Date value supplied to Joomla calendar.
         */
        public static function _(
            string $method,
            mixed $value,
            string $name,
            string $id,
            string $format,
            array $attributes
        ): string {
            return (string) $value;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Service {
    /** Fake administrator identity and configuration for timezone selection. */
    final class SportsManagementAdministratorApplicationResolver
    {
        /**
         * Resolve a minimal Joomla application stub.
         *
         * @return object Application with timezone configuration and identity.
         */
        public static function resolve(): object
        {
            return new class {
                /**
                 * Return a configuration stub with an invalid saved timezone.
                 *
                 * @return object Joomla-like configuration.
                 */
                public function getConfig(): object
                {
                    return new class {
                        /**
                         * Read the default administrator timezone preference.
                         *
                         * @param string $name Configuration property.
                         * @param mixed $default Fallback value.
                         * @return string Invalid timezone for regression testing.
                         */
                        public function get(string $name, mixed $default = null): string
                        {
                            return 'Invalid/Not_A_Real_Timezone';
                        }
                    };
                }

                /**
                 * Return a user identity with an invalid timezone preference.
                 *
                 * @return object Joomla-like user identity.
                 */
                public function getIdentity(): object
                {
                    return new class {
                        /**
                         * Read the stored user timezone.
                         *
                         * @param string $name User preference key.
                         * @param mixed $default Fallback site timezone.
                         * @return string Invalid timezone name.
                         */
                        public function getParam(string $name, mixed $default = null): string
                        {
                            return 'No/Such_Timezone';
                        }
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS {
    /** Deterministic Joomla date facade using PHP DateTime methods. */
    final class Factory
    {
        /**
         * Return a synthetic Joomla-compatible date for calendar formatting.
         *
         * @param mixed $value UTC source value.
         * @param string $timezone Source timezone identifier.
         * @return object Date object with Joomla-style format arguments.
         */
        public static function getDate(mixed $value = 'now', string $timezone = 'UTC'): object
        {
            return new class((string) $value, $timezone) extends \DateTime {
                /**
                 * Create a PHP date in the requested input timezone.
                 *
                 * @param string $value Date expression.
                 * @param string $timezone Source timezone name.
                 */
                public function __construct(string $value, string $timezone)
                {
                    parent::__construct($value, new \DateTimeZone($timezone));
                }

                /**
                 * Ignore Joomla-only formatting flags in isolated assertions.
                 *
                 * @param string $format Date output format.
                 * @param bool $local Joomla local-format flag.
                 * @param bool $translate Joomla translation flag.
                 * @return string Formatted date string.
                 */
                public function format(string $format, bool $local = false, bool $translate = false): string
                {
                    return parent::format($format);
                }
            };
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Field/DatetimechooserField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\DatetimechooserField;

    /**
     * Render the protected datetime chooser input without Joomla runtime.
     *
     * @param mixed $value Saved datetime field value.
     * @param array<string,string> $attributes XML field configuration.
     * @return string Value passed to the native Joomla calendar helper.
     */
    function smRenderDatetimeChooser(mixed $value, array $attributes = []): string
    {
        return (new \ReflectionMethod(DatetimechooserField::class, 'getInput'))
            ->invoke(new DatetimechooserField($value, $attributes));
    }

    $tzMethod = new \ReflectionMethod(DatetimechooserField::class, 'validatedTimezone');

    if ($tzMethod->invoke(null, 'Invalid/Timezone')->getName() !== 'UTC'
        || $tzMethod->invoke(null, 'Europe/Berlin')->getName() !== 'Europe/Berlin') {
        throw new \RuntimeException('Datetime chooser did not validate saved timezones.');
    }

    foreach ([[], new \stdClass(), false, null] as $value) {
        if (smRenderDatetimeChooser($value) !== '') {
            throw new \RuntimeException('Datetime chooser accepted a nested saved value.');
        }
    }

    foreach (['SERVER_UTC', 'USER_UTC'] as $filter) {
        if (smRenderDatetimeChooser('2026-11-09 10:30:00', ['filter' => $filter])
            !== '2026-11-09 10:30:00') {
            throw new \RuntimeException('Invalid timezone configuration broke ' . $filter . '.');
        }
    }

    echo "Joomla 5/6 datetime chooser timezone regressions passed.\n";
}
