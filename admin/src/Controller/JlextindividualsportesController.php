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

use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchAdminService;
use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchWriteService;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

/** Joomla 5/6 administrator controller for the individual-match list/editor. */
final class JlextindividualsportesController extends SportsManagementAdminController
{
    /**
     * Generate individual matches from the submitted pairing data after checking creation permission. Pass the active administrator identity and modification time to the service, then redirect to the modal close view with the generation result.
     * @return void
     */
    public function generatematchsingles(): void
    {
        $this->assertWritePermission('core.create');

        $app = $this->app;
        $post = $this->requestData();

        try {
            [$inserted, $failed] = $this->adminService()->generateSingles(
                $post,
                (int) $app->getIdentity()->id,
                Factory::getDate()->toSql()
            );
            $message = 'Wir haben ' . $inserted . ' Spiele eingefügt!<br />'
                . 'Wir konnten ' . $failed . ' Spiele nicht einfügen!';
        } catch (\Throwable $e) {
            $message = $e->getMessage();
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $message);
    }

    /**
     * Save the selected individual match fields and close the editor through the shared save handler.
     * @return void
     */
    public function saveshort(): void
    {
        $this->saveShortAndRedirect(true);
    }

    /**
     * Save the selected individual match fields while keeping the individual-match editor open.
     * @return void
     */
    public function applyshort(): void
    {
        $this->saveShortAndRedirect(false);
    }

    /**
     * Publish the selected individual matches through the shared state-change handler, which checks edit-state permission.
     * @return void
     */
    public function publish(): void
    {
        $this->setPublishState(1);
    }

    /**
     * Unpublish the selected individual matches through the shared state-change handler, which checks edit-state permission.
     * @return void
     */
    public function unpublish(): void
    {
        $this->setPublishState(0);
    }

    /**
     * Check deletion permission, validate the selected identifiers and delegate removal to the individual-match administrator service. Redirect back to the editor with the result.
     * @return void
     */
    public function delete(): void
    {
        $this->assertWritePermission('core.delete');

        $post = $this->requestData();
        $ids = $this->selectedIds();

        try {
            $saved = $this->adminService()->deleteSingles($ids);
            $message = $saved
                ? Text::sprintf('COM_SPORTSMANAGEMENT_N_ITEMS_DELETED', count($ids))
                : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');
            $type = $saved ? 'message' : 'error';
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $type = 'error';
        }

        $this->setRedirect($this->editorUrl($post), $message, $type);
    }

    /**
     * Check edit permission and persist the submitted short-form fields for the selected individual matches using the active administrator identity. Redirect to the editor or close view according to the requested action.
     * @param bool $close Whether to close the editor after the save attempt.
     * @return void
     */
    private function saveShortAndRedirect(bool $close): void
    {
        $this->assertWritePermission('core.edit');

        $app = $this->app;
        $post = $this->requestData();

        try {
            $saved = $this->writeService()->saveShort(
                $post,
                $this->selectedIds(),
                (int) $app->getIdentity()->id,
                Factory::getDate()->toSql()
            );
            $message = $saved ? '' : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');
            $type = $saved ? 'message' : 'error';
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $type = 'error';
        }

        $this->setRedirect(
            $close ? 'index.php?option=com_sportsmanagement&view=close&tmpl=component' : $this->editorUrl($post),
            $message,
            $type
        );
    }

    /**
     * Check edit-state permission and update the publication status of the selected individual matches. Return to the editor with a translated success or failure message.
     * @param int $state Requested publication state, one for published or zero for unpublished.
     * @return void
     */
    private function setPublishState(int $state): void
    {
        $this->assertWritePermission('core.edit.state');

        $post = $this->requestData();
        $ids = $this->selectedIds();

        try {
            $saved = $this->adminService()->setPublished($ids, $state);
            $message = $saved
                ? Text::sprintf(
                    $state ? 'COM_SPORTSMANAGEMENT_N_ITEMS_PUBLISHED' : 'COM_SPORTSMANAGEMENT_N_ITEMS_UNPUBLISHED',
                    count($ids)
                )
                : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');
            $type = $saved ? 'message' : 'error';
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $type = 'error';
        }

        $this->setRedirect($this->editorUrl($post), $message, $type);
    }

    /**
     * Read the posted individual-match form and resolve the project and round identifiers from the administrator session, falling back to the submitted values.
     * @return array Submitted data with project and round context.
     */
    private function requestData(): array
    {
        $app = $this->app;
        $post = $app->getInput()->post->getArray();
        $post['project_id'] = (int) $app->getUserState('com_sportsmanagement.pid', $post['project_id'] ?? 0);
        $post['round_id'] = (int) $app->getUserState('com_sportsmanagement.rid', $post['round_id'] ?? 0);
        return $post;
    }

    /**
     * Validate the posted individual-match selection, limiting its size to 500 entries. Retain unique positive integer identifiers and discard malformed, zero or overflowing values.
     * @return int[] Validated individual-match identifiers.
     * @throws \UnexpectedValueException If the selection is not an array or exceeds the size limit.
     */
    private function selectedIds(): array
    {
        $raw = $this->app->getInput()->post->get('cid', [], 'array');

        if (!is_array($raw) || count($raw) > 500) {
            throw new \UnexpectedValueException('Invalid individual-match selection.', 400);
        }

        $ids = [];

        foreach ($raw as $candidate) {
            if (!is_string($candidate) && !is_int($candidate)) {
                continue;
            }

            $value = trim((string) $candidate);

            if (!preg_match('/^[0-9]+$/D', $value)) {
                continue;
            }

            $digits = ltrim($value, '0');

            if ($digits === '') {
                continue;
            }

            $id = filter_var($digits, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if ($id !== false) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /**
     * Construct the individual-match modal editor URL from the submitted round, match and home and away project-team identifiers.
     * @param array $post Individual-match form and navigation context.
     * @return string Component editor URL.
     */
    private function editorUrl(array $post): string
    {
        return 'index.php?option=com_sportsmanagement&view=jlextindividualsportes&tmpl=component'
            . '&rid=' . (int) ($post['round_id'] ?? 0)
            . '&id=' . (int) ($post['match_id'] ?? 0)
            . '&team1=' . (int) ($post['projectteam1_id'] ?? 0)
            . '&team2=' . (int) ($post['projectteam2_id'] ?? 0);
    }

    /**
     * Create the individual-match administrator service with the effective database connection for generation, deletion and publication operations.
     * @return IndividualMatchAdminService Administrator write service.
     */
    private function adminService(): IndividualMatchAdminService
    {
        return new IndividualMatchAdminService($this->database());
    }

    /**
     * Create the individual-match write service with the effective database connection for short-form updates.
     * @return IndividualMatchWriteService Short-form persistence service.
     */
    private function writeService(): IndividualMatchWriteService
    {
        return new IndividualMatchWriteService($this->database());
    }

    /**
     * Resolve the administrator's configured SportsManagement database through the compatibility helper, loading it if necessary. Fall back to Joomla's primary database service when the optional connection cannot be obtained.
     * @return DatabaseInterface Effective database connection.
     */
    private function database(): DatabaseInterface
    {
        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        $app = $this->app;
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );

        try {
            $db = \sportsmanagementHelper::getDBConnection(true, $selector);
            if ($db instanceof DatabaseInterface) {
                return $db;
            }
        } catch (\Throwable) {
        }

        return \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);
    }
}
