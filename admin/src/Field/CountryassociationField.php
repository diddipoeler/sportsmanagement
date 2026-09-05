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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

final class CountryassociationField extends SportsManagementListField
{
    protected $type = 'countryassociation';

    protected function getOptions(): array
    {
        $varname = trim((string) ($this->element['varname'] ?? ''));
        $targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targettable'] ?? ''));

        if ($varname === '' || $targetTable === '') {
            return parent::getOptions();
        }

        $selectedId = Factory::getApplication()->getInput()->get($varname, null, 'raw');

        if (is_array($selectedId)) {
            $selectedId = reset($selectedId) ?: 0;
        }

        $selectedId = (int) $selectedId;

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
            ->where($db->quoteName('wt.id') . ' = ' . $selectedId)
            ->order($db->quoteName('t.name'));

        try {
            $db->setQuery($query);
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage(
                Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
                'notice'
            );
            $items = [];
        }

        $options = [];

        foreach ($items as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
