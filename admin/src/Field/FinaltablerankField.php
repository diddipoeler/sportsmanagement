<?php
/**
 * Joomla 5/6 native final table rank field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

final class FinaltablerankField extends ListField
{
    protected $type = 'finaltablerank';

    protected function getOptions(): array
    {
        $options = [];

        for ($rank = 1; $rank <= 40; ++$rank) {
            $options[] = (object) [
                'value' => (string) $rank,
                'text' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAM_FINALTABLERANK') . ' - ' . $rank,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
