<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editclub;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\EditclubModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;

/** Joomla 5/6 frontend view for club editing. */
final class HtmlView extends SportsManagementHtmlView
{
    public object $item;
    public Form|false $form;
    public Form|false $extended = false;
    public array $lists = [];
    public int $cfg_which_media_tool = 0;
    public int $projectId = 0;
    public int $clubId = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EditclubModel) {
            throw new \RuntimeException('EditclubModel is unavailable.', 500);
        }

        $this->item = $model->getData();
        $this->form = $model->getForm();

        if (!$this->form) {
            throw new \RuntimeException('Editclub form is unavailable.', 500);
        }

        $this->normaliseDates();
        $this->item->merge_teams = explode(',', (string) ($this->item->merge_teams ?? ''));
        $this->extended = ExtendedFormHelper::load((string) ($this->item->extended ?? ''), 'club');
        $this->cfg_which_media_tool = (int) $this->params->get('cfg_which_media_tool', 0);
        $this->projectId = $this->input->getInt('p', 0);
        $this->clubId = $this->input->getInt('cid', (int) ($this->item->id ?? 0));

        $webAssetManager = $this->getDocument()->getWebAssetManager();
        $webAssetManager->useScript('form.validate');
        $webAssetManager->registerAndUseScript(
            'com_sportsmanagement.editclub',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/editclub.js',
            [],
            ['defer' => true],
            ['core']
        );
        $webAssetManager->registerAndUseScript(
            'com_sportsmanagement.editclub-geocode',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/editgeocode.js',
            [],
            ['defer' => true],
            ['core']
        );

        parent::display($tpl);
    }

    private function normaliseDates(): void
    {
        if ((int) ($this->item->id ?? 0) > 0) {
            if (($this->item->founded ?? '') === '0000-00-00') {
                $this->item->founded = '';
                $this->form->setValue('founded', null, '');
            }

            if (($this->item->dissolved ?? '') === '0000-00-00') {
                $this->item->dissolved = '';
                $this->form->setValue('dissolved', null, '');
            }

            return;
        }

        $this->form->setValue('founded', null, '');
        $this->form->setValue('dissolved', null, '');
    }
}
