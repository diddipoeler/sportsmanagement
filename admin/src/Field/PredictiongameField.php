<?php
/**
 * Joomla 5/6 native prediction game field.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** Prediction game selector using the historic numeric prediction-game ID. */
final class PredictiongameField extends SportsManagementListField
{
    protected $type = 'predictiongame';

    protected function getOptions(): array
    {
        $db = $this->getSportsManagementDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_prediction_game'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('name'));
        $db->setQuery($query);

        $options = [
            (object) [
                'value' => '',
                'text' => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT'),
            ],
        ];

        foreach ($db->loadObjectList() ?: [] as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => "\u{00A0}" . (string) $item->text . ' (' . (int) $item->value . ')',
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
