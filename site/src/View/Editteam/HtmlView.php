<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editteam;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Form\Form;
use Joomla\Registry\Registry;

/** Joomla 5/6 frontend view for team editing. */
final class HtmlView extends SportsManagementHtmlView
{
    public object $item;
    public Form|false $form;
    public Form|false $extended;
    public array $lists = [];
    public int $cfg_which_media_tool = 0;

    public function __construct($config = [])
    {
        $config['template_path'] = JPATH_SITE . '/components/com_sportsmanagement/views/editteam/tmpl';
        parent::__construct($config);
    }

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
