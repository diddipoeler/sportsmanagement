<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Editperson view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Editperson;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;
use Diddipoeler\Component\SportsManagement\Site\Service\PersonExtraFieldReadService;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Diddipoeler\Component\SportsManagement\Site\Helper\ExtendedFormHelper;

/** Joomla 5/6 frontend view for person editing. */
final class HtmlView extends SportsManagementHtmlView
{
    public object $item;
    public Form|false $form;
    public Form|false $extended = false;
    public bool $checkextrafields = false;
    public array $lists = [];
    public int $cfg_which_database = 0;
    public int $projectId = 0;
    public int $teamId = 0;
    public int $personId = 0;

    /**
     * Render the editable person profile and bind its requested fields.
     *
     * @param mixed $tpl tpl argument.
     * @return mixed
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        if (!$model instanceof EditpersonModel) {
            throw new \RuntimeException('EditpersonModel is unavailable.', 500);
        }

        $this->item = $model->getData();
        $this->form = $model->getForm();

        if (!$this->form) {
            throw new \RuntimeException('Editperson form is unavailable.', 500);
        }

        $this->normaliseDates();
        $this->bindRequestFields();

        // Preserve the legacy behaviour: getExtended(..., 'person') returned false when person.xml is absent.
        $this->extended = $this->buildExtendedForm((string) ($this->item->extended ?? ''), 'person');

        $extraFields = new PersonExtraFieldReadService($model->getSportsManagementDatabase());
        $this->checkextrafields = $extraFields->hasFields('frontend', 'clubinfo');

        if ($this->checkextrafields) {
            $this->lists['ext_fields'] = $extraFields->fields((int) ($this->item->id ?? 0), 'frontend', 'clubinfo');
        }

        $this->cfg_which_database = $this->databaseSelector;
        $this->projectId = $this->input->getInt('p', 0);
        $this->teamId = $this->input->getInt('tid', 0);
        $this->personId = $this->input->getInt('id', 0);

        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $webAssetManager = $document->getWebAssetManager();
            $webAssetManager->useScript('form.validate');
            $webAssetManager->registerAndUseScript(
                'com_sportsmanagement.editperson',
                Uri::root(true) . '/components/com_sportsmanagement/assets/js/editperson.js',
                [],
                ['defer' => true],
                ['core']
            );
        }

        parent::display($tpl);
    }

    /**
     * Normalise unset person birth and death dates for the form.
     * @return void
     */
    private function normaliseDates(): void
    {
        if ((int) ($this->item->id ?? 0) > 0) {
            if (($this->item->birthday ?? '') === '0000-00-00') {
                $this->item->birthday = '';
                $this->form->setValue('birthday', null, '');
            }

            if (($this->item->deathday ?? '') === '0000-00-00') {
                $this->item->deathday = '';
                $this->form->setValue('deathday', null, '');
            }

            return;
        }

        $this->form->setValue('birthday', null, '');
        $this->form->setValue('deathday', null, '');
    }

    /**
     * Fill extended person selector fields using current item values.
     * @return void
     */
    private function bindRequestFields(): void
    {
        foreach ([
            'sports_type_id',
            'position_id',
            'agegroup_id',
            'person_art',
            'person_id1',
            'person_id2',
        ] as $field) {
            $this->form->setValue($field, 'request', $this->item->{$field} ?? 0);
        }
    }

    /**
     * Load and bind the optional person extension form.
     * Delegates XML path validation, native Joomla FormFactory creation,
     * persisted Registry binding and safe failure handling to the shared
     * frontend helper. No deprecated static form instance is required.
     * @param string $data Stored extended field values.
     * @param string $file Installed extension definition identifier.
     * @return Form|false Bound extension form or false when unavailable.
     */
    private function buildExtendedForm(string $data, string $file): Form|false
    {
        return ExtendedFormHelper::load($data, $file);
    }
}
