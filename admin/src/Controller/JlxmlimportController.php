<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Http\HttpFactory;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 controller for the SportsManagement JLG/XML import workflow. */
final class JlxmlimportController extends BaseController
{
    public function __construct($config = [], $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        foreach (['edit', 'insert', 'selectpage', 'update'] as $task) {
            $this->registerTask($task, 'display');
        }
    }

    public function display($cachable = false, $urlparams = [])
    {
        $input = $this->getApplication()->getInput();

        switch ($this->getTask()) {
            case 'edit':
                $input->set('layout', 'form');
                $input->set('view', 'jlxmlimports');
                $input->set('edit', true);
                break;

            case 'insert':
                $input->set('layout', 'info');
                $input->set('view', 'jlxmlimports');
                $input->set('edit', true);
                break;

            case 'update':
                $input->set('layout', 'update');
                $input->set('view', 'jlxmlimports');
                $input->set('edit', true);
                break;
        }

        return parent::display($cachable, $urlparams);
    }

    public function select()
    {
        $app = $this->getApplication();
        $input = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';

        $app->setUserState($option . 'selectType', $input->getInt('type', 0));
        $app->setUserState($option . 'recordID', $input->getInt('id', 0));

        $input->set('hidemainmenu', 1);
        $input->set('layout', 'selectpage');
        $input->set('view', 'jlxmlimports');

        return parent::display();
    }

    public function save()
    {
        $this->checkToken();

        $app = $this->getApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $projectId = $input->getInt('projektfussballineuropa', 0);
        $importUpdate = !empty($post['importupdate']);

        $app->setUserState('com_sportsmanagementimportelanska', $post['importelanska'] ?? 0);
        $app->setUserState('com_sportsmanagementcountry', $post['country'] ?? '');
        $app->setUserState('com_sportsmanagementagegroup', $post['agegroup'] ?? 0);
        $app->setUserState('com_sportsmanagementseasons', $post['seasons'] ?? 0);

        if ($projectId > 0) {
            if (!$this->downloadRemoteImport($projectId, $importUpdate)) {
                return false;
            }
        } elseif ((int) ($post['sent'] ?? 0) === 1) {
            $upload = (array) $input->files->get('import_package', []);
            $app->setUserState('com_sportsmanagementuploadArray', $upload);

            if (!$this->prepareImportFile($upload)) {
                return false;
            }
        }

        $task = $importUpdate ? 'update' : 'edit';
        $this->setRedirect(
            'index.php?option=com_sportsmanagement&task=jlxmlimport.' . $task
            . '&project_id=' . $projectId
        );

        return true;
    }

    public function cancel()
    {
        $this->setRedirect('index.php?option=com_sportsmanagement&task=jlxmlimport.display');

        return true;
    }

    private function downloadRemoteImport(int $projectId, bool $update): bool
    {
        $app = $this->getApplication();
        $remoteUrl = 'https://www.fussballineuropa.de/index.php?option=com_sportsmanagement'
            . '&view=jlxmlexports&p=' . $projectId
            . '&update=' . ($update ? '1' : '0');
        $importFile = JPATH_SITE . '/tmp/sportsmanagement_import.jlg';

        $app->enqueueMessage(Text::_('hole daten von -> ' . $remoteUrl), 'notice');

        try {
            $http = HttpFactory::getHttp(new Registry(), ['curl', 'stream']);
            $response = $http->get($remoteUrl);
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            $app->enqueueMessage(
                Text::_('daten -> ' . $remoteUrl . ' konnten nicht kopiert werden!'),
                'error'
            );

            return false;
        }

        if ($response->code < 200 || $response->code >= 300 || !File::write($importFile, (string) $response->body)) {
            $app->enqueueMessage(
                Text::_('daten -> ' . $remoteUrl . ' konnten nicht kopiert werden!'),
                'error'
            );

            return false;
        }

        $app->setUserState('com_sportsmanagementuploadArray', ['name' => $remoteUrl]);
        $app->setUserState('com_sportsmanagementprojectidimport', $projectId);
        $app->enqueueMessage(Text::_('daten -> ' . $remoteUrl . ' sind kopiert worden!'), 'notice');

        return true;
    }

    private function prepareImportFile(array $upload): bool
    {
        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $originalName = File::makeSafe((string) ($upload['name'] ?? ''));

        if ($temporaryPath === '' || $originalName === '' || !File::exists($temporaryPath)) {
            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD');
        }

        $temporaryDirectory = JPATH_SITE . '/tmp';
        $destination = $temporaryDirectory . '/' . $originalName;
        $importFile = $temporaryDirectory . '/sportsmanagement_import.jlg';

        if (File::exists($importFile)) {
            File::delete($importFile);
        }

        if (File::exists($destination)) {
            File::delete($destination);
        }

        if (!File::upload($temporaryPath, $destination)) {
            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_CANT_UPLOAD');
        }

        $extension = strtolower(File::getExt($destination));

        if ($extension === 'zip') {
            return $this->prepareArchive($destination, $temporaryDirectory, $importFile);
        }

        if (!in_array($extension, ['jlg', 'xml'], true)) {
            File::delete($destination);

            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_WRONG_EXTENSION');
        }

        if (!@rename($destination, $importFile)) {
            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_RENAME_FAILED');
        }

        return true;
    }

    private function prepareArchive(string $archiveFile, string $temporaryDirectory, string $importFile): bool
    {
        try {
            $archive = new Archive();
            $result = $archive->extract($archiveFile, $temporaryDirectory);
        } catch (\Throwable $e) {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');

            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR');
        }

        File::delete($archiveFile);

        if ($result === false) {
            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_ERROR');
        }

        $sources = Folder::files($temporaryDirectory, '\\.(jlg|xml)$', false, true);

        if (!$sources) {
            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_EXTRACT_NOJLG');
        }

        if (!@rename((string) $sources[0], $importFile)) {
            return $this->reportUploadError('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_CTRL_ERROR_RENAME');
        }

        return true;
    }

    private function reportUploadError(string $languageKey): bool
    {
        $message = Text::_($languageKey);
        Log::add($message, Log::WARNING, 'jsmerror');
        $this->getApplication()->enqueueMessage($message, 'error');

        return false;
    }
}
