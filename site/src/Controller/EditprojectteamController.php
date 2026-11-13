<?php
/**
 * Joomla 5/6 frontend controller for project-team editing.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for project-team editing. */
final class EditprojectteamController extends FormController
{
    /**
     * Apply project-team changes via the controller's secured save task.
     *
     * Reuses save() so Joomla's CSRF token and component ACL checks apply
     * equally to Apply and Save actions.
     *
     * @param string|null $key Optional legacy record key.
     * @param string|null $urlVar Optional URL record identifier.
     * @return bool Whether the model update succeeded.
     */
    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    /**
     * Retain the legacy project-team submit task without writing records.
     *
     * Actual changes must use save(), which validates the form token and
     * edit authorisation before calling the project-team model.
     *
     * @return bool Always true.
     */
    public function submit()
    {
        return true;
    }

    /**
     * Save edited project-team values submitted through the frontend form.
     *
     * Validates Joomla's form token and the user's component edit rights
     * before delegating persistence to EditprojectteamModel::updItem().
     * Keeps the selected IDs in the editor redirect after Apply or errors;
     * closes the editor popup after an ordinary successful Save. Validates
     * route identifiers separately to avoid PHP 8 implicit array casts.
     *
     * @param string|null $key Optional legacy record key.
     * @param string|null $urlVar Optional legacy URL identifier.
     * @return bool True on successful persistence, otherwise false.
     * @throws \RuntimeException When authorisation or model resolution fails.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();
        $this->assertEditorPermission();

        $input = $this->input;
        $data = $input->post->getArray();
        $id = $input->getInt('id', 0);
        $saved = $this->editProjectTeamModel()->updItem($data);

        if (!$saved) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editprojectteam&tmpl=component&id=' . $id
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
                'index.php?option=com_sportsmanagement&view=editprojectteam&tmpl=component&id=' . $id
                . '&ptid=' . self::redirectId($data['ptid'] ?? 0)
                . '&p=' . self::redirectId($data['p'] ?? 0)
                . '&tid=' . self::redirectId($data['tid'] ?? 0),
                Text::_('COM_SPORTSMANAGEMENT_EDIT_PROJECTTEAM_SAVED')
            );

            return true;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');

        return true;
    }

    /**
     * Exit the frontend project-team editing popup without saving.
     *
     * Sets the component close-view redirect and preserves legacy
     * controller expectations by returning true.
     *
     * @param string|null $key Optional legacy record identifier.
     * @return bool Always true.
     */
    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');

        return true;
    }

    /**
     * Parse a positive project-team navigation ID without implicit casts.
     *
     * Accepts validated decimal strings with legacy leading zeros; rejects
     * malformed arrays, signs, fractional numbers and integer overflow.
     *
     * @param mixed $value Submitted team or project redirect identifier.
     * @return int Positive record ID or zero for invalid input.
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
     * Check whether the Joomla identity may edit project-team records.
     *
     * Requires core.edit for com_sportsmanagement independently of
     * successful CSRF verification so unauthorised users cannot write.
     *
     * @return void
     * @throws \RuntimeException When component edit permission is missing.
     */
    private function assertEditorPermission(): void
    {
        if (!$this->app->getIdentity()->authorise('core.edit', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

    /**
     * Obtain the native project-team edit model from Joomla's MVC factory.
     *
     * Rejects missing or unexpected models before calling updItem(), so
     * controller write actions operate only on the supported model type.
     *
     * @return EditprojectteamModel The validated project-team edit model.
     * @throws \RuntimeException If the model is unavailable.
     */
    private function editProjectTeamModel(): EditprojectteamModel
    {
        $model = $this->getModel('Editprojectteam');

        if (!$model instanceof EditprojectteamModel) {
            throw new \RuntimeException('EditprojectteamModel is unavailable.', 500);
        }

        return $model;
    }
}
