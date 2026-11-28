<?php
/**
 * Native Joomla 5/6 prediction round ID field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\Database\ParameterType;

/** Rounds belonging to projects assigned to the active prediction game. */
final class PredictionroundidField extends SportsManagementListField
{
    protected $type = 'predictionroundid';

    /** Native Joomla searchable list layout; retains the original option values. */
    protected $layout = 'joomla.form.field.list-fancy-select';

    private ?array $roundOptions = null;

    /**
     * Configure a Joomla multi-select for prediction round identifiers.
     *
     * Enables the native Bootstrap select styling, resets cached options
     * for the current form instance and delegates field initialisation to
     * Joomla so saved round IDs remain selectable.
     *
     * @param \SimpleXMLElement $element XML field definition.
     * @param mixed $value Saved selection.
     * @param string|null $group Joomla form group.
     * @return bool Whether parent form setup succeeded.
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $element['multiple'] = 'true';
        $element['class'] = 'form-select';
        $this->roundOptions = null;

        return parent::setup($element, $value, $group);
    }

    /**
     * Render a prediction-round select sized to the current options.
     *
     * Computes the available round choices once, then renders the normal
     * Joomla form control with a row count matching the results.
     *
     * @return string Joomla multi-select HTML.
     */
    protected function getInput(): string
    {
        $options = $this->getOptions();
        $this->size = max(1, count($options));

        return parent::getInput();
    }

    /**
     * Load rounds assigned to the selected prediction game.
     *
     * Validates the prediction ID from administrator user state before
     * querying, binds the ID using the Joomla database API, and caches
     * the resulting round options. Invalid or nested settings produce
     * an empty selector instead of querying another prediction game.
     *
     * @return array<int,object> Round ID and translated name options.
     */
    protected function getOptions(): array
    {
        if ($this->roundOptions !== null) {
            return $this->roundOptions;
        }

        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);
        $predictionId = self::predictionId($app->getUserState('com_sportsmanagement.prediction_id', 0));
        $options = [];

        if ($predictionId > 0) {
            $db = $this->getSportsManagementDatabase();
            $query = $db->createQuery()
                ->select([
                    $db->quoteName('r.id', 'value'),
                    $db->quoteName('r.name', 'text'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match', 'm'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
                ->join('INNER', $db->quoteName('#__sportsmanagement_prediction_project', 'prepro') . ' ON ' . $db->quoteName('prepro.project_id') . ' = ' . $db->quoteName('r.project_id'))
                ->where($db->quoteName('prepro.prediction_id') . ' = :predictionId')
                ->bind(':predictionId', $predictionId, ParameterType::INTEGER)
                ->group([$db->quoteName('r.id'), $db->quoteName('r.name')])
                ->order($db->quoteName('r.id'));
            $db->setQuery($query);

            foreach ($db->loadObjectList() ?: [] as $round) {
                $options[] = (object) [
                    'value' => (string) $round->value,
                    'text' => "\u{00A0} ( " . (string) $round->text . ' ) ',
                ];
            }
        }

        $this->roundOptions = $options;

        return $this->roundOptions;
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
