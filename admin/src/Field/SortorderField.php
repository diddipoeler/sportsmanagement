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

    /**
     * Generate numeric ordering choices from the component configuration.
     * Rejects malformed settings and bounds the option count to prevent
     * excessive allocation from corrupted configuration values.
     * @return array<int,object> Inherited and numeric ordering options.
     */
    protected function getOptions(): array
    {
        $options = [];
        $rawMaximum = ComponentHelper::getParams('com_sportsmanagement')->get('template_sort_orders', 0);
        $maximum = (is_int($rawMaximum) || is_string($rawMaximum))
            ? filter_var($rawMaximum, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])
            : false;
        $maximum = $maximum === false ? 0 : min(10000, $maximum);

        for ($order = 1; $order <= $maximum; ++$order) {
            $options[] = (object) [
                'value' => (string) $order,
                'text' => (string) $order,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
