<?php
/**
 * Native Joomla 5/6 administrator controller for the GitHub helper view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native administrator controller for the GitHub helper view. */
final class GithubController extends BaseController
{
    /**
     * Create a GitHub issue through the component's administrator model.
     *
     * Requires a valid Joomla form token, a real authenticated identity and
     * the core.manage permission for com_sportsmanagement before invoking
     * the external GitHub issue model. Rejects guests, malformed user IDs and unavailable identities
     * before any issue data is read or sent. Redirects to the result
     * layout with either a success or an error message after the attempt.
     * The resolved model must expose the issue-creation operation before it
     * is called, avoiding fatal errors from an unexpected MVC factory result.
     *
     * @return void
     * @throws \RuntimeException When the current user lacks manage permission.
     */
    public function addissue(): void
    {
        $this->checkToken();
        $identity = $this->app->getIdentity();

        // Guest identities or a missing Joomla user must never reach the
        // external GitHub issue writer, even if an ACL stub permits manage.
        if (!is_object($identity) || self::authenticatedUserId($identity->id ?? null) === 0
            || !is_callable([$identity, 'authorise'])
            || !$identity->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Github', 'Administrator', ['ignore_request' => true]);
        $success = is_object($model)
            && is_callable([$model, 'addissue'])
            && (bool) $model->addissue();

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=github&tmpl=component&layout=github_result',
            $success ? 'GitHub-Issue wurde erstellt.' : 'GitHub-Issue konnte nicht erstellt werden.',
            $success ? 'message' : 'error'
        );
    }

    /**
     * Validate the authenticated Joomla user ID without loose type casting.
     *
     * Joomla normally supplies a positive integer identity ID, but older
     * integrations may expose a decimal string. Accept complete positive
     * decimal values only, rejecting guest IDs, partial numbers, booleans,
     * nested input and integer overflow before authorizing an API write.
     *
     * @param mixed $raw User ID supplied by Joomla's identity implementation.
     * @return int Positive authenticated ID, or zero for invalid identities.
     */
    private static function authenticatedUserId(mixed $raw): int
    {
        if (is_int($raw)) {
            return $raw > 0 ? $raw : 0;
        }

        if (!is_string($raw) || $raw === '' || strlen($raw) > 128
            || preg_match('/^[0-9]+$/D', $raw) !== 1) {
            return 0;
        }

        $digits = ltrim($raw, '0');

        if ($digits === '') {
            return 0;
        }

        $id = filter_var($digits, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Close the administrator GitHub helper workflow without submitting data.
     *
     * Redirects the component popup to its close view using Joomla's
     * translated close message; no issue creation is attempted.
     *
     * @return void
     */
    public function cancel(): void
    {
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&view=close&tmpl=component',
            Text::_('JLIB_HTML_BEHAVIOR_CLOSE')
        );
    }

    /**
     * Retrieve an administrator MVC model without applying request state.
     *
     * Supplies the native Joomla administrator prefix and enables
     * ignore_request unless the caller explicitly provided a different
     * configuration value, avoiding unintended state from query parameters.
     *
     * @param string $name Model name, defaulting to the GitHub integration.
     * @param string $prefix MVC model class prefix.
     * @param array $config Additional model factory configuration.
     * @return mixed The model instance or the parent controller's failure result.
     */
    public function getModel($name = 'Github', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config + ['ignore_request' => true]);
    }
}
