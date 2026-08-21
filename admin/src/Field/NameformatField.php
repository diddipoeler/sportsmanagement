<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Joomla 5/6-native SportsManagement person-name format field. */
final class NameformatField extends FormField
{
    protected $type = 'nameformat';

    protected function getInput(): string
    {
        $language = Factory::getApplication()->getLanguage();
        $extension = 'com_sportsmanagement';
        $source = JPATH_ADMINISTRATOR . '/components/' . $extension;

        $language->load($extension, JPATH_ADMINISTRATOR, null, false, false)
            || $language->load($extension, $source, null, false, false)
            || $language->load($extension, JPATH_ADMINISTRATOR, $language->getDefault(), false, false)
            || $language->load($extension, $source, $language->getDefault(), false, false);

        $options = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NICK_FIRST')),
            HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK')),
            HTMLHelper::_('select.option', 3, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST')),
            HTMLHelper::_('select.option', 4, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST')),
            HTMLHelper::_('select.option', 5, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_FIRST_LAST')),
            HTMLHelper::_('select.option', 6, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_LAST_FIRST')),
            HTMLHelper::_('select.option', 7, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST_NICK')),
            HTMLHelper::_('select.option', 8, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST2')),
            HTMLHelper::_('select.option', 9, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST2')),
            HTMLHelper::_('select.option', 10, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST')),
            HTMLHelper::_('select.option', 11, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST2')),
            HTMLHelper::_('select.option', 12, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK')),
            HTMLHelper::_('select.option', 13, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST3')),
            HTMLHelper::_('select.option', 14, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST2_FIRST')),
            HTMLHelper::_('select.option', 15, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NEWLINE_FIRST')),
            HTMLHelper::_('select.option', 16, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NEWLINE_LAST')),
            HTMLHelper::_('select.option', 17, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK')),
            HTMLHelper::_('select.option', 18, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRSTNAME_FIRST_CHAR_DOT')),
        ];

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $this->name,
            'class="inputbox" size="1"',
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
