<?php
/**
 * Joomla 5/6 time form validation rule.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Rule;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;

/**
 * Validates the historical SportsManagement HH:MM field while allowing
 * an empty value when the XML field is not required.
 */
final class TimeRule extends FormRule
{
    protected $regex = '^[0-9]{1,2}:[0-9]{1,2}$';

    public function test(
        \SimpleXMLElement $element,
        $value,
        $group = null,
        ?Registry $input = null,
        ?Form $form = null
    ) {
        if ($value === null || $value === '') {
            return true;
        }

        return parent::test($element, $value, $group, $input, $form);
    }
}
