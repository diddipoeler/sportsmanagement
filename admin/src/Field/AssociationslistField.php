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

/** Joomla 5/6-native replacement for the historical associationslist field. */
final class AssociationslistField extends SportsManagementListField
{
    protected $type = 'AssociationsList';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $view = $input->getCmd('view');
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $filter = $input->post->get('filter', [], 'array');

        $country = trim((string) ($filter['search_nation'] ?? ''));

        if ($view === 'projects') {
            $country = $country !== ''
                ? $country
                : trim((string) $app->getUserState($option . '.projects_search_nation', ''));

            return array_merge(parent::getOptions(), $this->getCountryOptions($country, false));
        }

        if ($view === 'leagues' || $view === 'clubs') {
            $country = $country !== ''
                ? $country
                : trim((string) $app->getUserState($option . '.clubnation', ''));

            return array_merge(parent::getOptions(), $this->getCountryOptions($country, true));
        }

        $selectedId = $input->get('id', 0, 'raw');

        if (is_array($selectedId)) {
            $selectedId = reset($selectedId) ?: 0;
        }

        $selectedId = (int) $selectedId;

        if ($selectedId <= 0) {
            return parent::getOptions();
        }

        $targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targettable'] ?? ''));

        if ($targetTable === '') {
            return parent::getOptions();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('country'))
            ->from($db->quoteName('#__sportsmanagement_' . $targetTable))
            ->where($db->quoteName('id') . ' = ' . $selectedId);
        $db->setQuery($query);
        $country = trim((string) $db->loadResult());

        if ($country === '') {
            return parent::getOptions();
        }

        return array_merge(parent::getOptions(), $this->getCountryTreeOptions($country));
    }

    private function getCountryOptions(string $country, bool $topLevelOnly): array
    {
        if ($country === '') {
            return [];
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations'))
            ->where($db->quoteName('country') . ' = ' . $db->quote($country))
            ->order($db->quoteName('name'));

        if ($topLevelOnly) {
            $query->where($db->quoteName('parent_id') . ' = 0');
        }

        $db->setQuery($query);
        $options = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return $options;
    }

    private function getCountryTreeOptions(string $country): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('parent_id'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_associations'))
            ->where($db->quoteName('country') . ' = ' . $db->quote($country))
            ->order([
                $db->quoteName('ordering'),
                $db->quoteName('name'),
            ]);
        $db->setQuery($query);

        $children = [];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $children[(int) $item->parent_id][] = $item;
        }

        $options = [];
        $this->appendChildren($options, $children, 0, 0);

        return $options;
    }

    private function appendChildren(array &$options, array $children, int $parentId, int $level): void
    {
        if ($level > 10 || empty($children[$parentId])) {
            return;
        }

        foreach ($children[$parentId] as $item) {
            $id = (int) $item->id;
            $indent = str_repeat('...', $level);
            $prefix = $level > 0 ? "\u{00A0}" : '';
            $options[] = (object) [
                'value' => (string) $id,
                'text' => $indent . $prefix . (string) $item->name,
            ];
            $this->appendChildren($options, $children, $id, $level + 1);
        }
    }
}
