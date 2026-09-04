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
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class CountRekordHelper
{
    public function getData(Registry $params, object $module, DatabaseInterface $fallbackDatabase): array
    {
        if (!(int) $params->get('jsm_stat_spielpaarungen', 0)) {
            return [];
        }

        $db = $this->database($params, $fallbackDatabase);
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match'));
        $db->setQuery($query);

        $count = (int) $db->loadResult();
        $target = max(0, (int) $params->get('jsm_stat_paarungen', 0));
        $difference = $target - $count;

        return [(object) [
            'image' => 'modules/' . $module->module . '/images/matches.png',
            'anzahl' => $count,
            'anzahlbis' => $target,
            'anzahldiff' => $difference,
            'text' => Text::sprintf(
                'SHOW_MATCHES_DIFF',
                '<strong>' . number_format($difference, 0, ',', '.') . '</strong>',
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
