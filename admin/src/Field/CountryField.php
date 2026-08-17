<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class CountryField extends SportsManagementListField
{
    protected $type = 'Country';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('alpha3', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $country) {
            $options[] = HTMLHelper::_('select.option', $country->value, Text::_($country->text));
        }

        return array_merge(parent::getOptions(), $options);
    }
}
