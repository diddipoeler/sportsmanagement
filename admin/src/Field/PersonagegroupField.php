<?php
/**
 * Joomla 5/6 native person age group field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

final class PersonagegroupField extends SportsManagementListField
{
    protected $type = 'personagegroup';

    protected function getOptions(): array
    {
        $targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targettable'] ?? ''));
        $selectedId = (int) $this->form->getValue('id');

        if ($targetTable === '' || $selectedId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $target = '#__sportsmanagement_' . $targetTable;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('a.id'),
                $db->quoteName('a.name'),
                $db->quoteName('a.age_from'),
                $db->quoteName('a.age_to'),
                $db->quoteName('a.deadline_day'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup', 'a'))
            ->join(
                'INNER',
                $db->quoteName($target, 't')
                . ' ON ' . $db->quoteName('t.sports_type_id') . ' = ' . $db->quoteName('a.sportstype_id')
            )
            ->where($db->quoteName('t.id') . ' = ' . $selectedId)
            ->order($db->quoteName('a.name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $label = (string) $item->name
                . ' von: ' . (string) $item->age_from
                . ' bis: ' . (string) $item->age_to
                . ' Stichtag: ' . (string) $item->deadline_day;
            $options[] = (object) [
                'value' => (string) $item->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
