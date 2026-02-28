<?php
/**
 * Admin Panel - Manage products, categories, users
 */
if ( ! defined( 'ABSPATH' ) ) exit;
$branch = Muchroom_Gadget_Inventory::get_current_branch();

if ( ! MGI_Auth::is_admin() ) {
    if ( MGI_Shortcodes::is_shortcode_mode() ) {
        echo '<p class="mgi-shortcode-error">Admin access required. Please log in as an admin.</p>';
        return;
    }
    wp_redirect( home_url( '/muchroom/' . $branch . '/home/' ) );
    exit;
}

$page_title = 'Admin Panel';
include MGI_PLUGIN_DIR . 'templates/header.php';

$categories = MGI_Products::get_categories();
$products   = MGI_Products::get_products();
$users      = MGI_Users::get_all();
$stats      = MGI_Admin_Panel::get_dashboard_stats();
?>

<div class="page-header">
    <h1 class="heading">Admin Panel</h1>
</div>

<!-- Admin Stats -->
<div class="mgi-grid stagger-in mb-24" style="grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));">
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:box-linear"></iconify-icon></div>
        <div class="kpi-value"><?php echo intval( $stats['total_products'] ); ?></div>
        <div class="kpi-label">Products</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:folder-linear"></iconify-icon></div>
        <div class="kpi-value"><?php echo intval( $stats['total_categories'] ); ?></div>
        <div class="kpi-label">Categories</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:users-group-two-rounded-linear"></iconify-icon></div>
        <div class="kpi-value"><?php echo intval( $stats['total_staff'] ); ?></div>
        <div class="kpi-label">Staff</div>
    </div>
    <div class="glass-card kpi-card">
        <div class="kpi-icon"><iconify-icon icon="solar:wallet-money-linear"></iconify-icon></div>
        <div class="kpi-value"><?php echo esc_html( '₦' . number_format( $stats['today_sales'], 0 ) ); ?></div>
        <div class="kpi-label">Today's Sales</div>
    </div>
</div>

<!-- Tabs -->
<div class="mgi-tabs">
    <button class="mgi-tab active" data-tab-group="admin" data-tab="products" onclick="switchTab('admin','products')">Products</button>
    <button class="mgi-tab" data-tab-group="admin" data-tab="categories" onclick="switchTab('admin','categories')">Categories</button>
    <button class="mgi-tab" data-tab-group="admin" data-tab="users" onclick="switchTab('admin','users')">Users</button>
</div>

<!-- Products Tab -->
<div class="tab-content active" data-tab-content-group="admin" data-tab-content="products">
    <div class="glass-card mb-24">
        <div class="flex items-center justify-between mb-16">
            <h2 class="font-display" style="font-size:18px;">Products</h2>
            <button class="pill-btn beam-btn btn-text" onclick="openModal('product-modal'); resetProductForm();"><iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add Product</button>
        </div>

        <div class="mgi-table-wrap">
            <table class="mgi-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Model</th>
                        <th>Category</th>
                        <th>Cost</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $products as $p ) : ?>
                    <tr>
                        <td><?php echo esc_html( $p->product_code ); ?></td>
                        <td><strong><?php echo esc_html( $p->name ); ?></strong></td>
                        <td><?php echo esc_html( $p->model ); ?></td>
                        <td><?php echo esc_html( $p->category_name ); ?></td>
                        <td class="amount"><?php echo esc_html( '₦' . number_format( $p->cost, 0 ) ); ?></td>
                        <td class="amount"><?php echo esc_html( '₦' . number_format( $p->price, 0 ) ); ?></td>
                        <td><?php echo intval( $p->stock ); ?></td>
                        <td>
                            <button class="pill-btn pill-btn-glass btn-text" style="padding:4px 8px;font-size:11px;" onclick="editProduct(<?php echo intval( $p->id ); ?>, '<?php echo esc_js( $p->product_code ); ?>', '<?php echo esc_js( $p->name ); ?>', '<?php echo esc_js( $p->model ); ?>', '<?php echo esc_js( $p->description ); ?>', <?php echo intval( $p->category_id ); ?>, <?php echo floatval( $p->cost ); ?>, <?php echo floatval( $p->price ); ?>)"><iconify-icon icon="solar:pen-linear"></iconify-icon></button>
                            <button class="pill-btn pill-btn-danger btn-text" style="padding:4px 8px;font-size:11px;" onclick="deleteProduct(<?php echo intval( $p->id ); ?>)"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Categories Tab -->
