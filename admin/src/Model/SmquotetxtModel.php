<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * Native Joomla 5/6 file-editor model for random-quote module source files.
 */
final class SmquotetxtModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms');
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/forms');

        return $this->loadForm(
            'com_sportsmanagement.smquotetxt',
            'smquotetxt',
            ['control' => 'jform', 'load_data' => $loadData]
        );
    }

    public function save($data)
    {
        $fileName = $this->normaliseFileName((string) ($data['filename'] ?? ''));

        if ($fileName === null) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return false;
        }

        $filePath = $this->getEditorPath() . DIRECTORY_SEPARATOR . $fileName;
        $source = (string) ($data['source'] ?? '');

        if (@file_put_contents($filePath, $source, LOCK_EX) === false) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_FILE_WRITE'));

            return false;
        }

        return true;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState(
            'com_sportsmanagement.edit.source.data',
            []
        );

        if (empty($data)) {
            $data = $this->getSource();
        }

        return $data;
    }

    public function getSource()
    {
        $fileName = $this->normaliseFileName(
            Factory::getApplication()->getInput()->getString('file_name')
        );
        $item = new \stdClass();

        if ($fileName === null) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $filePath = $this->getEditorPath() . DIRECTORY_SEPARATOR . $fileName;

        if (!is_file($filePath) || !is_readable($filePath)) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $source = @file_get_contents($filePath);

        if ($source === false) {
            $this->setError(Text::_('COM_SPORTSMANAGEMENT_ERROR_SOURCE_FILE_NOT_FOUND'));

            return $item;
        }

        $item->filename = $fileName;
        $item->source = $source;

        return $item;
    }

    private function getEditorPath(): string
    {
        return JPATH_SITE
            . DIRECTORY_SEPARATOR . 'modules'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes'
            . DIRECTORY_SEPARATOR . 'mod_sportsmanagement_rquotes';
    }

    private function normaliseFileName(string $fileName): ?string
    {
        $fileName = trim($fileName);

        if ($fileName === '' || basename($fileName) !== $fileName) {
            return null;
        }

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return in_array($extension, ['txt', 'php'], true) ? $fileName : null;
    }
}
