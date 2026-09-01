<?php
namespace Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class PlaygroundTickerHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app): array
    {
        $projectId = (int) $params->get('p', 0);
        $limit = max(1, (int) $params->get('limit', 1));
        $whichDatabase = $this->databaseSelector($params, $app);
        $db = SportsManagementDatabaseResolver::resolve(
            \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class),
            $whichDatabase
        );
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
        $unique = [];

        foreach ($rows as $row) {
            $unique[(int) $row->id_playground] = $row;
        }

        $playgrounds = array_values($unique);

        if (count($playgrounds) <= $limit) {
            return $playgrounds;
        }

        $keys = array_rand($playgrounds, $limit);
        $keys = is_array($keys) ? $keys : [$keys];

        return array_values(array_map(static fn (int $key): object => $playgrounds[$key], $keys));
    }

    public function getPictureServer(Registry $params, CMSApplicationInterface $app): string
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $useExternal = (bool) $componentParams->get('cfg_which_database', false)
            || $this->databaseSelector($params, $app) === 1;

        if ($useExternal) {
            $server = trim((string) $componentParams->get('cfg_which_database_server', ''));

            if ($server !== '') {
                return rtrim($server, '/\\') . '/';
            }
        }

        return Uri::root();
    }

    private function databaseSelector(Registry $params, CMSApplicationInterface $app): int
    {
        return (int) $params->get(
            'cfg_which_database',
            $app->getInput()->getInt('cfg_which_database', 0)
        ) === 1 ? 1 : 0;
    }
}
