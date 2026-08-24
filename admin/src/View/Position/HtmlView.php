<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Position;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PositionModel;
use Joomla\CMS\Factory;
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

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Position form could not be loaded.', 500);
        }

        $model = $this->getModel();
        $positionId = (int) ($this->item->id ?? 0);
        $sportsTypeId = (int) ($this->item->sports_type_id ?? 0);

        if ($model instanceof PositionModel) {
            $this->assignedEvents = $model->getAssignedEvents($positionId);
            $this->availableEvents = $model->getAvailableEvents($positionId, $sportsTypeId);
            $this->assignedStatistics = $model->getAssignedStatistics($positionId);
            $this->availableStatistics = $model->getAvailableStatistics($positionId);
        }

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

    private function registerAssignmentScript(): void
    {
        $this->getDocument()->getWebAssetManager()->addInlineScript(<<<'JS'
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
}
