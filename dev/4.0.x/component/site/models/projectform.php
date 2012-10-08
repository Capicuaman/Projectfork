<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Base this model on the backend version.
JLoader::register('ProjectforkModelProject', JPATH_ADMINISTRATOR . '/components/com_projectfork/models/project.php');


/**
 * Projectfork Component Project Form Model
 *
 */
class ProjectforkModelProjectForm extends ProjectforkModelProject
{

    /**
     * Constructor.
     *
     * @param    array          $config    An optional associative array of configuration settings.
     *
     * @see      jcontroller
     */
    public function __construct($config = array())
    {
       // Register dependencies
       JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_projectfork/tables');
       JForm::addFieldPath(JPATH_ADMINISTRATOR    . '/components/com_projectfork/models/fields');
       JForm::addFormPath(JPATH_ADMINISTRATOR     . '/components/com_projectfork/models/forms');

       // Call parent constructor
       parent::__construct($config);
    }


    /**
     * Method to get item data.
     *
     * @param     integer    $id    The id of the item.
     * @return    mixed             Item data object on success, false on failure.
     */
    public function getItem($id = null)
    {
        // Initialise variables.
        $id = (int) (!empty($id)) ? $id : $this->getState($this->getName() . '.id');

        // Get a row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($id);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());
            return false;
        }

        $properties = $table->getProperties(1);
        $value = JArrayHelper::toObject($properties, 'JObject');

        // Convert attrib field to Registry.
        $value->params = new JRegistry;
        $value->params->loadString($value->attribs);

        // Get the attachments
        $attachments = $this->getInstance('Attachments', 'ProjectforkModel');
        $value->attachment = $attachments->getItems('project', $value->id);

        // Compute selected asset permissions.
        $uid = JFactory::getUser()->get('id');

        if ($id) {
            $access = ProjectforkHelperAccess::getActions('project', $value->id);
        }
        else {
            $access = ProjectforkHelperAccess::getActions();
        }


        // Check general edit permission first.
        if ($access->get('project.edit')) {
            $value->params->set('access-edit', true);
        }
        elseif (!empty($uid) &&  $access->get('project.edit.own')) {
            // Now check if edit.own is available.
            // Check for a valid user and that they are the owner.
            if ($uid == $value->created_by) {
                $value->params->set('access-edit', true);
            }
        }

        // Check edit state permission.
        if ($id) {
            // Existing item
            $value->params->set('access-change', $access->get('project.edit.state'));
        }
        else {
            // New item
            $access = ProjectforkHelperAccess::getActions();
            $value->params->set('access-change', $access->get('project.edit.state', 'com_projectfork'));
        }

        return $value;
    }


    /**
     * Get the return URL.
     *
     * @return    string    The return URL.
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page'));
    }


    /**
     * Method to auto-populate the model state.
     * Note. Calling getState in this method will result in recursion.
     *
     * @return    void
     */
    protected function populateState()
    {
        $app = JFactory::getApplication();

        // Load state from the request.
        $pk = JRequest::getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        $return = JRequest::getVar('return', null, 'default', 'base64');
        $this->setState('return_page', base64_decode($return));

        // Load the parameters.
        $params = $app->getParams();
        $this->setState('params', $params);

        $this->setState('layout', JRequest::getCmd('layout'));
    }
}
