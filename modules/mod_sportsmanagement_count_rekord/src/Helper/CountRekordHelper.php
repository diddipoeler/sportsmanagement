<?php
/**
 * Joomla 5/6 data helper for mod_sportsmanagement_count_rekord.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementCountRekord\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class CountRekordHelper
{
    public function getData(Registry $params, object $module, DatabaseInterface $fallbackDatabase): array
    {
        if (!(int) $params->get('jsm_stat_spielpaarungen', 0)) {
            return [];
        }

        try {
            $db = $this->database($params, $fallbackDatabase);
            $query = $db->createQuery()
                ->select('COUNT(*)')
                ->from($db->quoteName('#__sportsmanagement_match'));
            $db->setQuery($query);
            $count = max(0, (int) $db->loadResult());
        } catch (\Throwable $e) {
            // A missing match table or unavailable external database must not
            // take down the site; details belong in the Joomla error log.
            Log::add('SportsManagement Count Record query failed: ' . $e->getMessage(), Log::ERROR, 'com_sportsmanagement');

            return [];
        }

        $target = max(0, (int) $params->get('jsm_stat_paarungen', 0));
        $difference = max(0, $target - $count);
        $reached = $count >= $target;

        return [(object) [
            'image' => 'modules/' . $module->module . '/images/matches.png',
            'anzahl' => $count,
            'anzahlbis' => $target,
            'anzahldiff' => $difference,
            'text' => Text::sprintf(
                $reached ? 'SHOW_MATCHES_TARGET_REACHED' : 'SHOW_MATCHES_DIFF',
                '<strong>' . number_format($reached ? $count : $difference, 0, ',', '.') . '</strong>',
                '<strong>' . number_format($target, 0, ',', '.') . '</strong>'
            ),
        ]];
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
