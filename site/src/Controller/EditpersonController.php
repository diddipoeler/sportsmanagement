<?php
/**
 * Joomla 5/6 frontend controller for person editing.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for person editing. */
final class EditpersonController extends FormController
{
    /**
     * Apply submitted person edits while leaving the modal editor open.
     * Delegates to save(), which validates the Joomla token and authorisation
     * and uses the current task to choose the post-save redirect.
     * @param mixed $key Joomla form identifier forwarded by callers.
     * @param mixed $urlVar Joomla route identifier forwarded by callers.
     * @return bool Whether the person was saved.
     */
    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    /**
     * Return the legacy submission task's acknowledgement.
     * Preserves the historical task contract for callers that do not
     * use this method to perform a person mutation.
     * @return bool True for a recognised legacy submit task.
     */
    public function submit()
    {
        return true;
    }

    /**
     * Validate and persist a person edited in the frontend modal.
     * Requires a valid Joomla form token and edit permission, delegates
     * the update to EditpersonModel and redirects to edit or close
     * depending on whether this is the apply or save task. Redirect IDs are
     * validated separately to avoid PHP 8 array-conversion warnings.
     * @param mixed $key Joomla form record-key argument.
     * @param mixed $urlVar Joomla form route-key argument.
     * @return bool Whether the update succeeded.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();
        $this->assertEditorPermission();

        $input = $this->input;
        $data = $input->post->getArray();
        $id = $input->getInt('id', 0);
        $saved = $this->editPersonModel()->updItem($data);

        if (!$saved) {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editperson&tmpl=component&id=' . $id
                . '&pid=' . $id
                . '&p=' . self::redirectId($data['p'] ?? 0)
                . '&tid=' . self::redirectId($data['tid'] ?? 0),
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );
            return false;
        }

        if ($this->getTask() === 'apply') {
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&view=editperson&tmpl=component&id=' . $id
                . '&pid=' . $id
                . '&p=' . self::redirectId($data['p'] ?? 0)
                . '&tid=' . self::redirectId($data['tid'] ?? 0),
                Text::_('COM_SPORTSMANAGEMENT_EDIT_PERSON_SAVED')
            );
            return true;
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');
        return true;
    }

    /**
     * Close the frontend person-edit dialog without persisting data.
     * Redirects to the modal-close view while retaining Joomla's
     * historical controller task return value.
     * @param mixed $key Optional Joomla form record identifier.
     * @return bool True once the cancel redirect is configured.
     */
    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');
        return true;
    }

    /**
     * Validate a positive identifier used in the person editor redirect.
     *
     * Accepts positive integers and zero-padded decimal strings, rejecting
     * arrays, signs and overflowing numbers before URL construction.
     *
     * @param mixed $value Submitted project or team identifier.
     * @return int Valid positive ID or zero for malformed input.
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
     * Retrieve the namespaced Joomla model for person edits.
     * Verifies MVCFactory supplied an EditpersonModel before accepting
     * user-submitted data for the write operation.
     * @return EditpersonModel Validated edit model.
     * @throws \RuntimeException If MVCFactory cannot produce it.
     */
    private function editPersonModel(): EditpersonModel
    {
        $model = $this->getModel('Editperson');

        if (!$model instanceof EditpersonModel) {
            throw new \RuntimeException('EditpersonModel is unavailable.', 500);
        }

        return $model;
    }
    /**
     * Require component-level edit permission for the active Joomla identity.
     *
     * Missing identity objects and guests are denied even when a submitted
     * form contains a valid token, protecting person writes in site context.
     *
     * @return void
     * @throws \RuntimeException When no authorised editing identity exists.
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
