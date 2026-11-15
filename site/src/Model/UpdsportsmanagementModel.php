<?php
/**
 * Native Joomla 5/6 frontend update form model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\Database\ParameterType;
use Throwable;

/**
 * Joomla 5/6 form model for the small SportsManagement sample record updater.
 */
final class UpdsportsmanagementModel extends FormModel
{
    /**
     * Resolve the Joomla site application for the native update model.
     *
     * Loads the namespaced application resolver when legacy callers directly
     * include this model before Joomla has registered component autoloading.
     * The resolver selects the current site application from Joomla DI.
     *
     * @return CMSApplicationInterface Active Joomla frontend application.
     * @throws \RuntimeException If the resolver file is unavailable.
     */
    private function siteApplication(): CMSApplicationInterface
    {
        if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
            $resolverFile = dirname(__DIR__) . '/Service/SportsManagementSiteApplicationResolver.php';

            if (!is_file($resolverFile)) {
                throw new \RuntimeException('SportsManagement site application resolver is missing.', 500);
            }

            require_once $resolverFile;
        }

        return SportsManagementSiteApplicationResolver::resolve();
    }

    /**
     * Load the Joomla XML form used to edit a sample SportsManagement record.
     * Uses the native FormModel loader and the component form name,
     * reporting a generic, translated failure through the application
     * instead of returning an incomplete form. The exception type is logged
     * server-side without exposing file paths or database details to visitors.
     * @param array $data Existing record fields for the form.
     * @param bool $loadData Whether Joomla should load stored form data.
     * @return Form|false Loaded Joomla form, or false on failure.
     */
    public function getForm($data = [], $loadData = true): Form|false
    {
        try {
            return $this->loadForm(
                'com_sportsmanagement.updsportsmanagement',
                'updsportsmanagement',
                [
                    'control' => 'jform',
                    'load_data' => (bool) $loadData,
                ]
            );
        } catch (Throwable $e) {
            Log::add('SportsManagement update form unavailable (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            $this->siteApplication()->enqueueMessage(\Joomla\CMS\Language\Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }
    }

    /**
     * Load the sample record selected by the current frontend request.
     * Applies active menu parameters when present, resolves the item ID
     * from model state or input, and uses a bound ID for the database lookup.
     * Database failures are logged by exception type while the frontend
     * receives a generic message to avoid leaking query or connection data.
     * @return object|null Selected sample record or null if unavailable.
     */
    public function getItem(): ?object
    {
        $app = $this->siteApplication();
        $input = $app->getInput();
        $itemId = $input->getInt('Itemid', 0);

        if ($itemId > 0) {
            $menu = $app->getMenu();
            if ($menu) {
                $this->setState('menuparams', $menu->getParams($itemId));
            }
        }

        $id = (int) $this->getState('sportsmanagement.id', 0);
        if ($id <= 0) {
            $id = $input->getInt('id', 0);
        }

        if ($id <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('greeting'),
            ])
            ->from($db->quoteName('#__sportsmanagement'))
            ->where($db->quoteName('id') . ' = :recordId')
            ->bind(':recordId', $id, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable $e) {
            Log::add('SportsManagement update item unavailable (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            $app->enqueueMessage(\Joomla\CMS\Language\Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return null;
        }
    }

    /**
     * Update a sample record only for a Joomla user with edit permission.
     * Rejects malformed IDs and greetings, trims the accepted text and
     * delegates the update to Joomla's database API; failed database writes
     * return false and report a generic error through the site application.
     * Exception classes are logged without exposing database diagnostics.
     * @param array $data Submitted record fields including id and greeting.
     * @return bool True after a successful authorised update.
     */
    public function updItem(array $data): bool
    {
        // Controller CSRF checks must not be the only write protection:
        // the native Joomla model can also be invoked by other callers.
        $identity = $this->siteApplication()->getIdentity();

        if ($identity === null || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            return false;
        }

        // Never convert malformed identifiers (e.g. "12garbage") or arrays
        // into valid record IDs or the literal string "Array".
        $id = filter_var($data['id'] ?? null, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        $greeting = $data['greeting'] ?? null;

        // Old frontend forms sometimes submit arrays where a single string
        // was expected. Bound the text to avoid overflowing legacy columns.
        if ($id === false
            || !is_string($greeting)
            || trim($greeting) === ''
            || strlen($greeting) > 4096
            || str_contains($greeting, "\0")) {
            return false;
        }

        $greeting = trim($greeting);

        $record = (object) [
            'id' => $id,
            'greeting' => $greeting,
        ];

        try {
            return (bool) $this->getDatabase()->updateObject(
                '#__sportsmanagement',
                $record,
                'id'
            );
        } catch (Throwable $e) {
            Log::add('SportsManagement sample update failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
            $this->siteApplication()->enqueueMessage(\Joomla\CMS\Language\Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

            return false;
        }
    }
}
