<?php
/**
 * Joomla 5/6 native user list field.
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

final class UserlistField extends ListField
{
    protected $type = 'userlist';

    /**
     * Load Joomla user IDs and names from the primary Joomla database.
     * Uses the container database rather than an alternate sports database
     * and combines valid user rows with inherited XML options.
     * @return array Joomla selection options.
     */
    protected function getOptions(): array
    {
        SportsManagementAdministratorApplicationResolver::resolve();

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        if (!$db instanceof DatabaseInterface) {
            return parent::getOptions();
        }

        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__users'))
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [];

        $rows = $db->loadObjectList();
        foreach (is_array($rows) ? $rows : [] as $item) {
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
