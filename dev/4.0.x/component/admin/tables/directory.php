<?php
/**
 * @package      Projectfork
 *
 * @author       Tobias Kuhn (eaxs)
 * @copyright    Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


jimport('joomla.database.tablenested');


/**
 * Directory table
 *
 */
class PFTableDirectory extends JTableNested
{
    /**
     * Constructor
     *
     * @param    database    $db    A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__pf_repo_dirs', 'id', $db);
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
        return 'com_projectfork.directory.' . (int) $this->$k;
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
     * Get the parent asset id for the record
     *
     * @param     jtable     $table    A JTable object for the asset parent.
     * @param     integer    $id       The id for the asset
     *
     * @return    integer              The id of the asset's parent
     */
    protected function _getAssetParentId($table = null, $id = null)
    {
        $asset_id = null;
        $result   = null;
        $query    = $this->_db->getQuery(true);

        // This is a directory under another directory.
        if ($this->parent_id > 1) {
            // Build the query to get the asset id for the parent comment.
            $query->select($this->_db->quoteName('asset_id'))
                  ->from($this->_db->quoteName('#__pf_repo_dirs'))
                  ->where($this->_db->quoteName('id') . ' = ' . $this->parent_id);

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        }
        elseif ($this->project_id > 0) {
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__pf_repo_dirs'))
                  ->where($this->_db->quoteName('project_id') . ' = ' . $this->_db->quote((int) $this->project_id))
                  ->where($this->_db->quoteName('parent_id') . ' = 1');

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        }
        else {
            // Build the query to get the asset id for the parent component.
            $query->select($this->_db->quoteName('id'))
                  ->from($this->_db->quoteName('#__assets'))
                  ->where($this->_db->quoteName('name') . ' = ' . $this->_db->quote("com_projectfork"));

            // Get the asset id from the database.
            $this->_db->setQuery($query);
            $result = $this->_db->loadResult();
        }

        if (!empty($result)) {
            $asset_id = $result;
        }

        // Return the asset id.
        if ($asset_id) {
            return $asset_id;
        }
        else {
            return parent::_getAssetParentId($table, $id);
        }
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

        return parent::bind($array, $ignore);
    }


    /**
     * Overloaded check function
     *
     * @return    boolean    True on success, false on failure
     */
    public function check()
    {
        if (trim($this->title) == '') {
            $this->setError(JText::_('COM_PROJECTFORK_WARNING_PROVIDE_VALID_TITLE'));
            return false;
        }

        if (trim($this->alias) == '') $this->alias = $this->title;
        $this->alias = JApplication::stringURLSafe($this->alias);
        if (trim(str_replace('-','', $this->alias)) == '') $this->alias = JApplication::stringURLSafe(JFactory::getDate()->format('Y-m-d-H-i-s'));

        // Check attribs
        $registry = new JRegistry;
        $registry->loadString($this->attribs);

        $this->attribs = (string) $registry;

        return true;
    }


    /**
     * Overridden JTable::store to set created/modified and user id.
     *
     * @param     boolean    $updateNulls    True to update fields even if they are null.
     *
     * @return    boolean                    True on success.
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
            $this->created    = $date->toSql();
            $this->created_by = $user->get('id');
        }
        //return false;

        return parent::store($updateNulls);
    }


    public function publish($pks = null, $state = 1, $userId = 0)
    {
        return $this->setState($pks, $state, $userId);
    }


    /**
     * Converts record to XML
     *
     * @param     boolean    $mapKeysToText    Map foreign keys to text values
     * @return    string                       Record in XML format
     */
    public function toXML($mapKeysToText = false)
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
