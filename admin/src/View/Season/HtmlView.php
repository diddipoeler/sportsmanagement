<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Season edit view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Season;
\defined('_JEXEC') or die;
use Diddipoeler\Component\SportsManagement\Administrator\Model\SeasonModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;

    /**
     * Render the season editing form and configure its administrator toolbar.
     *
     * Require the native season model and obtain its editable record, form
     * and state directly. Report model errors generically to protect internal
     * diagnostics, then choose the add or edit title according to the record
     * identifier. Register save, apply, copy and cancel toolbar actions before
     * delegating layout rendering to Joomla's base view.
     *
     * @param string|null $tpl Optional layout template suffix.
     * @return void The parent view renders the selected administrator layout.
     * @throws \RuntimeException When the model reports an error.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof SeasonModel) {
            throw new \RuntimeException('SeasonModel is unavailable.', 500);
        }

        $this->form = $model->getForm();
        $this->item = $model->getItem();
        $this->state = $model->getState();

        if ($errors = $model->getErrors()) {
            Log::add(__METHOD__ . ': model reported ' . count((array) $errors) . ' errors.', Log::ERROR, 'com_sportsmanagement');
            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
        }

        if (!$this->form instanceof Form || !is_object($this->item)) {
            throw new \RuntimeException('Season form could not be loaded.', 500);
        }

        $isNew = empty($this->item->id);
        ToolbarHelper::title(Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_SEASON_ADD_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_SEASON_EDIT'), 'calendar');
        ToolbarHelper::apply('season.apply');
        ToolbarHelper::save('season.save');
        ToolbarHelper::save2new('season.save2new');
        ToolbarHelper::save2copy('season.save2copy');
        ToolbarHelper::cancel('season.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        parent::display($tpl);
    }
}
