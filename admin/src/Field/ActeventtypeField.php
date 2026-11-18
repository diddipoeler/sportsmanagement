<?php
/**
 * Joomla 5/6 native active event type field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

final class ActeventtypeField extends SportsManagementListField
{
    protected $type = 'acteventtype';

    /**
     * Select event types for the current record's sport in an admin form.
     *
     * Validates the XML-defined target table as a short unmodified SQL
     * identifier. Invalid table names or missing IDs return parent options
     * without querying an unintended table. Binds the current record ID and
     * translates the matching sport-specific event types.
     *
     * @return array<int,object> Default and matching event selector choices.
     */
    protected function getOptions(): array
    {
        $rawTarget = $this->element['targettable'] ?? '';
        $targetTable = is_string($rawTarget) || $rawTarget instanceof \Stringable
            ? (string) $rawTarget : '';

        if ($targetTable === '' || strlen($targetTable) > 64
            || preg_match('/^[A-Za-z0-9_]+$/D', $targetTable) !== 1) {
            return parent::getOptions();
        }
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $selectedId = $app->getInput()->getInt('id', 0);

        if ($selectedId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $target = '#__sportsmanagement_' . $targetTable;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_eventtype', 's'))
            ->join(
                'INNER',
                $db->quoteName($target, 't')
                . ' ON ' . $db->quoteName('t.sports_type_id') . ' = ' . $db->quoteName('s.sports_type_id')
            )
            ->where($db->quoteName('t.id') . ' = :selectedId')
            ->bind(':selectedId', $selectedId, ParameterType::INTEGER)
            ->order($db->quoteName('s.name'));
        $db->setQuery($query);

        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => Text::_((string) $item->text),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
