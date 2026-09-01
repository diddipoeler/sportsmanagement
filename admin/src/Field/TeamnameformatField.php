<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

/** Joomla 5/6 team-name display format field. */
final class TeamnameformatField extends ListField
{
    protected $type = 'teamnameformat';

    protected function getOptions(): array
    {
        $options = [
            (object) [
                'value' => '0',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_SHORT'),
            ],
            (object) [
                'value' => '1',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_MEDIUM'),
            ],
            (object) [
                'value' => '2',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TEAM_NAME_FORMAT_FULL'),
            ],
        ];

        return array_merge(parent::getOptions(), $options);
    }
}
