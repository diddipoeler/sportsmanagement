<?php
/**
 * Joomla 5/6 native serial number field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

final class SerialnumberField extends FormField
{
    protected $type = 'serialnumber';

    /**
     * Render the serial number input with an existing or generated value.
     * Generates a new serial only when the current value is empty or invalid
     * and escapes the field attributes before returning the HTML.
     * @return string Serial number input markup.
     */
    protected function getInput(): string
    {
        $value = is_string($this->value) ? trim($this->value) : '';

        if ($value === '') {
            $value = $this->generateSerialNumber();
            $this->value = $value;
        }

        return '<input type="text" id="'
            . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" name="'
            . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8')
            . '" value="'
            . htmlspecialchars($value, ENT_QUOTES, 'UTF-8')
            . '" />';
    }

    /**
     * Generate a random serial using the historical letter and digit pattern.
     * Uses PHP's secure random integer generator for each variable character.
     * @return string Newly generated formatted serial number.
     */
    private function generateSerialNumber(): string
    {
        $template = 'XX99-XX99-99XX-99XX-XXXX-99XX';
        $serial = '';

        foreach (str_split($template) as $token) {
            $serial .= match ($token) {
                'X' => chr(random_int(65, 90)),
                '9' => (string) random_int(0, 9),
                default => $token,
            };
        }

        return $serial;
    }
}
