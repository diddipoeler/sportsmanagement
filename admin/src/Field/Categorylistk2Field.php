<?php
/**
 * Joomla 5/6 K2 category list form field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

final class Categorylistk2Field extends ListField
{
    protected $type = 'categorylistk2';

    /**
     * Load non-trashed categories from the optional K2 database table.
     * Uses the primary Joomla database and retains inherited XML choices
     * when K2 is absent or the database service or query is unavailable.
     * @return array<int,object> Valid category selection options.
     */
    protected function getOptions(): array
    {
        SportsManagementAdministratorApplicationResolver::resolve();

        try {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
            if (!$db instanceof DatabaseInterface) {
                return parent::getOptions();
            }
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('id', 'value'),
                    $db->quoteName('name', 'text'),
                ])
                ->from($db->quoteName('#__k2_categories'))
                ->where($db->quoteName('trash') . ' = 0')
                ->order($db->quoteName('name'));

            $db->setQuery($query);
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable) {
            $items = [];
        }

        $options = [];

        foreach (is_array($items) ? $items : [] as $item) {
            if (!is_object($item) || (!is_int($item->value ?? null) && !is_string($item->value ?? null))
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
