<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editprojectteam;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend view for project-team editing. */
final class HtmlView extends SportsManagementHtmlView
{
    public object $item;
    public ?object $team_info = null;
    public Form|false $form;
    public Form|false $extended;
    public array $lists = [];
    public int $cfg_which_media_tool = 0;

    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EditprojectteamModel) {
            throw new \RuntimeException('EditprojectteamModel is unavailable.', 500);
        }

        $this->item = $model->getData();

        if ((int) ($this->item->id ?? 0) > 0) {
            $this->team_info = $model->getTeamInfo((int) $this->item->id);

            if ($this->team_info !== null && !empty($this->team_info->name)) {
                $this->item->name = (string) $this->team_info->name;
            }
        }

        $this->form = $model->getForm();
        $this->extended = $this->buildExtendedForm((string) ($this->item->extended ?? ''));
        $this->cfg_which_media_tool = (int) $this->params->get('cfg_which_media_tool', 0);
        $this->getDocument()->getWebAssetManager()->useScript('form.validate');

        parent::display($tpl);
    }

    private function buildExtendedForm(string $data): Form|false
    {
        $xmlFile = JPATH_ADMINISTRATOR
            . '/components/com_sportsmanagement/assets/extended/projectteam.xml';
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
