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

use Joomla\Database\ParameterType;

final class PersonagegroupField extends SportsManagementListField
{
    protected $type = 'personagegroup';

    /**
     * Load age groups matching the sport of the selected record.
     * Validates the configured table suffix and record ID, binds the ID
     * and formats age boundaries and deadline information for the selector.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $targetTable = (string) ($this->element['targettable'] ?? '');
        $rawId = $this->form->getValue('id');
        $selectedId = (is_int($rawId) || is_string($rawId))
            ? filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

        if (strlen($targetTable) > 64 || preg_match('/^[A-Za-z0-9_]+$/D', $targetTable) !== 1 || $selectedId === false) {
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
            ->where($db->quoteName('t.id') . ' = :selectedId')
            ->bind(':selectedId', $selectedId, ParameterType::INTEGER)
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
