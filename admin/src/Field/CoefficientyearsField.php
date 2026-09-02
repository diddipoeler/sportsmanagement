<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class CoefficientyearsField extends SportsManagementListField
{
    protected $type = 'coefficientyears';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('season'))
            ->from($db->quoteName('#__sportsmanagement_uefawertung'))
            ->group($db->quoteName('season'))
            ->order($db->quoteName('season') . ' DESC');

        try {
            $db->setQuery($query);
            $seasons = $db->loadColumn() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'error'
            );
            $seasons = [];
        }

        $options = [];

        foreach ($seasons as $season) {
            $season = (string) $season;
            $options[] = (object) [
                'value' => $season,
                'text' => $season,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
