<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Editprojectteam view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Editprojectteam;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditprojectteamModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Form;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;

/** Joomla 5/6 frontend view for project-team editing. */
final class HtmlView extends SportsManagementHtmlView
{
    public object $item;
    public ?object $team_info = null;
    public Form|false $form;
    public Form|false $extended;
    public array $lists = [];
    public int $cfg_which_media_tool = 0;
    public int $projectId = 0;
    public int $teamId = 0;

    /**
     * Prepare and render the frontend project-team editing interface.
     *
     * Resolve the native MVC model and a valid team record, enrich an
     * existing project-team with its team name, obtain the edit form and
     * optional extended fields, and populate project and team identifiers.
     * Register Joomla validation and project-team assets for the layout.
     *
     * @param string|null $tpl Optional Joomla layout template suffix.
     * @return void Joomla's base view renders the form.
     * @throws \RuntimeException If model, record or form data is missing.
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EditprojectteamModel) {
            throw new \RuntimeException('EditprojectteamModel is unavailable.', 500);
        }

        $item = $model->getData();

        if (!is_object($item)) {
            throw new \RuntimeException('Editprojectteam data is unavailable.', 500);
        }

        $this->item = $item;

        if ((int) ($this->item->id ?? 0) > 0) {
            $this->team_info = $model->getTeamInfo((int) $this->item->id);

            if ($this->team_info !== null && !empty($this->team_info->name)) {
                $this->item->name = (string) $this->team_info->name;
            }
        }

        $this->form = $model->getForm();

        if (!$this->form) {
            throw new \RuntimeException('Editprojectteam form is unavailable.', 500);
        }

        $this->extended = $this->buildExtendedForm((string) ($this->item->extended ?? ''));
        $this->cfg_which_media_tool = (int) $this->params->get('cfg_which_media_tool', 0);
        $this->projectId = $this->input->getInt('p', 0);
        $this->teamId = $this->input->getInt('tid', 0);

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $webAssetManager = $document->getWebAssetManager();
            $webAssetManager->useScript('form.validate');
            if (!$webAssetManager->assetExists('script', 'com_sportsmanagement.editprojectteam')) {
                $webAssetManager->registerScript(
                    'com_sportsmanagement.editprojectteam',
                    'components/com_sportsmanagement/assets/js/editprojectteam.js',
                    ['version' => 'auto'],
                    ['defer' => true],
                    ['core']
                );
            }

            $webAssetManager->useScript('com_sportsmanagement.editprojectteam');
        }

        parent::display($tpl);
    }

    /**
     * Load and bind the optional projectteam extension form.
     * Delegates XML path validation, native Joomla FormFactory creation,
     * persisted Registry binding and safe failure handling to the shared
     * frontend helper. No deprecated static form instance is required.
     * @param string $data Stored extended field values.
     * @return Form|false Bound extension form or false when unavailable.
     */
    private function buildExtendedForm(string $data): Form|false
    {
        return ExtendedFormHelper::load($data, 'projectteam');
    }
}
