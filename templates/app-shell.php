<?php
global $wpdb;
$bsm_company_row = $wpdb->get_row("SELECT company_name, logo_url FROM {$this->prefix}company_settings ORDER BY id DESC LIMIT 1", ARRAY_A);
$bsm_company_name = $bsm_company_row['company_name'] ?? '';
$bsm_company_logo = trim((string)($bsm_company_row['logo_url'] ?? ''));
$bsm_company_name = trim((string) $bsm_company_name);
if ($bsm_company_name === '') {
    $bsm_company_name = get_bloginfo('name') ?: 'Company';
}
?>
<div id="bsm-app" class="bsm-app">
    <div id="bsm-login" class="bsm-login-card" style="display:none">
        <?php if ($bsm_company_logo !== ''): ?>
            <div class="bsm-login-logo-wrap">
                <img class="bsm-login-logo" src="<?php echo esc_attr($bsm_company_logo); ?>" alt="<?php echo esc_attr($bsm_company_name); ?> Logo">
            </div>
        <?php endif; ?>
        <form id="bsm-login-form">
            <label>User ID<input type="text" name="user_id" autocomplete="username" required></label>
            <label>Password
                <div class="bsm-password-wrap">
                    <input id="bsm-login-password" type="password" name="password" autocomplete="current-password" required>
                    <button type="button" id="bsm-toggle-password" class="bsm-password-toggle" aria-label="Show password" title="Show password">
                        <span class="bsm-eye-show" aria-hidden="true">&#128065;</span>
                    </button>
                </div>
            </label>
            <button type="submit" class="bsm-btn bsm-btn-primary">Login</button>
            <div class="bsm-form-msg"></div>
        </form>
    </div>

    <div id="bsm-shell" class="bsm-shell" style="display:none">
        <aside class="bsm-sidebar">
            <div class="bsm-sidebar-head">
                <div><strong><?php echo esc_html($bsm_company_name); ?></strong></div>
                <button id="bsm-menu-close" class="bsm-icon-btn mobile-only">×</button>
            </div>
            <nav id="bsm-nav"></nav>
            <div class="bsm-sidebar-user" id="bsm-sidebar-user"></div>
            <button id="bsm-logout" class="bsm-logout">Logout</button>
        </aside>
        <div class="bsm-main">
            <header class="bsm-topbar">
                <button id="bsm-sidebar-toggle" class="bsm-icon-btn bsm-desktop-sidebar-toggle" type="button" title="Collapse Menu" aria-label="Collapse Menu">☰</button>
                <button id="bsm-menu-open" class="bsm-icon-btn mobile-only">☰</button>
                <div>
                    <h1 id="bsm-page-title">Dashboard</h1>
                    <div id="bsm-react-header" aria-live="polite"></div>
                </div>
                <div class="bsm-topbar-spacer"></div>
                <?php if ($bsm_company_logo !== ''): ?>
                    <div class="bsm-topbar-company-logo-wrap" title="<?php echo esc_attr($bsm_company_name); ?>">
                        <img class="bsm-topbar-company-logo" src="<?php echo esc_attr($bsm_company_logo); ?>" alt="<?php echo esc_attr($bsm_company_name); ?> Logo">
                    </div>
                <?php endif; ?>

            </header>
            <main id="bsm-content" class="bsm-content"></main>
        </div>
    </div>

    <div id="bsm-modal" class="bsm-modal" style="display:none">
        <div class="bsm-modal-panel">
            <div class="bsm-modal-head">
                <h2 id="bsm-modal-title">Add</h2>
                <button id="bsm-modal-close" class="bsm-icon-btn">×</button>
            </div>
            <form id="bsm-form">
                <div id="bsm-form-fields" class="bsm-form-grid"></div>
                <div id="bsm-line-items"></div>
                <div id="bsm-documents"></div>
                <div class="bsm-form-actions">
                    <button type="button" id="bsm-form-cancel" class="bsm-btn">Cancel</button>
                    <button type="submit" class="bsm-btn bsm-btn-primary">Save</button>
                </div>
                <div class="bsm-form-msg"></div>
            </form>
        </div>
    </div>

    <div id="bsm-quick-modal" class="bsm-quick-modal" style="display:none">
        <div class="bsm-quick-panel" role="dialog" aria-modal="true" aria-labelledby="bsm-quick-title">
            <div class="bsm-quick-head">
                <h3 id="bsm-quick-title">Add</h3>
                <button type="button" id="bsm-quick-close" class="bsm-icon-btn">×</button>
            </div>
            <form id="bsm-quick-form">
                <div class="bsm-quick-body" id="bsm-quick-body">
                    <label><span>Name <span class="bsm-required-star">*</span></span>
                        <input type="text" id="bsm-quick-name" autocomplete="off">
                    </label>
                    <div id="bsm-quick-msg" class="bsm-form-msg"></div>
                </div>
                <div class="bsm-quick-actions">
                    <button type="button" id="bsm-quick-cancel" class="bsm-btn">Cancel</button>
                    <button type="submit" id="bsm-quick-save" class="bsm-btn bsm-btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
