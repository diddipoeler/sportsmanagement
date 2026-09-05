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

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\Database\DatabaseInterface;

final class Categorylistk2Field extends ListField
{
    protected $type = 'categorylistk2';

    protected function getOptions(): array
    {
        $app = Factory::getApplication();

        if (!$app instanceof AdministratorApplication) {
            throw new \RuntimeException('SportsManagement administrator application is unavailable.');
        }

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__k2_categories'))
            ->where($db->quoteName('trash') . ' = 0')
            ->order($db->quoteName('name'));

        try {
            $db->setQuery($query);
            $items = $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
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
