<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class ActeventtypeField extends SportsManagementListField
{
    protected $type = 'acteventtype';

    protected function getOptions(): array
    {
        $targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targettable'] ?? ''));
        $selectedId = Factory::getApplication()->getInput()->getInt('id', 0);

        if ($targetTable === '' || $selectedId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $target = '#__sportsmanagement_' . $targetTable;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 's'))
            ->join(
                'INNER',
                $db->quoteName($target, 't')
                . ' ON ' . $db->quoteName('t.sports_type_id') . ' = ' . $db->quoteName('s.sports_type_id')
            )
            ->where($db->quoteName('t.id') . ' = ' . $selectedId)
            ->order($db->quoteName('s.name'));
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
