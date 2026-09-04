<?php
/**
 * Joomla 5/6 native season team person field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class SeasonteampersonField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'checkboxes';

    protected function getInput(): string
    {
        $selectedId = Factory::getApplication()->getInput()->getInt('id', 0);
        $targetTable = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targettable'] ?? ''));
        $targetId = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($this->element['targetid'] ?? ''));

        if ($selectedId <= 0 || $targetTable === '' || $targetId === '') {
            return $this->renderNoResults();
        }

        $db = $this->getSportsManagementDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('stp.season_id'),
                $db->quoteName('stp.team_id'),
                $db->quoteName('t.name', 'teamname'),
                $db->quoteName('s.name', 'seasonname'),
                $db->quoteName('c.logo_big', 'clublogo'),
            ])
            ->from($db->quoteName('#__sportsmanagement_' . $targetTable, 'stp'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_team', 't')
                . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('stp.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_club', 'c')
                . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season', 's')
                . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('stp.season_id')
            )
            ->where($db->quoteName('stp.' . $targetId) . ' = ' . $selectedId)
            ->order($db->quoteName('s.name'));
        $db->setQuery($query);
        $items = $db->loadObjectList() ?: [];

        if ($items === []) {
            return $this->renderNoResults();
        }

        $html = '<table>';
        $imageAttributes = ['width' => '25px', 'height' => '25px'];

        foreach ($items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars((string) $item->seasonname, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . HTMLHelper::_('image', (string) $item->clublogo, '', $imageAttributes) . '</td>';
            $html .= '<td>' . htmlspecialchars((string) $item->teamname, ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }

        return $html . '</table>';
    }

    private function renderNoResults(): string
    {
        return '<div class="alert alert-no-items">'
            . Text::_('JGLOBAL_NO_MATCHING_RESULTS')
            . '</div>';
    }
}
