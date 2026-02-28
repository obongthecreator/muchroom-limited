<?php
/**
 * Category Page - Products in a specific category
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$branch = Muchroom_Gadget_Inventory::get_current_branch();

$slug     = get_query_var( 'mgi_category' );
$category = MGI_Products::get_category_by_slug( $slug );

if ( ! $category ) {
    wp_redirect( home_url( '/muchroom/' . $branch . '/home/' ) );
    exit;
}

$page_title = $category->name;
include MGI_PLUGIN_DIR . 'templates/header.php';

$products = MGI_Products::get_products( $category->id );
$is_laptop = in_array( $slug, array( 'laptops' ), true );
?>

<div class="page-header">
    <div>
        <h1 class="heading"><?php echo esc_html( $category->icon . ' ' . $category->name ); ?></h1>
        <p class="text-muted body-text" style="font-size:14px;"><?php echo count( $products ); ?> products</p>
    </div>
    <a href="<?php echo esc_url( home_url( '/muchroom/' . $branch . '/home/' ) ); ?>" class="pill-btn pill-btn-outline btn-text" onclick="event.preventDefault(); navigateTo(this.href);">
        ← Back to Home
    </a>
</div>

<div class="glass-card">
    <div class="mgi-table-wrap">
        <table class="mgi-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <?php if ( $is_laptop ) : ?>
                    <th>Model</th>
                    <th>Description</th>
                    <?php endif; ?>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <tbody>
                <?php if ( empty( $products ) ) : ?>
                <tr><td colspan="<?php echo $is_laptop ? 7 : 5; ?>" class="text-center text-muted">No products in this category</td></tr>
                <?php else : ?>
                    <?php foreach ( $products as $p ) : ?>
                    <tr>
                        <td><?php echo esc_html( $p->product_code ); ?></td>
                        <td><strong><?php echo esc_html( $p->name ); ?></strong></td>
                        <?php if ( $is_laptop ) : ?>
                        <td><?php echo esc_html( $p->model ); ?></td>
                        <td class="text-muted" style="max-width:200px;font-size:12px;"><?php echo esc_html( $p->description ); ?></td>
                        <?php endif; ?>
                        <td class="amount"><?php echo esc_html( '₦' . number_format( $p->cost, 0 ) ); ?></td>
                        <td class="amount fw-bold"><?php echo esc_html( '₦' . number_format( $p->price, 0 ) ); ?></td>
                        <td>
                            <?php
                            $stock = intval( $p->stock );
                            if ( $stock <= 0 ) {
                                echo '<span class="badge badge-danger">Out of Stock</span>';
                            } elseif ( $stock < 5 ) {
                                echo '<span class="badge badge-warning">' . $stock . '</span>';
                            } else {
                                echo '<span class="badge badge-success">' . $stock . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
