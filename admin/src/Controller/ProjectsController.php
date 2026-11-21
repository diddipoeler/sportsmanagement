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

use Diddipoeler\Component\SportsManagement\Administrator\Model\ProjectModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/** Native Joomla 5/6 list controller for projects. */
final class ProjectsController extends SportsManagementAdminController
{
    /**
     * Save abbreviated project fields from the administrator list.
     *
     * Delegates to the shared project action runner with the core.edit
     * permission and the model's saveshort operation.
     *
     * @return void
     */
    public function saveshort(): void
    {
        $this->runProjectAction('core.edit', 'saveshort');
    }

    /**
     * Mark the selected league champion in the project model.
     *
     * Enforces administrator edit permission and POST token validation
     * through the shared project-action dispatcher.
     *
     * @return void
     */
    public function setleaguechampion(): void
    {
        $this->runProjectAction('core.edit', 'setleaguechampion');
    }

    /**
     * Copy selected SportsManagement projects through the native model.
     *
     * Requires component core.create permission and a valid POST token
     * before invoking ProjectModel::copy().
     *
     * @return void
     */
    public function copy(): void
    {
        $this->runProjectAction('core.create', 'copy');
    }

    /**
     * Resolve a project model with the Joomla administrator MVC factory.
     *
     * Uses the Project model by default, preserves the list controller's
     * request-state handling and allows caller configuration to override
     * the ignore_request default.
     *
     * @param string $name Requested model name.
     * @param string $prefix MVC namespace prefix.
     * @param array $config Additional model options.
     * @return mixed Model instance or the parent's unsuccessful result.
     */
    public function getModel($name = 'Project', $prefix = 'Administrator', $config = [])
    {
        // Keep the list's default request handling without discarding
        // options passed by Joomla's MVC factory or legacy callers.
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => false], $config));
    }

    /**
     * Validate a positive administrator identity ID before project writes.
     *
     * Supports native Joomla integer IDs and complete decimal strings from
     * legacy identity providers. Rejects guests, negative IDs, booleans,
     * nested structures, partial numeric strings and integer overflow so
     * permissive ACL stubs cannot authorize unauthenticated writes.
     *
     * @param mixed $value Joomla identity's raw user ID.
     * @return int Positive authenticated ID or zero for invalid identities.
     */
    private static function authenticatedUserId(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }

        if (!is_string($value) || $value === '' || strlen($value) > 128
            || preg_match('/^[0-9]+$/D', $value) !== 1) {
            return 0;
        }

        $digits = ltrim($value, '0');
        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $id === false ? 0 : $id;
    }

    /**
     * Execute a protected administrator project operation and redirect.
     *
     * Validates the Joomla POST token, a positive authenticated user ID and
     * the requested component ACL permission. Verifies the operation is one of the supported
     * project actions, resolves the native ProjectModel and invokes only
     * that operation. Treats false and null write results as failures and
     * uses a translated fallback when the model supplies no text error.
     * Reports the outcome on the administrator projects page.
     *
     * @param string $permission Joomla ACL action required for the operation.
     * @param string $method Name of the ProjectModel operation to invoke.
     * @return void
     * @throws \InvalidArgumentException If the action or permission pair is not supported.
     * @throws \RuntimeException If the token, permission or model is invalid.
     */
    private function runProjectAction(string $permission, string $method): void
    {
        // Keep dynamic model calls restricted to these existing controller
        // tasks, even if additional callers are introduced in the future.
        $allowedActions = [
            'saveshort' => 'core.edit',
            'setleaguechampion' => 'core.edit',
            'copy' => 'core.create',
        ];

        if (!isset($allowedActions[$method]) || $allowedActions[$method] !== $permission) {
            throw new \InvalidArgumentException('Unsupported SportsManagement project action.', 400);
        }

        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();

        // An ACL shim may grant access for a guest or malformed identity.
        // Only a real authenticated user can reach project write models.
        if (!is_object($identity) || self::authenticatedUserId($identity->id ?? null) === 0
            || !is_callable([$identity, 'authorise'])
            || !$identity->authorise($permission, 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel();

        if (!$model instanceof ProjectModel) {
            throw new \RuntimeException('ProjectModel is unavailable.', 500);
        }

        $result = $model->{$method}();

        // Legacy model methods may return null on unsuccessful writes.
        // Treat only a non-null, non-false result as success.
        $ok = $result !== false && $result !== null;
        $error = $ok ? '' : $model->getError();
        $message = $ok
            ? (is_string($result) && $result !== '' ? $result : Text::_('JLIB_APPLICATION_SAVE_SUCCESS'))
            : (is_string($error) && $error !== '' ? $error : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'));
        $this->setRedirect(
            Route::_('index.php?option=com_sportsmanagement&view=projects', false),
            $message,
            $ok ? 'message' : 'warning'
        );
    }
}
