<?php
/**
 * Joomla 5/6 native season checkbox field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class SeasoncheckboxField extends FormField
{
    use SportsManagementDatabaseTrait;

    protected $type = 'checkboxes';

    /**
     * Render validated season checkbox associations for a team or person.
     *
     * For team assignments, includes a hidden empty checkbox value so an
     * all-unchecked form still posts jform[season_ids][]; TeamModel treats
     * that sentinel as an intentional request to clear assignments. Person
     * association forms keep their existing behaviour unchanged.
     *
     * @return string Escaped Joomla HTML fieldset and checkbox inputs.
     */
    protected function getInput(): string
    {
        $target = $this->resolveTarget();

        if ($target === null) {
            return '<div class="alert alert-warning">Invalid season assignment target.</div>';
        }

        [$targetTable, $targetId] = $target;
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $selectedId = $app->getInput()->getInt('id', 0);
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

        // Unchecked checkboxes are omitted from POST by browsers. Preserve a
        // deliberate "remove every team-season assignment" submission without
        // changing season-person forms that reuse this Joomla field.
        $html = $targetTable === 'season_team_id'
            ? '<input type="hidden" name="'
                . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8') . '[]" value="">'
            : '';
        $html .= '<fieldset id="' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8') . '" class="'
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

    /**
     * Validate the season-assignment table and record relationship.
     *
     * Only team and person association tables with their matching foreign
     * keys may be queried from the Joomla form element settings.
     *
     * @return array{0:string,1:string}|null Safe table and FK, or null.
     */
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

    /**
     * Retrieve season, club or position options through native Joomla SQL.
     *
     * The caller supplies only fixed internal table names and order clauses
     * from the field renderer; no visitor-controlled SQL is accepted.
     *
     * @param object $db Selected SportsManagement database connection.
     * @param string $table Trusted Joomla-prefixed table name.
     * @param string $order Trusted sort expression.
     * @return array<int,object> Available option records.
     */
    private function loadSimpleOptions($db, string $table, string $order): array
    {
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id', 'value'),
                $db->quoteName('name', 'text'),
            ])
            ->from($db->quoteName($table))
            ->order($order);
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Load existing season associations for the selected team or person.
     *
     * The table and ID column were validated by resolveTarget(), and the
     * selected record ID was normalised through Joomla's integer input.
     *
     * @param object $db Selected SportsManagement database.
     * @param string $targetTable Allowlisted association table suffix.
     * @param string $targetId Allowlisted foreign-key field name.
     * @param int $selectedId Current item ID, or zero for a new item.
     * @return array<int,array<string,mixed>> Season-indexed association data.
     */
    private function loadAssignments($db, string $targetTable, string $targetId, int $selectedId): array
    {
        if ($selectedId <= 0) {
            return [];
        }

        $fields = $targetTable === 'season_team_id'
            ? ['season_id', 'teamname', 'season_teamname']
            : ['season_id', 'position_id', 'club_id'];
        $query = $db->createQuery()
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

    /**
     * Resolve the sports type used for optional person-season club columns.
     *
     * @param object $db Selected SportsManagement database.
     * @param int $personId Positive person record ID.
     * @return string Stored sports type name, or empty when unavailable.
     */
    private function loadPersonSportsTypeName($db, int $personId): string
    {
        if ($personId <= 0) {
            return '';
        }

        $query = $db->createQuery()
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

    /**
     * Decide whether the person season table exposes club/position controls.
     *
     * @param string $sportsTypeName Selected person's sports type name.
     * @return bool Whether supplementary person association columns appear.
     */
    private function showPersonAssignments(string $sportsTypeName): bool
    {
        return (bool) ComponentHelper::getParams('com_sportsmanagement')->get('assign_club_position_to_player', 0)
            || $sportsTypeName === 'COM_SPORTSMANAGEMENT_ST_TABLETENNIS';
    }

    /**
     * Render an HTML-escaped text input for team-season display names.
     *
     * @param string $id DOM input identifier.
     * @param string $name Submitted Joomla input name.
     * @param string $value Existing association display value.
     * @return string Escaped HTML text field.
     */
    private function textInput(string $id, string $name, string $value): string
    {
        return '<input class="form-control" size="70" type="text" id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8')
            . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" value="'
            . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Keep the compatibility generic-list renderer because these selects are
     * embedded inside the season assignment table, but feed it native option
     * objects instead of the legacy select.option adapter.
     *
     * @param array<int,object> $items Available club or position options.
     * @param string $name Submitted select field name.
     * @param string $id DOM selector identifier.
     * @param int $value Currently selected option ID.
     * @param string $placeholder Joomla placeholder language key.
     * @return string Native Joomla select markup.
     */
    private function selectInput(array $items, string $name, string $id, int $value, string $placeholder): string
    {
        $options = [
            (object) [
                'value' => '',
                'text' => Text::_($placeholder),
            ],
        ];

        foreach ($items as $item) {
            $options[] = (object) [
                'value' => (string) $item->value,
                'text' => (string) $item->text,
            ];
        }

        return HTMLHelper::_('select.genericlist', $options, $name, 'class="form-select"', 'value', 'text', $value, $id);
    }
}
