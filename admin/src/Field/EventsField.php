<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

final class EventsField extends SportsManagementListField
{
    protected $type = 'events';

    protected function getInput(): string
    {
        $this->element['multiple'] = 'true';
        $this->element['size'] = (string) ($this->element['size'] ?? '10');

        return parent::getInput();
    }

    protected function getOptions(): array
    {
        $tablePrefix = preg_replace(
            '/[^A-Za-z0-9_]/',
            '',
            (string) ComponentHelper::getParams('com_sportsmanagement')->get(
                'cfg_which_database_table',
                'sportsmanagement'
            )
        );
        $tablePrefix = $tablePrefix !== '' ? $tablePrefix : 'sportsmanagement';

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('e.id', 'value'),
                $db->quoteName('e.name', 'text'),
            ])
            ->from($db->quoteName('#__' . $tablePrefix . '_eventtype', 'e'))
            ->where($db->quoteName('e.published') . ' = 1')
            ->order($db->quoteName('e.name') . ' ASC');

        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text) . ' (' . $item->value . ')',
            ];
        }

        return array_merge($options, parent::getOptions());
    }
}
