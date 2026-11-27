<?php
/**
 * Native Joomla 5/6 multilist compatibility field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;

/** Joomla 5/6 replacement for the historical comma-separated multi-list field. */
final class MultilistField extends ListField
{
    protected $type = 'multilist';

    /**
     * Render the native Joomla list with historical comma-separated selections.
     * Temporarily converts saved comma-separated values to individual options,
     * discards unsupported structured selection entries and restores the original
     * field value even if Joomla's renderer throws an exception.
     * @return string Rendered list field markup.
     */
    protected function getInput(): string
    {
        $originalValue = $this->value;

        if (!is_array($this->value) && is_string($this->value) && str_contains($this->value, ',')) {
            $this->value = array_values(array_filter(
                array_map('trim', explode(',', $this->value)),
                static fn (string $value): bool => $value !== ''
            ));
        }

        if (is_array($this->value)) {
            $this->value = array_values(array_filter(
                $this->value,
                static fn (mixed $value): bool => is_string($value) || is_int($value)
            ));
        } elseif (!is_string($this->value) && !is_int($this->value) && $this->value !== null) {
            $this->value = null;
        }

        try {
            return parent::getInput();
        } finally {
            $this->value = $originalValue;
        }
    }
}