<div class="tab-content" data-tab-content-group="admin" data-tab-content="categories">
    <div class="glass-card mb-24">
        <div class="flex items-center justify-between mb-16">
            <h2 class="font-display" style="font-size:18px;">Categories</h2>
            <button class="pill-btn beam-btn btn-text" onclick="openModal('category-modal'); resetCategoryForm();"><iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add Category</button>
        </div>

        <div class="mgi-table-wrap">
            <table class="mgi-table">
                <thead>
                    <tr><th>Icon</th><th>Name</th><th>Slug</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ( $categories as $cat ) : ?>
                    <tr>
                        <td><iconify-icon icon="<?php echo esc_attr( $cat->icon ); ?>"></iconify-icon></td>
                        <td><strong><?php echo esc_html( $cat->name ); ?></strong></td>
                        <td class="text-muted"><?php echo esc_html( $cat->slug ); ?></td>
                        <td>
                            <button class="pill-btn pill-btn-glass btn-text" style="padding:4px 8px;font-size:11px;" onclick="editCategory(<?php echo intval( $cat->id ); ?>, '<?php echo esc_js( $cat->name ); ?>', '<?php echo esc_js( $cat->icon ); ?>')"><iconify-icon icon="solar:pen-linear"></iconify-icon></button>
                            <button class="pill-btn pill-btn-danger btn-text" style="padding:4px 8px;font-size:11px;" onclick="deleteCategory(<?php echo intval( $cat->id ); ?>)"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Users Tab -->
<div class="tab-content" data-tab-content-group="admin" data-tab-content="users">
    <div class="glass-card mb-24">
        <div class="flex items-center justify-between mb-16">
            <h2 class="font-display" style="font-size:18px;">Staff Users</h2>
            <button class="pill-btn beam-btn btn-text" onclick="openModal('user-modal'); resetUserForm();"><iconify-icon icon="solar:add-circle-linear"></iconify-icon> Add User</button>
        </div>

        <div class="mgi-table-wrap">
            <table class="mgi-table">
                <thead>
                    <tr><th>Username</th><th>Full Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ( $users as $u ) : ?>
                    <tr>
                        <td><?php echo esc_html( $u->username ); ?></td>
                        <td><strong><?php echo esc_html( $u->full_name ); ?></strong></td>
                        <td><span class="badge <?php echo 'admin' === $u->role ? 'badge-warning' : 'badge-success'; ?>"><?php echo esc_html( $u->role ); ?></span></td>
                        <td><?php echo esc_html( $u->email ); ?></td>
                        <td><?php echo esc_html( $u->phone ); ?></td>
                        <td><?php echo $u->is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>'; ?></td>
                        <td>
                            <button class="pill-btn pill-btn-glass btn-text" style="padding:4px 8px;font-size:11px;" onclick="editUser(<?php echo intval( $u->id ); ?>, '<?php echo esc_js( $u->username ); ?>', '<?php echo esc_js( $u->full_name ); ?>', '<?php echo esc_js( $u->role ); ?>', '<?php echo esc_js( $u->email ); ?>', '<?php echo esc_js( $u->phone ); ?>', <?php echo intval( $u->is_active ); ?>)"><iconify-icon icon="solar:pen-linear"></iconify-icon></button>
                            <button class="pill-btn pill-btn-danger btn-text" style="padding:4px 8px;font-size:11px;" onclick="deleteUser(<?php echo intval( $u->id ); ?>)"><iconify-icon icon="solar:trash-bin-minimalistic-linear"></iconify-icon></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="mgi-modal-overlay" id="product-modal">
    <div class="mgi-modal">
        <div class="mgi-modal-header">
            <h3 id="product-modal-title">Add Product</h3>
            <button class="mgi-modal-close" onclick="closeModal('product-modal')">&times;</button>
        </div>
        <form onsubmit="return saveProduct(event)">
            <input type="hidden" id="edit-product-id" value="" />
            <div class="mgi-form-group">
                <label>Product Code</label>
                <input type="text" id="edit-product-code" class="mgi-input" required />
            </div>
            <div class="mgi-form-group">
                <label>Name</label>
                <input type="text" id="edit-product-name" class="mgi-input" required />
            </div>
            <div class="mgi-form-group">
                <label>Model</label>
                <input type="text" id="edit-product-model" class="mgi-input" />
            </div>
            <div class="mgi-form-group">
                <label>Description</label>
                <textarea id="edit-product-desc" class="mgi-textarea"></textarea>
            </div>
            <div class="mgi-form-group">
                <label>Category</label>
                <select id="edit-product-category" class="mgi-select" required>
                    <?php foreach ( $categories as $cat ) : ?>
                    <option value="<?php echo esc_attr( $cat->id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mgi-grid" style="grid-template-columns:1fr 1fr;gap:12px;">
                <div class="mgi-form-group">
                    <label>Cost (₦)</label>
                    <input type="number" id="edit-product-cost" class="mgi-input" step="0.01" min="0" required />
                </div>
                <div class="mgi-form-group">
                    <label>Price (₦)</label>
                    <input type="number" id="edit-product-price" class="mgi-input" step="0.01" min="0" required />
                </div>
            </div>
            <button type="submit" class="pill-btn beam-btn btn-text w-full mt-16"><iconify-icon icon="solar:diskette-linear"></iconify-icon> Save Product</button>
        </form>
    </div>
