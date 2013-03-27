<?php
/**
 * @package      pkg_projectfork
 * @subpackage   com_pfrepo
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2013 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.controlleradmin');


/**
 * Repository controller class.
 *
 */
class PFrepoControllerRepository extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param     string    $name      The name of the model.
     * @param     string    $prefix    The prefix for the PHP class name.
     *
     * @return    jmodel
     */
    public function getModel($name = 'Repository', $prefix = 'PFrepoModel', $config = array('ignore_request' => true))
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }


    /**
     * Removes an item.
     *
     * @return    void
     */
    public function delete()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $parent_id = (int) JRequest::getUInt('filter_parent_id', 0);

        // Get items to remove from the request.
        $did = JRequest::getVar('did', array(), 'post', 'array');
        $nid = JRequest::getVar('nid', array(), 'post', 'array');
        $fid = JRequest::getVar('fid', array(), 'post', 'array');

        if ((count($did) < 1 && count($nid) < 1 && count($fid) < 1)) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = JFactory::getApplication();

            // Delete directories
            if (count($did)) {
                $model = $this->getModel('Directory');

                JArrayHelper::toInteger($did);

                if ($model->delete($did)) {
                    $app->enqueueMessage(JText::plural('COM_PROJECTFORK_DIRECTORIES_N_ITEMS_DELETED', count($did)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete notes
            if (count($nid)) {
                $model = $this->getModel('Note');

                JArrayHelper::toInteger($nid);

                if ($model->delete($nid)) {
                    $app->enqueueMessage(JText::plural('COM_PROJECTFORK_NOTES_N_ITEMS_DELETED', count($nid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Delete files
            if (count($fid)) {
                $model = $this->getModel('File');

                JArrayHelper::toInteger($fid);

                if ($model->delete($fid)) {
                    $app->enqueueMessage(JText::plural('COM_PROJECTFORK_FILES_N_ITEMS_DELETED', count($fid)));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        $this->setRedirect(JRoute::_($link, false));
    }


    /**
     * Method to run batch operations.
     *
     * @return    void
     */
    public function batch()
    {
        // Check for request forgeries
        JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

        $parent_id = (int) JRequest::getUInt('filter_parent_id', 0);

        $vars = JRequest::getVar('batch', array(), 'post', 'array');
        $did  = JRequest::getVar('did', array(), 'post', 'array');
        $nid  = JRequest::getVar('nid', array(), 'post', 'array');
        $fid  = JRequest::getVar('fid', array(), 'post', 'array');

        if (count($did) < 1 && count($nid) < 1 && count($fid) < 1) {
            JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
        }
        else {
            jimport('joomla.utilities.arrayhelper');
            $app = JFactory::getApplication();

            // Batch directories
            if (count($did) > 0) {
                $model = $this->getModel('Directory');

                JArrayHelper::toInteger($did);

                if ($model->batch($vars, $did)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_DIRECTORIES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Batch notes
            if (count($nid) > 0) {
                $model = $this->getModel('Note');

                JArrayHelper::toInteger($nid);

                if ($model->batch($vars, $nid)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_NOTES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }

            // Batch files
            if (count($fid) > 0) {
                $model = $this->getModel('File');

                JArrayHelper::toInteger($fid);

                if ($model->batch($vars, $fid)) {
                    $app->enqueueMessage(JText::_('COM_PROJECTFORK_SUCCESS_BATCH_FILES'));
                }
                else {
                    $app->enqueueMessage($model->getError(), 'error');
                }
            }
        }

        $link = 'index.php?option=' . $this->option . '&view=' . $this->view_list
              . ($parent_id > 1 ? '&filter_parent_id=' . $parent_id : '');

        $this->setRedirect(JRoute::_($link, false));
    }
}
