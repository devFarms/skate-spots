<?php
/**
 * Admin template for skaters list
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Skaters</h1>
    
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
            <a href="<?php echo admin_url('admin.php?page=skate-spots-skaters'); ?>" <?php echo !isset($_GET['status']) ? 'class="current"' : ''; ?>>
                All <span class="count">(<?php echo Skate_Skaters::count(); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-skaters&status=0'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '0' ? 'class="current"' : ''; ?>>
                Pending <span class="count">(<?php echo Skate_Skaters::count(0); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-skaters&status=1'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '1' ? 'class="current"' : ''; ?>>
                Approved <span class="count">(<?php echo Skate_Skaters::count(1); ?>)</span>
            </a> |
        </li>
        <li>
            <a href="<?php echo admin_url('admin.php?page=skate-spots-skaters&status=2'); ?>" <?php echo isset($_GET['status']) && $_GET['status'] == '2' ? 'class="current"' : ''; ?>>
                Rejected <span class="count">(<?php echo Skate_Skaters::count(2); ?>)</span>
            </a>
        </li>
    </ul>
    
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Bio</th>
                <th>Social URL</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($skaters)): ?>
                <?php foreach ($skaters as $skater): ?>
                    <tr>
                        <td><?php echo esc_html($skater->id); ?></td>
                        <td><strong><?php echo esc_html($skater->name); ?></strong></td>
                        <td>
                            <?php if ($skater->bio): ?>
                                <?php echo esc_html(wp_trim_words($skater->bio, 20)); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($skater->social_url): ?>
                                <a href="<?php echo esc_url($skater->social_url); ?>" target="_blank">View Profile</a>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-<?php echo esc_attr($skater->status); ?>">
                                <?php echo esc_html(Skate_Spots_Database::get_status_label($skater->status)); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('Y-m-d', strtotime($skater->created_at))); ?></td>
                        <td>
                            <?php if ($skater->status != 1): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="skater">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($skater->id); ?>">
                                    <input type="hidden" name="status" value="1">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots-skaters">
                                    <button type="submit" class="button button-small">Approve</button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($skater->status != 2): ?>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                    <?php wp_nonce_field('skate_update_status'); ?>
                                    <input type="hidden" name="action" value="skate_update_status">
                                    <input type="hidden" name="type" value="skater">
                                    <input type="hidden" name="id" value="<?php echo esc_attr($skater->id); ?>">
                                    <input type="hidden" name="status" value="2">
                                    <input type="hidden" name="redirect" value="admin.php?page=skate-spots-skaters">
                                    <button type="submit" class="button button-small">Reject</button>
                                </form>
                            <?php endif; ?>
                            
                            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this skater?');">
                                <?php wp_nonce_field('skate_delete_entry'); ?>
                                <input type="hidden" name="action" value="skate_delete_entry">
                                <input type="hidden" name="type" value="skater">
                                <input type="hidden" name="id" value="<?php echo esc_attr($skater->id); ?>">
                                <input type="hidden" name="redirect" value="admin.php?page=skate-spots-skaters">
                                <button type="submit" class="button button-small button-link-delete">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No skaters found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>