<?php
/**
 * Joomla 5/6 native person list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

final class PersonlistField extends SportsManagementListField
{
    protected $type = 'personlist';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('firstname'),
                $db->quoteName('lastname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person'))
            ->order([
                $db->quoteName('lastname'),
                $db->quoteName('firstname'),
            ]);
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $label = trim((string) $item->lastname . ' - ' . (string) $item->firstname, " -\t\n\r\0\x0B");
            $options[] = (object) [
                'value' => (string) $item->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
