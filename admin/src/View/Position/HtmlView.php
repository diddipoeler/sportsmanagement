<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Position edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Position;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionModel;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a position. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    /** @var array<int, object> */
    public array $assignedEvents = [];

    /** @var array<int, object> */
    public array $availableEvents = [];

    /** @var array<int, object> */
    public array $assignedStatistics = [];

    /** @var array<int, object> */
    public array $availableStatistics = [];

    /**
     * Render the position editor and its event/statistic assignment lists.
     *
     * Require the native PositionModel, read form, item and state directly,
     * redact model errors, and retrieve assigned
     * and available events and statistics for the selected sport, and attach
     * JavaScript for the dual-list controls. Set an add/edit toolbar title
     * and expose the position controller's save and cancel commands.
     *
     * @param string|null $tpl Optional edit layout template suffix.
     * @return void The Joomla parent view outputs the editor.
     * @throws \RuntimeException If form loading fails.
     */
    public function display($tpl = null)
    {
        $app = self::administratorApplication();
        $app->getInput()->set('hidemainmenu', true);

        $model = $this->getModel();

        if (!$model instanceof PositionModel) {
            throw new \RuntimeException('PositionModel is unavailable.', 500);
        }

        $this->form = $model->getForm();
        $this->item = $model->getItem();
        $this->state = $model->getState();

        if ($errors = $model->getErrors()) {
            Log::add(__METHOD__ . ': model reported ' . count((array) $errors) . ' errors.', Log::ERROR, 'com_sportsmanagement');
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
        }

        if (!$this->form instanceof Form || !is_object($this->item)) {
            throw new \RuntimeException('Position form could not be loaded.', 500);
        }

        $positionId = (int) ($this->item->id ?? 0);
        $sportsTypeId = (int) ($this->item->sports_type_id ?? 0);

        $this->assignedEvents = $model->getAssignedEvents($positionId);
        $this->availableEvents = $model->getAvailableEvents($positionId, $sportsTypeId);
        $this->assignedStatistics = $model->getAssignedStatistics($positionId);
        $this->availableStatistics = $model->getAvailableStatistics($positionId);

        $this->registerAssignmentScript();

        $isNew = $positionId <= 0;
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_POSITION_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_POSITION_EDIT'),
            'position'
        );
        ToolbarHelper::apply('position.apply');
        ToolbarHelper::save('position.save');
        ToolbarHelper::save2new('position.save2new');
        ToolbarHelper::save2copy('position.save2copy');
        ToolbarHelper::cancel('position.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    /**
     * Attach JavaScript for editing position event and statistic relations.
     *
     * Only HTML documents receive the inline module script. The controls
     * move selected options between available and assigned lists, reorder
     * assignments and select submitted options so Joomla includes all
     * retained relationships in the form request.
     *
     * @return void The inline asset is attached when supported.
     */
    private function registerAssignmentScript(): void
    {
        $document = $this->getDocument();

        if (!$document instanceof HtmlDocument) {
            return;
        }

        $document->getWebAssetManager()->addInlineScript(<<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('position-form');

    if (!form) {
        return;
    }

    const moveSelected = (sourceId, destinationId) => {
        const source = document.getElementById(sourceId);
        const destination = document.getElementById(destinationId);

        if (!source || !destination) {
            return;
        }

        Array.from(source.selectedOptions).forEach((option) => destination.append(option));
    };

    const moveOrdered = (selectId, direction) => {
        const select = document.getElementById(selectId);

        if (!select) {
            return;
        }

        const selected = Array.from(select.selectedOptions);

        if (direction === 'up') {
            selected.forEach((option) => {
                const previous = option.previousElementSibling;

                if (previous && !previous.selected) {
                    select.insertBefore(option, previous);
                }
            });

            return;
        }

        selected.reverse().forEach((option) => {
            const next = option.nextElementSibling;

            if (next && !next.selected) {
                select.insertBefore(next, option);
            }
        });
    };

    const selectAssignedOptions = () => {
        ['position_eventslist', 'position_statistic'].forEach((id) => {
            const select = document.getElementById(id);

            if (select) {
                Array.from(select.options).forEach((option) => {
                    option.selected = true;
                });
            }
        });
    };

    form.querySelectorAll('[data-jsm-move]').forEach((button) => {
        button.addEventListener('click', () => moveSelected(button.dataset.source, button.dataset.destination));
    });

    form.querySelectorAll('[data-jsm-order]').forEach((button) => {
        button.addEventListener('click', () => moveOrdered(button.dataset.target, button.dataset.jsmOrder));
    });

    form.addEventListener('submit', selectAssignedOptions);

    if (window.Joomla && typeof window.Joomla.submitbutton === 'function') {
        const originalSubmitbutton = window.Joomla.submitbutton;

        window.Joomla.submitbutton = function (task) {
            selectAssignedOptions();
            return originalSubmitbutton.call(this, task);
        };
    }
});
JS);
    }

    /**
     * Resolve the Joomla backend application for the position editor.
     *
     * Reuse the shared administrator application resolver to validate the
     * backend client before accessing input state.
     *
     * @return AdministratorApplication Validated Joomla backend application.
     * @throws \RuntimeException If the current client is not administrator.
     */
    private static function administratorApplication(): AdministratorApplication
    {
        return SportsManagementAdministratorApplicationResolver::resolve();
    }
}
