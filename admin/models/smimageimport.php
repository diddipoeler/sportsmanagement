<?php

/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      smimageimport.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library

use Joomla\Archive\Archive;

jimport('joomla.application.component.model');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive');



/**
 * sportsmanagementModelsmimageimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */

/**
 * sportsmanagementModelsmimageimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelsmimageimport extends JModelLegacy {

    /**
     * Method override to check if you can edit an existing record.
     *
     * @param	array	$data	An array of input data.
     * @param	string	$key	The name of the key for the primary key.
     *
     * @return	boolean
     * @since	1.6
     */
    protected function allowEdit($data = array(), $key = 'id') {
        // Check specific edit permission then general edit permission.
        return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.' . ((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param	type	The table type to instantiate
     * @param	string	A prefix for the table class name. Optional.
     * @param	array	Configuration array for model. Optional.
     * @return	JTable	A database object
     * @since	1.6
     */
    public function getTable($type = 'Pictures', $prefix = 'sportsmanagementTable', $config = array()) {
        $config['dbo'] = sportsmanagementHelper::getDBConnection();
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param	array	$data		Data for the form.
     * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
     * @return	mixed	A JForm object on success, false on failure
     * @since	1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_sportsmanagement.smimageimport', 'smimageimport', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) {
            return false;
        }
        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return	mixed	The data for the form.
     * @since	1.6
     */
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.smimageimport.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }

    /**
     * sportsmanagementModelsmimageimport::import()
     * 
     * @return
     */
    function import() {
        $app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        //$post = JFactory::getApplication()->input->post->getArray(array());
        $option = $app->input->getCmd('option');
        $post = $app->input->post->getArray(array());

        $server = 'http://sportsmanagement.fussballineuropa.de/jdownloads/';

        $cid = $post['cid'];

        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($post,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($cid,true).'</pre>'),'');

        foreach ($cid as $key => $value) {
            $name = $post['picture'][$value];
            $folder = $post['folder'][$value];
            $directory = $post['directory'][$value];
            $file = $post['file'][$value];

            $folder = str_replace(' ', '%20', $folder);

            $servercopy = $server . $folder . '/' . $file;

//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($folder,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($directory,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($file,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($servercopy,true).'</pre>'),'Notice');
            //set the target directory
            $base_Dir = JPATH_SITE . DS . 'tmp' . DS;
            //$file['name'] = basename($servercopy);
            //$filename = $file['name'];
            //$filepath = $base_Dir . $filename;
            //$file['name'] = $file;
            $filename = $file;
            $filepath = $base_Dir . $filename;

//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'file<br><pre>'.print_r($file,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'filename<br><pre>'.print_r($filename,true).'</pre>'),'Notice');
//            $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'filepath<br><pre>'.print_r($filepath,true).'</pre>'),'Notice');

            if (!copy($servercopy, $filepath)) {
                $app->enqueueMessage(JText::_(get_class($this) . ' ' . __FUNCTION__ . 'file<br><pre>' . print_r($servercopy, true) . '</pre>'), 'Error');
            } else {
                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'file<br><pre>'.print_r($servercopy,true).'</pre>'),'');

                $extractdir = JPATH_SITE . DS . 'images' . DS . 'com_sportsmanagement' . DS . 'database' . DS . $directory;
                $dest = JPATH_SITE . DS . 'tmp' . DS . $filename;

                //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'dest<br>'.$dest.''),'Notice');

                if (strtolower(JFile::getExt($dest)) == 'zip') {
                    if (version_compare(JSM_JVERSION, '4', 'eq')) {
                        $archive = new Archive;
                        $result = $archive->extract($dest, $extractdir);
                    } else {
                        $result = JArchive::extract($dest, $extractdir);
                    }
                    if ($result === false) {
                        $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_ERROR'), 'error');
                        return false;
                    } else {
                        $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_DONE', $name), 'notice');
                        // Must be a valid primary key value.
                        $object->id = $value;
                        $object->published = 1;
                        // Update their details in the users table using id as the primary key.
                        $result = JFactory::getDbo()->updateObject('#__' . COM_SPORTSMANAGEMENT_TABLE . '_pictures', $object, 'id');
                    }
                } else {
                    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_NO_ZIP_ERROR'), 'error');
                    return false;
                }
            }
        }
    }

}

?>
