<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a topic form.
 *
 */
class ProjectforkModelTopic extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_TOPIC';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Topic', $prefix = 'PFTable', $config = array())
    {
        return JTable::getInstance($type, $prefix, $config);
    }


    /**
     * Method to get a single record.
     *
     * @param     integer    The id of the primary key.
     * @return    mixed      Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry = new JRegistry;
            $registry->loadString($item->attribs);
            $item->attribs = $registry->toArray();
        }

        return $item;
    }


    /**
     * Method to get the record form.
     *
     * @param     array      Data for the form.
     * @param     boolean    True if the form is to load its own data (default case), false if not.
     * @return    mixed      A JForm object on success, false on failure
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_projectfork.topic', 'topic', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;


        // Check if a project id is already selected. If not, set the currently active project as value
        $project_id = (int) $form->getValue('project_id');
        if (!$this->getState('topic.id') && $project_id == 0) {
            $app       = JFactory::getApplication();
            $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

            $form->setValue('project_id', null, $active_id);
        }

        return $form;
    }


    /**
     * Method to change the published state of one or more records.
     *
     * @param     array      A list of the primary keys to change.
     * @param     integer    The value of the published state.
     *
     * @return    boolean    True on success.
     */
    public function publish(&$pks, $value = 1)
    {
        $result = parent::publish($pks, $value);

        if ($result) {
            // State change succeeded. Now update all children
            $replies = JTable::getInstance('Reply', 'PFTable');

            $parent_data = array();
            $parent_data['state'] = $value;

            $replies->updateByReference($pks, 'topic_id', $parent_data);
        }

        return $result;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.topic.data', array());

        if (empty($data)) $data = $this->getItem();

        return $data;
    }


    /**
     * Method to save the form data.
     *
     * @param     array      The form data
     *
     * @return    boolean    True on success
     */
    public function save($data)
    {
        // Alter the title for save as copy
        if (JRequest::getVar('task') == 'save2copy') {
            list($title, $alias) = $this->generateNewTitle($data['alias'], $data['title'], $data['project_id']);
            $data['title'] = $title;
            $data['alias'] = $alias;
        }
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        $id     = (int) $this->getState('topic.id');
        $is_new = ($id > 0) ? false : true;
        $item   = null;

        if (!$is_new) {
            // Load the existing record before updating it
            $item = $this->getTable();
            $item->load($id);
        }


        // Store the record
        if (parent::save($data)) {
            // To keep data integrity, update all child assets
            if (!$is_new && is_object($item)) {
                $updated = $this->getTable();
                $replies = JTable::getInstance('Reply', 'PFTable');

                $parent_data = array();
                $null_date   = JFactory::getDbo()->getNullDate();

                // Load the just updated row
                if ($updated->load($this->getState('topic.id')) === false) return false;

                // Check if any relevant values have changed that need to be updated to children
                if ($item->access != $updated->access) {
                    $parent_data['access'] = $updated->access;
                }

                if ($item->state != $updated->state) {
                    $parent_data['state'] = $updated->state;
                }

                if (count($parent_data)) {
                    $replies->updateByReference($id, 'topic_id', $parent_data);
                }
            }

            return true;
        }

        return false;
    }


    /**
     * Method to delete one or more records.
     *
     * @param     array      An array of record primary keys.
     *
     * @return    boolean    True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        // Delete the records
        $success = parent::delete($pks);

        // Cancel if something went wrong
        if (!$success) return false;

        $replies = JTable::getInstance('Reply', 'PFTable');

        // Delete all topic replies
        if (!$replies->deleteByReference($pks, 'topic_id')) $success = false;

        return $success;
    }


    /**
     * Custom clean the cache of com_projectfork and projectfork modules
     *
     */
    protected function cleanCache()
    {
        parent::cleanCache('com_projectfork');
    }


    /**
     * Method to change the title & alias.
     * Overloaded from JModelAdmin class
     *
     * @param     string    The alias
     * @param     string    The title
     *
     * @return    array     Contains the modified title and alias
     */
    protected function generateNewTitle($alias, $title, $project_id)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias, 'project_id' => $project_id)))
        {
            $m = null;
            if (preg_match('#-(\d+)$#', $alias, $m)) {
                $alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
            }
            else {
                $alias .= '-2';
            }

            if (preg_match('#\((\d+)\)$#', $title, $m)) {
                $title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
            }
            else {
                $title .= ' (2)';
            }
        }

        return array($title, $alias);
    }


    /**
     * Method to test whether a record can be deleted.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->state != -2) return false;

            $access = ProjectforkHelperAccess::getActions('topic', $record->id);
            return $access->get('topic.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('topic.delete');
        }
    }


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to delete the record.
     */
    protected function canEditState($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('topic', $record->id);
            return $access->get('topic.edit.state');
        }
        else {
            return parent::canEditState('com_projectfork');
        }
    }


    /**
     * Method to test whether a record can be edited.
     * Defaults to the permission for the component.
     *
     * @param     object     A record object.
     *
     * @return    boolean    True if allowed to edit the record.
     */
    protected function canEdit($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('topic', $record->id);
            return $access->get('topic.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('topic.edit');
        }
    }
}
