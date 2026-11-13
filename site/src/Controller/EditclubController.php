<?php
/**
 * Joomla 5/6 frontend controller for club editing.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditclubModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;

/** Joomla 5/6 frontend controller for club editing. */
final class EditclubController extends FormController
{
    /**
     * Apply changes to the current club through the secured save action.
     * Delegates to save(), which validates the Joomla form token, verifies
     * component or club editing rights and updates the club using its model.
     * @param string|null $key Optional legacy record identifier.
     * @param string|null $urlVar Optional URL record identifier.
     * @return bool Whether saving the club succeeded.
     */
    public function apply($key = null, $urlVar = null)
    {
        return $this->save($key, $urlVar);
    }

    /**
     * Close the club edit popup without saving submitted changes.
     * Redirects to the legacy component close view, preserving existing
     * editor behaviour for frontend forms.
     * @param string|null $key Optional record identifier.
     * @return bool Always true after the redirect is set.
     */
    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');

        return true;
    }

    /**
     * Open a club edit form, checking out the requested club first.
     * Checks edit authorisation, reads the integer-filtered club ID and
     * checks out the corresponding table record for the current user when
     * the record exists, then delegates view rendering to Joomla.
     * @return void
     * @throws \RuntimeException If the current user cannot edit clubs.
     */
    public function load(): void
    {
        $this->assertEditorPermission();

        $app = $this->app;
        $clubId = $app->getInput()->getInt('cid', 0);

        if ($clubId > 0) {
            $table = $this->editClubModel()->getTable('club');

            if ($table->load($clubId)) {
                $table->checkout((int) $app->getIdentity()->id);
            }
        }

        parent::display();
    }

    /**
     * Persist changes from the frontend club editing form.
     * Checks Joomla's form token and component or club-specific editing
     * rights, normalises submitted team merge IDs and delegates the update
     * to EditclubModel. Redirects to the editor on failure or Apply, and
     * closes the popup following a successful ordinary Save. Redirect IDs
     * are validated separately to avoid PHP 8 array-to-integer conversion
     * warnings from malformed legacy form submissions.
     * @param string|null $key Optional record key for legacy callers.
     * @param string|null $urlVar Optional URL record identifier.
     * @return bool True when the model update succeeded.
     * @throws \RuntimeException When authorisation or model resolution fails.
     */
    public function save($key = null, $urlVar = null)
    {
        $this->checkToken();
        $this->assertEditorPermission();

        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $post['merge_teams'] = $this->normalizeMergeTeams($post['merge_teams'] ?? null);

        $saved = $this->editClubModel()->updItem($post);

        if (!$saved) {
            $clubId = self::redirectId($post['id'] ?? 0);
            $projectId = self::redirectId($post['p'] ?? 0);
            $this->setRedirect(
                'index.php?option=com_sportsmanagement&tmpl=component&view=editclub'
                . '&cid=' . $clubId
                . '&id=' . $clubId
                . '&p=' . $projectId,
                Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
                'error'
            );

            return false;
        }

        if ($this->getTask() === 'save') {
            $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component');

            return true;
        }

        $clubId = self::redirectId($post['id'] ?? 0);
        $projectId = self::redirectId($post['p'] ?? 0);

        $this->setRedirect(
            'index.php?option=com_sportsmanagement&tmpl=component&view=editclub'
            . '&cid=' . $clubId
            . '&id=' . $clubId
            . '&p=' . $projectId
        );

        return true;
    }

    /**
     * Validate a positive club or project ID used in editor redirect URLs.
     *
     * Accepts nonnegative decimal integers and zero-padded legacy strings;
     * rejects arrays, signs and integer overflow rather than coercing an
     * unintended record identifier.
     *
     * @param mixed $value Raw posted redirect identifier.
     * @return int Valid positive identifier, or zero for malformed input.
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
     * Load and type-check the native frontend club editing model.
     * Ensures update and checkout operations use an EditclubModel returned
     * by Joomla's MVC factory rather than an unavailable or invalid model.
     * @return EditclubModel Native club editing model.
     * @throws \RuntimeException If the expected model is unavailable.
     */
    private function editClubModel(): EditclubModel
    {
        $model = $this->getModel('Editclub');

        if (!$model instanceof EditclubModel) {
            throw new \RuntimeException('EditclubModel is unavailable.', 500);
        }

        return $model;
    }

    /**
     * Convert only complete positive decimal team IDs to the legacy list
     * format. Never pass nested data or arbitrary text to the table layer.
     */
    /**
     * Convert submitted team IDs into a safe legacy comma-separated list.
     * Rejects nested values, non-decimal strings, zero and integer overflow,
     * normalises leading zeros and removes duplicates without expanding
     * arbitrary user input into database expressions.
     * @param mixed $mergeTeams Raw team IDs submitted with the edit form.
     * @return string Deduplicated positive decimal IDs, comma-separated.
     */
    private function normalizeMergeTeams(mixed $mergeTeams): string
    {
        if (!is_array($mergeTeams)) {
            return '';
        }

        $ids = [];
        $max = (string) PHP_INT_MAX;

        foreach ($mergeTeams as $value) {
            if (!is_int($value) && !is_string($value)) {
                continue;
            }

            $raw = trim((string) $value);

            if ($raw === '' || !ctype_digit($raw)) {
                continue;
            }

            $digits = ltrim($raw, '0');
            $digits = $digits === '' ? '0' : $digits;

            if ($digits === '0'
                || strlen($digits) > strlen($max)
                || (strlen($digits) === strlen($max) && strcmp($digits, $max) > 0)) {
                continue;
            }

            $id = (int) $digits;
            $ids[$id] = $id;
        }

        return implode(',', $ids);
    }
    /** Ensure a frontend editor cannot write using a token without component ACL. */
    /**
     * Require frontend permissions for changing club records.
     * Allows users authorised for core.edit or club.edit on the component
     * and rejects any other identity even if the form token is valid.
     * @return void
     * @throws \RuntimeException When club editing rights are missing.
     */
    private function assertEditorPermission(): void
    {
        $identity = $this->app->getIdentity();
        $allowed = $identity->authorise('core.edit', 'com_sportsmanagement')
            || $identity->authorise('club.edit', 'com_sportsmanagement');

        if (!$allowed) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }
    }

}
