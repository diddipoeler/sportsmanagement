<?php
/**
 * Native Joomla 5/6 extension-translators field.
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

final class ExtensiontranslatorsField extends FormField
{
    protected $type = 'ExtensionTranslators';

    private string $translators = '';

    /**
     * Initialize the translators field from its Joomla XML configuration.
     *
     * Reads an optional translation key only if the inherited field setup
     * succeeds; no stale translator text is reused between form instances.
     *
     * @param SimpleXMLElement $element XML definition of the field.
     * @param mixed $value Initial field value.
     * @param string|null $group Optional Joomla form group.
     * @return bool Whether field initialization was successful.
     */
    public function setup(SimpleXMLElement $element, $value, $group = null): bool
    {
        $result = parent::setup($element, $value, $group);

        if ($result) {
            $key = (string) ($this->element['translators'] ?? '');
            $this->translators = $key !== '' ? Text::_($key) : '';
        }

        return $result;
    }

    /**
     * Display a translated label only for configured translator credits.
     *
     * Escapes the translated caption instead of trusting language overrides.
     *
     * @return string HTML label, or an empty label wrapper when unused.
     */
    protected function getLabel(): string
    {
        $language = SportsManagementAdministratorApplicationResolver::resolve()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        return '<div style="clear: both;">'
            . ($this->translators !== ''
                ? htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_TRANSLATORS_LABEL'), ENT_QUOTES, 'UTF-8')
                : '')
            . '</div>';
    }

    /**
     * Render translator attribution as escaped HTML if it is configured.
     *
     * @return string Safely escaped translator credits or empty text.
     */
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
