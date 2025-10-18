<?php
/**
 * Admin template for spots list
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Skate Spots</h1>
    
    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Status updated successfully!</p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Entry deleted successfully!</p>
        </div>
    <?php endif; ?>
    
    <hr class="wp-header-end">
    
    <ul class="subsubsub">
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots'); ?>" <?php echo !isset($_GET['status']) ? 'class="current"' : ''; ?>>
                All <span class="count">(<?php echo Skate_Spots::count(); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots&status=0'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '0' ? 'class="current"' : ''; ?>>
                Pending <span class="count">(<?php echo Skate_Spots::count(0); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots&status=1'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '1' ? 'class="current"' : ''; ?>>
                Approved <span class="count">(<?php echo Skate_Spots::count(1); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots&status=2'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '2' ? 'class="current"' : ''; ?>>
                Rejected <span class="count">(<?php echo Skate_Spots::count(2); ?>)</span>
            </a>
        </li>
    </ul>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Location</th>
                <th>Type</th>
                <th>Coordinates</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($spots)): ?>
                <?php foreach ($spots as $spot): ?>
                    <tr>
                        <td><?php echo esc_html($spot->id); ?></td>
                        <td>
                            <strong><?php echo esc_html($spot->title); ?></strong>
                            <?php if ($spot->description): ?>
                                <br><small><?php echo esc_html(wp_trim_words($spot->description, 10)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $location_parts = array();
                            if (!empty($spot->city)) $location_parts[] = esc_html($spot->city);
                            if (!empty($spot->state)) $location_parts[] = esc_html($spot->state);
                            if (!empty($spot->zip)) $location_parts[] = esc_html($spot->zip);
                            if (!empty($spot->country)) $location_parts[] = esc_html($spot->country);
                            echo implode(', ', $location_parts);
                            ?>
                            <?php if (!empty($spot->address)): ?>
                                <br><small><?php echo esc_html($spot->address); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($spot->spot_type); ?></td>
                        <td>
                            <?php if ($spot->latitude && $spot->longitude): ?>
                                <?php echo esc_html($spot->latitude); ?>, <?php echo esc_html($spot->longitude); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-<?php echo esc_attr($spot->status); ?>">
                                <?php echo esc_html(Skate_Spots_Database::get_status_label($spot->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($spot->created_at))); ?></td>
                        <td>
                            <?php if ($spot->status != 1): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="spot">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($spot->id); ?>">
                                    <input type="hidden" name="status" value="1">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots">
                                    <button type="submit" class="button button-small">Approve</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($spot->status != 2): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="spot">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($spot->id); ?>">
                                    <input type="hidden" name="status" value="2">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots">
                                    <button type="submit" class="button button-small">Reject</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this spot?');">
                                <?php wp_nonce_field('skate_delete_entry'); ?>
                                <input type="hidden" name="action" value="skate_delete_entry">
                                <input type="hidden" name="type" value="spot">
                                <input type="hidden" name="id" value="<?php echo esc_attr($spot->id); ?>">
                                <input type="hidden" name="redirect" value="admin.php?page=skate-spots">
                                <button type="submit" class="button button-small button-link-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No spots found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>