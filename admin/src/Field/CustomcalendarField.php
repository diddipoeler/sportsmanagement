<?php
/**
 * Native Joomla 5/6 custom calendar form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\CalendarField;
use Joomla\CMS\HTML\HTMLHelper;

final class CustomcalendarField extends CalendarField
{
    protected $type = 'CustomCalendar';

    protected string $defaultFormat = 'd-m-Y';

    /**
     * Render the native Joomla calendar field with normalized date input.
     *
     * Preserves the configured HTML attributes, validates the requested date
     * strictly against the supported input patterns and returns a neutral
     * placeholder for invalid or missing saved dates.
     *
     * @return string Calendar input HTML built by Joomla's HTML helper.
     */
    protected function getInput(): string
    {
        $attributes = [];

        if (!empty($this->size)) {
            $attributes['size'] = $this->size;
        }
        if (!empty($this->maxlength)) {
            $attributes['maxlength'] = $this->maxlength;
        }
        if (!empty($this->class)) {
            $attributes['class'] = $this->class;
        }
        if ($this->readonly) {
            $attributes['readonly'] = '';
        }
        if ($this->disabled) {
            $attributes['disabled'] = '';
        }
        if (!empty($this->onchange)) {
            $attributes['onchange'] = $this->onchange;
        }
        if (!empty($this->hint)) {
            $attributes['placeholder'] = $this->hint;
        }
        if (!$this->autocomplete) {
            $attributes['autocomplete'] = 'off';
        }
        if ($this->autofocus) {
            $attributes['autofocus'] = '';
        }
        if ($this->required) {
            $attributes['required'] = '';
            $attributes['aria-required'] = 'true';
        }

        $format = (string) ($this->element['format'] ?? $this->defaultFormat);
        $value = is_string($this->value) ? trim($this->value) : '';
        $supportedFormat = null;

        if (preg_match('/^\\d{4}-\\d{2}-\\d{2}$/D', $value) === 1) {
            $supportedFormat = 'Y-m-d';
        } elseif (preg_match('/^\\d{2}-\\d{2}-\\d{4}$/D', $value) === 1) {
            $supportedFormat = 'd-m-Y';
        }

        // Use strict round-trip validation: PHP's date parser otherwise
        // normalizes impossible dates such as 31 February into March.
        $parsed = $supportedFormat !== null
            ? \DateTimeImmutable::createFromFormat('!' . $supportedFormat, $value)
            : false;
        $errors = \DateTimeImmutable::getLastErrors();

        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsed->format($supportedFormat) !== $value) {
            $value = '00-00-0000';
        } else {
            $value = $parsed->format($this->defaultFormat);
        }

        return HTMLHelper::_('calendar', $value, $this->name, $this->id, $format, $attributes);
    }
}
