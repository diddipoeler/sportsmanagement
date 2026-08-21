<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class ExtensionsubtitleField extends FormField
{
    protected $type = 'extensionsubtitle';

    protected function getLabel(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $color = trim((string) ($this->element['color'] ?? ''));

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#e65100';
        }

        $html = '<div style="display: inherit; position: relative; background: ' . $color
            . '; background: linear-gradient(to right, ' . $color . ' 0%, #fff 100%); height: 5px;">';

        if ($title !== '') {
            $label = htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8');
            $html .= '<div style="font-family: Courier New, Courier, monospace; letter-spacing: 2px; '
                . 'font-size: 10px; font-weight: bold; background-color: #fff; color: ' . $color . '; '
                . 'padding: 0 8px 0 10px; position: absolute; left: 20px; top: -6px;">'
                . $label . '</div>';
        }

        return $html . '</div>';
    }

    protected function getInput(): string
    {
        return '';
    }
}
