<?php
/**
 * SportsManagement legacy administrator playground view.
 *
 * Data preparation now uses Joomla 5/6 helpers/services. This file remains as
 * a compatibility view until the fieldset markup is moved to admin/tmpl.
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\PlaygroundGeocoder;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class sportsmanagementViewPlayground extends sportsmanagementView
{
    public function init()
    {
        $model = $this->getModel();
        $database = $model && method_exists($model, 'getDatabase') ? $model->getDatabase() : null;
        $extendedLoader = new ExtendedFormHelper();

        $this->extended = $extendedLoader->load(
            'extended',
            'playground',
            (string) ($this->item->extended ?? '')
        );
        $this->extendeduser = $extendedLoader->load(
            'extendeduser',
            'playground',
            (string) ($this->item->extendeduser ?? '')
        );

        $this->lists = [];
        $this->lists['ext_fields'] = (new ExtraFieldsReadHelper())->getFields(
            (int) ($this->item->id ?? 0),
            'playground',
            'backend',
            $database
        );
        $this->checkextrafields = $this->lists['ext_fields'] !== [];

        $this->applyGeocoding();

        if ($this->item->id) {
            $this->playgroundnotic = $this->model->getPlaygroundNotic($this->item->id);
            $this->logohistory = $this->model->getlogohistoryPlayground($this->item->id, 0);
        } else {
            $this->playgroundnotic = [];
            $this->logohistory = [];
        }

        $this->namevisitorsoptions = [];

        foreach ([
            'NAME' => Text::_('NAME'),
            'VISITORS' => Text::_('VISITORS'),
        ] as $key => $value) {
            $this->namevisitorsoptions[] = HTMLHelper::_('select.option', $key, $value);
        }
    }

    private function applyGeocoding(): void
    {
        if (!class_exists(PlaygroundGeocoder::class)) {
            $serviceFile = JPATH_ADMINISTRATOR
                . '/components/com_sportsmanagement/src/Service/PlaygroundGeocoder.php';

            if (is_file($serviceFile)) {
                require_once $serviceFile;
            }
        }

        $model = $this->getModel();

        if (class_exists(PlaygroundGeocoder::class) && $model && method_exists($model, 'getDatabase')) {
            try {
                $result = (new PlaygroundGeocoder($model->getDatabase()))->geocode($this->item);

                if ($result !== null) {
                    if ($result['state'] !== '') {
                        $this->item->state = $result['state'];
                        $this->form->setValue('state', null, $result['state']);
                    }

                    if ($result['latitude'] !== null) {
                        $this->item->latitude = $result['latitude'];
                        $this->form->setValue('latitude', null, $result['latitude']);
                    }

                    if ($result['longitude'] !== null) {
                        $this->item->longitude = $result['longitude'];
                        $this->form->setValue('longitude', null, $result['longitude']);
                    }
                }
            } catch (\Throwable) {
                // The editor must remain usable when the external geocoder is unavailable.
            }
        }

        $latitude = is_numeric($this->item->latitude ?? null) ? (float) $this->item->latitude : 255.0;
        $longitude = is_numeric($this->item->longitude ?? null) ? (float) $this->item->longitude : 255.0;
        $this->map = $latitude >= -90.0 && $latitude <= 90.0
            && $longitude >= -180.0 && $longitude <= 180.0;

        if (!$this->map) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'warning');
        }
    }

    protected function addToolBar()
    {
        $this->jinput->set('hidemainmenu', true);
        parent::addToolbar();
    }
}
