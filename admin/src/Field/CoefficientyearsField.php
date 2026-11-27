<?php
/**
 * Joomla 5/6 coefficient years form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;

final class CoefficientyearsField extends SportsManagementListField
{
    protected $type = 'coefficientyears';

    /**
     * Load distinct coefficient seasons in descending order.
     * Retains inherited options when the optional coefficient table cannot
     * be queried and logs the exception class without exposing SQL details.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('season'))
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->group($db->quoteName('season'))
            ->order($db->quoteName('season') . ' DESC');

        try {
            $db->setQuery($query);
            $seasons = $db->loadColumn() ?: [];
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::WARNING, 'jsmerror');
            $seasons = [];
        }

        $options = [];

        foreach (is_array($seasons) ? $seasons : [] as $season) {
            if (!is_string($season) && !is_int($season)) {
                continue;
            }
            $season = (string) $season;
            $options[] = (object) [
                'value' => $season,
                'text' => $season,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
