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


$user     = JFactory::getUser();
$uid      = $user->get('id');
$x        = count($this->items['directories']) + count($this->items['notes']);

$this_dir  = $this->items['directory'];
$this_path = (empty($this_dir) ? '' : $this_dir->path);

$filter_search  = $this->state->get('filter.search');
$filter_project = (int) $this->state->get('filter.project');
$is_search      = empty($filter_search) ? false : true;

foreach ($this->items['files'] as $i => $item) :
    $link   = PFrepoHelperRoute::getFileRoute($item->slug, $item->project_slug, $item->dir_slug, $item->path);
    $access = PFrepoHelper::getActions('file', $item->id);
    $icon   = $this->escape(strtolower($item->file_extension));

    $can_create   = $access->get('core.create');
    $can_edit     = $access->get('core.edit');
    $can_checkin  = ($user->authorise('core.manage', 'com_checkin') || $item->checked_out == $uid || $item->checked_out == 0);
    $can_edit_own = ($access->get('core.edit.own') && $item->created_by == $uid);
    $can_change   = ($access->get('core.edit.state') && $can_checkin);
    $date_opts    = array('past-class' => '', 'past-icon' => 'calendar');
    ?>
    <tr class="row<?php echo $i % 2; ?>">
        <?php if ($this_dir->parent_id >= 1) : ?>
        <td>
            <label for="cb<?php echo $x; ?>" class="checkbox">
                <?php echo JHtml::_('pf.html.id', $x, $item->id, false, 'fid'); ?>
            </label>
        </td>
        <?php endif; ?>
        <td>
            <?php if ($item->checked_out) : ?><i class="icon-lock"></i> <?php endif; ?>
            <i class="icon-flag-2 icon-<?php echo $icon;?>"></i>
            <a href="<?php echo JRoute::_($link);?>">
                <?php echo JText::_($this->escape($item->title)); ?>
            </a>

            <?php if ($filter_project && $is_search): ?>
                <div class="small">
                    <?php echo str_replace($this_path, '.', $item->path) . '/'; ?>
                </div>
            <?php endif; ?>
        </td>
        <td>
            <?php
                $this->menu->start(array('class' => 'btn-mini'));
                $this->menu->itemEdit('fileform', $item->id, ($can_edit || $can_edit_own));
                $this->menu->itemDelete('repository', $x, ($can_edit || $can_edit_own));
                $this->menu->end();

                echo $this->menu->render(array('class' => 'btn-mini'));
            ?>
        </td>
        <td>
            <?php echo JHtml::_('pfhtml.label.datetime', $item->created, false, $date_opts); ?>
        </td>
        <td>
            <?php echo JHtml::_('pf.html.truncate', $item->description); ?>
            <?php echo JHtml::_('pfhtml.label.author', $item->author_name, $item->created); ?>
            <?php echo JHtml::_('pfhtml.label.access', $item->access); ?>
            <?php if ($item->label_count) : echo JHtml::_('pfhtml.label.labels', $item->labels); endif; ?>
        </td>
    </tr>
<?php $x++; endforeach; ?>
