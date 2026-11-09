<?php
/**
 * Standalone Joomla 5/6 tests for strict administrator calendar field dates.
 *
 * Run: php .github/tests/joomla56-custom-calendar-field.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Form\Field {
    /** Minimal parent calendar field exposing Joomla-style configuration. */
    class CalendarField
    {
        /** @var array<string,string> Calendar XML attributes. */
        protected array $element = [];
        protected mixed $value = null;
        protected string $name = 'testdate';
        protected string $id = 'testdate';
        protected ?int $size = null;
        protected ?int $maxlength = null;
        protected string $class = '';
        protected bool $readonly = false;
        protected bool $disabled = false;
        protected string $onchange = '';
        protected string $hint = '';
        protected bool $autocomplete = true;
        protected bool $autofocus = false;
        protected bool $required = false;

        /**
         * Set the test date without a Joomla installation.
         *
         * @param mixed $value Raw form field value.
         * @return void
         */
        public function __construct(mixed $value)
        {
            $this->value = $value;
        }
    }
}

namespace Joomla\CMS\HTML {
    /** Captures the calendar value emitted by the native field. */
    final class HTMLHelper
    {
        /**
         * Preserve the normalized date for isolated assertions.
         *
         * @param string $method Joomla HTML helper function name.
         * @param string $value Date to render.
         * @param string $name Field name.
         * @param string $id HTML element identifier.
         * @param string $format Calendar date format.
         * @param array<string,mixed> $attributes HTML input attributes.
         * @return string Normalized date string.
         */
        public static function _(
            string $method,
            string $value,
            string $name,
            string $id,
            string $format,
            array $attributes
        ): string {
            return $value;
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Field/CustomcalendarField.php';

    use Diddipoeler\Component\SportsManagement\Administrator\Field\CustomcalendarField;

    /**
     * Return a form field's calendar value through its protected renderer.
     *
     * @param mixed $raw Date input under test.
     * @return string Date passed into the Joomla calendar HTML helper.
     */
    function smRenderCustomCalendar(mixed $raw): string
    {
        return (new \ReflectionMethod(CustomcalendarField::class, 'getInput'))
            ->invoke(new CustomcalendarField($raw));
    }

    foreach ([
        '2024-02-29' => '29-02-2024',
        '29-02-2024' => '29-02-2024',
        '2026-11-09' => '09-11-2026',
    ] as $input => $expected) {
        if (smRenderCustomCalendar($input) !== $expected) {
            throw new \RuntimeException('Valid calendar date was not normalized: ' . $input);
        }
    }

    foreach ([
        '2023-02-29', '2026-02-31', '31-04-2026', '0000-00-00',
        '', 'not-a-date', '2026-11-09 10:00:00', '<script>alert(1)</script>',
        null, false, [], new \stdClass(),
    ] as $input) {
        if (smRenderCustomCalendar($input) !== '00-00-0000') {
            throw new \RuntimeException('Invalid calendar date was not rejected.');
        }
    }

    echo "Joomla 5/6 custom calendar field validation passed.\n";
}
