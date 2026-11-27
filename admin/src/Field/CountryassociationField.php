<?php
/**
 * Joomla 5/6 country association form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\Database\ParameterType;
use Joomla\CMS\Log\Log;

final class CountryassociationField extends SportsManagementListField
{
    protected $type = 'countryassociation';

    /**
     * Load associations sharing the country of the selected record.
     * Validates the configured table identifier and request ID, binds the ID
     * to the native query and preserves inherited options on lookup failure.
     * @return array Joomla selection options for the matching country.
     */
    protected function getOptions(): array
    {
        $varname = trim((string) ($this->element['varname'] ?? ''));
        $targetTable = (string) ($this->element['targettable'] ?? '');

        if ($varname === '' || strlen($targetTable) > 64 || preg_match('/^[A-Za-z0-9_]+$/D', $targetTable) !== 1) {
            return parent::getOptions();
        }

        $selectedId = SportsManagementAdministratorApplicationResolver::resolve()->getInput()->get($varname, null, 'raw');

        if (is_array($selectedId)) {
            $selectedId = array_is_list($selectedId) && count($selectedId) === 1 ? $selectedId[0] : null;
        }

        $selectedId = (is_int($selectedId) || is_string($selectedId))
            ? filter_var($selectedId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) : false;

        if ($selectedId <= 0) {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $target = '#__sportsmanagement_' . $targetTable;
        $query = $db->createQuery()
            ->select([
                $db->quoteName('t.id', 'value'),
                $db->quoteName('t.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations', 't'))
            ->join(
                'INNER',
                $db->quoteName($target, 'wt')
                . ' ON ' . $db->quoteName('wt.country') . ' = ' . $db->quoteName('t.country')
            )
            ->where($db->quoteName('wt.id') . ' = :selectedId')
            ->bind(':selectedId', $selectedId, ParameterType::INTEGER)
            ->order($db->quoteName('t.name'));

        try {
            $db->setQuery($query);
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::NOTICE, 'jsmerror');
            SportsManagementAdministratorApplicationResolver::resolve()->enqueueMessage('SportsManagement country associations could not be loaded.', 'notice');
            $items = [];
        }

        $options = [];

        foreach (is_array($items) ? $items : [] as $item) {
            if (!is_object($item)
                || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
                || !is_string($item->text ?? null)) {
                continue;
            }

            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
