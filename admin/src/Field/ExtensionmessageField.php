<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;

final class ExtensionmessageField extends FormField
{
    protected $type = 'extensionmessage';

    private string $messageType = 'info';
    private string $message = '';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->messageType = trim((string) ($this->element['style'] ?? 'info')) ?: 'info';
            $this->message = trim((string) ($this->element['text'] ?? ''));
        }

        return $result;
    }

    protected function getLabel(): string
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_installer', JPATH_ADMINISTRATOR);

        if ($this->messageType === 'example') {
            return '<span class="visually-hidden">' . Text::_('LIB_SYW_MESSAGE_EXAMPLE') . '</span>';
        }

        if (in_array($this->messageType, ['fieldwarning', 'fielderror', 'fieldinfo'], true)) {
            return parent::getLabel();
        }

        return '<div style="clear: both;"></div>';
    }

    protected function getInput(): string
    {
        Factory::getApplication()->getLanguage()->load('lib_syw.sys', JPATH_SITE);

        $label = trim((string) ($this->element['label'] ?? ''));

        if ($label !== '' && $this->translateLabel) {
            $label = Text::_($label);
        }

        $message = $this->message !== '' ? Text::_($this->message) : '';

        if ($this->messageType === 'example') {
            $label = $label !== '' ? $label : Text::_('LIB_SYW_MESSAGE_EXAMPLE');

            return '<span class="badge text-bg-secondary">' . $label . '</span>&nbsp;'
                . '<span class="text-body-secondary" style="font-size: 0.8em;">' . $message . '</span>';
        }

        $style = match ($this->messageType) {
            'warning', 'fieldwarning' => 'warning',
            'error', 'fielderror' => 'danger',
            'info', 'fieldinfo' => 'info',
            default => 'success',
        };
        $fieldStyle = in_array($this->messageType, ['fieldwarning', 'fielderror', 'fieldinfo'], true);
        $badge = $label !== '' && !$fieldStyle
            ? '<span class="badge text-bg-' . $style . '">' . $label . '</span>&nbsp;'
            : '';

        return '<div class="alert alert-' . $style . ' mb-0">'
            . $badge . '<span>' . $message . '</span></div>';
    }
}
