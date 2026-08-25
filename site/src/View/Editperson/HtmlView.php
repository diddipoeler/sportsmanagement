<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Editperson;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel;
use Diddipoeler\Component\SportsManagement\Site\Service\PersonExtraFieldReadService;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

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

        $extraFields = new PersonExtraFieldReadService($model->getDatabase());
        $this->checkextrafields = $extraFields->hasFields('frontend', 'clubinfo');

        if ($this->checkextrafields) {
            $this->lists['ext_fields'] = $extraFields->fields((int) ($this->item->id ?? 0), 'frontend', 'clubinfo');
        }

        $this->cfg_which_database = $this->databaseSelector;
        $this->projectId = $this->input->getInt('p', 0);
        $this->teamId = $this->input->getInt('tid', 0);
        $this->personId = $this->input->getInt('id', 0);

        $webAssetManager = $this->getDocument()->getWebAssetManager();
        $webAssetManager->useScript('form.validate');
        $webAssetManager->registerAndUseScript(
            'com_sportsmanagement.editperson',
            Uri::root(true) . '/components/com_sportsmanagement/assets/js/editperson.js',
            [],
            ['defer' => true],
            ['core']
        );

        parent::display($tpl);
    }

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

    private function buildExtendedForm(string $data, string $file): Form|false
    {
        $xmlFile = JPATH_ADMINISTRATOR
            . '/components/com_sportsmanagement/assets/extended/'
            . $file
            . '.xml';

        if (!is_file($xmlFile)) {
            return false;
        }

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
