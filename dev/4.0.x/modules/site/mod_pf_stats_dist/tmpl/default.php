<?php
/**
* @package   Projectfork Task Distribution Statistics
* @copyright Copyright (C) 2012 Tobias Kuhn. All rights reserved.
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

// no direct access
defined('_JEXEC') or die;
?>
<table id="mod-pf-stats-dist" style="display:none;">
    <thead>
        <tr>
            <td></td>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($stats['users'] AS $user) : ?>
            <tr>
                <th scope="row"><?php echo $user->name." (".$user->total.")";?></th>
                <td scope="row"><?php echo $user->total;?></td>
            </tr>
        <?php endforeach;?>
        <?php if($show_u) : ?>
        <tr>
            <th scope="row"><?php echo JText::_('MOD_PF_STATS_DIST_UNASSIGNED')." (".$stats['unassigned'].")";?></th>
            <td scope="row"><?php echo $stats['unassigned'];?></td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
