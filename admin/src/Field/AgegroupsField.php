<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/** Age groups filtered by the sport type of the currently edited team. */
final class AgegroupsField extends SportsManagementListField
{
    protected $type = 'agegroups';

    protected function getOptions(): array
    {
        $options = parent::getOptions();
        $teamId = Factory::getApplication()->getInput()->getInt('id', 0);

        if ($teamId <= 0) {
            return $options;
        }

        $db = $this->getSportsManagementDatabase();
        $teamQuery = $db->getQuery(true)
            ->select($db->quoteName('sports_type_id'))
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('id') . ' = ' . $teamId);
        $db->setQuery($teamQuery, 0, 1);
        $sportsTypeId = (int) $db->loadResult();

        if ($sportsTypeId <= 0) {
            return $options;
        }

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_agegroup'))
            ->where($db->quoteName('sportstype_id') . ' = ' . $sportsTypeId)
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => "\u{00A0}" . Text::_((string) $item->text) . ' (' . (int) $item->value . ')',
            ];
        }

        return $options;
    }
}
