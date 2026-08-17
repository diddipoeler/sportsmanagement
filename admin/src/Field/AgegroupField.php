<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class AgegroupField extends SportsManagementListField
{
    protected $type = 'Agegroup';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
                $db->quoteName('country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $label = Text::_($item->text);

            if (!empty($item->country)) {
                $label .= ' (' . $item->country . ')';
            }

            $options[] = HTMLHelper::_('select.option', $item->value, $label);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
