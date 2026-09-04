<?php
/**
 * Joomla 5/6 native sort order field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;

final class SortorderField extends ListField
{
    protected $type = 'sortorder';

    protected function getOptions(): array
    {
        $options = [];
        $maximum = max(0, (int) ComponentHelper::getParams('com_sportsmanagement')->get('template_sort_orders', 0));

        for ($order = 1; $order <= $maximum; ++$order) {
            $options[] = (object) [
                'value' => (string) $order,
                'text' => (string) $order,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
