<?php
/**
 * Joomla 5/6 data helper for the SportsManagement TrainingsData module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) 2015 diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementTrainingsData\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

final class TrainingsDataHelper
{
    public function getData(Registry $params, DatabaseInterface $fallbackDatabase): array
    {
        $teamId = self::positiveId($params->get('teams', 0));

        if ($teamId <= 0) {
            return [];
        }

        try {
            $db = $this->database($params, $fallbackDatabase);
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('dayofweek'),
                    $db->quoteName('time_start'),
                    $db->quoteName('time_end'),
                    $db->quoteName('place'),
                    $db->quoteName('notes'),
                ])
                ->from($db->quoteName('#__sportsmanagement_team_trainingdata'))
                ->where($db->quoteName('team_id') . ' = :teamId')
                ->bind(':teamId', $teamId, ParameterType::INTEGER)
                ->order([
                    $db->quoteName('dayofweek') . ' ASC',
                    $db->quoteName('time_start') . ' ASC',
                ]);
            $db->setQuery($query);

            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            // Log remote/database failures server-side without exposing SQL
            // or connection information to frontend site visitors.
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');

            return [];
        }
    }

    /** Parse Joomla's optional ID:alias without accepting partial numeric IDs. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if (!preg_match('/^([0-9]+)(?::[\pL\pN_-]+)?$/Du', $raw, $matches)) {
            return 0;
        }

        $decimal = ltrim($matches[1], '0');
        $decimal = $decimal === '' ? '0' : $decimal;
        $max = (string) PHP_INT_MAX;

        if ($decimal === '0'
            || strlen($decimal) > strlen($max)
            || (strlen($decimal) === strlen($max) && strcmp($decimal, $max) > 0)) {
            return 0;
        }

        return (int) $decimal;
    }

    private function database(Registry $params, DatabaseInterface $fallbackDatabase): DatabaseInterface
    {
        return SportsManagementDatabaseResolver::resolve(
            $fallbackDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
