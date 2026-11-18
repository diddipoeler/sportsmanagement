<?php
/**
 * Joomla 5/6 native position list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class PositionlistField extends SportsManagementListField
{
    protected $type = 'positionlist';

    /**
     * Retrieve published player positions for native administrator forms.
     *
     * Joins positions with the associated sports type, orders published
     * records by their configured order and translated name, and appends
     * inherited Joomla list options. Missing optional database tables
     * leave an empty set rather than interrupting the backend form.
     *
     * @return array<int,object> Published position ID/name choices.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('pos.sports_type_id')
            )
            ->where($db->quoteName('pos.published') . ' = 1')
            ->order([
                $db->quoteName('pos.ordering'),
                $db->quoteName('pos.name'),
            ]);
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $items = [];
        }

        $options = [];

        foreach ($items as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
