<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;

final class ExtensiontranslatorsField extends FormField
{
    protected $type = 'ExtensionTranslators';

    private string $translators = '';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $key = (string) ($this->element['translators'] ?? '');
            $this->translators = $key !== '' ? Text::_($key) : '';
        }

        return $result;
    }

    protected function getLabel(): string
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        return '<div style="clear: both;">'
            . ($this->translators !== '' ? Text::_('COM_SPORTSMANAGEMENT_TRANSLATORS_LABEL') : '')
            . '</div>';
    }

    protected function getInput(): string
    {
        if ($this->translators === '') {
            return '';
        }

        return '<div style="padding-top: 5px; overflow: inherit">'
            . htmlspecialchars($this->translators, ENT_QUOTES, 'UTF-8')
            . '</div>';
    }
}
