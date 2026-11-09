<?php
/**
 * Native Joomla 5/6 extension subtitle field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class ExtensionsubtitleField extends FormField
{
    protected $type = 'extensionsubtitle';

    /**
     * Render the configured extension subtitle and a safe gradient marker.
     *
     * Restricts color settings to six-digit hexadecimal values, uses a known
     * default for invalid colors and escapes the translated subtitle text.
     *
     * @return string Safe administrator subtitle and gradient HTML.
     */
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

    /**
     * Return no separate form control for this display-only subtitle field.
     *
     * @return string Empty input markup; the field renders only a label.
     */
    protected function getInput(): string
    {
        return '';
    }
}
