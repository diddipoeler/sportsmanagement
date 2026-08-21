<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use SimpleXMLElement;

final class ExtensionversionField extends FormField
{
    protected $type = 'ExtensionVersion';

    private string $version = '';

    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->version = (string) ($this->element['version'] ?? '');
        }

        return $result;
    }

    protected function getLabel(): string
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        return '<div style="clear: both;">' . Text::_('COM_SPORTSMANAGEMENT_VERSION_LABEL') . '</div>';
    }

    protected function getInput(): string
    {
        return '<div style="padding-top: 5px; overflow: inherit"><span class="badge bg-secondary">'
            . htmlspecialchars($this->version, ENT_QUOTES, 'UTF-8')
            . '</span></div>';
    }
}
