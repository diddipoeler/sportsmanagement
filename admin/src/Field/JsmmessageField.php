<?php
/**
 * Native Joomla 5/6 message field for SportsManagement forms.
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

final class JsmmessageField extends FormField
{
    protected $type = 'JSMMessage';

    /**
     * Render the display-only message field label for the selected style.
     *
     * @return string Visually hidden example marker or decorative label HTML.
     */
    protected function getLabel(): string
    {
        $type = trim((string) ($this->element['style'] ?? ''));

        return $type === 'example'
            ? '<span class="visually-hidden">Example</span>'
            : '<div style="clear: both;"></div>';
    }

    /**
     * Render the configured Joomla language message as safe HTML text.
     *
     * Translates administrator-provided labels and message keys, escapes
     * their output, and selects a fixed Bootstrap notification style.
     *
     * @return string Encoded example badge or administrator alert markup.
     */
    protected function getInput(): string
    {
        $message = trim((string) ($this->element['text'] ?? ''));
        $type = trim((string) ($this->element['style'] ?? ''));
        $label = trim((string) ($this->element['label'] ?? ''));
        $translatedLabel = $label !== '' ? Text::_($label) : '';
        // Joomla language overrides are text, not trusted HTML markup.
        $translatedMessage = $message !== ''
            ? htmlspecialchars(Text::_($message), ENT_QUOTES, 'UTF-8')
            : '';

        if ($type === 'example') {
            $badge = $translatedLabel !== ''
                ? '<span class="badge text-bg-secondary">' . htmlspecialchars($translatedLabel, ENT_QUOTES, 'UTF-8') . '</span>&nbsp;'
                : '';

            return $badge . '<span class="text-body-secondary" style="font-size:0.8em;">' . $translatedMessage . '</span>';
        }

        $style = match ($type) {
            'warning' => 'warning',
            'error' => 'danger',
            'info' => 'info',
            default => 'success',
        };
        $badge = $translatedLabel !== ''
            ? '<span class="badge text-bg-' . $style . '">' . htmlspecialchars($translatedLabel, ENT_QUOTES, 'UTF-8') . '</span>&nbsp;'
            : '';

        return '<div class="alert alert-' . $style . ' mb-0">' . $badge . '<span>' . $translatedMessage . '</span></div>';
    }
}
