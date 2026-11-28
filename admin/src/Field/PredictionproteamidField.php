<?php
/**
 * Native Joomla 5/6 prediction project-team ID field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementAdministratorApplicationResolver;
use Joomla\Database\ParameterType;

/** Project teams belonging to projects assigned to the active prediction game. */
final class PredictionproteamidField extends SportsManagementListField
{
    protected $type = 'predictionproteamid';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    private ?array $teamOptions = null;

    /**
     * Initialise the project-team selector for prediction configurations.
     *
     * Enables multiple selection and Joomla 5/6 form styling, invalidates
     * the previous option cache and forwards the field's original saved
     * selections to Joomla's parent setup implementation.
     *
     * @param \SimpleXMLElement $element Joomla form XML.
     * @param mixed $value Saved project-team choices.
     * @param string|null $group Optional form group.
     * @return bool Whether Joomla accepted the field setup.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['class'] = 'form-select';
        $this->teamOptions = null;

        return parent::setup($element, $value, $group);
    }

    /**
     * Render the native project-team multi-select with matched row height.
     *
     * Reuses the field's cached options and delegates the HTML output to
     * Joomla's select field renderer.
     *
     * @return string HTML markup for project-team selection.
     */
    protected function getInput(): string
    {
        $options = $this->getOptions();
        $this->size = max(1, count($options));

        return parent::getInput();
    }

    /**
     * Query project teams for a validated active prediction game.
     *
     * Rejects corrupt or partially numeric user-state IDs without database
     * access. Valid IDs are bound as integers in the Joomla project/team
     * joins, and the results are cached for the form's current rendering.
     *
     * @return array<int,object> Project-team ID and name options.
     */
    protected function getOptions(): array
    {
        if ($this->teamOptions !== null) {
            return $this->teamOptions;
        }

        $app = SportsManagementAdministratorApplicationResolver::resolve();
        $predictionId = self::predictionId($app->getUserState('com_sportsmanagement.prediction_id', 0));
        $options = [];

        if ($predictionId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('tl.id'),
                    $db->quoteName('t.name', 'teamname'),
                ])
                ->from($db->quoteName('#__sportsmanagement_project_team', 'tl'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('tl.team_id') . ' = ' . $db->quoteName('st.id'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_project', 'prepro') . ' ON ' . $db->quoteName('prepro.project_id') . ' = ' . $db->quoteName('tl.project_id'))
                ->where($db->quoteName('prepro.prediction_id') . ' = :predictionId')
                ->bind(':predictionId', $predictionId, ParameterType::INTEGER)
                ->group([$db->quoteName('tl.id'), $db->quoteName('t.name')])
                ->order($db->quoteName('t.name'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $team) {
                $options[] = (object) [
                    'value' => (string) $team->id,
                    'text' => "\u{00A0} ( " . (string) $team->teamname . ' ) ',
                ];
            }
        }

        $this->teamOptions = $options;

        return $this->teamOptions;
    }

    /**
     * Parse a complete positive prediction game identifier from user state.
     *
     * Accepts PHP integers and zero-padded decimal strings, rejecting
     * arrays, booleans, floats, partial numeric values and overflows
     * before they can select an unintended game's rounds or teams.
     *
     * @param mixed $raw Administrator's stored prediction identifier.
     * @return int Positive prediction game ID, or zero when invalid.
     */
    private static function predictionId(mixed $raw): int
    {
        if (is_int($raw)) {
            return $raw > 0 ? $raw : 0;
        }

        if (!is_string($raw) || $raw === '' || strlen($raw) > 64
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 0;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return 0;
        }

        $value = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $value === false ? 0 : $value;
    }
}
