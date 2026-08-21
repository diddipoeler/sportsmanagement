<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class FinaltablerankField extends ListField
{
    protected $type = 'finaltablerank';

    protected function getOptions(): array
    {
        $options = [];

        for ($rank = 1; $rank <= 40; ++$rank) {
            $options[] = HTMLHelper::_(
                'select.option',
                $rank,
                Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_FINALTABLERANK') . ' - ' . $rank
            );
        }

        return array_merge(parent::getOptions(), $options);
    }
}
