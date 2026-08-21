<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class JsmsubtitleField extends FormField
{
    protected $type = 'JSMSubtitle';

    protected function getLabel(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $text = $title !== '' ? htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8') : '';

        return '<div style="clear: both;"></div>'
            . '<div style="margin: 20px 0 20px 20px; font-weight: bold; padding: 5px; color: #444444; border-bottom: 1px solid #444444;">'
            . $text . '</div>';
    }

    protected function getInput(): string
    {
        return '';
    }
}
