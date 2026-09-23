<?php
/**
 * Joomla 5/6 LiveScore connector for the SportsManagement calendar module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    throw new \RuntimeException('SportsManagement site application resolver could not be loaded.', 500);
}

final class LivescoreConnector extends JSMCalendar
{
    private Registry $xparams;
    private string $connectorPrefix = '';

    public function appendMatches(array &$caldates, Registry &$params, array &$matches): array
    {
        $this->xparams = $params;
        $this->connectorPrefix = (string) $params->get('prefix', '');

        return $this->formatRows($this->getRows($caldates), $matches);
    }

    private function getRows(array $caldates, string $ordering = 'ASC'): array
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        if (!$app->isClient('site')) {
            throw new \RuntimeException('SportsManagement Calendar LiveScore requires the Joomla site application.', 500);
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $table = $this->connectorPrefix !== ''
            ? str_replace('#__', $this->connectorPrefix, '#__livescore_games')
            : '#__livescore_games';
        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';

        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName($table));

        if (!empty($caldates['start'])) {
            $query->where($db->quoteName('mdate') . ' >= ' . $db->quote((string) $caldates['start']));
        }
        if (!empty($caldates['end'])) {
            $query->where($db->quoteName('mdate') . ' <= ' . $db->quote((string) $caldates['end']));
        }

        $query->order($db->quoteName('mdate') . ' ' . $direction);

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    private function formatRows(array $rows, array &$matches): array
    {
        $newRows = [];

        foreach ($rows as $key => $row) {
            $formatted = [
                'type' => 'ls',
                'date' => (string) ($row->mdate ?? ''),
                'result' => 'LIVE!',
                'headingtitle' => parent::jl_utf8_convert('LiveScore'),
                'homename' => parent::jl_utf8_convert((string) ($row->heim ?? '')),
                'homepic' => '',
                'awaypic' => '',
                'awayname' => parent::jl_utf8_convert((string) ($row->gast ?? '')),
                'matchcode' => (string) ($row->saison ?? ''),
                'project_id' => 'LIVE!',
            ];

            $newRows[$key] = $formatted;
            $matches[] = $formatted;
        }

        return $newRows;
    }
}
