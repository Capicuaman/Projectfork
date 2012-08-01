<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see license.txt
*
* This file is part of Projectfork.
*
* Projectfork is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
*
* Projectfork is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Projectfork. If not, see <http://www.gnu.org/licenses/gpl.html>.
**/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


/**
 * Item Model for a milestone form.
 *
 */
class ProjectforkModelMilestone extends JModelAdmin
{
	/**
	 * @var    string    The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_PROJECTFORK_MILESTONE';


	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param    type	   The table type to instantiate
	 * @param	 string    A prefix for the table class name. Optional.
	 * @param	 array	   Configuration array for model. Optional.
	 * @return	 JTable	   A database object
	 */
	public function getTable($type = 'Milestone', $prefix = 'PFTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * Method to get a single record.
	 *
	 * @param	  integer	The id of the primary key.
	 * @return    mixed     Object on success, false on failure.
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
	 * @param    array      $data		Data for the form.
	 * @param	 boolean    $loadData	True if the form is to load its own data (default case), false if not.
	 * @return	 mixed                  A JForm object on success, false on failure
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_projectfork.milestone', 'milestone', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) return false;


        // Check if a project id is already selected. If not, set the currently active project as value
        $project_id = (int) $form->getValue('project_id');
        if (!$this->getState('milestone.id') && $project_id == 0) {
            $app       = JFactory::getApplication();
            $active_id = (int) $app->getUserState('com_projectfork.project.active.id', 0);

            $form->setValue('project_id', null, $active_id);
        }

		return $form;
	}


	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return    mixed    The data for the form.
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_projectfork.edit.milestone.data', array());

		if(empty($data)) $data = $this->getItem();

		return $data;
	}


    /**
     * Method to delete one or more records.
     *
     * @param     array      $pks    An array of record primary keys.
     *
     * @return    boolean            True if successful, false if an error occurs.
     */
    public function delete(&$pks)
    {
        // Delete the records
        $success = parent::delete($pks);

        // Cancel if something went wrong
        if (!$success) return false;

        $tasklists = JTable::getInstance('Tasklist', 'PFTable');
        $tasks     = JTable::getInstance('Task', 'PFTable');

        // Delete all other items referenced to each project
        if (!$tasklists->deleteByReference($pks, 'milestone_id')) $success = false;
        if (!$tasks->deleteByReference($pks, 'milestone_id'))     $success = false;

        return $success;
    }


	/**
	 * Method to save the form data.
	 *
	 * @param     array	     The form data
	 * @return    boolean    True on success
	 */
	public function save($data)
	{
		// Alter the title for save as copy
		if (JRequest::getVar('task') == 'save2copy') {
			list($title,$alias) = $this->generateNewTitle($data['alias'], $data['title']);
			$data['title']	= $title;
			$data['alias']	= $alias;
		}
        else {
            // Always re-generate the alias unless save2copy
            $data['alias'] = '';
        }

        $id      = (int) $this->getState('milestone.id');
        $is_new  = ($id > 0) ? false : true;
        $item    = null;

        if (!$is_new) {
            // Load the existing record before updating it
            $item = $this->getTable();
            $item->load($id);
        }

        // Store the record
		if (parent::save($data)) {
		    // To keep data integrity, update all child assets
            if (!$is_new && is_object($item)) {
                $updated   = $this->getTable();
                $tasklists = JTable::getInstance('Tasklist', 'PFTable');
                $tasks     = JTable::getInstance('Task', 'PFTable');

                $parent_data = array();
                $null_date   = JFactory::getDbo()->getNullDate();

                // Load the just updated row
                if ($updated->load($this->getState('milestone.id')) === false) return false;

                // Check if any relevant values have changed that need to be updated to children
                if ($item->access != $updated->access) {
                    $parent_data['access'] = $updated->access;
                }

                if ($item->start_date != $updated->start_date && $item->start_date != $null_date) {
                    $parent_data['start_date'] = $updated->start_date;
                }

                if ($item->start_date != $updated->end_date && $item->end_date != $null_date) {
                    $parent_data['end_date'] = $updated->end_date;
                }

                if ($item->state != $updated->state) {
                    $parent_data['state'] = $updated->state;
                }


                if (count($parent_data)) {
                    $tasklists->updateByReference($id, 'milestone_id', $parent_data);
                    $tasks->updateByReference($id, 'milestone_id', $parent_data);
                }
            }

            return true;
		}

		return false;
	}


    /**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 */
    public function publish(&$pks, $value = 1)
	{
	    $result = parent::publish($pks, $value);

        if ($result) {
            // State change succeeded. Now update all children
            $tasklists  = JTable::getInstance('Tasklist', 'PFTable');
            $tasks      = JTable::getInstance('Task', 'PFTable');

            $parent_data = array();
            $parent_data['state'] = $value;

            $tasklists->updateByReference($pks, 'milestone_id', $parent_data);
            $tasks->updateByReference($pks, 'milestone_id', $parent_data);
        }

        return $result;
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
	 * @param    string     $alias    The alias
	 * @param    string     $title    The title
	 * @return	 array                Contains the modified title and alias
	 */
	protected function generateNewTitle($alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();
		while ($table->load(array('alias' => $alias))) {
			$m = null;
			if (preg_match('#-(\d+)$#', $alias, $m)) {
				$alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
			} else {
				$alias .= '-2';
			}
			if (preg_match('#\((\d+)\)$#', $title, $m)) {
				$title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
			} else {
				$title .= ' (2)';
			}
		}

		return array($title, $alias);
	}


    /**
     * Method to test whether a record can have its state edited.
     * Defaults to the permission set in the component.
     *
     * @param     object     $record    A record object.
     *
     * @return    boolean               True if allowed to delete the record.
     */
    protected function canEditState($record)
    {
        // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('milestone', $record->id);
            return $access->get('milestone.edit.state');
        }
        else {
            return parent::canEditState('com_projectfork');
        }
    }


    /**
	 * Method to test whether a record can be edited.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to edit the record. Defaults to the permission for the component.
	 */
	protected function canEdit($record)
	{
	    // Check for existing item.
        if (!empty($record->id)) {
            $access = ProjectforkHelperAccess::getActions('milestone', $record->id);
            return $access->get('milestone.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('milestone.edit');
        }
	}
}