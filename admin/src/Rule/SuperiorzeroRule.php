<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Rule;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormRule;

/** Joomla 5/6 rule requiring an integer greater than zero. */
final class SuperiorzeroRule extends FormRule
{
    protected $regex = '^[1-9][0-9]*$';
}
