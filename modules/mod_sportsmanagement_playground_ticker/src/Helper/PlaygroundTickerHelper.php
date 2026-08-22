<?php
namespace Diddipoeler\Module\SportsManagementPlaygroundTicker\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

final class PlaygroundTickerHelper
{
    public function getData(Registry $params, CMSApplicationInterface $app): array
    {
        $this->ensureSportsManagementHelper();

        $projectId = (int) $params->get('p', 0);
        $limit = max(1, (int) $params->get('limit', 1));
        $whichDatabase = (int) $params->get(
            'cfg_which_database',
            $app->getInput()->getInt('cfg_which_database', 0)
        );

        $db = \sportsmanagementHelper::getDBConnection(true, $whichDatabase);
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

    public function getPictureServer(): string
    {
        $componentParams = ComponentHelper::getParams('com_sportsmanagement');

        if ((bool) $componentParams->get('cfg_which_database', false)) {
            $server = trim((string) $componentParams->get('cfg_which_database_server', ''));

            if ($server !== '') {
                return rtrim($server, '/\\') . '/';
            }
        }

        return Uri::root();
    }

    private function ensureSportsManagementHelper(): void
    {
        if (!class_exists('sportsmanagementHelper')) {
            $file = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($file)) {
                require_once $file;
            }
        }

        if (!class_exists('sportsmanagementHelper')) {
            throw new \RuntimeException('SportsManagement database helper is not available.');
        }
    }
}
