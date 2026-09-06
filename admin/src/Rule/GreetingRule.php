<?php
/**
 * Joomla 5/6 greeting form validation rule.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Rule;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormRule;

/** Server-side validation rule for the sample greeting field. */
final class GreetingRule extends FormRule
{
    protected $regex = '^[^0-9]+$';
}
