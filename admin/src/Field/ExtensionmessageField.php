<?php
/**
 * Native Joomla 5/6 extension-message field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;

final class ExtensionmessageField extends FormField
{
    protected $type = 'extensionmessage';

    private string $messageType = 'info';
    private string $message = '';

    /**
     * Initialize extension status message text and its display style.
     *
     * Retains the parent Joomla XML configuration contract and stores the
     * message translation key and style only when setup succeeds.
     *
     * @param SimpleXMLElement $element Joomla field XML definition.
     * @param mixed $value Current form field value.
     * @param string|null $group Optional form group.
     * @return bool Whether field setup succeeded.
     */
    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->messageType = trim((string) ($this->element['style'] ?? 'info')) ?: 'info';
            $this->message = trim((string) ($this->element['text'] ?? ''));
        }

        return $result;
    }

    /**
     * Render the label corresponding to the selected message style.
     *
     * Delegates per-field warning/info labels to the Joomla base class and
     * renders a visually hidden example caption when appropriate.
     *
     * @return string Translated label markup for the status message.
     */
    protected function getLabel(): string
    {
        $language = SportsManagementAdministratorApplicationResolver::resolve()->getLanguage();
        $language->load('com_installer', JPATH_ADMINISTRATOR);

        if ($this->messageType === 'example') {
            return '<span class="visually-hidden">' . Text::_('LIB_SYW_MESSAGE_EXAMPLE') . '</span>';
        }

        if (in_array($this->messageType, ['fieldwarning', 'fielderror', 'fieldinfo'], true)) {
            return parent::getLabel();
        }

        return '<div style="clear: both;"></div>';
    }

    /**
     * Render an installer-style notification with translated text.
     *
     * Chooses a fixed Bootstrap color class for each supported message style
     * and displays optional translation placeholders and labels.
     *
     * @return string Administrator notification or example badge HTML.
     */
    protected function getInput(): string
    {
        SportsManagementAdministratorApplicationResolver::resolve()->getLanguage()->load('lib_syw.sys', JPATH_SITE);

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
