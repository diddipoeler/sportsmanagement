<?php
/**
 * Joomla 5/6 native favorite team field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\Database\ParameterType;

final class FavteamField extends SportsManagementListField
{
    protected $type = 'Favteam';

    /**
     * Load teams participating in the active project for favourite-team selection.
     * Resolves the administrator project context and validates the ID before
     * joining season and project memberships through a bound query.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $app = SportsManagementAdministratorApplicationResolver::resolve();

        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $rawId = $input->getCmd('layout') === 'edit'
            ? $input->getInt('id', 0)
            : $app->getUserState($option . '.pid', 0);
        $projectId = (is_int($rawId) || is_string($rawId))
            ? filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id', 'value'),
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
            ->where($db->quoteName('pt.project_id') . ' = :projectId')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name'));
        $db->setQuery($query);

        return array_merge(parent::getOptions(), $db->loadObjectList() ?: []);
    }
}
