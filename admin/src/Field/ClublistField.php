<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class ClublistField extends SportsManagementListField
{
    protected $type = 'clublist';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $sportType = trim((string) ($this->element['target'] ?? ''));
        $clubId = $input->getInt('club_id', 0)
            ?: (int) $app->getUserState('com_sportsmanagement.club_id', 0);

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('c.id', 'value'),
                $db->quoteName('c.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id')
            );

        if ($clubId > 0) {
            $query->where($db->quoteName('c.id') . ' = ' . $clubId);
        }

        if ($sportType !== '') {
            $query->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 'st')
                . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('t.sports_type_id')
            );
            $query->where($db->quoteName('st.name') . ' = ' . $db->quote($sportType));
        }

        $query->group([
            $db->quoteName('c.id'),
            $db->quoteName('c.name'),
        ])->order($db->quoteName('c.name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
