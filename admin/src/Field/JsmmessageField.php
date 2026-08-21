<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class JsmmessageField extends FormField
{
    protected $type = 'JSMMessage';

    protected function getLabel(): string
    {
        $type = trim((string) ($this->element['style'] ?? ''));

        return $type === 'example'
            ? '<span class="visually-hidden">Example</span>'
            : '<div style="clear: both;"></div>';
    }

    protected function getInput(): string
    {
        $message = trim((string) ($this->element['text'] ?? ''));
        $type = trim((string) ($this->element['style'] ?? ''));
        $label = trim((string) ($this->element['label'] ?? ''));
        $translatedLabel = $label !== '' ? Text::_($label) : '';
        $translatedMessage = $message !== '' ? Text::_($message) : '';

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
