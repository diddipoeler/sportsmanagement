<?php
/**
 * Joomla 5/6 native project position list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class ProjectpositionlistField extends SportsManagementListField
{
    protected $type = 'projectpositionlist';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $projectId = (int) $app->getUserState($option . '.pid', 0);
        $personType = $app->getUserState($option . '.persontype', 0);

        if ($projectId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pp.id', 'value'),
                $db->quoteName('pos.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_position', 'pp')
                . ' ON ' . $db->quoteName('pp.position_id') . ' = ' . $db->quoteName('pos.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_sports_type', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('pos.sports_type_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('pp.project_id') . ' = ' . $db->quoteName('ppp.project_id')
            )
            ->where($db->quoteName('pp.project_id') . ' = ' . $projectId);

        if ($personType !== null && $personType !== '') {
            $query->where($db->quoteName('pos.persontype') . ' = ' . (int) $personType);
        }

        $query->group([
            $db->quoteName('pp.id'),
            $db->quoteName('pos.name'),
            $db->quoteName('pos.ordering'),
        ])->order([
            $db->quoteName('pos.ordering'),
            $db->quoteName('pos.name'),
        ]);
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            $app->enqueueMessage($e->getMessage(), 'error');
            $items = [];
        }

        $options = [];

        foreach ($items as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
