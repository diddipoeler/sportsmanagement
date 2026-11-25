<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\LegacyModelDatabaseHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\ProjectPanelService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 form controller for projects. */
final class ProjectController extends SportsManagementFormController
{
    /**
     * Assign the sport's initial standard positions to an existing project.
     *
     * Requires a valid POST token and component editing rights before any
     * project lookup or insertion. Only a bounded positive posted project
     * ID is accepted; sport and project-kind metadata are loaded from the
     * selected database by the service. Redirects to the read-only panel
     * and never exposes driver diagnostics in the administrator response.
     * An unavailable external database cannot redirect the setup write to
     * the default Joomla connection.
     *
     * @return void
     * @throws \RuntimeException For invalid token, identity or permission.
     * @throws \InvalidArgumentException For malformed project selection.
     */
    public function initializestandardpositions(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();
        if (!$identity || (int) ($identity->id ?? 0) <= 0
            || !$identity->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $raw = $this->app->getInput()->post->get('id', null, 'raw');
        if (!is_int($raw) && (!is_string($raw) || strlen($raw) > 64
            || preg_match('/^[0-9]+$/D', $raw) !== 1)) {
            throw new \InvalidArgumentException('Invalid SportsManagement project ID.', 400);
        }

        $digits = is_string($raw) ? ltrim($raw, '0') : $raw;
        $projectId = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($projectId === false) {
            throw new \InvalidArgumentException('Invalid SportsManagement project ID.', 400);
        }

        $selector = LegacyModelDatabaseHelper::selector();
        $success = false;
        try {
            $fallback = Factory::getContainer()->get(DatabaseInterface::class);
            if (!$fallback instanceof DatabaseInterface) {
                throw new \RuntimeException('Joomla database service is unavailable.');
            }

            $database = LegacyModelDatabaseHelper::resolve($fallback);
            if ($selector === 1 && $database === $fallback) {
                throw new \RuntimeException('Selected external database is unavailable for project setup.');
            }

            $service = new ProjectPanelService($database);
            $success = $service->initializeForProject($projectId);
        } catch (\Throwable $e) {
            Log::add(__METHOD__ . ' failed (' . get_class($e) . ').', Log::ERROR, 'com_sportsmanagement');
        }

        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $projectId
                . '&cfg_which_database=' . $selector, false),
            Text::_($success ? 'JLIB_APPLICATION_SAVE_SUCCESS' : 'JLIB_APPLICATION_ERROR_SAVE_FAILED'),
            $success ? 'message' : 'error'
        );
    }

    /**
     * Create the native Joomla 5/6 project form model with caller options.
     *
     * Project editors normally inherit the selected request context, so
     * ignore_request defaults to false. Explicit caller configuration takes
     * precedence, including ignore_request for isolated model writes.
     *
     * @param string $name Requested administrator model.
     * @param string $prefix Joomla MVC model namespace.
     * @param array $config Additional MVC factory model options.
     * @return mixed Parent Joomla form controller model or failure result.
     */
    public function getModel($name = 'Project', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }
}
