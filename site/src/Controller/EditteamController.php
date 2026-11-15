<?php
/**
 * Joomla 5/6 frontend controller for team editing.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for team editing. */
final class EditteamController extends FormController
{
    /**
     * Apply team changes while keeping the frontend editing form available.
     *
     * Delegates to save(), which performs Joomla CSRF and component edit
     * permission checks before updating the team and choosing a redirect.
     *
     * @param string|null $key Optional model record key.
     * @param string|null $urlVar Optional URL identifier field.
     * @return bool Whether saving the edited team succeeded.
     */
    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    /**
     * Acknowledge the historical frontend team submission securely.
     *
     * This legacy task does not persist a team itself, but it is still a
     * form-submission entry point. Check Joomla's POST CSRF token and the
     * current identity's component edit permission before responding; actual
     * team updates continue to be handled by save().
     *
     * @return bool True after the submission passes both security checks.
     * @throws \RuntimeException If component edit permission is missing.
     */
    public function submit(): bool
    {
        $this->checkToken('post');
        $this->assertEditorPermission();

        return true;
    }

    /**
     * Persist a frontend team's posted editing fields via the native model.
     *
     * Requires a Joomla form token and component-level edit permission,
     * passes the posted fields to EditteamModel::updItem() and redirects
     * either back to the editor with an error, back after Apply, or to the
     * popup close view after a successful Save. Posted redirect selectors
     * are validated independently to prevent PHP 8 numeric-cast warnings.
     *
     * @param string|null $key Optional legacy record key.
     * @param string|null $urlVar Optional legacy URL identifier.
     * @return bool True when the model reports a successful update.
     * @throws \RuntimeException When edit permission or model resolution fails.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();
        $this->assertEditorPermission();

        $input = $this->input;
        $data = $input->post->getArray();
        $id = $input->getInt('id', 0);
        $saved = $this->editTeamModel()->updItem($data);

        if (!$saved) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editteam&tmpl=component&id=' . $id
                . '&ptid=' . self::redirectId($data['ptid'] ?? 0)
                . '&p=' . self::redirectId($data['p'] ?? 0)
                . '&tid=' . self::redirectId($data['tid'] ?? 0),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );

            return false;
        }

        if ($this->getTask() === 'apply') {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editteam&tmpl=component&id=' . $id
                . '&ptid=' . self::redirectId($data['ptid'] ?? 0)
                . '&p=' . self::redirectId($data['p'] ?? 0)
                . '&tid=' . self::redirectId($data['tid'] ?? 0),
                Text::_('COM_SPORTSMANAGEMENT_EDIT_TEAM_SAVED')
            );

            return true;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');

        return true;
    }

    /**
     * Cancel frontend team editing without modifying the database.
     *
     * Directs the legacy component popup to its close view.
     *
     * @param string|null $key Optional legacy record key.
     * @return bool Always true after setting the redirect.
     */
    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');

        return true;
    }

    /**
     * Parse a positive team or project redirect ID from legacy form fields.
     *
     * Rejects arrays, non-decimal text and values exceeding the PHP integer
     * range before including a record identifier in an editing URL.
     *
     * @param mixed $value Raw submitted navigation identifier.
     * @return int Valid positive ID or zero for invalid input.
     */
    private static function redirectId(mixed $value): int
    {
        if (!is_int($value) && (!is_string($value) || !preg_match('/^[0-9]+$/D', $value))) {
            return 0;
        }

        if (is_string($value)) {
            $digits = ltrim($value, '0');
            $value = $digits === '' ? '0' : $digits;
        }

        $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $id === false ? 0 : $id;
    }

    /**
     * Retrieve the native Joomla 5/6 team editing model.
     *
     * Verifies the MVC factory result before allowing the save task to
     * invoke the model's team-update operation.
     *
     * @return EditteamModel Validated team editing model.
     * @throws \RuntimeException If the expected model cannot be loaded.
     */
    private function editTeamModel(): EditteamModel
    {
        $model = $this->getModel('Editteam');

        if (!$model instanceof EditteamModel) {
            throw new \RuntimeException('EditteamModel is unavailable.', 500);
        }

        return $model;
    }
    /**
     * Enforce component edit permission for frontend team updates.
     *
     * The presence of a valid session token does not itself grant write
     * access: an existing Joomla identity must also be authorised for
     * core.edit on com_sportsmanagement. Missing identities are denied
     * without dereferencing null on PHP 8.
     *
     * @return void
     * @throws \RuntimeException If the identity is not allowed to edit.
     */
    private function assertEditorPermission(): void
    {
        $identity = $this->app->getIdentity();
        $allowed = $identity !== null && $identity->authorise('core.edit', 'com_sportsmanagement');

        if (!$allowed) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

}
