<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

final class ProjectteamlistField extends SportsManagementListField
{
    protected $type = 'projectteamlist';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $projectId = (int) $app->getUserState($option . '.pid', 0);

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId)
            ->order($db->quoteName('t.name'));
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
