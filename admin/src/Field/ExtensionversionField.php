<?php
/**
 * Native Joomla 5/6 extension-version field.
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

final class ExtensionversionField extends FormField
{
    protected $type = 'ExtensionVersion';

    private string $version = '';

    /**
     * Initialize the Joomla field and read the configured extension version.
     *
     * @param SimpleXMLElement $element Form XML element for the version field.
     * @param mixed $value Current field value.
     * @param string|null $group Optional form control group.
     * @return bool Whether the parent field setup succeeded.
     */
    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $this->version = (string) ($this->element['version'] ?? '');
        }

        return $result;
    }

    /**
     * Render the localized administrator extension-version field label.
     *
     * Escapes the localized caption before inserting it in markup.
     *
     * @return string HTML containing the translated version label.
     */
    protected function getLabel(): string
    {
        $language = SportsManagementAdministratorApplicationResolver::resolve()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        return '<div style="clear: both;">'
            . htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_VERSION_LABEL'), ENT_QUOTES, 'UTF-8')
            . '</div>';
    }

    /**
     * Render the configured extension version as escaped badge text.
     *
     * @return string HTML badge with a safely encoded version string.
     */
    protected function getInput(): string
    {
        return '<div style="padding-top: 5px; overflow: inherit"><span class="badge bg-secondary">'
            . htmlspecialchars($this->version, ENT_QUOTES, 'UTF-8')
            . '</span></div>';
    }
}
