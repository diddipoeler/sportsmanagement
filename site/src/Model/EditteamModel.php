<?php
/**
 * Native Joomla 5/6 frontend team editor model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\AdminModel;

/** Joomla 5/6 frontend model for editing teams. */
final class EditteamModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $name = 'editteam';

    private ?TeamTable $team = null;

    /**
     * Resolve the current Joomla frontend application.
     *
     * Delegate application lookup to the component's native site resolver,
     * ensuring form state is read from the correct client rather than
     * using removed global Joomla application accessors.
     *
     * @return CMSApplicationInterface Active Joomla site application.
     */
    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Preserve native jform data and legacy editor fields without accepting
     * nested record IDs that could redirect a write to another team.
     */
    public function updItem($data): bool
    {
        $data = (array) $data;
        $teamId = self::positiveId($data['id'] ?? null);

        if ($teamId <= 0) {
            $this->setError('Invalid team record.');
            return false;
        }

        $request = is_array($data['request'] ?? null) ? $data['request'] : [];
        $form = is_array($data['jform'] ?? null) ? $data['jform'] : [];

        // Joomla's form input is nested under jform. Keep zero/false/empty
        // values, while the top-level editor ID remains authoritative.
        unset($data['request'], $data['jform']);
        unset($request['id'], $form['id']);
        $data = array_replace($data, $request, $form);
        $data['id'] = $teamId;

        try {
            $table = $this->getTable('team');

            if (!$table->bind($data) || !$table->check()) {
                return false;
            }

            return (bool) $table->store();
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError('Team changes could not be saved.');
            return false;
        }
    }

    /** Reject partial, negative, floating-point and overflowing team IDs. */
    private static function positiveId(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            return 0;
        }

        $raw = trim((string) $value);

        if ($raw === '' || !ctype_digit($raw)) {
            return 0;
        }

        $digits = ltrim($raw, '0');
        $digits = $digits === '' ? '0' : $digits;
        $max = (string) PHP_INT_MAX;

        if ($digits === '0'
            || strlen($digits) > strlen($max)
            || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
            return 0;
        }

        return (int) $digits;
    }

    /**
     * Preserve the already selected site database connection for table writes.
     * Return the native team database table for the frontend editor.
     *
     * Resolve the case-insensitive legacy team table name to the
     * namespaced TeamTable class, and delegate other table names to
     * Joomla's base administrator model for compatibility.
     *
     * @param string $type Requested table type.
     * @param string $prefix Historical table class prefix.
     * @param array $config Table construction options.
     * @return mixed Resolved Joomla table object.
     */
    public function getTable($type = 'team', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'team') === 0) {
            return new TeamTable($this->getDatabase(), true);
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Build the Joomla team form using registered site and admin fields.
     *
     * Register the component's form directory and custom field namespace,
     * load the native XML form and optionally bind saved form data.
     *
     * @param array $data Optional form data supplied by the caller.
     * @param bool $loadData Whether to populate data from model state.
     * @return \Joomla\CMS\Form\Form|false Prepared form or false on failure.
     */
    public function getForm($data = [], $loadData = true)
    {
        FormHelper::addFormPath(JPATH_SITE . '/components/com_sportsmanagement/models/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        return $form ?: false;
    }

    /**
     * Load unsaved team editor input or the current team record.
     *
     * Prefer Joomla's session-stored form values after validation errors
     * so edits are not lost, falling back to the selected team record
     * when there is no pending form state.
     *
     * @return mixed Previously posted fields or the current team record.
     */
    protected function loadFormData()
    {
        $app = $this->siteApplication();
        $data = $app->getUserState('com_sportsmanagement.edit.' . $this->name . '.data', []);

        if (empty($data)) {
            $data = $this->getData();
        }

        return $data;
    }

    /**
     * Load and cache the TeamTable selected in the frontend request.
     *
     * Read the positive team identifier from Joomla's site input and load
     * the corresponding row once per model instance. When creating a
     * team, return a new unpopulated native table object.
     *
     * @return TeamTable Cached team record for display or editing.
     */
    public function getData(): TeamTable
    {
        if ($this->team === null) {
            $teamId = $this->siteApplication()->getInput()->getInt('tid', 0);
            $this->team = $this->getTable('team');

            if ($teamId > 0) {
                $this->team->load($teamId);
            }
        }

        return $this->team;
    }
}
