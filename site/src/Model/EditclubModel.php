<?php
/**
 * Joomla 5/6 frontend model for editing clubs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Table\ClubTable;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;

final class EditclubModel extends AdminModel
{
    public $latitude = null;
    public $longitude = null;
    public $projectid = 0;
    public $clubid = 0;
    public $club = null;
    public $name = 'club';

    /**
     * Initialise the frontend club editor with its Joomla MVC services.
     *
     * Delegate model and form factory setup to Joomla's AdminModel, then
     * read the selected project and club IDs from the resolved site
     * application for subsequent form and record retrieval.
     *
     * @param array $config MVC model configuration.
     * @param MVCFactoryInterface|null $factory Optional Joomla MVC factory.
     * @param FormFactoryInterface|null $formFactory Optional form factory.
     * @return void
     */
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        parent::__construct($config, $factory, $formFactory);

        $input = $this->siteApplication()->getInput();
        $this->projectid = $input->getInt('p', 0);
        $this->clubid = $input->getInt('cid', 0);
    }

    /**
     * Persist validated changes to an existing frontend club record.
     *
     * Accept native jform fields and historical request fields while keeping
     * the top-level club ID authoritative. Normalise optional foundation and
     * dissolution dates, update their companion year/timestamp fields and
     * save through the native Joomla club table after validation.
     *
     * @param mixed $data Submitted club form fields and record identifier.
     * @return bool True when the existing club was saved successfully.
     */
    public function updItem($data): bool
    {
        $data = (array) $data;
        $clubId = self::positiveId($data['id'] ?? null);

        if ($clubId <= 0) {
            $this->setError('Invalid club record.');
            return false;
        }

        $request = is_array($data['request'] ?? null) ? $data['request'] : [];
        $form = is_array($data['jform'] ?? null) ? $data['jform'] : [];

        unset($data['request'], $data['jform']);
        unset($request['id'], $form['id']);

        // Native Joomla jform values take precedence; nested IDs do not.
        $data = array_replace($data, $request, $form);
        $data['id'] = $clubId;

        foreach (['founded', 'dissolved'] as $field) {
            // Not every frontend editor posts both date fields. Never clear
            // an existing date just because an unrelated field was saved.
            if (!array_key_exists($field, $data)) {
                continue;
            }

            if (!is_string($data[$field]) && !is_int($data[$field])) {
                $this->setError('Invalid ' . $field . ' date.');
                return false;
            }

            $provided = trim((string) $data[$field]);

            if ($provided === '' || $provided === '0000-00-00') {
                $data[$field] = '0000-00-00';
                $data[$field . '_timestamp'] = 0;
                $data[$field . '_year'] = $field === 'founded' ? 'kein' : 0;
                continue;
            }

            $normalized = $this->normaliseFrontendDate($provided);

            if ($normalized === '') {
                $this->setError('Invalid ' . $field . ' date.');

                return false;
            }

            $data[$field] = $normalized;
            $data[$field . '_year'] = (int) substr($normalized, 0, 4);
            $data[$field . '_timestamp'] = $this->dateTimestamp($normalized);
        }

        try {
            $table = $this->getTable('club');

            if (!$table->bind($data) || !$table->check() || !$table->store()) {
                $this->setError((string) $table->getError());

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ': ' . $e->getMessage(), Log::ERROR, 'jsmerror');
            $this->setError('Club changes could not be saved.');

            return false;
        }
    }

    /**
     * Resolve the namespaced Joomla club table for frontend edit operations.
     *
     * Map the historical case-insensitive club table name to ClubTable and
     * delegate all other table requests to the native parent model.
     *
     * @param string $type Requested table name.
     * @param string $prefix Historical Joomla table class prefix.
     * @param array $config Optional table factory configuration.
     * @return mixed Resolved club table or the parent table instance.
     */
    public function getTable($type = 'club', $prefix = 'sportsmanagementTable', $config = [])
    {
        if (strcasecmp((string) $type, 'club') === 0) {
            return new ClubTable($this->getDatabase());
        }

        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Construct the frontend club form with media and community settings.
     *
     * Register the component's administrator XML forms and namespaced
     * fields, hide team merging when disabled, and configure club logos and
     * kit fields with the selected media widget and default image paths.
     *
     * @param array $data Optional form data supplied by the caller.
     * @param bool $loadData Whether Joomla should load saved form state.
     * @return \Joomla\CMS\Form\Form|false Configured club form or false.
     */
    public function getForm($data = [], $loadData = true)
    {
        $option = $this->siteApplication()->getInput()->getCmd('option', 'com_sportsmanagement');
        $params = ComponentHelper::getParams($option);
        $mediaTool = trim((string) $params->get('cfg_which_media_tool', 'media'));
        $showTeamCommunity = (bool) $params->get('show_team_community', 0);

        if ($mediaTool === '' || ctype_digit($mediaTool)) {
            $mediaTool = 'media';
        }

        FormHelper::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        FormHelper::addFieldPrefix('Diddipoeler\\Component\\SportsManagement\\Administrator\\Field');

        $form = $this->loadForm(
            'com_sportsmanagement.' . $this->name,
            $this->name,
            ['load_data' => $loadData]
        );

        if (!$form) {
            return false;
        }

        if (!$showTeamCommunity) {
            $form->setFieldAttribute('merge_teams', 'type', 'hidden');
        }

        $mediaFields = [
            'logo_small' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/small'],
            'logo_middle' => ['ph_logo_medium', 'com_sportsmanagement/database/clubs/medium'],
            'logo_big' => ['ph_logo_big', 'com_sportsmanagement/database/clubs/large'],
            'trikot_home' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/trikot_home'],
            'trikot_away' => ['ph_logo_small', 'com_sportsmanagement/database/clubs/trikot_away'],
        ];

        foreach ($mediaFields as $field => [$placeholder, $directory]) {
            if (!$form->getField($field)) {
                continue;
            }

            $form->setFieldAttribute($field, 'default', (string) $params->get($placeholder, ''));
            $form->setFieldAttribute($field, 'directory', $directory);
            $form->setFieldAttribute($field, 'type', $mediaTool);
        }

        return $form;
    }

    /**
     * Retrieve retained user input or the selected club record for editing.
     *
     * Prefer Joomla's user-state form data after a failed submission to
     * preserve changes, and fall back to the stored club when no pending
     * form data exists.
     *
     * @return mixed Session form values or the native club table record.
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
     * Load and cache the club selected by the Joomla frontend request.
     *
     * Construct a native ClubTable once and load its row using the club
     * identifier captured by the model constructor. Subsequent calls
     * reuse the same table to avoid repeated database queries.
     *
     * @return mixed Cached Joomla club table record.
     */
    public function getData()
    {
        if ($this->club === null) {
            $this->club = $this->getTable('Club', 'sportsmanagementTable');
            $this->club->load($this->clubid);
        }

        return $this->club;
    }

    /**
     * Resolve the active Joomla frontend application.
     *
     * Delegate to SportsManagement's site application resolver so model
     * input and session state come from the proper Joomla 5/6 client.
     *
     * @return CMSApplicationInterface Active frontend application.
     */
    private function siteApplication(): CMSApplicationInterface
    {
        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Validate an existing club identifier without lossy PHP type coercion.
     *
     * Accept only integer or decimal-string values, reject zero, negative,
     * partial, floating-point and overflowing identifiers, and compare
     * decimal digits with PHP_INT_MAX before converting to an integer.
     * This prevents malformed nested request values from selecting a
     * different club record during frontend saves.
     *
     * @param mixed $value Untrusted club record identifier.
     * @return int Valid positive club ID or zero if validation fails.
     */
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
     * Convert supported club date formats into ISO year-month-day values.
     *
     * Accept Joomla calendar day-first values, compact day/month/year
     * strings and already-normalised ISO dates. Reject impossible dates
     * using checkdate rather than relying on strtotime coercion.
     *
     * @param string $date Date supplied by a frontend editor.
     * @return string ISO-formatted valid date or an empty invalid marker.
     */
    private function normaliseFrontendDate(string $date): string
    {
        $date = trim($date);

        // Joomla's calendar uses day-month-year, while native date inputs and
        // stored club dates may already use the ISO year-month-day format.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts)) {
            [$year, $month, $day] = [(int) $parts[1], (int) $parts[2], (int) $parts[3]];
        } elseif (preg_match('/^(\d{2})[-.](\d{2})[.-](\d{4})$/', $date, $parts)) {
            [$day, $month, $year] = [(int) $parts[1], (int) $parts[2], (int) $parts[3]];
        } elseif (preg_match('/^(\d{2})(\d{2})(\d{4})$/', $date, $parts)) {
            [$day, $month, $year] = [(int) $parts[1], (int) $parts[2], (int) $parts[3]];
        } elseif (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $date, $parts)) {
            [$day, $month, $year] = [
                (int) $parts[1],
                (int) $parts[2],
                (int) (substr(date('Y'), 0, 2) . $parts[3]),
            ];
        } else {
            return '';
        }

        return checkdate($month, $day, $year)
            ? sprintf('%04d-%02d-%02d', $year, $month, $day)
            : '';
    }

    /**
     * Convert a validated club date into its Unix timestamp companion.
     *
     * Used after normaliseFrontendDate() when preparing the historical
     * founded and dissolved timestamp fields for persistence.
     *
     * @param string $date Previously validated ISO date string.
     * @return int Parsed Unix timestamp, or zero when conversion fails.
     */
    private function dateTimestamp(string $date): int
    {
        return (int) strtotime($date);
    }
}
