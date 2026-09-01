<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class SportstypeField extends SportsManagementListField
{
    protected $type = 'Sportstype';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id', 'value'), $db->quoteName('name', 'text')])
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
