<?php
namespace Diddipoeler\Module\SportsManagementCountRekord\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class CountRekordHelper
{
    public function getData(Registry $params, object $module): array
    {
        if (!(int) $params->get('jsm_stat_spielpaarungen', 0)) {
            return [];
        }

        $db = $this->database($params);
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

    private function database(Registry $params): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            $helperFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';

            if (is_file($helperFile)) {
                require_once $helperFile;
            }
        }

        try {
            if (class_exists('sportsmanagementHelper', false)) {
                $db = \sportsmanagementHelper::getDBConnection(
                    true,
                    (int) $params->get('cfg_which_database', 0)
                );

                if ($db instanceof DatabaseInterface) {
                    return $db;
                }
            }
        } catch (\Throwable) {
            // Fall back to Joomla's container database connection.
        }

        return Factory::getContainer()->get(DatabaseInterface::class);
    }
}
