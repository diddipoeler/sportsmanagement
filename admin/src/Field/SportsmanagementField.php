<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Native selector for records from the historic #__sportsmanagement table.
 */
final class SportsmanagementField extends SportsManagementListField
{
    protected $type = 'sportsmanagement';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.greeting'),
                $db->quoteName('c.title', 'category'),
                $db->quoteName('s.catid'),
            ])
            ->from($db->quoteName('#__sportsmanagement', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('s.catid') . ' = ' . $db->quoteName('c.id')
            );
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $message) {
            $label = (string) $message->greeting;

            if ((int) $message->catid > 0) {
                $label .= ' (' . (string) $message->category . ')';
            }

            $options[] = HTMLHelper::_('select.option', (int) $message->id, $label);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
