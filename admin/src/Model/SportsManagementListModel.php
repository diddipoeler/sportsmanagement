<?php
/**
 * Joomla 5/6 base list model for SportsManagement administrator models.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    SportsManagement
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseInterface;
abstract class SportsManagementListModel extends ListModel
{
    /**
     * Guards Joomla's lazy state initialisation against re-entrant getState() calls
     * from legacy-compatible populateState() implementations.
     */
    private bool $stateReadInProgress = false;

    /**
     * Resolve the active administrator application through the shared runtime service.
     * Supplies the correct Joomla client for list filters, user state and database selection.
     * @return AdministratorApplication Active administrator application.
     */
    protected function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }

    /**
     * Load the administrator list filter form from component form paths.
     * @param array $data Optional existing filter form values.
     * @param bool $loadData Whether Joomla should load model state.
     * @return mixed Joomla filter form returned by the parent list model.
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        return parent::getFilterForm($data, $loadData);
    }

    /**
     * Read list model state while guarding against recursive initialisation.
     * Returns the existing state for re-entrant calls from populateState.
     * @param string|null $property Requested state property.
     * @param mixed $default Default value for a missing property.
     * @return mixed State object or requested state value.
     */
    public function getState($property = null, $default = null)
    {
        if ($this->stateReadInProgress) {
            return $property === null ? $this->state : $this->state->get($property, $default);
        }

        $this->stateReadInProgress = true;

        try {
            return parent::getState($property, $default);
        } finally {
            $this->stateReadInProgress = false;
        }
    }

    /**
     * Expose the resolved list-model database to administrator views and services.
     * Avoids direct access to the protected database API of Joomla's model base.
     * @return DatabaseInterface Database currently assigned to the model.
     */
    public function getSportsManagementDatabase(): DatabaseInterface
    {
        return $this->getDatabase();
    }

    /**
     * Bind the list model to the chosen administrator database.
     * Attempts the active configured SportsManagement connection and falls
     * back to the primary Joomla database when resolution fails or the raw
     * database preference is malformed. Accepts only supported modes 0 and 1.
     * @param DatabaseInterface $db Default Joomla database connection.
     * @return void
     */
    public function setDatabase(DatabaseInterface $db): void
    {
        try {
            $app = $this->administratorApplication();
            $raw = $app->getInput()->get('cfg_which_database', null, 'raw');
            $raw = $raw ?? $app->getUserState('com_sportsmanagement.cfg_which_database', 0);
            $selector = self::databaseSelector($raw);

            if ($selector === null) {
                parent::setDatabase($db);

                return;
            }
            parent::setDatabase((new SportsManagementDatabaseResolver())->resolve($selector, $db));
            return;
        } catch (\Throwable) {
            // List model construction must remain usable when external DB resolution fails.
        }

        parent::setDatabase($db);
    }

    /**
     * Validate the raw request or persisted database preference before resolution.
     * Accepts integers zero and one and bounded zero-padded decimal strings;
     * rejects structured, signed and partially numeric input without coercion.
     * @param mixed $value Raw database preference.
     * @return int|null Supported database mode or null for malformed input.
     */
    private static function databaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
