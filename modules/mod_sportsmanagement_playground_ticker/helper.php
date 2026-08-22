<?php
/** Joomla 5/6 compatibility helper for the SportsManagement playground ticker module. */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class modJSMPlaygroundTicker
{
    public static function getData($params): array
    {
        self::ensureSportsManagementHelper();

        $app = Factory::getApplication();
        $input = $app->getInput();
        $projectId = (int) $params->get('p', 0);
        $limit = max(1, (int) $params->get('limit', 1));
        $whichDatabase = $input->getInt('cfg_which_database', 0);
        $db = sportsmanagementHelper::getDBConnection(true, $whichDatabase);
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pg.id', 'id_playground'),
                $db->quoteName('pg.name', 'playground_name'),
                $db->quoteName('pg.name'),
                $db->quoteName('pg.address'),
                $db->quoteName('pg.zipcode'),
                $db->quoteName('pg.city'),
                $db->quoteName('pg.country'),
                $db->quoteName('pg.club_id'),
                $db->quoteName('pg.extended'),
                $db->quoteName('pg.latitude'),
                $db->quoteName('pg.longitude'),
                $db->quoteName('pg.state'),
                $db->quoteName('pg.picture'),
                $db->quoteName('pg.website'),
                $db->quoteName('pg.max_visitors'),
                $db->quoteName('cl.name', 'club_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_playground', 'pg'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'cl')
                . ' ON ' . $db->quoteName('cl.id') . ' = ' . $db->quoteName('pg.club_id')
            );

        if ($projectId > 0) {
            $query->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.standard_playground') . ' = ' . $db->quoteName('pg.id')
            )
                ->where($db->quoteName('pt.project_id') . ' = ' . $projectId);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        $byId = [];

        foreach ($rows as $row) {
            $byId[(int) $row->id_playground] = $row;
        }

        $playgrounds = array_values($byId);

        if (count($playgrounds) <= $limit) {
            return $playgrounds;
        }

        $keys = array_rand($playgrounds, $limit);
        $keys = is_array($keys) ? $keys : [$keys];

        return array_values(array_map(static fn (int $key) => $playgrounds[$key], $keys));
    }

    public static function getEstadios_Proyecto($params): array
    {
        return self::getData($params);
    }

    private static function ensureSportsManagementHelper(): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($file)) {
                require_once $file;
            }
        }

        if (!class_exists('sportsmanagementHelper')) {
            throw new RuntimeException('SportsManagement database helper is not available.');
        }
    }
}