</div>

<!-- Category Modal -->
<div class="mgi-modal-overlay" id="category-modal">
    <div class="mgi-modal" style="max-width:400px;">
        <div class="mgi-modal-header">
            <h3 id="category-modal-title">Add Category</h3>
            <button class="mgi-modal-close" onclick="closeModal('category-modal')">&times;</button>
        </div>
        <form onsubmit="return saveCategory(event)">
            <input type="hidden" id="edit-category-id" value="" />
            <div class="mgi-form-group">
                <label>Name</label>
                <input type="text" id="edit-category-name" class="mgi-input" required />
            </div>
            <div class="mgi-form-group">
                <label>Icon (Iconify name)</label>
                <input type="text" id="edit-category-icon" class="mgi-input" placeholder="e.g. solar:smartphone-linear" />
            </div>
            <button type="submit" class="pill-btn beam-btn btn-text w-full mt-16"><iconify-icon icon="solar:diskette-linear"></iconify-icon> Save Category</button>
        </form>
    </div>
</div>

<!-- User Modal -->
<div class="mgi-modal-overlay" id="user-modal">
    <div class="mgi-modal">
        <div class="mgi-modal-header">
            <h3 id="user-modal-title">Add User</h3>
            <button class="mgi-modal-close" onclick="closeModal('user-modal')">&times;</button>
        </div>
        <form onsubmit="return saveUser(event)">
            <input type="hidden" id="edit-user-id" value="" />
            <div class="mgi-form-group">
                <label>Username</label>
                <input type="text" id="edit-user-username" class="mgi-input" required />
            </div>
            <div class="mgi-form-group">
                <label>Full Name</label>
                <input type="text" id="edit-user-fullname" class="mgi-input" required />
            </div>
            <div class="mgi-form-group">
                <label>Password <span class="text-muted">(leave empty to keep current)</span></label>
                <input type="password" id="edit-user-password" class="mgi-input" autocomplete="new-password" />
            </div>
            <div class="mgi-form-group">
                <label>Role</label>
                <select id="edit-user-role" class="mgi-select">
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="mgi-grid" style="grid-template-columns:1fr 1fr;gap:12px;">
                <div class="mgi-form-group">
                    <label>Email</label>
                    <input type="email" id="edit-user-email" class="mgi-input" />
                </div>
                <div class="mgi-form-group">
                    <label>Phone</label>
                    <input type="text" id="edit-user-phone" class="mgi-input" />
                </div>
            </div>
            <div class="mgi-form-group">
                <label>
                    <input type="checkbox" id="edit-user-active" checked /> Active
                </label>
            </div>
            <button type="submit" class="pill-btn beam-btn btn-text w-full mt-16"><iconify-icon icon="solar:diskette-linear"></iconify-icon> Save User</button>
        </form>
    </div>
</div>

<script>
// Product CRUD
function resetProductForm() {
    document.getElementById('product-modal-title').textContent = 'Add Product';
    document.getElementById('edit-product-id').value = '';
    document.getElementById('edit-product-code').value = '';
    document.getElementById('edit-product-name').value = '';
    document.getElementById('edit-product-model').value = '';
    document.getElementById('edit-product-desc').value = '';
    document.getElementById('edit-product-cost').value = '';
    document.getElementById('edit-product-price').value = '';
}

function editProduct(id, code, name, model, desc, catId, cost, price) {
    document.getElementById('product-modal-title').textContent = 'Edit Product';
    document.getElementById('edit-product-id').value = id;
    document.getElementById('edit-product-code').value = code;
    document.getElementById('edit-product-name').value = name;
    document.getElementById('edit-product-model').value = model;
    document.getElementById('edit-product-desc').value = desc;
    document.getElementById('edit-product-category').value = catId;
    document.getElementById('edit-product-cost').value = cost;
    document.getElementById('edit-product-price').value = price;
    openModal('product-modal');
}

