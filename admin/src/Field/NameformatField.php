<?php
/**
 * Joomla 5/6 native SportsManagement person-name format field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

/** Joomla 5/6-native SportsManagement person-name format field. */
final class NameformatField extends ListField
{
    protected $type = 'nameformat';

    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['class'] = 'inputbox';
        $element['size'] = '1';

        return parent::setup($element, $value, $group);
    }

    protected function getOptions(): array
    {
        $language = Factory::getApplication()->getLanguage();
        $extension = 'com_sportsmanagement';
        $source = JPATH_ADMINISTRATOR . '/components/' . $extension;

        $language->load($extension, JPATH_ADMINISTRATOR, null, false, false)
            || $language->load($extension, $source, null, false, false)
            || $language->load($extension, JPATH_ADMINISTRATOR, $language->getDefault(), false, false)
            || $language->load($extension, $source, $language->getDefault(), false, false);

        $keys = [
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NICK_FIRST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_FIRST_LAST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK_LAST_FIRST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST_NICK',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST2',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST2',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NICK_LAST2',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_NICK',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_LAST3',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST2_FIRST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_NEWLINE_FIRST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_FIRST_NEWLINE_LAST',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRST_NICK',
            'COM_SPORTSMANAGEMENT_GLOBAL_NAME_FORMAT_LAST_FIRSTNAME_FIRST_CHAR_DOT',
        ];
        $options = [];

        foreach ($keys as $value => $key) {
            $options[] = (object) [
                'value' => (string) $value,
                'text' => Text::_($key),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
