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

use Joomla\CMS\Date\Date;
use Joomla\CMS\Form\Field\CalendarField;
use Joomla\CMS\HTML\HTMLHelper;

final class CustomcalendarField extends CalendarField
{
    protected $type = 'CustomCalendar';

    protected string $defaultFormat = 'd-m-Y';

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
        $value = (string) $this->value;

        if ($value === '' || $value === '0000-00-00') {
            $value = '00-00-0000';
        } else {
            try {
                $value = (new Date($value))->format($this->defaultFormat);
            } catch (\Throwable $e) {
                $value = (string) $this->value;
            }
        }

        return HTMLHelper::_('calendar', $value, $this->name, $this->id, $format, $attributes);
    }
}
