<?php
/**
 * Native Joomla 5/6 prediction games field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

final class PredictiongamesField extends SportsManagementListField
{
    protected $type = 'predictiongames';

    /**
     * Read prediction game IDs and names for Joomla administrator filters.
     *
     * Uses the configured SportsManagement database query builder, orders
     * games by name and merges the query results with inherited Joomla
     * select options to retain existing backend form behaviour.
     *
     * @return array<int,object> Game ID/name choices and parent options.
     */
    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        return array_merge(parent::getOptions(), $db->loadObjectList() ?: []);
    }
}
