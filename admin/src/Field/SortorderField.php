<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

final class SortorderField extends ListField
{
    protected $type = 'sortorder';

    protected function getOptions(): array
    {
        $options = [];
        $maximum = max(0, (int) ComponentHelper::getParams('com_sportsmanagement')->get('template_sort_orders', 0));

        for ($order = 1; $order <= $maximum; ++$order) {
            $options[] = HTMLHelper::_('select.option', $order, $order, 'value', 'text');
        }

        return array_merge(parent::getOptions(), $options);
    }
}
