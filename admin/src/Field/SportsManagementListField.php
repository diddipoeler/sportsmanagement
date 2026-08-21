<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;

abstract class SportsManagementListField extends ListField
{
    use SportsManagementDatabaseTrait;
}
