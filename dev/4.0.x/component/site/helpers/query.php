<?php
/**
* @package   Projectfork
* @copyright Copyright (C) 2006-2012 Tobias Kuhn. All rights reserved.
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.php
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

defined('_JEXEC') or die;


/**
 * Projectfork Component Query Helper
 *
 * @static
 */
class ProjectforkHelperQuery
{
    public function buildFilter(&$query, $filters = array())
    {
        $db = JFactory::getDbo();

        foreach($filters AS $field => $filter)
        {
            if(count($filter) != 2) continue;

            $type  = $filter[0];
            $value = $filter[1];

            switch(strtoupper($type))
            {
                case 'STR-EQUALS':
                    if(!empty($value)) $query->where($field. ' = '.$db->Quote($db->getEscaped($value, true)));
                    break;

                case 'STR-LIKE':
                    if(!empty($value)) $query->where($field. ' LIKE '.$db->Quote('%'.$db->getEscaped($value, true).'%'));
                    break;

                case 'SEARCH':
            		if (!empty($value)) {
            			if (stripos($value, 'id:') === 0) {
            				$query->where($field.'.id = '.(int) substr($value, 4));
            			}
            			elseif (stripos($value, 'author:') === 0) {
            				$search = $db->Quote('%'.$db->getEscaped(trim(substr($value, 8)), true).'%');
            				$query->where('(u.name LIKE '.$value.' OR u.username LIKE '.$value.')');
            			}
            			else {
            				$value = $db->Quote('%'.$db->getEscaped($value, true).'%');
            				$query->where('('.$field.'.title LIKE '.$value.' OR '.$field.'.alias LIKE '.$value.')');
            			}
            		}
                    break;

                case 'STATE':
                    if (is_numeric($value)) {
            			$query->where($field.' = ' . (int) $value);
            		}
            		elseif ($value === '') {
            			$query->where('('.$field.' = 0 OR '.$field.' = 1)');
            		}
                    break;

                case 'INT-NOTZERO':
            		if(is_numeric($value) && $value != 0) $query->where($field. ' = '.(int) $value);
                    break;

                case 'INT':
                default:
            		if(is_numeric($value)) $query->where($field. ' = '.(int) $value);
                    break;
            }
        }
    }

}