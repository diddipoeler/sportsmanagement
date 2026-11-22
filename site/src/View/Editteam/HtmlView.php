<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Editteam view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Editteam;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Document\HtmlDocument;
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
    public int $projectId = 0;
    public int $teamId = 0;
    public int $projectTeamId = 0;

    /**
     * Load and render the frontend team editing form.
     *
     * Resolve the native team model and verify both its record and Joomla
     * form before reading any properties. Prepare optional extended fields,
     * module parameters and project/team request identifiers, then activate
     * Joomla validation and the team editing asset before rendering.
     *
     * @param string|null $tpl Optional Joomla layout suffix.
     * @return void The inherited view renders the editing form.
     * @throws \RuntimeException If the model, record or form is unavailable.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EditteamModel) {
            throw new \RuntimeException('EditteamModel is unavailable.', 500);
        }

        $item = $model->getData();

        if (!is_object($item)) {
            throw new \RuntimeException('Editteam data is unavailable.', 500);
        }

        $this->item = $item;
        $this->form = $model->getForm();

        if (!$this->form instanceof Form) {
            throw new \RuntimeException('Editteam form is unavailable.', 500);
        }

        $this->extended = $this->buildExtendedForm((string) ($this->item->extended ?? ''));
        $this->cfg_which_media_tool = (int) $this->params->get('cfg_which_media_tool', 0);
        $this->projectId = $this->input->getInt('p', 0);
        $this->teamId = $this->input->getInt('tid', 0);
        $this->projectTeamId = $this->input->getInt('ptid', 0);

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $webAssetManager = $document->getWebAssetManager();
            $webAssetManager->useScript('form.validate');
            if (!$webAssetManager->assetExists('script', 'com_sportsmanagement.editteam')) {
                $webAssetManager->registerScript(
                    'com_sportsmanagement.editteam',
                    'components/com_sportsmanagement/assets/js/editteam.js',
                    ['version' => 'auto'],
                    ['defer' => true],
                    ['core']
                );
            }

            $webAssetManager->useScript('com_sportsmanagement.editteam');
        }

        parent::display($tpl);
    }

    /**
     * Construct the extended team fields form from its administrator XML.
     *
     * Decode the team's stored extension settings into a Joomla Registry,
     * load the team-specific XML form definition and bind all settings to
     * the extended form so templates can render additional editable fields.
     *
     * @param string $data Persisted Joomla registry configuration.
     * @return Form|false Bound extended form or false if creation failed.
     */
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
