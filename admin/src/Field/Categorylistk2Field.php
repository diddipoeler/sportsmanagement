<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

final class Categorylistk2Field extends ListField
{
    protected $type = 'categorylistk2';

    protected function getOptions(): array
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
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
            $options[] = HTMLHelper::_('select.option', $item->value, $item->text);
        }

        return array_merge(parent::getOptions(), $options);
    }
}
