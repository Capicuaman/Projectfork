<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.database.tableasset');


/**
 * File Table Class
 *
 */
class PFTableFile extends JTable
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        // Register dependencies
        JLoader::register('ProjectforkHelper', JPATH_ADMINISTRATOR . '/components/com_projectfork/helpers/projectfork.php');

        parent::__construct('#__pf_repo_files', 'id', $db);
    }


    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return    string
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;
        return 'com_projectfork.file.' . (int) $this->$k;
    }


    /**
     * Method to return the title to use for the asset table.
     *
     * @return    string
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }


    /**
     * Method to get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent
     * @param     integer    $id
     *
     * @return    integer
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        $asset_id = null;
        $query    = $this->_db->getQuery(true);

        if ($this->dir_id) {
            // Build the query to get the asset id for the parent topic.
            $query->select('asset_id')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . (int) $this->dir_id);

            // Get the asset id from the database.
            $this->_db->setQuery((string) $query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }
        elseif ($this->project_id) {
            $params   = ProjectforkHelper::getProjectParams($this->project_id);
            $repo_dir = (int) $params->get('repo_dir');
            $result   = null;

            if ($repo_dir) {
                // Try to get the asset id of the project repo
                $query->clear();
                $query->select('asset_id')
                      ->from('#__pf_repo_dirs')
                      ->where('id = ' . $repo_dir);

                $this->_db->setQuery((string) $query);
                $result = $this->_db->loadResult();
            }

            if ($result) {
                $this->dir = $repo_dir;
                $asset_id  = (int) $result;
            }
            else {
                // Build the query to get the asset id for the parent project.
                $query->select('asset_id')
                      ->from('#__pf_projects')
                      ->where('id = ' . (int) $this->project_id);

                // Get the asset id from the database.
                $this->_db->setQuery((string) $query);
                $result = $this->_db->loadResult();

                if ($result) $asset_id = (int) $result;
            }
        }

        if (!$asset_id) {
            // Build the query to get the asset id for the parent component.
            $query->clear();
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_projectfork"));

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();

            if ($result) $asset_id = (int) $result;
        }

        // Return the asset id.
        if ($asset_id) return $asset_id;

        return parent::_getAssetParentId($table, $id);
    }


    /**
     * Method to get the access level of the parent asset
     *
     * @return    integer
     */
    protected function _getParentAccess()
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        $dir     = (int) $this->dir_id;
        $project = (int) $this->project_id;

        if ($dir > 1) {
            $query->select('access')
                  ->from('#__pf_repo_dirs')
                  ->where('id = ' . $db->quote($dir));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }
        elseif ($project > 0) {
            $query->select('access')
                  ->from('#__pf_projects')
                  ->where('id = ' . $db->quote($project));

            $db->setQuery($query);
            $access = (int) $db->loadResult();
        }

        if (!$access) $access = 1;

        return $access;
    }


    /**
     * Overloaded bind function
     *
     * @param     array    $array     Named array
     * @param     mixed    $ignore    An optional array or space separated list of properties to ignore while binding.
     *
     * @return    mixed               Null if operation was satisfactory, otherwise returns an error string
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['attribs']) && is_array($array['attribs'])) {
            $registry = new JRegistry;
            $registry->loadArray($array['attribs']);
            $array['attribs'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && is_array($array['rules'])) {
            $rules = new JRules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean    True on success, false on failure
     */
    public function check()
    {
        if (trim(str_replace('&nbsp;', '', $this->title)) == '') {
            if ($this->file_name == '') {
                $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
                return false;
            }
            else {
                $this->title = $this->file_name;
            }
        }

        // Check if a project is selected
        if ((int) $this->project_id <= 0) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_PROJECT'));
            return false;
        }

        // Check if a directory is selected
        if ((int) $this->dir_id <= 1) {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_SELECT_DIRECTORY'));
            return false;
        }

        // Check for selected access level
        if ($this->access <= 0) {
            $this->access = $this->_getParentAccess();
        }

        return true;
    }


    /**
     * Overrides JTable::store to set modified data and user id.
     *
     * @param     boolean    True to update fields even if they are null.
     * @return    boolean    True on success.
     */
    public function store($updateNulls = false)
    {
        $date = JFactory::getDate();
        $user = JFactory::getUser();

        if ($this->id) {
            // Existing item
            $this->modified    = $date->toSql();
            $this->modified_by = $user->get('id');
        }
        else {
            // New item
            $this->created = $date->toSql();
            if (empty($this->created_by)) $this->created_by = $user->get('id');
        }

        // Store the main record
        $success = parent::store($updateNulls);

        return $success;
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    public function toXML($mapKeysToText=false)
    {
        $db = JFactory::getDbo();

        if ($mapKeysToText) {
            $query = 'SELECT name'
            . ' FROM #__users'
            . ' WHERE id = ' . (int) $this->created_by;
            $db->setQuery($query);
            $this->created_by = $db->loadResult();
        }

        return parent::toXML($mapKeysToText);
    }
}
