<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagehandler
 * @file       imagehandler.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;

JLoader::import('components.com_sportsmanagement.helpers.imageselect', JPATH_SITE);

/**
 * sportsmanagementControllerImagehandler
 *
 * @package
 * @author    Dieter Plöger
 * @copyright 2018
 * @version   $Id$
 * @access    public
 */
class sportsmanagementControllerImagehandler extends JSMControllerAdmin
{
    /**
     * Upload a project-team image.
     *
     * @return void
     */
    public function uploadprojectteams()
    {
        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $data      = $input->getArray();
        $file      = $input->files->get('userfile', array(), 'array');
        $type      = (string) ($data['type'] ?? '');
        $field     = (string) ($data['field'] ?? '');
        $fieldId   = (string) ($data['fieldid'] ?? '');
        $pid       = (int) ($data['pid'] ?? 0);
        $mid       = (int) ($data['mid'] ?? 0);
        $imageList = !empty($data['imagelist']);
        $folder    = ImageSelectSM::getfolder($type);

        ClientHelper::setCredentialsFromRequest('ftp');

        if (empty($file['name']))
        {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY'));
            return;
        }

        $baseDir = $this->imageBaseDirectory($folder);
        $filename = ImageSelectSM::sanitize($baseDir, $file['name']);

        if (!$this->uploadFile($file['tmp_name'], $baseDir . $filename))
        {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'), 'message');
        $this->closeUploadModal($type, $filename, $field, $fieldId, !$imageList);
    }

    /**
     * Proxy for getModel.
     *
     * @since 1.6
     */
    public function getModel($name = 'imagehandler', $prefix = 'sportsmanagementModel', $config = array())
    {
        return parent::getModel($name, $prefix, array('ignore_request' => true));
    }

    /**
     * Save a person image selection.
     *
     * @return void
     */
    public function saveimageplayer()
    {
        $input = Factory::getApplication()->getInput();
        $this->saveImageSelection(
            'saveimageplayer',
            array(
                'player_id' => $input->getInt('player_id'),
                'picture' => $input->getString('picture'),
            )
        );
    }

    /**
     * Save a club image selection.
     *
     * @return void
     */
    public function saveimageclub()
    {
        $input = Factory::getApplication()->getInput();
        $this->saveImageSelection(
            'saveimageclub',
            array(
                'club_id' => $input->getInt('club_id'),
                'picture' => $input->getString('picture'),
            )
        );
    }

    /**
     * Save a team-player image selection.
     *
     * @return void
     */
    public function saveimageteamplayer()
    {
        $input = Factory::getApplication()->getInput();
        $this->saveImageSelection(
            'saveimageteamplayer',
            array(
                'teamplayer_id' => $input->getInt('teamplayer_id'),
                'picture' => $input->getString('picture'),
            )
        );
    }

    /**
     * Upload an image.
     *
     * @return void
     */
    public function upload()
    {
        $app       = Factory::getApplication();
        $input     = $app->getInput();
        $data      = $input->getArray();
        $file      = $input->files->get('userfile', array(), 'array');
        $type      = (string) ($data['type'] ?? '');
        $field     = (string) ($data['field'] ?? '');
        $fieldId   = (string) ($data['fieldid'] ?? '');
        $link      = (string) ($data['linkaddress'] ?? '');
        $pid       = (int) ($data['pid'] ?? 0);
        $mid       = (int) ($data['mid'] ?? 0);
        $imageList = !empty($data['imagelist']);
        $folder    = ImageSelectSM::getfolder($type);

        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        if ($type === 'projectimages' && $pid > 0)
        {
            $folder .= '/' . $pid;
            $imageList = true;
        }
        elseif ($type === 'matchreport' && $mid > 0)
        {
            $folder .= '/' . $mid;
            $imageList = true;
        }

        ClientHelper::setCredentialsFromRequest('ftp');
        $baseDir = $this->imageBaseDirectory($folder);

        if ($link !== '')
        {
            $file['name'] = basename($link);
            $filename = preg_match('/dfs_/i', $link)
                ? $file['name']
                : ImageSelectSM::sanitize($baseDir, $file['name']);

            if (!@copy($link, $baseDir . $filename))
            {
                $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED'));
                return;
            }

            $this->closeUploadModal($type, $filename, $field, $fieldId, !$imageList);
            return;
        }

        if (empty($file['name']))
        {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY'));
            return;
        }

        $filename = ImageSelectSM::sanitize($baseDir, $file['name']);

        if (!$this->uploadFile($file['tmp_name'], $baseDir . $filename))
        {
            $this->showUploadError(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED'));
            return;
        }

        $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'), 'message');
        $this->closeUploadModal($type, $filename, $field, $fieldId, !$imageList);
    }

    /**
     * Delete selected images.
     *
     * @return void
     */
    public function delete()
    {
        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $option = $input->getCmd('option', 'com_sportsmanagement');
        $images = $input->get('rm', array(), 'array');
        $type   = $input->getCmd('type');
        $folder = ImageSelectSM::getfolder($type);

        ClientHelper::setCredentialsFromRequest('ftp');

        foreach ($images as $image)
        {
            $image = (string) $image;

            if ($image === '' || $image !== InputFilter::clean($image, 'path'))
            {
                Log::add(
                    Text::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE') . ' '
                    . htmlspecialchars($image, ENT_COMPAT, 'UTF-8'),
                    Log::WARNING,
                    'jsmerror'
                );
                continue;
            }

            $fullPath = Path::clean(
                JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option
                . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder
                . DIRECTORY_SEPARATOR . $image
            );
            $thumbPath = Path::clean(
                JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option
                . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . $folder
                . DIRECTORY_SEPARATOR . 'small' . DIRECTORY_SEPARATOR . $image
            );

            try
            {
                if (is_file($fullPath))
                {
                    File::delete($fullPath);
                }

                if (is_file($thumbPath))
                {
                    File::delete($thumbPath);
                }
            }
            catch (Throwable $e)
            {
                Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            }
        }

        $app->redirect('index.php?option=' . $option . '&view=imagehandler&type=' . $type . '&tmpl=component');
    }

    /**
     * Persist an image selection through the legacy model.
     *
     * @param string $method Model method name.
     * @param array  $data   Method payload.
     *
     * @return void
     */
    private function saveImageSelection($method, array $data)
    {
        $app = Factory::getApplication();
        $model = $this->getModel();
        $resultUpdate = $model->{$method}($data);

        if ($resultUpdate !== true)
        {
            $result = '0&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE_FALSE') . ': ' . $resultUpdate;
        }
        else
        {
            $result = 'Nachricht&' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_SAVE_IMAGE');
        }

        echo json_encode($result);
        $app->close();
    }

    /**
     * Return the image directory for the given relative folder.
     *
     * @param string $folder Relative SportsManagement image folder.
     *
     * @return string
     */
    private function imageBaseDirectory($folder)
    {
        $option = Factory::getApplication()->getInput()->getCmd('option', 'com_sportsmanagement');

        return Path::clean(
            JPATH_SITE . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . $option
            . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . trim((string) $folder, '/\\')
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * Upload a file and normalise framework filesystem exceptions to false.
     *
     * @param string $source Source temporary file.
     * @param string $target Destination file.
     *
     * @return bool
     */
    private function uploadFile($source, $target)
    {
        try
        {
            return (bool) File::upload($source, $target);
        }
        catch (Throwable $e)
        {
            Log::add($e->getMessage(), Log::WARNING, 'jsmerror');
            return false;
        }
    }

    /**
     * Emit the legacy modal callback using Joomla's current modal API.
     *
     * @param string $type     Image type.
     * @param string $filename Uploaded filename.
     * @param string $field    Target field.
     * @param string $fieldId  Target field id.
     * @param bool   $select   Whether to invoke the selectImage callback.
     *
     * @return void
     */
    private function closeUploadModal($type, $filename, $field, $fieldId, $select)
    {
        $type = json_encode((string) $type);
        $filename = json_encode((string) $filename);
        $field = json_encode((string) $field);
        $fieldId = json_encode((string) $fieldId);

        if ($select)
        {
            echo '<script>'
                . 'window.parent["selectImage_" + ' . $type . '](' . $filename . ', ' . $filename . ', ' . $field . ', ' . $fieldId . ');'
                . 'window.parent.Joomla.Modal.getCurrent().close();'
                . '</script>';
            return;
        }

        echo '<script>'
            . 'window.parent.Joomla.Modal.getCurrent().close();'
            . 'parent.location.reload();'
            . '</script>';
    }

    /**
     * Show the existing JavaScript upload error and return control to the form.
     *
     * @param string $message Error message.
     *
     * @return void
     */
    private function showUploadError($message)
    {
        echo '<script>alert(' . json_encode((string) $message) . ');window.history.go(-1);</script>';
    }
}
