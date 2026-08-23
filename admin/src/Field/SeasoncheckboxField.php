<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class SeasoncheckboxField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'checkboxes';

    protected function getInput(): string
    {
        $target = $this->resolveTarget();

        if ($target === null) {
            return '<div class="alert alert-warning">Invalid season assignment target.</div>';
        }

        [$targetTable, $targetId] = $target;
        $selectedId = Factory::getApplication()->getInput()->getInt('id', 0);
        $db = $this->getSportsManagementDatabase();
        $seasons = $this->loadSimpleOptions($db, '#__sportsmanagement_season', 'name DESC');
        $assignments = $this->loadAssignments($db, $targetTable, $targetId, $selectedId);
        $selectedSeasons = array_map('intval', array_keys($assignments));
        $clubs = [];
        $positions = [];
        $sportsTypeName = '';

        if ($targetTable === 'season_person_id') {
            $clubs = $this->loadSimpleOptions($db, '#__sportsmanagement_club', 'name DESC');
            $positions = $this->loadSimpleOptions($db, '#__sportsmanagement_position', 'name DESC');
            $sportsTypeName = $this->loadPersonSportsTypeName($db, $selectedId);
        }

        $class = 'checkboxes ' . trim((string) ($this->element['class'] ?? ''));
        $html = '<fieldset id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '" class="'
            . htmlspecialchars(trim($class), ENT_QUOTES, 'UTF-8') . '">';
        $html .= '<table class="table table-striped align-middle"><thead><tr>';
        $html .= '<th scope="col">#</th><th scope="col">' . Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SEASON') . '</th>';

        if ($targetTable === 'season_team_id') {
            $html .= '<th scope="col">1 Teamname</th><th scope="col">2 Teamname</th>';
        } elseif ($this->showPersonAssignments($sportsTypeName)) {
            $html .= '<th scope="col">' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB') . '</th>';
            $html .= '<th scope="col">' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION') . '</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($seasons as $index => $season) {
            $seasonId = (int) $season->value;
            $assignment = $assignments[$seasonId] ?? [];
            $checked = in_array($seasonId, $selectedSeasons, true) ? ' checked' : '';
            $checkboxId = $this->id . $index;
            $html .= '<tr><td><input type="checkbox" id="' . htmlspecialchars($checkboxId, ENT_QUOTES, 'UTF-8')
                . '" name="' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '[]" value="' . $seasonId . '"' . $checked . '></td>';
            $html .= '<td><label for="' . htmlspecialchars($checkboxId, ENT_QUOTES, 'UTF-8') . '">'
                . htmlspecialchars(Text::_((string) $season->text), ENT_QUOTES, 'UTF-8') . '</label></td>';

            if ($targetTable === 'season_team_id') {
                $html .= '<td>' . $this->textInput(
                    'jform_teamvalue' . $index,
                    'jform[teamvalue][' . $seasonId . ']',
                    (string) ($assignment['teamname'] ?? '')
                ) . '</td>';
                $html .= '<td>' . $this->textInput(
                    'jform_season_teamname' . $index,
                    'jform[season_teamname][' . $seasonId . ']',
                    (string) ($assignment['season_teamname'] ?? '')
                ) . '</td>';
            } elseif ($this->showPersonAssignments($sportsTypeName)) {
                $html .= '<td>' . $this->selectInput(
                    $clubs,
                    'season_person_club_id[' . $seasonId . ']',
                    'season_person_club_id_' . $seasonId,
                    (int) ($assignment['club_id'] ?? 0),
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB'
                ) . '</td>';
                $html .= '<td>' . $this->selectInput(
                    $positions,
                    'season_person_position_id[' . $seasonId . ']',
                    'season_person_position_id_' . $seasonId,
                    (int) ($assignment['position_id'] ?? 0),
                    'COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION'
                ) . '</td>';
            }

            $html .= '</tr>';
        }

        return $html . '</tbody></table></fieldset>';
    }

    private function resolveTarget(): ?array
    {
        $table = trim((string) ($this->element['targettable'] ?? ''));
        $id = trim((string) ($this->element['targetid'] ?? ''));
        $allowed = [
            'season_team_id' => 'team_id',
            'season_person_id' => 'person_id',
        ];

        return isset($allowed[$table]) && $allowed[$table] === $id ? [$table, $id] : null;
    }

    private function loadSimpleOptions($db, string $table, string $order): array
    {
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName($table))
            ->order($order);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    private function loadAssignments($db, string $targetTable, string $targetId, int $selectedId): array
    {
        if ($selectedId <= 0) {
            return [];
        }

        $fields = $targetTable === 'season_team_id'
            ? ['season_id', 'teamname', 'season_teamname']
            : ['season_id', 'position_id', 'club_id'];
        $query = $db->getQuery(true)
            ->select(array_map([$db, 'quoteName'], $fields))
            ->from($db->quoteName('#__sportsmanagement_' . $targetTable))
            ->where($db->quoteName($targetId) . ' = ' . $selectedId)
            ->order($db->quoteName('season_id'));
        $db->setQuery($query);
        $rows = $db->loadAssocList('season_id') ?: [];
        $assignments = [];

        foreach ($rows as $seasonId => $row) {
            $assignments[(int) $seasonId] = $row;
        }

        return $assignments;
    }

    private function loadPersonSportsTypeName($db, int $personId): string
    {
        if ($personId <= 0) {
            return '';
        }

        $query = $db->getQuery(true)
            ->select($db->quoteName('st.name'))
            ->from($db->quoteName('#__sportsmanagement_sports_type', 'st'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_person', 'p')
                . ' ON ' . $db->quoteName('p.sports_type_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $personId);
        $db->setQuery($query);

        return (string) $db->loadResult();
    }

    private function showPersonAssignments(string $sportsTypeName): bool
    {
        return (bool) ComponentHelper::getParams('com_sportsmanagement')->get('assign_club_position_to_player', 0)
            || $sportsTypeName === 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS';
    }

    private function textInput(string $id, string $name, string $value): string
    {
        return '<input class="form-control" size="70" type="text" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8')
            . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="'
            . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
    }

    private function selectInput(array $items, string $name, string $id, int $value, string $placeholder): string
    {
        $options = [HTMLHelper::_('select.option', '', Text::_($placeholder))];

        foreach ($items as $item) {
            $options[] = HTMLHelper::_('select.option', (int) $item->value, (string) $item->text);
        }

        return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'text', $value, $id);
    }
}
