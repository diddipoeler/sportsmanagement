<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editteam;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend view for team editing. */
final class HtmlView extends SportsManagementHtmlView
{
    public object $item;
    public Form|false $form;
    public Form|false $extended;
    public array $lists = [];
    public int $cfg_which_media_tool = 0;
    public int $projectId = 0;
    public int $teamId = 0;
    public int $projectTeamId = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EditteamModel) {
            throw new \RuntimeException('EditteamModel is unavailable.', 500);
        }

        $this->item = $model->getData();
        $this->form = $model->getForm();
        $this->extended = $this->buildExtendedForm((string) ($this->item->extended ?? ''));
        $this->cfg_which_media_tool = (int) $this->params->get('cfg_which_media_tool', 0);
        $this->projectId = $this->input->getInt('p', 0);
        $this->teamId = $this->input->getInt('tid', 0);
        $this->projectTeamId = $this->input->getInt('ptid', 0);

        $webAssetManager = $this->getDocument()->getWebAssetManager();
        $webAssetManager->useScript('form.validate');
        $webAssetManager->registerAndUseScript(
            'com_sportsmanagement.editteam',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/editteam.js',
            [],
            ['defer' => true],
            ['core']
        );

        parent::display($tpl);
    }

    private function buildExtendedForm(string $data): Form|false
    {
        $xmlFile = JPATH_ADMINISTRATOR
            . '/components/com_sportsmanagement/assets/extended/team.xml';
        $registry = new Registry();

        if ($data !== '') {
            $registry->loadString($data);
        }

        $form = Form::getInstance(
            'extended',
            $xmlFile,
            ['control' => 'extended'],
            false,
            '/config'
        );

        if (!$form) {
            return false;
        }

        $form->bind($registry);

        return $form;
    }
}
