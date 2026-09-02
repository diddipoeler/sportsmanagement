<?php
/**
 * Joomla 5/6 frontend controller for match editing actions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\IndividualMatchWriteService;
use Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend controller for match editing actions. */
final class EditmatchController extends FormController
{
    public function applyshortsinglematch(): void
    {
        $this->saveIndividualShort();
    }

    public function saveshortsinglematch(): void
    {
        $this->saveIndividualShort();
    }

    /** Preserve the historical no-op task until individual-sport deletion gets an explicit product decision. */
    public function deletesinglematch(): void
    {
        $this->setRedirect($this->returnUrl(), Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED'));
    }

    public function savestats(): void
    {
        $post = $this->getApplication()->getInput()->post->getArray();
        $saved = $this->editMatchModel()->savestats($post);
        $message = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_UPDATE_STATS'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_ERROR_UPDATE_STATS');

        $this->setRedirect($this->returnUrl(), $message);
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', 'cancel');
        return true;
    }

    public function saveReferees(): void
    {
        $post = $this->getApplication()->getInput()->post->getArray();
        $saved = $this->editMatchModel()->updateReferees($post);
        $message = Text::_($saved
            ? 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES'
            : 'COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED_MR_REFEREES_ERROR');

        $this->setRedirect($this->returnUrl(), $message);
    }

    public function saverosterbillard(): void
    {
        $post = $this->getApplication()->getInput()->post->getArray();
        $saved = $this->editMatchModel()->updateRosterBillard($post);
        $message = $saved
            ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED')
            : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    public function saveroster(): void
    {
        $post = $this->getApplication()->getInput()->post->getArray();
        $model = $this->editMatchModel();
        $playersSaved = $model->updateRoster($post);
        $staffSaved = $model->updateStaff($post);
        $saved = $playersSaved && $staffSaved;

        $this->setRedirect(
            $this->returnUrl(),
            $saved ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED') : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            $saved ? 'message' : 'error'
        );
    }

    public function saveshort(): void
    {
        $app = $this->getApplication();
        $post = $app->getInput()->post->getArray();
        $matchId = (int) ($post['matchid'] ?? 0);

        if ($matchId <= 0) {
            $this->setRedirect($this->returnUrl(), Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            return;
        }

        $data = [
            'id' => $matchId,
            'team1_bonus' => $this->nullIfEmpty($post['team1_bonus'] ?? null),
            'team2_bonus' => $this->nullIfEmpty($post['team2_bonus'] ?? null),
            'team1_legs' => $this->nullIfEmpty($post['team1_legs'] ?? null),
            'team2_legs' => $this->nullIfEmpty($post['team2_legs'] ?? null),
            'modified' => Factory::getDate()->toSql(),
            'modified_by' => (int) $app->getIdentity()->id,
            'cancel' => $post['cancel'] ?? 0,
            'cancel_reason' => $post['cancel_reason'] ?? '',
            'playground_id' => $post['playground_id'] ?? null,
            'overtime' => ($post['overtime'] ?? '') === '' ? 0 : $post['overtime'],
            'count_result' => $post['count_result'] ?? 0,
            'alt_decision' => $post['alt_decision'] ?? 0,
            'team_won' => $post['team_won'] ?? 0,
            'preview' => $post['preview'] ?? '',
            'match_result_detail' => $post['match_result_detail'] ?? '',
            'show_report' => $post['show_report'] ?? 0,
            'summary' => $post['summary'] ?? '',
            'old_match_id' => $post['old_match_id'] ?? 0,
            'new_match_id' => $post['new_match_id'] ?? 0,
            'team1_result_decision' => $post['team1_result_decision'] ?? null,
            'team2_result_decision' => $post['team2_result_decision'] ?? null,
            'decision_info' => $post['decision_info'] ?? '',
        ];

        if (isset($post['extended']) && is_array($post['extended'])) {
            $registry = new Registry();
            $registry->loadArray($post['extended']);
            $data['extended'] = (string) $registry;
        }

        $saved = $this->editMatchModel()->updItem($data);
        $message = $saved
            ? sprintf(Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_SAVED'), $matchId)
            : Text::_('JERROR_AN_ERROR_HAS_OCCURRED');

        $this->setRedirect($this->returnUrl(), $message, $saved ? 'message' : 'error');
    }

    private function editMatchModel(): EditmatchModel
    {
        $model = $this->getModel('Editmatch');
        if (!$model instanceof EditmatchModel) {
            throw new \RuntimeException('EditmatchModel is unavailable.', 500);
        }

        return $model;
    }

    private function saveIndividualShort(): void
    {
        $app = $this->getApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $ids = (array) $input->post->get('cid', [], 'array');

        try {
            $saved = $this->individualMatchWriteService()->saveShort(
                $post,
                $ids,
                (int) $app->getIdentity()->id,
                Factory::getDate()->toSql()
            );
        } catch (\Throwable $e) {
            $saved = false;
            $app->enqueueMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(
            $this->returnUrl(),
            $saved ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCH_CTRL_SAVED') : Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            $saved ? 'message' : 'error'
        );
    }

    private function individualMatchWriteService(): IndividualMatchWriteService
    {
        return new IndividualMatchWriteService($this->database());
    }

    private function database(): DatabaseInterface
    {
        $app = $this->getApplication();
        $selector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = \Joomla\CMS\Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, $selector);
    }

    private function nullIfEmpty(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }

    private function returnUrl(): string
    {
        $referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
        if ($referer !== '') {
            $current = Uri::getInstance();
            $target = Uri::getInstance($referer);
            if ($target->getHost() === '' || strcasecmp($target->getHost(), $current->getHost()) === 0) {
                return $referer;
            }
        }

        return 'index.php?option=com_sportsmanagement&view=editmatch';
    }
}
