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

    protected function getInput(): string
    {
        $value = trim((string) $this->value);

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