function saveProduct(e) {
    e.preventDefault();
    mgiAjax('save_product', {
        product_id:   document.getElementById('edit-product-id').value,
        product_code: document.getElementById('edit-product-code').value,
        product_name: document.getElementById('edit-product-name').value,
        model:        document.getElementById('edit-product-model').value,
        description:  document.getElementById('edit-product-desc').value,
        category_id:  document.getElementById('edit-product-category').value,
        cost:         document.getElementById('edit-product-cost').value,
        price:        document.getElementById('edit-product-price').value
    }, function(err, res) {
        if (!err && res.success) {
            showToast('Product saved!', 'success');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(res && res.data ? res.data.message : 'Failed', 'error');
        }
    });
    return false;
}

function deleteProduct(id) {
    if (!confirm('Delete this product?')) return;
    mgiAjax('delete_product', { product_id: id }, function(err, res) {
        if (!err && res.success) {
            showToast('Product deleted', 'success');
            setTimeout(function() { location.reload(); }, 500);
        }
    });
}

// Category CRUD
function resetCategoryForm() {
    document.getElementById('category-modal-title').textContent = 'Add Category';
    document.getElementById('edit-category-id').value = '';
    document.getElementById('edit-category-name').value = '';
    document.getElementById('edit-category-icon').value = '';
}

function editCategory(id, name, icon) {
    document.getElementById('category-modal-title').textContent = 'Edit Category';
    document.getElementById('edit-category-id').value = id;
    document.getElementById('edit-category-name').value = name;
    document.getElementById('edit-category-icon').value = icon;
    openModal('category-modal');
}

function saveCategory(e) {
    e.preventDefault();
    mgiAjax('save_category', {
        category_id:   document.getElementById('edit-category-id').value,
        category_name: document.getElementById('edit-category-name').value,
        icon:          document.getElementById('edit-category-icon').value
    }, function(err, res) {
        if (!err && res.success) {
            showToast('Category saved!', 'success');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(res && res.data ? res.data.message : 'Failed', 'error');
        }
    });
    return false;
}

function deleteCategory(id) {
    if (!confirm('Delete this category? Products in this category will not be deleted.')) return;
    mgiAjax('delete_category', { category_id: id }, function(err, res) {
        if (!err && res.success) {
            showToast('Category deleted', 'success');
            setTimeout(function() { location.reload(); }, 500);
        }
    });
}

// User CRUD
function resetUserForm() {
    document.getElementById('user-modal-title').textContent = 'Add User';
    document.getElementById('edit-user-id').value = '';
    document.getElementById('edit-user-username').value = '';
    document.getElementById('edit-user-fullname').value = '';
    document.getElementById('edit-user-password').value = '';
    document.getElementById('edit-user-role').value = 'staff';
    document.getElementById('edit-user-email').value = '';
    document.getElementById('edit-user-phone').value = '';
    document.getElementById('edit-user-active').checked = true;
}

function editUser(id, username, fullName, role, email, phone, isActive) {
    document.getElementById('user-modal-title').textContent = 'Edit User';
    document.getElementById('edit-user-id').value = id;
    document.getElementById('edit-user-username').value = username;
    document.getElementById('edit-user-fullname').value = fullName;
    document.getElementById('edit-user-password').value = '';
    document.getElementById('edit-user-role').value = role;
    document.getElementById('edit-user-email').value = email;
    document.getElementById('edit-user-phone').value = phone;
    document.getElementById('edit-user-active').checked = isActive == 1;
    openModal('user-modal');
}

function saveUser(e) {
    e.preventDefault();
    mgiAjax('save_user', {
        user_id:   document.getElementById('edit-user-id').value,
        username:  document.getElementById('edit-user-username').value,
        full_name: document.getElementById('edit-user-fullname').value,
        password:  document.getElementById('edit-user-password').value,
        role:      document.getElementById('edit-user-role').value,
        email:     document.getElementById('edit-user-email').value,
        phone:     document.getElementById('edit-user-phone').value,
        is_active: document.getElementById('edit-user-active').checked ? 1 : 0
    }, function(err, res) {
        if (!err && res.success) {
            showToast('User saved!', 'success');
            setTimeout(function() { location.reload(); }, 500);
        } else {
            showToast(res && res.data ? res.data.message : 'Failed', 'error');
        }
    });
    return false;
}

function deleteUser(id) {
    if (!confirm('Delete this user?')) return;
    mgiAjax('delete_user', { user_id: id }, function(err, res) {
        if (!err && res.success) {
            showToast('User deleted', 'success');
            setTimeout(function() { location.reload(); }, 500);
        }
    });
}
</script>

<?php include MGI_PLUGIN_DIR . 'templates/footer.php'; ?>
