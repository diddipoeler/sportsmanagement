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
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

final class DatetimechooserField extends FormField
{
    protected $type = 'Datetimechooser';

    /**
     * Render the administrator date/time chooser with Joomla's calendar helper.
     *
     * Supports legacy relative date keywords and the SERVER_UTC / USER_UTC
     * display filters. Rejects unsupported value types and falls back to UTC
     * when a saved timezone name is invalid. Malformed stored dates leave the
     * input empty rather than causing a fatal administrator form error.
     *
     * @return string Rendered calendar input markup.
     */
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

        // Untrusted form state must not trigger array-to-string conversions.
        $this->value = is_int($this->value) || is_string($this->value)
            ? $this->value
            : '';
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

        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $config = $app->getConfig();
        $identity = $app->getIdentity();

        try {
            switch (strtoupper((string) ($this->element['filter'] ?? ''))) {
            case 'SERVER_UTC':
                if ((string) $this->value !== '' && (string) $this->value !== '0') {
                    $date = Factory::getDate($this->value, 'UTC');
                    $date->setTimezone(self::validatedTimezone((string) $config->get('offset', 'UTC')));
                    $this->value = $date->format('Y-m-d H:i:s', true, false);
                }
                break;

            case 'USER_UTC':
                if ((string) $this->value !== '' && (string) $this->value !== '0') {
                    $date = Factory::getDate($this->value, 'UTC');
                    $date->setTimezone(self::validatedTimezone((string) $identity->getParam('timezone', $config->get('offset', 'UTC'))));
                    $this->value = $date->format('Y-m-d H:i:s', true, false);

                    if ((string) ($this->element['all_day'] ?? '') === '1') {
                        $this->value = $date->format('Y-m-d', true, false);
                    }
                }
                break;
            }
        } catch (\Throwable) {
            // Broken persisted dates must not abort the administrator form.
            $this->value = '';
        }

        return HTMLHelper::_('calendar', $this->value, $this->name, $this->id, $format, $attributes);
    }

    /**
     * Resolve the configured Joomla display timezone with a safe fallback.
     *
     * Malformed timezone identifiers must not interrupt the administrator
     * form; UTC retains a predictable calendar value in that situation.
     *
     * @param string $name Site or user timezone preference.
     * @return DateTimeZone Valid timezone, or UTC when invalid.
     */
    private static function validatedTimezone(string $name): DateTimeZone
    {
        try {
            return new DateTimeZone($name !== '' ? $name : 'UTC');
        } catch (\Throwable) {
            return new DateTimeZone('UTC');
        }
    }
}
