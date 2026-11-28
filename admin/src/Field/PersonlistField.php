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

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    /**
     * Load person identifiers and formatted surname/first-name labels.
     * Orders records by surname and first name and skips malformed records
     * before combining them with the options declared in the XML field.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
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

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->id ?? null) && !is_string($item->id ?? null))
                || !is_string($item->lastname ?? '')
                || !is_string($item->firstname ?? '')) {
                continue;
            }

            $label = trim(($item->lastname ?? '') . ' - ' . ($item->firstname ?? ''), " -\t\n\r\0\x0B");
            $options[] = (object) [
                'value' => (string) $item->id,
                'text' => $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
