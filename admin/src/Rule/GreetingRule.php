<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Rule;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormRule;

/** Server-side validation rule for the sample greeting field. */
final class GreetingRule extends FormRule
{
    protected $regex = '^[^0-9]+$';
}
