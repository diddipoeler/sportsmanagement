<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/** Joomla 5/6-native league selector used by administrator list filters. */
final class LeagueField extends SportsManagementListField
{
    protected $type = 'League';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $filter = $input->post->get('filter', [], 'array');
        $country = trim((string) ($filter['search_nation'] ?? $app->getUserState('com_sportsmanagement.projects_search_nation', '')));
        $association = (int) ($filter['search_associations_leagues'] ?? $app->getUserState('com_sportsmanagement.projects_search_associations_leagues', 0));

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_league'))
            ->order($db->quoteName('name') . ' ASC');

        if ($country !== '') {
            $query->where($db->quoteName('country') . ' = ' . $db->quote($country));
        }

        if ($association > 0) {
            $query->where($db->quoteName('associations') . ' = ' . $association);
        }

        $db->setQuery($query);
        $options = [];

        foreach ($db->loadObjectList() ?: [] as $league) {
            $options[] = HTMLHelper::_('select.option', $league->value, $league->text);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
