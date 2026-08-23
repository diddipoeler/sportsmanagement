<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** Joomla 5/6 team-name display format field. */
final class TeamnameformatField extends ListField
{
    protected $type = 'teamnameformat';

    protected function getOptions(): array
    {
        $options = [
            HTMLHelper::_('select.option', 0, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_SHORT')),
            HTMLHelper::_('select.option', 1, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_MEDIUM')),
            HTMLHelper::_('select.option', 2, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_FULL')),
        ];

        return array_merge(parent::getOptions(), $options);
    }
}
