<?php
/**
 * Native Joomla 5/6 textarea field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextareaField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;

final class Textarea2Field extends TextareaField
{
    protected $type = 'Textarea2';

    /**
     * Initialise the textarea using Joomla's native field setup.
     * Uses the XML content element as a default only if no saved value exists,
     * preserving explicitly stored content including a string zero.
     * @param SimpleXMLElement $element Field definition with optional content.
     * @param mixed $value Current saved field value.
     * @param string|null $group Optional Joomla form group.
     * @return bool Whether parent field setup succeeded.
     */
    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        if (isset($element->content) && ($value === null || $value === '')) {
            $value = (string) $element->content;
        }

        return parent::setup($element, $value, $group);
    }

    /**
     * Render the native textarea and optional translated help description.
     * Keeps the existing XML description convention and appends help markup
     * only when a nonempty description is configured.
     * @return string Joomla textarea and optional description markup.
     */
    public function getInput(): string
    {
        $html = parent::getInput();

        if (isset($this->element->description)) {
            $description = trim((string) $this->element->description);

            if ($description !== '') {
                $html .= '<div class="form-text">' . Text::_($description) . '</div>';
            }
        }

        return $html;
    }
}
