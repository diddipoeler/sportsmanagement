<?php
/**
 * Joomla 5/6 native datetime chooser field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use DateTimeZone;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

final class DatetimechooserField extends FormField
{
    protected $type = 'Datetimechooser';

    protected function getInput(): string
    {
        $format = (string) ($this->element['format'] ?? '%Y-%m-%d');
        $attributes = [];

        if ((string) ($this->element['size'] ?? '') !== '') {
            $attributes['size'] = (int) $this->element['size'];
        }
        if ((string) ($this->element['maxlength'] ?? '') !== '') {
            $attributes['maxlength'] = (int) $this->element['maxlength'];
        }
        if ((string) ($this->element['class'] ?? '') !== '') {
            $attributes['class'] = (string) $this->element['class'];
        }
        if ((string) ($this->element['readonly'] ?? '') === 'true') {
            $attributes['readonly'] = 'readonly';
        }
        if ((string) ($this->element['disabled'] ?? '') === 'true') {
            $attributes['disabled'] = 'disabled';
        }
        if ((string) ($this->element['onchange'] ?? '') !== '') {
            $attributes['onchange'] = (string) $this->element['onchange'];
        }

        $upperValue = strtoupper((string) $this->value);

        if ($upperValue === 'NOW') {
            $date = Factory::getDate();
            $date->setTime((int) $date->format('H', true), 0, 0);
            $this->value = $date->format('U');
        } elseif ($upperValue === '+1 HOUR' || $upperValue === '+2 MONTH') {
            $date = Factory::getDate();
            $date->setTime((int) $date->format('H', true), 0, 0);
            $date->modify((string) $this->value);
            $this->value = $date->format('U');
        }

        $app = Factory::getApplication();
        $config = $app->getConfig();
        $identity = $app->getIdentity();

        switch (strtoupper((string) ($this->element['filter'] ?? ''))) {
            case 'SERVER_UTC':
                if ((int) $this->value !== 0) {
                    $date = Factory::getDate($this->value, 'UTC');
                    $date->setTimezone(new DateTimeZone((string) $config->get('offset')));
                    $this->value = $date->format('Y-m-d H:i:s', true, false);
                }
                break;

            case 'USER_UTC':
                if ((int) $this->value !== 0) {
                    $date = Factory::getDate($this->value, 'UTC');
                    $date->setTimezone(new DateTimeZone((string) $identity->getParam('timezone', $config->get('offset'))));
                    $this->value = $date->format('Y-m-d H:i:s', true, false);

                    if ((string) ($this->element['all_day'] ?? '') === '1') {
                        $this->value = $date->format('Y-m-d', true, false);
                    }
                }
                break;
        }

        return HTMLHelper::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
    }
}
