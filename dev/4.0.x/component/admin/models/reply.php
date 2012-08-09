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
 * Item Model for a topic reply form.
 *
 */
class ProjectforkModelReply extends JModelAdmin
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     */
    protected $text_prefix = 'COM_PROJECTFORK_REPLY';


    /**
     * Returns a Table object, always creating it.
     *
     * @param     string    The table type to instantiate
     * @param     string    A prefix for the table class name. Optional.
     * @param     array     Configuration array for model. Optional.
     *
     * @return    jtable    A database object
     */
    public function getTable($type = 'Reply', $prefix = 'PFTable', $config = array())
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
        $form = $this->loadForm('com_projectfork.reply', 'reply', array('control' => 'jform', 'load_data' => $loadData));
        if (empty($form)) return false;

        // Check if a project and topic is already selected. If not, get them from the current state
        $project_id = (int) $form->getValue('project_id');
        $topic_id   = (int) $form->getValue('topic_id');

        if (!$project_id) {
            $form->setValue('project_id', null, $this->getState($this->getName() . '.project'));
        }
        if (!$topic_id) {
            $form->setValue('topic_id', null, $this->getState($this->getName() . '.topic'));
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
        $data = JFactory::getApplication()->getUserState('com_projectfork.edit.reply.data', array());

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
        // Store the record
        return parent::save($data);
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

            $access = ProjectforkHelperAccess::getActions('reply', $record->id);
            return $access->get('reply.delete');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('reply.delete');
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
            $access = ProjectforkHelperAccess::getActions('reply', $record->id);
            return $access->get('reply.edit.state');
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
            $access = ProjectforkHelperAccess::getActions('reply', $record->id);
            return $access->get('reply.edit');
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
            return $access->get('reply.edit');
        }
    }


    /**
     * Method to auto-populate the model state.
     * Note: Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        // Initialise variables.
        $app   = JFactory::getApplication();
		$table = $this->getTable();
		$key   = $table->getKeyName();

		// Get the pk of the record from the request.
		$pk = JRequest::getInt($key);
		$this->setState($this->getName() . '.id', $pk);

        if ($pk) {
            $table = $this->getTable();

            if ($table->load($pk)) {
                $project = (int) $table->project_id;
                $this->setState($this->getName() . '.project', $project);
                ProjectforkHelper::setActiveProject($project);

                $topic = (int) $table->topic_id;
                $this->setState($this->getName() . '.topic', $topic);
            }
        }
        else {
            $topic = JRequest::getUInt('filter_topic', 0);
            $this->setState($this->getName() . '.topic', $topic);

            $project = (int) $app->getUserStateFromRequest('com_projectfork.project.active.id', 'filter_project', '');

            if ($project) {
                $this->setState($this->getName() . '.project', $project);
                ProjectforkHelper::setActiveProject($project);
            }
            elseif($topic) {
                $table = $this->getTable('Topic');

                if ($table->load($topic)) {
                    $project = (int) $table->project_id;

                    $this->setState($this->getName() . '.project', $project);
                    ProjectforkHelper::setActiveProject($project);
                }
            }
        }

		// Load the parameters.
		$value = JComponentHelper::getParams($this->option);
		$this->setState('params', $value);
    }
}
