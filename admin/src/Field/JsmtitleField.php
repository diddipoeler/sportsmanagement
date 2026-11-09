<?php
/**
 * Native Joomla 5/6 JSM title field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class JsmtitleField extends FormField
{
    protected $type = 'JSMTitle';

    /**
     * Render the translated administrator section heading with safe styling.
     *
     * Validates the hexadecimal color and icon name and escapes translated
     * title text and configured image paths before inserting HTML attributes.
     *
     * @return string Escaped heading markup with optional image or icon.
     */
    protected function getLabel(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $image = trim((string) ($this->element['imagesrc'] ?? ''));
        $icon = preg_replace('/[^A-Za-z0-9_-]/', '', trim((string) ($this->element['icon'] ?? '')));
        $color = trim((string) ($this->element['color'] ?? ''));

        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#e65100';
        }

        $style = 'background: ' . $color . '; background: linear-gradient(to right, ' . $color . ' 0%, #fff 100%); '
            . 'color: #fff; border-radius: 3px; font-family: "Courier New", Courier, monospace; margin: 5px 0; '
            . 'text-transform: uppercase; letter-spacing: 3px; font-weight: bold; padding: 5px 5px 5px 10px;';
        $html = '<div style="' . $style . '">';

        // Disallow executable and protocol-relative URLs in the image source.
        if ($image !== '' && (preg_match('~^(?:https?://|/)~i', $image)
            || preg_match('~^[a-z][a-z0-9+.-]*:~i', $image) !== 1)) {
            $html .= '<img style="margin: -1px 4px 0 0; float: left; padding: 0; width: 16px; height: 16px" src="'
                . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="">';
        } elseif ($icon !== '') {
            HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);
            $html .= '<i style="font-size: inherit; vertical-align: baseline" class="SYWicon-'
                . htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') . '">&nbsp;</i>';
        }

        if ($title !== '') {
            $html .= htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8');
        }

        return $html . '</div>';
    }

    /**
     * Return no editable input control for this display-only heading field.
     *
     * @return string Empty string because the field renders in its label.
     */
    protected function getInput(): string
    {
        return '';
    }
}
