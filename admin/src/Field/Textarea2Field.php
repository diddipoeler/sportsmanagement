<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextareaField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;

final class Textarea2Field extends TextareaField
{
    protected $type = 'Textarea2';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        if (isset($element->content) && ($value === null || $value === '')) {
            $value = (string) $element->content;
        }

        return parent::setup($element, $value, $group);
    }

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
