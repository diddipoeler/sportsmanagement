<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;

/** Native Joomla 5/6 administrator form model for federations. */
final class JlextfederationModel extends SportsManagementAdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.jlextfederation',
            'jlextfederation',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function getTable($type = 'jlextfederation', $prefix = 'sportsmanagementTable', $config = [])
    {
        $config['dbo'] = $this->getDatabase();

        return Table::getInstance($type, $prefix, $config);
    }

    protected function prepareSportsManagementData(array $data): array
    {
        if (!class_exists('sportsmanagementHelper')) {
            \JLoader::register(
                'sportsmanagementHelper',
                JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/helpers/sportsmanagement.php'
            );
        }

        $founded = trim((string) ($data['founded'] ?? ''));
        $dissolved = trim((string) ($data['dissolved'] ?? ''));

        if ($founded !== '' && $founded !== '0000-00-00') {
            $founded = (string) \sportsmanagementHelper::convertDate($founded, 0);
        }

        if ($dissolved !== '' && $dissolved !== '0000-00-00') {
            $dissolved = (string) \sportsmanagementHelper::convertDate($dissolved, 0);
        }

        $data['founded'] = $founded !== '' ? $founded : '0000-00-00';
        $data['dissolved'] = $dissolved !== '' ? $dissolved : '0000-00-00';

        if ($data['founded'] !== '0000-00-00') {
            $data['founded_year'] = date('Y', strtotime($data['founded']));
            $data['founded_timestamp'] = \sportsmanagementHelper::getTimestamp($data['founded']);
        } elseif (empty($data['founded_year'])) {
            $data['founded_year'] = 'kein';
        }

        if ($data['dissolved'] !== '0000-00-00') {
            $data['dissolved_year'] = date('Y', strtotime($data['dissolved']));
            $data['dissolved_timestamp'] = \sportsmanagementHelper::getTimestamp($data['dissolved']);
        }

        return $data;
    }
}
