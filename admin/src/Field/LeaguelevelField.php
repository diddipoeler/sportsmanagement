<?php
/**
 * Joomla 5/6 native league-level field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

final class LeaguelevelField extends SportsManagementListField
{
    protected $type = 'Leaguelevel';

    /**
     * Create the legacy league, cup and tournament level choices.
     *
     * Builds translated name/number options for league levels 1–20,
     * cup levels 21–40 and tournament levels 41–50, then merges the
     * fixed list with the options required by Joomla's list-field base.
     *
     * @return array<int,object> Localised competition level options.
     */
    protected function getOptions(): array
    {
        $options = [];

        for ($level = 1; $level <= 20; ++$level) {
            $options[] = (object) [
                'value' => (string) $level,
                'text' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_LEVEL') . ' - ' . $level,
            ];
        }

        for ($level = 21, $label = 1; $level <= 40; ++$level, ++$label) {
            $options[] = (object) [
                'value' => (string) $level,
                'text' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_POKAL_LEVEL') . ' - ' . $label,
            ];
        }

        for ($level = 41, $label = 1; $level <= 50; ++$level, ++$label) {
            $options[] = (object) [
                'value' => (string) $level,
                'text' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_TOURNEMENT_LEVEL') . ' - ' . $label,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
