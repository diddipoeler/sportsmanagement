<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Player;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\PlayerModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator form view for persons/players. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public $user;
    public $extended = null;
    public $extendeduser = null;
    public array $lists = [];
    public int $checkextrafields = 0;
    public bool $map = true;
    public string $option = 'com_sportsmanagement';
    public string $tmpl = '';

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);

        $this->user = $app->getIdentity();
        $this->tmpl = $input->getCmd('tmpl', '');
        $this->setLayout('edit');

        $model = $this->getModel();
        if (!$model instanceof PlayerModel) {
            throw new \RuntimeException('PlayerModel is unavailable.', 500);
        }

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }
        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Player form could not be loaded.', 500);
        }

        if (!class_exists('sportsmanagementHelper', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php';
        }

        foreach (['sports_type_id', 'position_id', 'agegroup_id', 'person_art', 'person_id1', 'person_id2'] as $field) {
            $this->form->setValue($field, 'request', $this->item->{$field} ?? null);
        }

        foreach (['birthday', 'deathday', 'injury_date_start', 'injury_date_end', 'susp_date_start', 'susp_date_end', 'away_date_start', 'away_date_end'] as $field) {
            if (($this->item->{$field} ?? '') === '0000-00-00') {
                $this->item->{$field} = '';
                $this->form->setValue($field, null, '');
            }
        }

        $this->map = (float) ($this->item->latitude ?? 0) !== 255.0;
        if (!$this->map) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'warning');
        }

        $this->extended = \sportsmanagementHelper::getExtended(
            (string) ($this->item->extended ?? ''),
            'player'
        );
        $this->extendeduser = \sportsmanagementHelper::getExtendedUser(
            (string) ($this->item->extendeduser ?? ''),
            'player'
        );
        $this->checkextrafields = (int) \sportsmanagementHelper::checkUserExtraFields('backend', 0, 'player');

        if ($this->checkextrafields && (int) ($this->item->id ?? 0) > 0) {
            $this->lists['ext_fields'] = \sportsmanagementHelper::getUserExtraFields(
                (int) $this->item->id,
                'backend',
                0,
                'player'
            );
        }

        $birthday = $this->form->getValue('birthday');
        $deathday = $this->form->getValue('deathday');
        if ($birthday) {
            $personAge = \sportsmanagementHelper::getAge($birthday, $deathday);
            $personRange = $model->getAgeGroupID($personAge);
            if ($personRange) {
                $this->form->setValue('agegroup_id', 'request', (int) $personRange);
            }
        }

        $document = $this->getDocument();
        $document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/sm_functions.js');
        $document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/editgeocode.js');

        $language = $app->getLanguage();
        $language->load('com_contact', JPATH_ADMINISTRATOR, 'en-GB', true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, $language->getDefault(), true);
        $language->load('com_contact', JPATH_ADMINISTRATOR, null, true);

        $name = trim((string) ($this->item->lastname ?? '') . ' ' . (string) ($this->item->firstname ?? ''));
        $app->setUserState($this->option . '.itemname', $name);

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        $isNew = (int) ($this->item->id ?? 0) === 0;
        $identity = $this->user;
        $canEdit = $identity->authorise('core.edit', $this->option);
        $canCreate = $identity->authorise('core.create', $this->option);

        ToolbarHelper::title(
            $isNew
                ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_NEW')
                : Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSON_EDIT'),
            'user'
        );

        if (($isNew && $canCreate) || (!$isNew && $canEdit)) {
            ToolbarHelper::apply('player.apply');
            ToolbarHelper::save('player.save');
        }
        if ($canCreate) {
            ToolbarHelper::save2new('player.save2new');
            if (!$isNew) {
                ToolbarHelper::save2copy('player.save2copy');
            }
        }

        ToolbarHelper::cancel('player.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
        ToolbarHelper::back(
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_TITLE'),
            Route::_('index.php?option=com_sportsmanagement&view=players', false)
        );
    }
}
