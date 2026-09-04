<?php
/**
 * Joomla 5/6 federations list field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Joomla 5/6-native replacement for the historical federationslist field.
 *
 * The league filter keeps the old top-level-only behaviour. Other edit views
 * retain the hierarchical federation tree when an item is being edited.
 */
final class FederationslistField extends SportsManagementListField
{
    protected $type = 'FederationsList';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $view = $input->getCmd('view');
        $selectedId = $input->get('id', 0, 'raw');

        if (is_array($selectedId)) {
            $selectedId = reset($selectedId) ?: 0;
        }

        $selectedId = (int) $selectedId;

        if ($view === 'leagues') {
            return array_merge(parent::getOptions(), $this->getTopLevelOptions());
        }

        if ($selectedId <= 0) {
            return parent::getOptions();
        }

        return array_merge(parent::getOptions(), $this->getTreeOptions());
    }

    private function getTopLevelOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
            ->where($db->quoteName('parent_id') . ' = 0')
            ->order($db->quoteName('name'));
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

    private function getTreeOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('parent_id'),
                $db->quoteName('name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_federations'))
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
