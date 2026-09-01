<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Parent-position selector for the administrator position form.
 */
final class ParentpositionField extends SportsManagementListField
{
    protected $type = 'parentposition';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $currentId = Factory::getApplication()->getInput()->getInt('id', 0);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->order($db->quoteName('ordering') . ' ASC');

        if ($currentId > 0) {
            $query->where($db->quoteName('id') . ' <> ' . $currentId);
        }

        $db->setQuery($query);
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IS_P_POSITION'),
            ],
        ];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
