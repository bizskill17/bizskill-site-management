<?php
/**
 * Plugin Name: Site Management
 * Description: Phase 1 site, warehouse, material, contractor, receipts & payments management system for one company with multiple sites.
 * Version: 0.0.065
 * Author: BizSkill
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/includes/workforce-transfers.php';

final class BSM_Site_Management {
    use BSM_Workforce_Transfers;
    const VERSION = '0.0.065';
    const PAGE_SLUG = 'site-management';

    private static $instance = null;
    private $prefix;

    public static function instance() {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->prefix = $wpdb->prefix . 'bsm_';

        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('init', [$this, 'register_roles']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_shortcode('bizskill_site_management', [$this, 'shortcode']);
        add_filter('theme_page_templates', [$this, 'register_blank_page_template']);
        add_filter('template_include', [$this, 'blank_template']);
        add_action('init', [$this, 'maybe_upgrade']);
        add_action('init', [$this, 'operations_upgrade'], 11);
        add_action('wp_ajax_bsm_operations', [$this, 'ajax_operations']);

        add_action('wp_ajax_bsm_project_tracker', [$this, 'ajax_project_tracker']);
        add_action('wp_ajax_bsm_save_view_visibility', [$this, 'ajax_save_view_visibility']);
        add_action('wp_ajax_bsm_project_tracker_save', [$this, 'ajax_project_tracker_save']);
        add_action('wp_ajax_bsm_boq_list', [$this, 'ajax_boq_list']);
        add_action('wp_ajax_bsm_boq_pdf', [$this, 'ajax_boq_pdf']);
        add_action('wp_ajax_bsm_requisition_boq_items', [$this, 'ajax_requisition_boq_items']);
        add_action('wp_ajax_bsm_boq_get', [$this, 'ajax_boq_get']);
        add_action('wp_ajax_bsm_boq_site_sections', [$this, 'ajax_boq_site_sections']);
        add_action('wp_ajax_bsm_boq_save', [$this, 'ajax_boq_save']);
        add_action('wp_ajax_bsm_boq_template', [$this, 'ajax_boq_template']);
        add_action('wp_ajax_bsm_boq_import', [$this, 'ajax_boq_import']);
        add_action('wp_ajax_bsm_boq_delete', [$this, 'ajax_boq_delete']);
        add_action('wp_ajax_bsm_list', [$this, 'ajax_list']);
        add_action('wp_ajax_bsm_pending_invoice_copy', [$this, 'ajax_pending_invoice_copy']);
        add_action('wp_ajax_bsm_pending_contractor_bill_copy', [$this, 'ajax_pending_contractor_bill_copy']);
        add_action('wp_ajax_bsm_purchase_items_register', [$this, 'ajax_purchase_items_register']);
        add_action('wp_ajax_bsm_purchase_pending_approval', [$this, 'ajax_purchase_pending_approval']);
        add_action('wp_ajax_bsm_purchase_approval_get', [$this, 'ajax_purchase_approval_get']);
        add_action('wp_ajax_bsm_purchase_approval_save', [$this, 'ajax_purchase_approval_save']);
        add_action('wp_ajax_bsm_vendor_outstanding', [$this, 'ajax_vendor_outstanding']);
        add_action('wp_ajax_bsm_vendor_payment_save', [$this, 'ajax_vendor_payment_save']);
        add_action('wp_ajax_bsm_pending_po_items', [$this, 'ajax_pending_po_items']);
        add_action('wp_ajax_bsm_purchase_order_create', [$this, 'ajax_purchase_order_create']);
        add_action('wp_ajax_bsm_purchase_order_manual_save', [$this, 'ajax_purchase_order_manual_save']);
        add_action('wp_ajax_bsm_purchase_order_master', [$this, 'ajax_purchase_order_master']);
        add_action('wp_ajax_bsm_purchase_order_items_master', [$this, 'ajax_purchase_order_items_master']);
        add_action('wp_ajax_bsm_purchase_order_cancel', [$this, 'ajax_purchase_order_cancel']);
        add_action('wp_ajax_bsm_purchase_order_get', [$this, 'ajax_purchase_order_get']);
        add_action('wp_ajax_bsm_purchase_order_update', [$this, 'ajax_purchase_order_update']);
        add_action('wp_ajax_bsm_purchase_order_pdf', [$this, 'ajax_purchase_order_pdf']);
        add_action('wp_ajax_bsm_purchase_order_pending_receipt', [$this, 'ajax_purchase_order_pending_receipt']);
        add_action('wp_ajax_bsm_purchase_order_receive_save', [$this, 'ajax_purchase_order_receive_save']);
        add_action('wp_ajax_bsm_material_requisition_item', [$this, 'ajax_material_requisition_item']);
        add_action('wp_ajax_bsm_dispatch_items_register', [$this, 'ajax_dispatch_items_register']);
        add_action('wp_ajax_bsm_site_material_item_receipt', [$this, 'ajax_site_material_item_receipt']);
        add_action('wp_ajax_bsm_consumption_items_register', [$this, 'ajax_consumption_items_register']);
        add_action('wp_ajax_bsm_return_receipt_master', [$this, 'ajax_return_receipt_master']);
        add_action('wp_ajax_bsm_return_receipt_item_master', [$this, 'ajax_return_receipt_item_master']);
        add_action('wp_ajax_bsm_material_returns_item_master', [$this, 'ajax_material_returns_item_master']);
        add_action('wp_ajax_bsm_sales_register', [$this, 'ajax_sales_register']);
        add_action('wp_ajax_bsm_sales_pdf', [$this, 'ajax_sales_pdf']);
        add_action('wp_ajax_bsm_dispatch_pdf', [$this, 'ajax_dispatch_pdf']);
        add_action('wp_ajax_bsm_daily_outward_summary', [$this, 'ajax_daily_outward_summary']);
        add_action('wp_ajax_bsm_daily_expense_summary', [$this, 'ajax_daily_expense_summary']);
        add_action('wp_ajax_bsm_pending_site_receive_challans', [$this, 'ajax_pending_site_receive_challans']);
        add_action('wp_ajax_bsm_pending_material_returns', [$this, 'ajax_pending_material_returns']);
        add_action('wp_ajax_bsm_get', [$this, 'ajax_get']);
        add_action('wp_ajax_bsm_save', [$this, 'ajax_save']);
        add_action('wp_ajax_bsm_delete', [$this, 'ajax_delete']);
        add_action('wp_ajax_bsm_options', [$this, 'ajax_options']);
        add_action('wp_ajax_bsm_dashboard', [$this, 'ajax_dashboard']);
        add_action('wp_ajax_bsm_report', [$this, 'ajax_report']);
        add_action('wp_ajax_bsm_update_contractor_payment_status', [$this, 'ajax_update_contractor_payment_status']);
        add_action('wp_ajax_bsm_update_contractor_paid_amount', [$this, 'ajax_update_contractor_paid_amount']);
        add_action('wp_ajax_bsm_contractor_advance_available', [$this, 'ajax_contractor_advance_available']);
        add_action('wp_ajax_bsm_user_list', [$this, 'ajax_user_list']);
        add_action('wp_ajax_bsm_user_save', [$this, 'ajax_user_save']);
        add_action('wp_ajax_bsm_user_delete', [$this, 'ajax_user_delete']);
        add_action('wp_ajax_bsm_office_attendance_day', [$this, 'ajax_office_attendance_day']);
        add_action('wp_ajax_bsm_office_attendance_save', [$this, 'ajax_office_attendance_save']);
        add_action('wp_ajax_bsm_office_attendance_master', [$this, 'ajax_office_attendance_master']);
        add_action('wp_ajax_bsm_monthly_salary', [$this, 'ajax_monthly_salary']);
        add_action('wp_ajax_bsm_allowance_submit', [$this, 'ajax_allowance_submit']);
        add_action('wp_ajax_bsm_allowance_list', [$this, 'ajax_allowance_list']);
        add_action('wp_ajax_bsm_allowance_decide', [$this, 'ajax_allowance_decide']);
        add_action('wp_ajax_bsm_allowance_limits', [$this, 'ajax_allowance_limits']);
        add_action('wp_ajax_bsm_salary_template_rows', [$this, 'ajax_salary_template_rows']);
        add_action('wp_ajax_bsm_salary_upload_preview', [$this, 'ajax_salary_upload_preview']);
        add_action('wp_ajax_bsm_salary_commit', [$this, 'ajax_salary_commit']);
        add_action('wp_ajax_bsm_salary_slips', [$this, 'ajax_salary_slips']);
        add_action('wp_ajax_bsm_pending_approval', [$this, 'ajax_pending_approval']);
        add_action('wp_ajax_bsm_requisition_approval_get', [$this, 'ajax_requisition_approval_get']);
        add_action('wp_ajax_bsm_requisition_approval_save', [$this, 'ajax_requisition_approval_save']);
        add_action('wp_ajax_bsm_pending_dispatch', [$this, 'ajax_pending_dispatch']);
        add_action('wp_ajax_bsm_dispatch_source_stock', [$this, 'ajax_dispatch_source_stock']);
        add_action('wp_ajax_bsm_dispatch_prefill', [$this, 'ajax_dispatch_prefill']);
        add_action('wp_ajax_bsm_site_receipt_prefill', [$this, 'ajax_site_receipt_prefill']);
        add_action('wp_ajax_bsm_site_stock_available', [$this, 'ajax_site_stock_available']);
        add_action('wp_ajax_bsm_site_stock_items', [$this, 'ajax_site_stock_items']);
        add_action('wp_ajax_bsm_labour_attendance_exists', [$this, 'ajax_labour_attendance_exists']);
        add_action('wp_ajax_bsm_upload', [$this, 'ajax_upload']);
        add_action('wp_ajax_bsm_consumption_item_upload', [$this, 'ajax_consumption_item_upload']);
        add_action('wp_ajax_bsm_company_logo_upload', [$this, 'ajax_company_logo_upload']);
        add_action('wp_ajax_bsm_company_upi_qr_upload', [$this, 'ajax_company_upi_qr_upload']);
        add_action('wp_ajax_nopriv_bsm_login', [$this, 'ajax_login']);
        add_action('wp_ajax_bsm_logout', [$this, 'ajax_logout']);
        add_filter('authenticate', [$this, 'enforce_bsm_user_status'], 30, 3);
    }

    public function register_roles() {
        $roles = [
            'bsm_owner' => 'Owner', 'bsm_management' => 'Management', 'bsm_site_manager' => 'Site Manager',
            'bsm_site_supervisor' => 'Site Supervisor', 'bsm_warehouse' => 'Warehouse',
            'bsm_accounts' => 'Accounts', 'bsm_checker' => 'Checker', 'bsm_employee' => 'Employee'
        ];
        foreach ($roles as $key => $label) {
            if (!get_role($key)) add_role($key, $label, ['read' => true, 'upload_files' => true]);
        }
    }

    public function activate() {
        // WordPress treats *any* text emitted during activation as unexpected output.
        // Some hosts display notices/warnings from schema migration helpers, which can
        // trigger a false activation warning even though the plugin activates correctly.
        // Isolate activation in its own output buffer and discard only output created here.
        $bsm_ob_level = ob_get_level();
        ob_start();
        try {
            $this->register_roles();
            $this->create_tables();
            $this->create_project_tracker_tables();
            $this->ensure_site_sections_column();
            $this->ensure_boq_section_column(); $this->ensure_boq_item_section_column();
            $this->migrate_boq_sections();
            $this->migrate_users_table();
            $this->migrate_office_attendance_table();
            $this->migrate_phase_0122();
            $this->migrate_requisition_table();
            $this->migrate_site_receipt_items_table();
            $this->migrate_material_returns_table();
            $this->migrate_customer_gst_type();
            $this->migrate_vendor_gst_type();
            $this->migrate_material_inward_type();
            $this->migrate_purchase_invoice_extras();
            $this->migrate_purchase_approval();
            $this->migrate_vendor_payment_allocations();
            $this->migrate_purchase_orders();
            $this->migrate_consumption_item_documents();
            $this->migrate_consumption_item_documents();
            $this->migrate_material_dispatch_courier();
            $this->migrate_vendor_payments_fund_type();
            $this->migrate_employee_allowance();
            $this->migrate_salary_system();
            $this->migrate_quantity_precision();
            $this->migrate_contractor_advance();
            $this->migrate_quotation_layout();
            $this->migrate_bank_master();
            $this->create_app_pages();
            $this->remove_obsolete_app_pages();
            $this->sync_existing_wp_users_to_bsm();
            update_option('bsm_plugin_version', self::VERSION);
            flush_rewrite_rules();
        } finally {
            while (ob_get_level() > $bsm_ob_level) {
                ob_end_clean();
            }
        }
    }

    private function create_project_tracker_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $p=$this->prefix;
        $charset=$wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$p}project_tasks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            site_id BIGINT UNSIGNED NOT NULL,
            section VARCHAR(190) NOT NULL,
            task_name VARCHAR(255) NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY site_section (site_id,section(100))
        ) $charset;");
        dbDelta("CREATE TABLE {$p}project_subtasks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            task_id BIGINT UNSIGNED NOT NULL,
            subtask_name VARCHAR(255) NOT NULL,
            completion DECIMAL(5,2) NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0,
            updated_at DATETIME NULL,
            updated_by BIGINT UNSIGNED NULL,
            PRIMARY KEY  (id),
            KEY task_id (task_id)
        ) $charset;");
        dbDelta("CREATE TABLE {$p}project_progress_history (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            subtask_id BIGINT UNSIGNED NOT NULL,
            old_progress DECIMAL(5,2) NOT NULL,
            new_progress DECIMAL(5,2) NOT NULL,
            changed_by BIGINT UNSIGNED NOT NULL,
            changed_at DATETIME NOT NULL,
            PRIMARY KEY  (id),
            KEY subtask_id (subtask_id)
        ) $charset;");
    }

    private function app_pages() {
        return [
            'dashboard' => ['Site Dashboard', ''],
            'project_tracker' => ['Site Tracker', 'project-tracker'],
            'boq_form' => ['BOQ Form', 'boq-form'],
            'boq_master' => ['BOQ Master', 'boq-master'],
            'boq_items' => ['BOQ Items Master', 'boq-items-master'],
            'boq_monitoring' => ['BOQ Monitoring', 'boq-monitoring'],
            'users' => ['Users', 'users'],
            'designations' => ['Designations', 'designations'],
            'bank_accounts' => ['Bank Master', 'bank-master'],
            'customers' => ['Customers', 'customers'],
            'sites' => ['Sites', 'sites'],
            'vendors' => ['Suppliers', 'vendors'],
            'labour_contractors' => ['Labour Contractors', 'labour-contractors'],
            'states' => ['States', 'states'],
            'item_categories' => ['Item Categories', 'item-categories'],
            'sections' => ['Section', 'sections'],
            'units' => ['Units', 'units'],
            'gst_rates' => ['GST Rates', 'gst-rates'],
            'items' => ['Items', 'items'],
            'warehouses' => ['Warehouses', 'warehouses'],
            'expense_categories' => ['Expense Categories', 'expense-categories'],
            'purchase_form' => ['Purchase Form', 'purchase-form'],
            'purchase_pending_approval' => ['Pending Approval', 'purchase-pending-approval'],
            'purchase_register' => ['Purchase Master', 'purchase-register'],
            'purchase_items_register' => ['Purchase Items Master', 'purchase-items-register'],
            'pending_invoice_copy' => ['Pending Invoice Copy', 'pending-invoice-copy'],
            'pending_po_items' => ['Pending PO Items', 'pending-po-items'],
            'purchase_order_pending_receipt' => ['Pending Receipt', 'purchase-order-pending-receipt'],
            'purchase_order_master' => ['PO Master', 'purchase-order-master'],
            'purchase_order_items_master' => ['PO Items Master', 'purchase-order-items-master'],
            'requisition_form' => ['Requisition Form', 'requisition-form'],
            'material_requisitions' => ['Requisitions Master', 'material-requisitions'],
            'material_requisition_item' => ['Requisition Item Master', 'material-requisition-item'],
            'pending_requisition_approval' => ['Pending Approval', 'pending-requisition-approval'],
            'pending_material_receipt' => ['Pending Site Return Approval', 'pending-site-return-approval'],
            'return_receipt_master' => ['Return Receipt Master', 'return-receipt-master'],
            'return_receipt_item_master' => ['Return Receipt Item Master', 'return-receipt-item-master'],
            'material_inwards' => ['Material Inward', 'material-inward'],
            'pending_material_dispatch' => ['Pending Dispatch', 'pending-material-dispatch'],
            'material_dispatches' => ['Dispatch Master', 'dispatch-master'],
            'dispatch_items_register' => ['Dispatch Items Master', 'dispatch-items-register'],
            'pending_site_receipt' => ['Pending Site Material Receipt', 'pending-site-material-receipt'],
            'site_receipts' => ['Material Receipts Master', 'site-material-receipts'],
            'site_material_item_receipt' => ['Material Receipts Item Master', 'site-material-item-receipt'],
            'pending_site_receive_challans' => ['Pending Site Receive Challans', 'pending-site-receive-challans'],
            'consumption_form' => ['Consumption Form', 'consumption-form'],
            'site_consumptions' => ['Consumption Master', 'consumption-master'],
            'consumption_items_register' => ['Consumption Items Master', 'consumption-items-master'],
            'return_form' => ['Return Form', 'return-form'],
            'material_returns' => ['Material Returns Master', 'material-returns-master'],
            'material_returns_item_master' => ['Material Returns Item Master', 'material-returns-item-master'],
            'material_stock' => ['Stock', 'material-stock'],
            'ho_inventory' => ['HO Inventory', 'ho-inventory'],
            'site_inventory' => ['Site Inventory', 'site-inventory'],
            'site_opening_stock' => ['Site Opening Stock', 'site-opening-stock'],
            'daily_outward_summary' => ['Daily Outward Summary', 'daily-outward-summary'],
            'labour_attendance' => ['Daily Labour Attendance', 'daily-labour-attendance'],
            'holiday_list' => ['Holiday List', 'holiday-list'],
            'time_sheet' => ['Time Sheet', 'time-sheet'],
            'time_sheet_master' => ['Time Sheet Master', 'time-sheet-master'],
            'contractor_employees' => ['Contractor Employees', 'contractor-employees'],
            'contractor_calculated_bill' => ['Employee / Time Based Bill', 'contractor-calculated-bill'],
            'site_transfer_form' => ['Site-to-Site Transfer Form', 'site-transfer-form'],
            'site_transfer_master' => ['Site-to-Site Transfer Master', 'site-transfer-master'],
            'office_daily_attendance' => ['Daily Attendance', 'office-daily-attendance'],
            'office_attendance_master' => ['Attendance Master', 'office-attendance-master'],
            'office_monthly_salary' => ['Monthly Salary', 'office-monthly-salary'],
            'allowance_form' => ['Allowance Form', 'allowance-form'],
            'pending_allowance_approval' => ['Pending Allowance Approval', 'pending-allowance-approval'],
            'pending_allowance_payment' => ['Pending Allowance Payment', 'pending-allowance-payment'],
            'allowance_master' => ['Allowance Master', 'allowance-master'],
            'employee_allowance_limits' => ['Employee Allowance Limit', 'employee-allowance-limit'],
            'allowance_types' => ['Allowance Types', 'allowance-types'],
            'monthly_salary_upload' => ['Monthly Salary Upload', 'monthly-salary-upload'],
            'salary_slips' => ['Salary Slips', 'salary-slips'],
            'pending_salary_payment' => ['Pending Salary Payment', 'pending-salary-payment'],
            'my_salary_slips' => ['My Salary Slips', 'my-salary-slips'],
            'contractor_form' => ['Labour Contractor Bill Form', 'contractor-form'],
            'pending_contractor_bill_copy' => ['Pending Bill Copy', 'pending-labour-contractor-bill-copy'],
            'pending_contractor_payment' => ['Pending Labour Contractor Payment', 'pending-contractor-payment'],
            'site_fund_allocations' => ['Site Opening', 'site-opening'],
            'site_expenses' => ['Site Expenses', 'site-expenses'],
            'daily_expense_summary' => ['Daily Expense Summary', 'daily-expense-summary'],
            'customer_receipts' => ['Customer Receipts', 'customer-receipts'],
            'payment_form' => ['Payment Form', 'payment-form'],
            'vendor_payments' => ['Payment Master', 'vendor-payments'],
            'contractor_bills' => ['Labour Contractor Bills', 'contractor-bills'],
            'contractor_advance' => ['Contractor Payment Form', 'contractor-advance'],
            'contractor_advance_register' => ['Contractor Payment Master', 'contractor-advance-register'],
            'contractor_payments' => ['Contractor Payments', 'contractor-payments'],
            'report_stock' => ['Stock Report', 'stock-report'],
            'report_site_fund' => ['Site Balance', 'site-balance'],
            'report_site_cost' => ['Site Cost Report', 'site-cost-report'],
            'report_labour' => ['Daily Labour Report', 'daily-labour-report'],
            'report_labour_summary' => ['Labour Summary', 'labour-summary'],
            'report_customer_outstanding' => ['Site Balance', 'customer-outstanding'],
            'report_vendor_outstanding' => ['Supplier Outstanding', 'vendor-outstanding'],
            'report_contractor_outstanding' => ['Sitewise Contractor Outstanding', 'contractor-outstanding'],
            'quotation_form' => ['Quotation Form', 'quotation-form'],
            'quotation_master' => ['Quotation Master', 'quotation-master'],
            'quotation_items' => ['Quotation Items Master', 'quotation-items'],
            'invoice_form' => ['Invoice Form', 'invoice-form'],
            'invoice_master' => ['Invoice Master', 'invoice-master'],
            'invoice_items' => ['Invoice Items Master', 'invoice-items'],
            'company_settings' => ['Company Settings', 'company-settings'],
            'view_settings' => ['Show / Hide Views', 'view-settings'],
        ];
    }

    private function ensure_site_sections_column() {
        global $wpdb;
        $table = $this->prefix . 'sites';
        if (!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'site_sections'))) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `site_sections` TEXT NULL AFTER `site_location`");
        }
        return (bool) $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'site_sections'));
    }

    public function maybe_upgrade() {
        if (get_option('bsm_plugin_version') !== self::VERSION) {
            $this->create_tables();
            $this->create_project_tracker_tables();
            $this->ensure_site_sections_column();
            $this->ensure_boq_section_column(); $this->ensure_boq_item_section_column();
            $this->split_existing_mixed_section_boqs();
            // Critical schema repair for sites upgraded from older builds where
            // bsm_users.user_id may still be numeric. This must run on upgrade,
            // not only on fresh activation.
            $this->migrate_users_table();
            $this->migrate_office_attendance_table();
            $this->migrate_phase_0122();
            $this->migrate_requisition_table();
            $this->migrate_dispatch_items_table();
            $this->migrate_material_dispatch_courier();
            $this->migrate_site_receipt_items_table();
            $this->migrate_material_returns_table();
            $this->migrate_customer_gst_type();
            $this->migrate_vendor_gst_type();
            $this->migrate_material_inward_type();
            $this->migrate_vendor_payments_fund_type();
            $this->migrate_employee_allowance();
            $this->migrate_salary_system();
            $this->migrate_quantity_precision();
            $this->migrate_contractor_advance();
            $this->migrate_quotation_layout();
            $this->migrate_company_settings_terms();
            $this->migrate_company_payment_details();
            $this->migrate_bank_master();
        $this->migrate_purchase_invoice_extras();
            $this->migrate_purchase_approval();
            $this->migrate_vendor_payment_allocations();
            $this->migrate_purchase_orders();
            $this->create_app_pages();
            $this->remove_obsolete_app_pages();
            $this->sync_existing_wp_users_to_bsm();
            update_option('bsm_plugin_version', self::VERSION);
            flush_rewrite_rules(false);
        }
    }

    private function ensure_boq_section_column() {
        global $wpdb;
        $table = $this->prefix . 'boqs';
        $found = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'section'));
        if (!$found) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `section` VARCHAR(190) NOT NULL DEFAULT '' AFTER `site_id`");
        }
        return (bool)$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'section'));
    }

    private function ensure_boq_item_section_column() {
        global $wpdb;
        $table = $this->prefix . 'boq_items';
        if (!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'section'))) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `section` VARCHAR(190) NOT NULL DEFAULT '' AFTER `item_id`");
        }
        $exists = (bool)$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'section'));
        if ($exists) {
            $wpdb->query("UPDATE `{$table}` bi INNER JOIN `{$this->prefix}boqs` b ON b.id=bi.boq_id SET bi.section=b.section WHERE bi.section='' AND b.section<>''");
        }
        return $exists;
    }

    private function migrate_boq_sections() {
        global $wpdb;
        $p=$this->prefix;
        // Preserve historical free-text BOQ section values after changing the UI to a master dropdown.
        $wpdb->query("INSERT IGNORE INTO {$p}sections (section_name,status) SELECT DISTINCT TRIM(section),'active' FROM {$p}boqs WHERE section IS NOT NULL AND TRIM(section)<>''");
    }

    private function migrate_company_settings_terms() {
        global $wpdb;
        $table=$this->prefix.'company_settings';
        $exists=$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table));
        if($exists!==$table) return;
        $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",'terms_conditions'));
        if(!$has) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `terms_conditions` TEXT NULL AFTER `financial_year_start`");
    }


    private function migrate_bank_master(){
        global $wpdb;
        foreach(['customer_receipts','vendor_payments','contractor_payments'] as $name){
            $table=$this->prefix.$name;
            if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table))===$table && !$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",'bank_account_id'))){
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `bank_account_id` BIGINT UNSIGNED NULL AFTER `payment_mode`");
            }
        }
    }

    private function migrate_company_payment_details() {
        global $wpdb;
        $table=$this->prefix.'company_settings';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table))!==$table) return;
        $cols=[
            'upi_qr_url'=>"LONGTEXT NULL",
            'bank_account_holder'=>"VARCHAR(190) NULL",
            'bank_name'=>"VARCHAR(190) NULL",
            'bank_branch'=>"VARCHAR(190) NULL",
            'bank_ifsc'=>"VARCHAR(60) NULL",
            'bank_account_no'=>"VARCHAR(100) NULL",
            'pdf_theme_color'=>"VARCHAR(20) NULL",
        ];
        foreach($cols as $name=>$def){
            $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",$name));
            if(!$has) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$def}");
        }
    }


    private function migrate_consumption_item_documents() {
        global $wpdb;
        $table=$this->prefix.'site_consumption_items';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table))!==$table) return;
        $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",'document_url'));
        if(!$has) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `document_url` TEXT NULL AFTER `remarks`");
        $hasName=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",'document_name'));
        if(!$hasName) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `document_name` VARCHAR(255) NULL AFTER `document_url`");
    }

    private function migrate_purchase_approval() {
        global $wpdb;
        $items=$this->prefix.'material_inward_items';
        $head=$this->prefix.'material_inwards';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$items))===$items){
            $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$items}` LIKE %s",'approved_qty'));
            if(!$has) $wpdb->query("ALTER TABLE `{$items}` ADD COLUMN `approved_qty` DECIMAL(18,2) DEFAULT 0 AFTER `qty`");
            $hasPrice=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$items}` LIKE %s",'item_price'));
            if(!$hasPrice){
                $wpdb->query("ALTER TABLE `{$items}` ADD COLUMN `item_price` DECIMAL(18,2) DEFAULT 0 AFTER `unit_id`");
                // Snapshot the current Item Master Price for legacy purchase rows once during migration.
                $wpdb->query("UPDATE `{$items}` mii LEFT JOIN `{$this->prefix}items` i ON i.id=mii.item_id SET mii.item_price=COALESCE(i.opening_rate,0) WHERE COALESCE(mii.item_price,0)=0");
            }
        }
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$head))===$head){
            // Existing purchase rows created before this workflow remain approved; only new/edited purchases enter Pending Approval.
            $wpdb->query("UPDATE `{$head}` SET status='approved' WHERE LOWER(TRIM(COALESCE(inward_type,'')))='purchase' AND (status IS NULL OR status='')");
        }
    }

    private function migrate_vendor_payment_allocations() {
        global $wpdb;
        $table=$this->prefix.'vendor_payment_allocations';
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $charset=$wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$table} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            payment_id BIGINT UNSIGNED NOT NULL,
            material_inward_id BIGINT UNSIGNED NOT NULL,
            amount DECIMAL(18,2) NOT NULL DEFAULT 0,
            created_at DATETIME NULL,
            PRIMARY KEY(id),
            KEY payment_id(payment_id),
            KEY material_inward_id(material_inward_id)
        ) {$charset};");
    }

    private function migrate_purchase_orders() {
        global $wpdb;
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        $p=$this->prefix;$c=$wpdb->get_charset_collate();
        dbDelta("CREATE TABLE {$p}purchase_orders (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            po_no VARCHAR(80) NOT NULL,
            po_date DATE NOT NULL,
            vendor_id BIGINT UNSIGNED NOT NULL,
            warehouse_id BIGINT UNSIGNED NOT NULL,
            required_date DATE NULL,
            remarks TEXT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'created',
            created_by BIGINT UNSIGNED NULL,
            created_at DATETIME NULL,
            updated_at DATETIME NULL,
            PRIMARY KEY(id), UNIQUE KEY po_no(po_no), KEY vendor_id(vendor_id), KEY warehouse_id(warehouse_id), KEY status(status)
        ) {$c};");
        dbDelta("CREATE TABLE {$p}purchase_order_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            purchase_order_id BIGINT UNSIGNED NOT NULL,
            requisition_item_id BIGINT UNSIGNED NULL,
            item_id BIGINT UNSIGNED NOT NULL,
            unit_id BIGINT UNSIGNED NULL,
            approved_qty_snapshot DECIMAL(18,2) NOT NULL DEFAULT 0,
            stock_snapshot DECIMAL(18,2) NOT NULL DEFAULT 0,
            pending_qty_snapshot DECIMAL(18,2) NOT NULL DEFAULT 0,
            po_qty DECIMAL(18,2) NOT NULL DEFAULT 0,
            fixed_price DECIMAL(18,2) NOT NULL DEFAULT 0,
            rate DECIMAL(18,2) NOT NULL DEFAULT 0,
            created_at DATETIME NULL,
            PRIMARY KEY(id), KEY purchase_order_id(purchase_order_id), KEY requisition_item_id(requisition_item_id), KEY item_id(item_id)
        ) {$c};");
        dbDelta("CREATE TABLE {$p}purchase_order_item_allocations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            purchase_order_item_id BIGINT UNSIGNED NOT NULL,
            requisition_item_id BIGINT UNSIGNED NOT NULL,
            allocated_qty DECIMAL(18,2) NOT NULL DEFAULT 0,
            created_at DATETIME NULL,
            PRIMARY KEY(id), KEY purchase_order_item_id(purchase_order_item_id), KEY requisition_item_id(requisition_item_id)
        ) {$c};");
        if(!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$p}material_inwards` LIKE %s",'purchase_order_id')))
            $wpdb->query("ALTER TABLE `{$p}material_inwards` ADD COLUMN `purchase_order_id` BIGINT UNSIGNED NULL AFTER `vendor_id`, ADD KEY `purchase_order_id` (`purchase_order_id`)");
        if(!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$p}material_inward_items` LIKE %s",'purchase_order_item_id')))
            $wpdb->query("ALTER TABLE `{$p}material_inward_items` ADD COLUMN `purchase_order_item_id` BIGINT UNSIGNED NULL AFTER `inward_id`, ADD KEY `purchase_order_item_id` (`purchase_order_item_id`)");
        // Backfill allocation links for POs created by older plugin versions.
        $wpdb->query("INSERT INTO {$p}purchase_order_item_allocations (purchase_order_item_id,requisition_item_id,allocated_qty,created_at)
            SELECT poi.id,poi.requisition_item_id,poi.po_qty,NOW() FROM {$p}purchase_order_items poi
            LEFT JOIN {$p}purchase_order_item_allocations a ON a.purchase_order_item_id=poi.id
            WHERE poi.requisition_item_id IS NOT NULL AND a.id IS NULL");
    }

    private function default_warehouse_id() {
        global $wpdb;
        return (int)$wpdb->get_var("SELECT id FROM {$this->prefix}warehouses ORDER BY (status='active') DESC,id ASC LIMIT 1");
    }

    private function warehouse_item_stock($warehouse_id,$item_id) {
        global $wpdb;$p=$this->prefix;$warehouse_id=(int)$warehouse_id;$item_id=(int)$item_id;
        if(!$warehouse_id||!$item_id)return 0.0;
        $default=$this->default_warehouse_id();
        $opening=($warehouse_id===$default)?(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(opening_qty,0) FROM {$p}items WHERE id=%d",$item_id)):0.0;
        $inward=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(d.qty),0) FROM {$p}material_inward_items d JOIN {$p}material_inwards h ON h.id=d.inward_id WHERE h.warehouse_id=%d AND d.item_id=%d AND LOWER(COALESCE(h.status,''))<>'cancelled'",$warehouse_id,$item_id));
        $returned=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(d.qty),0) FROM {$p}material_return_items d JOIN {$p}material_returns h ON h.id=d.return_id WHERE h.warehouse_id=%d AND d.item_id=%d AND LOWER(COALESCE(h.status,''))<>'cancelled'",$warehouse_id,$item_id));
        $dispatch=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(d.qty),0) FROM {$p}material_dispatch_items d JOIN {$p}material_dispatches h ON h.id=d.dispatch_id WHERE h.warehouse_id=%d AND d.item_id=%d AND LOWER(COALESCE(h.status,''))<>'cancelled'",$warehouse_id,$item_id));
        return round($opening+$inward+$returned-$dispatch,2);
    }

    private function plugin_user_id() {
        global $wpdb;
        return (int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()));
    }


    private function po_item_received_qty($po_item_id) {
        global $wpdb;$p=$this->prefix;
        return round((float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(mii.qty),0) FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mii.purchase_order_item_id=%d AND LOWER(COALESCE(mi.status,''))<>'cancelled'",(int)$po_item_id)),2);
    }

    private function refresh_purchase_order_status($po_id) {
        global $wpdb;$p=$this->prefix;$po_id=(int)$po_id;if(!$po_id)return;
        $status=(string)$wpdb->get_var($wpdb->prepare("SELECT status FROM {$p}purchase_orders WHERE id=%d",$po_id));
        if($status==='cancelled')return;
        $items=$wpdb->get_results($wpdb->prepare("SELECT id,po_qty FROM {$p}purchase_order_items WHERE purchase_order_id=%d",$po_id),ARRAY_A)?:[];
        $total=0.0;$received=0.0;
        foreach($items as $it){$total+=(float)$it['po_qty'];$received+=min((float)$it['po_qty'],$this->po_item_received_qty((int)$it['id']));}
        $new=$received<=0.009?'created':(($received+0.009>=$total&&$total>0)?'fully_received':'partly_received');
        $wpdb->update($p.'purchase_orders',['status'=>$new,'updated_at'=>current_time('mysql')],['id'=>$po_id]);
    }

    private function pending_po_summary($warehouse) {
        global $wpdb;$p=$this->prefix;$warehouse=(int)$warehouse;if(!$warehouse)$warehouse=$this->default_warehouse_id();
        $raw=$wpdb->get_results("SELECT ri.id requisition_item_id,ri.requisition_id,r.requisition_no,r.requisition_date,r.required_date,r.site_id,s.site_name,r.requested_by,r.approved_by,COALESCE(req_u.name,req_wp.name,'-') requested_by_name,COALESCE(app_u.name,app_wp.name,'-') approved_by_name,ri.item_id,i.item_name,ri.unit_id,u.unit_name,ri.approved_qty,i.opening_rate fixed_price,
            COALESCE((SELECT SUM(di.qty) FROM {$p}material_dispatch_items di JOIN {$p}material_dispatches d ON d.id=di.dispatch_id WHERE di.requisition_item_id=ri.id AND LOWER(COALESCE(d.status,''))<>'cancelled'),0) dispatched_qty
            FROM {$p}material_requisition_items ri JOIN {$p}material_requisitions r ON r.id=ri.requisition_id LEFT JOIN {$p}sites s ON s.id=r.site_id LEFT JOIN {$p}items i ON i.id=ri.item_id LEFT JOIN {$p}units u ON u.id=ri.unit_id
            LEFT JOIN {$p}users req_u ON req_u.id=r.requested_by LEFT JOIN {$p}users req_wp ON req_wp.wp_user_id=r.requested_by
            LEFT JOIN {$p}users app_u ON app_u.id=r.approved_by LEFT JOIN {$p}users app_wp ON app_wp.wp_user_id=r.approved_by
            WHERE r.status IN ('approved','partially_dispatched','partially dispatched','fully_dispatched','fully dispatched') AND ri.approved_qty>0
            ORDER BY r.required_date ASC,r.id ASC,ri.id ASC",ARRAY_A)?:[];
        $groups=[];
        foreach($raw as $x){
            $remaining=max(0,round((float)$x['approved_qty']-(float)$x['dispatched_qty'],2));if($remaining<=0.009)continue;
            $iid=(int)$x['item_id'];
            if(!isset($groups[$iid]))$groups[$iid]=['item_id'=>$iid,'item_name'=>$x['item_name'],'unit_id'=>(int)$x['unit_id'],'unit_name'=>$x['unit_name'],'fixed_price'=>(float)$x['fixed_price'],'approved_qty'=>0.0,'sites'=>[],'requirements'=>[]];
            $groups[$iid]['approved_qty']+=$remaining;$groups[$iid]['sites'][(string)$x['site_name']]=1;
            $groups[$iid]['requirements'][]=['requisition_item_id'=>(int)$x['requisition_item_id'],'requisition_no'=>$x['requisition_no'],'site_name'=>$x['site_name'],'item_name'=>$x['item_name'],'approved_qty'=>$remaining,'approved_by_name'=>$x['approved_by_name'],'requested_by_name'=>$x['requested_by_name'],'requisition_date'=>$x['requisition_date'],'required_date'=>$x['required_date'],'remaining_qty'=>$remaining];
        }
        $rows=[];
        foreach($groups as $iid=>$g){
            $physical=$this->warehouse_item_stock($warehouse,$iid);
            $po_items=$wpdb->get_results($wpdb->prepare("SELECT poi.id,poi.po_qty FROM {$p}purchase_order_items poi JOIN {$p}purchase_orders po ON po.id=poi.purchase_order_id WHERE po.warehouse_id=%d AND poi.item_id=%d AND LOWER(COALESCE(po.status,'')) NOT IN ('cancelled','fully_received')",$warehouse,$iid),ARRAY_A)?:[];
            $po_progress=0.0;foreach($po_items as $pi)$po_progress+=max(0,(float)$pi['po_qty']-$this->po_item_received_qty((int)$pi['id']));
            $planning=round($physical+$po_progress,2);$pending=max(0,round((float)$g['approved_qty']-$planning,2));if($pending<=0.009)continue;
            $coverage=max(0,$planning);$alloc=[];$detail_ids=[];
            foreach($g['requirements'] as $rq){
                $need=(float)$rq['remaining_qty'];
                $covered=min($need,$coverage);
                $coverage-=$covered;
                $short=max(0,round($need-$covered,2));
                if($short>0.009){
                    $alloc[]=['requisition_item_id'=>$rq['requisition_item_id'],'qty'=>$short,'required_date'=>$rq['required_date']];
                    $detail_ids[(int)$rq['requisition_item_id']]=1;
                }
            }
            $details=array_values(array_filter($g['requirements'],function($rq) use ($detail_ids){return !empty($detail_ids[(int)$rq['requisition_item_id']]);}));
            $rows[]=['item_id'=>$iid,'item_name'=>$g['item_name'],'unit_id'=>$g['unit_id'],'unit_name'=>$g['unit_name'],'fixed_price'=>$g['fixed_price'],'approved_qty'=>round($g['approved_qty'],2),'physical_stock'=>round($physical,2),'po_in_progress_qty'=>round($po_progress,2),'current_stock'=>$planning,'pending_po_qty'=>$pending,'allocations'=>$alloc,'details'=>$details];
        }
        usort($rows,function($a,$b){return strcasecmp($a['item_name'],$b['item_name']);});
        return $rows;
    }

    public function ajax_pending_po_items(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;
        $warehouse=(int)($_POST['warehouse_id']??0);if(!$warehouse)$warehouse=$this->default_warehouse_id();
        $rows=$this->pending_po_summary($warehouse);
        $warehouse_name=$warehouse?(string)$wpdb->get_var($wpdb->prepare("SELECT warehouse_name FROM {$p}warehouses WHERE id=%d",$warehouse)):'';
        wp_send_json_success(['rows'=>$rows,'warehouse_id'=>$warehouse,'warehouse_name'=>$warehouse_name]);
    }

    private function create_purchase_order_record($vendor_id,$warehouse_id,$po_date,$required_date,$remarks,$items){
        global $wpdb;$p=$this->prefix;$now=current_time('mysql');$uid=$this->plugin_user_id();
        $po_no=$this->next_no('purchase_orders','PO');
        $ok=$wpdb->insert($p.'purchase_orders',['po_no'=>$po_no,'po_date'=>$po_date,'vendor_id'=>$vendor_id,'warehouse_id'=>$warehouse_id,'required_date'=>$required_date?:null,'remarks'=>$remarks,'status'=>'created','created_by'=>$uid?:null,'created_at'=>$now,'updated_at'=>$now]);
        if(!$ok)return ['error'=>$wpdb->last_error?:'Unable to create PO'];$po_id=(int)$wpdb->insert_id;
        foreach($items as $it){
            $ok=$wpdb->insert($p.'purchase_order_items',['purchase_order_id'=>$po_id,'requisition_item_id'=>null,'item_id'=>(int)$it['item_id'],'unit_id'=>(int)$it['unit_id'],'approved_qty_snapshot'=>(float)($it['approved_qty_snapshot']??0),'stock_snapshot'=>(float)($it['stock_snapshot']??0),'pending_qty_snapshot'=>(float)($it['pending_qty_snapshot']??0),'po_qty'=>(float)$it['po_qty'],'fixed_price'=>(float)($it['fixed_price']??0),'rate'=>(float)($it['rate']??0),'created_at'=>$now]);
            if(!$ok)return ['error'=>$wpdb->last_error?:'Unable to save PO Item'];$poi=(int)$wpdb->insert_id;
            foreach(($it['allocations']??[]) as $al){$aq=round((float)($al['qty']??0),2);$ri=(int)($al['requisition_item_id']??0);if($ri&&$aq>0)$wpdb->insert($p.'purchase_order_item_allocations',['purchase_order_item_id'=>$poi,'requisition_item_id'=>$ri,'allocated_qty'=>$aq,'created_at'=>$now]);}
        }
        return ['id'=>$po_id,'po_no'=>$po_no];
    }

    public function ajax_purchase_order_create(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;
        $warehouse=(int)($_POST['warehouse_id']??0);if(!$warehouse)$warehouse=$this->default_warehouse_id();$items=json_decode(stripslashes($_POST['items']??'[]'),true);if(!$warehouse||!is_array($items)||!$items)wp_send_json_error(['message'=>'Select Supplier and enter PO Qty']);
        $summary=$this->pending_po_summary($warehouse);$by_item=[];foreach($summary as $x)$by_item[(int)$x['item_id']]=$x;$groups=[];
        foreach($items as $it){$iid=(int)($it['item_id']??0);$vendor=(int)($it['vendor_id']??0);$qty=round((float)($it['po_qty']??0),2);$rate=round((float)($it['rate']??0),2);if(!$iid||!$vendor||$qty<=0)continue;if($rate<0)wp_send_json_error(['message'=>'Rate cannot be negative']);if(empty($by_item[$iid]))wp_send_json_error(['message'=>'Item is no longer pending for PO']);$x=$by_item[$iid];if($qty>(float)$x['pending_po_qty']+0.009)wp_send_json_error(['message'=>'PO Qty cannot exceed Pending PO Qty for '.$x['item_name']]);
            $quotes=[];$quote_ids=[];$selected_rate=null;
            foreach((array)($it['quotations']??[]) as $quote){
                $qid=(int)($quote['vendor_id']??0);$raw=$quote['rate']??'';
                if(!$qid||isset($quote_ids[$qid])||!is_numeric($raw)||!is_finite((float)$raw)||(float)$raw<0)wp_send_json_error(['message'=>'Enter three different suppliers with valid rates.']);
                if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}vendors WHERE id=%d AND status='active'",$qid)))wp_send_json_error(['message'=>'Select active suppliers.']);
                $quote_ids[$qid]=true;$qr=round((float)$raw,2);$quotes[]=['supplier_id'=>$qid,'rate'=>$qr];if($qid===$vendor)$selected_rate=$qr;
            }
            if(count($quotes)!==3||$selected_rate===null)wp_send_json_error(['message'=>'Choose a supplier from three quotations.']);
            $rate=$selected_rate;
            $remain=$qty;$alloc=[];$dates=[];foreach($x['allocations'] as $al){if($remain<=0.009)break;$a=min($remain,(float)$al['qty']);if($a>0){$alloc[]=['requisition_item_id'=>(int)$al['requisition_item_id'],'qty'=>$a];$remain=round($remain-$a,2);if(!empty($al['required_date']))$dates[]=$al['required_date'];}}
            $groups[$vendor][]=['item_id'=>$iid,'unit_id'=>(int)$x['unit_id'],'approved_qty_snapshot'=>(float)$x['approved_qty'],'stock_snapshot'=>(float)$x['current_stock'],'pending_qty_snapshot'=>(float)$x['pending_po_qty'],'po_qty'=>$qty,'fixed_price'=>(float)$x['fixed_price'],'rate'=>$rate,'quotations'=>$quotes,'allocations'=>$alloc,'required_date'=>$dates?min($dates):null];
        }
        if(!$groups)wp_send_json_error(['message'=>'Select Supplier and enter PO Qty for at least one item']);
        // DDL auto-commits; migrate the legacy unique index BEFORE starting a transaction.
        $indexes=$wpdb->get_results("SHOW INDEX FROM `{$it}` WHERE Key_name='boq_item'",ARRAY_A);
        if($indexes && count($indexes)===2 && ($indexes[0]['Non_unique']??0)==0) {
            $wpdb->query("ALTER TABLE `{$it}` DROP INDEX `boq_item`, ADD KEY `boq_item` (`boq_id`,`item_id`)");
        }
        $wpdb->query('START TRANSACTION');$created=[];foreach($groups as $vendor=>$gitems){$dates=array_values(array_filter(array_column($gitems,'required_date')));sort($dates);$quote_record=wp_json_encode(['supplier_quotations'=>array_map(function($line){return ['item_id'=>$line['item_id'],'quotations'=>$line['quotations'],'selected_rate'=>$line['rate']];},$gitems)]);$res=$this->create_purchase_order_record((int)$vendor,$warehouse,current_time('Y-m-d'),$dates[0]??null,$quote_record,$gitems);if(!empty($res['error'])){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>$res['error']]);}$created[]=$res;}$wpdb->query('COMMIT');wp_send_json_success(['created'=>$created,'message'=>count($created).' PO'.(count($created)>1?'s':'').' created successfully']);
    }

    public function ajax_purchase_order_manual_save(){
        $current_user=wp_get_current_user();
        $created_by=$current_user && $current_user->exists() ? $current_user->display_name : '';

        $this->verify_ajax();$this->migrate_purchase_orders();$vendor=(int)($_POST['vendor_id']??0);$warehouse=(int)($_POST['warehouse_id']??0);$po_date=sanitize_text_field($_POST['po_date']??'');$required=sanitize_text_field($_POST['required_date']??'');$remarks=sanitize_textarea_field($_POST['remarks']??'');$items=json_decode(stripslashes($_POST['items']??'[]'),true);
        if(!$vendor||!$warehouse||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$po_date)||!is_array($items)||!$items)wp_send_json_error(['message'=>'Supplier, Warehouse, PO Date and at least one Item are required']);$clean=[];
        foreach($items as $it){$iid=(int)($it['item_id']??0);$qty=round((float)($it['po_qty']??0),2);if(!$iid||$qty<=0)continue;global $wpdb;$p=$this->prefix;$x=$wpdb->get_row($wpdb->prepare("SELECT id,unit_id,opening_rate FROM {$p}items WHERE id=%d",$iid),ARRAY_A);if(!$x)continue;$quotes=[];$seen=[];$selected=null;foreach((array)($it['quotations']??[]) as $q){$qid=(int)($q['vendor_id']??0);$raw=$q['rate']??'';if(!$qid||isset($seen[$qid])||!is_numeric($raw)||!is_finite((float)$raw)||(float)$raw<0)wp_send_json_error(['message'=>'Enter three different suppliers with valid rates.']);if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}vendors WHERE id=%d AND status='active'",$qid)))wp_send_json_error(['message'=>'Select active suppliers.']);$seen[$qid]=true;$qr=round((float)$raw,2);$quotes[]=['supplier_id'=>$qid,'rate'=>$qr];if($qid===$vendor)$selected=$qr;}if(count($quotes)!==3||$selected===null)wp_send_json_error(['message'=>'Choose a supplier from three quotations.']);$clean[]=['item_id'=>$iid,'unit_id'=>(int)$x['unit_id'],'po_qty'=>$qty,'fixed_price'=>(float)$x['opening_rate'],'rate'=>$selected,'quotations'=>$quotes];}
        if(!$clean)wp_send_json_error(['message'=>'Enter valid PO Items']);$remarks=wp_json_encode(['supplier_quotations'=>array_map(function($line){return ['item_id'=>$line['item_id'],'quotations'=>$line['quotations'],'selected_rate'=>$line['rate']];},$clean)]);$res=$this->create_purchase_order_record($vendor,$warehouse,$po_date,$required,$remarks,$clean);if(!empty($res['error']))wp_send_json_error(['message'=>$res['error']]);wp_send_json_success(['message'=>'PO created successfully','id'=>$res['id'],'po_no'=>$res['po_no']]);
    }

    public function ajax_purchase_order_master(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;$search=sanitize_text_field($_POST['search']??'');$vendor=(int)($_POST['vendor_id']??0);$status=sanitize_key($_POST['status']??'');$where='1=1';$args=[];if($vendor){$where.=' AND po.vendor_id=%d';$args[]=$vendor;}if($status){$where.=' AND po.status=%s';$args[]=$status;}if($search!==''){$n='%'.$wpdb->esc_like($search).'%';$where.=' AND (po.po_no LIKE %s OR v.vendor_name LIKE %s OR w.warehouse_name LIKE %s)';$args=array_merge($args,[$n,$n,$n]);}
        $sql="SELECT po.*,v.vendor_name,w.warehouse_name,u.name created_by_name,COALESCE((SELECT SUM(poi.po_qty*poi.rate) FROM {$p}purchase_order_items poi WHERE poi.purchase_order_id=po.id),0) total_amount,COALESCE((SELECT COUNT(*) FROM {$p}purchase_order_items poi WHERE poi.purchase_order_id=po.id),0) item_count,COALESCE((SELECT SUM(poi.po_qty) FROM {$p}purchase_order_items poi WHERE poi.purchase_order_id=po.id),0) po_qty,COALESCE((SELECT SUM(mii.qty) FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mi.purchase_order_id=po.id AND LOWER(COALESCE(mi.status,''))<>'cancelled'),0) received_qty FROM {$p}purchase_orders po LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}warehouses w ON w.id=po.warehouse_id LEFT JOIN {$p}users u ON u.id=po.created_by WHERE $where ORDER BY po.id DESC";if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A)?:[];foreach($rows as &$x){$x['pending_qty']=max(0,round((float)$x['po_qty']-(float)$x['received_qty'],2));}wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_purchase_order_items_master(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;$search=sanitize_text_field($_POST['search']??'');$vendor=(int)($_POST['vendor_id']??0);$where='1=1';$args=[];if($vendor){$where.=' AND po.vendor_id=%d';$args[]=$vendor;}if($search!==''){$n='%'.$wpdb->esc_like($search).'%';$where.=' AND (po.po_no LIKE %s OR v.vendor_name LIKE %s OR i.item_name LIKE %s)';$args=array_merge($args,[$n,$n,$n]);}
        $sql="SELECT poi.*,po.remarks,po.po_no,po.po_date,po.required_date,po.status,po.vendor_id,v.vendor_name,po.warehouse_id,w.warehouse_name,i.item_name,u.unit_name,COALESCE((SELECT SUM(mii.qty) FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mii.purchase_order_item_id=poi.id AND LOWER(COALESCE(mi.status,''))<>'cancelled'),0) received_qty FROM {$p}purchase_order_items poi JOIN {$p}purchase_orders po ON po.id=poi.purchase_order_id LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}warehouses w ON w.id=po.warehouse_id LEFT JOIN {$p}items i ON i.id=poi.item_id LEFT JOIN {$p}units u ON u.id=poi.unit_id WHERE $where ORDER BY po.id DESC,poi.id ASC";if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A)?:[];foreach($rows as &$x){$x['pending_qty']=max(0,round((float)$x['po_qty']-(float)$x['received_qty'],2));}wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_purchase_order_pending_receipt(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;
        $sql="SELECT poi.id po_item_id,po.id purchase_order_id,po.po_no,po.po_date,po.vendor_id,v.vendor_name,po.warehouse_id,w.warehouse_name,poi.item_id,i.item_name,poi.unit_id,u.unit_name,poi.fixed_price,poi.rate,poi.po_qty,COALESCE(i.gst_rate,0) gst_rate,COALESCE((SELECT SUM(mii.qty) FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mii.purchase_order_item_id=poi.id AND LOWER(COALESCE(mi.status,''))<>'cancelled'),0) received_qty FROM {$p}purchase_order_items poi JOIN {$p}purchase_orders po ON po.id=poi.purchase_order_id LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}warehouses w ON w.id=po.warehouse_id LEFT JOIN {$p}items i ON i.id=poi.item_id LEFT JOIN {$p}units u ON u.id=poi.unit_id WHERE LOWER(COALESCE(po.status,''))<>'cancelled' ORDER BY po.po_date,po.id,poi.id";
        $rows=$wpdb->get_results($sql,ARRAY_A)?:[];$out=[];foreach($rows as $x){$x['pending_qty']=max(0,round((float)$x['po_qty']-(float)$x['received_qty'],2));if($x['pending_qty']>0.009)$out[]=$x;}wp_send_json_success(['rows'=>$out]);
    }

    public function ajax_purchase_order_receive_save(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;$poi=(int)($_POST['po_item_id']??0);$qty=round((float)($_POST['qty']??0),2);$date=sanitize_text_field($_POST['inward_date']??'');$inv=sanitize_text_field($_POST['supplier_invoice_no']??'');$invdate=sanitize_text_field($_POST['supplier_invoice_date']??'');if(!$poi||$qty<=0)wp_send_json_error(['message'=>'Enter Received Qty']);if(!$date)$date=current_time('Y-m-d');
        $x=$wpdb->get_row($wpdb->prepare("SELECT poi.*,po.vendor_id,po.warehouse_id,po.id po_id,po.status,i.gst_rate FROM {$p}purchase_order_items poi JOIN {$p}purchase_orders po ON po.id=poi.purchase_order_id LEFT JOIN {$p}items i ON i.id=poi.item_id WHERE poi.id=%d",$poi),ARRAY_A);if(!$x||$x['status']==='cancelled')wp_send_json_error(['message'=>'PO Item not available']);$received=$this->po_item_received_qty($poi);$pending=max(0,round((float)$x['po_qty']-$received,2));if($qty>$pending+0.009)wp_send_json_error(['message'=>'Received Qty cannot exceed Pending Receipt Qty']);
        $no=$this->next_no('material_inwards','MIN');$now=current_time('mysql');$uid=get_current_user_id();$taxable=round($qty*(float)$x['rate'],2);$gst=(float)$x['gst_rate'];
        $gst_type=(string)$wpdb->get_var($wpdb->prepare("SELECT gst_type FROM {$p}vendors WHERE id=%d",(int)$x['vendor_id']));
        if(!in_array($gst_type,['intra_state','inter_state','no_gst'],true))$gst_type='no_gst';
        $cgst=$sgst=$igst=0.0;
        if($gst_type==='intra_state'){$cgst=round($taxable*$gst/200,2);$sgst=$cgst;}
        elseif($gst_type==='inter_state'){$igst=round($taxable*$gst/100,2);}
        else{$gst=0.0;}
        $total=round($taxable+$cgst+$sgst+$igst,2);
        $ok=$wpdb->insert($p.'material_inwards',['inward_no'=>$no,'warehouse_id'=>(int)$x['warehouse_id'],'vendor_id'=>(int)$x['vendor_id'],'purchase_order_id'=>(int)$x['po_id'],'inward_date'=>$date,'supplier_invoice_no'=>$inv?:null,'supplier_invoice_date'=>$invdate?:null,'taxable_amount'=>$taxable,'cgst_amount'=>$cgst,'sgst_amount'=>$sgst,'igst_amount'=>$igst,'total_amount'=>$total,'other_expenses'=>0,'other_expense_gst'=>0,'round_off'=>0,'status'=>'pending_approval','created_by'=>$uid,'created_at'=>$now,'updated_at'=>$now]);if(!$ok)wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to create Purchase Receipt']);$inward=(int)$wpdb->insert_id;
        $ok=$wpdb->insert($p.'material_inward_items',['inward_id'=>$inward,'purchase_order_item_id'=>$poi,'item_id'=>(int)$x['item_id'],'qty'=>$qty,'approved_qty'=>0,'unit_id'=>(int)$x['unit_id'],'item_price'=>(float)$x['fixed_price'],'rate'=>(float)$x['rate'],'taxable_amount'=>$taxable,'gst_rate'=>$gst,'cgst_amount'=>$cgst,'sgst_amount'=>$sgst,'igst_amount'=>$igst,'total_amount'=>$total]);if(!$ok){$wpdb->delete($p.'material_inwards',['id'=>$inward]);wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to save Purchase Receipt Item']);}
        wp_send_json_success(['message'=>'Receipt created and sent to Purchase Pending Approval','inward_id'=>$inward]);
    }

    public function ajax_purchase_order_get(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;$id=(int)($_POST['id']??0);if(!$id)wp_send_json_error(['message'=>'PO not found']);
        $head=$wpdb->get_row($wpdb->prepare("SELECT po.*,v.vendor_name,w.warehouse_name,u.name created_by_name FROM {$p}purchase_orders po LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}warehouses w ON w.id=po.warehouse_id LEFT JOIN {$p}users u ON u.id=po.created_by WHERE po.id=%d",$id),ARRAY_A);if(!$head)wp_send_json_error(['message'=>'PO not found']);
        $items=$wpdb->get_results($wpdb->prepare("SELECT poi.*,i.item_name,u.unit_name,COALESCE((SELECT SUM(mii.qty) FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mii.purchase_order_item_id=poi.id AND LOWER(COALESCE(mi.status,''))<>'cancelled'),0) received_qty FROM {$p}purchase_order_items poi LEFT JOIN {$p}items i ON i.id=poi.item_id LEFT JOIN {$p}units u ON u.id=poi.unit_id WHERE poi.purchase_order_id=%d ORDER BY poi.id",$id),ARRAY_A)?:[];
        $received=0.0;foreach($items as &$it){$received+=(float)$it['received_qty'];$it['pending_qty']=max(0,round((float)$it['po_qty']-(float)$it['received_qty'],2));}unset($it);$head['received_qty']=$received;
        wp_send_json_success(['head'=>$head,'items'=>$items]);
    }

    public function ajax_purchase_order_update(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$p=$this->prefix;$id=(int)($_POST['id']??0);$data=json_decode(stripslashes($_POST['data']??'{}'),true);$items=json_decode(stripslashes($_POST['items']??'[]'),true);if(!$id||!is_array($data)||!is_array($items))wp_send_json_error(['message'=>'Invalid PO data']);
        $po=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}purchase_orders WHERE id=%d",$id),ARRAY_A);if(!$po)wp_send_json_error(['message'=>'PO not found']);if($po['status']==='cancelled')wp_send_json_error(['message'=>'Cancelled PO cannot be edited']);
        $receivedTotal=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(mii.qty),0) FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mi.purchase_order_id=%d AND LOWER(COALESCE(mi.status,''))<>'cancelled'",$id));
        $vendor=(int)($data['vendor_id']??$po['vendor_id']);$warehouse=(int)($data['warehouse_id']??$po['warehouse_id']);if($receivedTotal>0.009){$vendor=(int)$po['vendor_id'];$warehouse=(int)$po['warehouse_id'];}
        $upd=['po_date'=>sanitize_text_field($data['po_date']??$po['po_date']),'vendor_id'=>$vendor,'warehouse_id'=>$warehouse,'required_date'=>sanitize_text_field($data['required_date']??'')?:null,'remarks'=>sanitize_textarea_field($data['remarks']??''),'updated_at'=>current_time('mysql')];
        // DDL auto-commits; migrate the legacy unique index BEFORE starting a transaction.
        $indexes=$wpdb->get_results("SHOW INDEX FROM `{$it}` WHERE Key_name='boq_item'",ARRAY_A);
        if($indexes && count($indexes)===2 && ($indexes[0]['Non_unique']??0)==0) {
            $wpdb->query("ALTER TABLE `{$it}` DROP INDEX `boq_item`, ADD KEY `boq_item` (`boq_id`,`item_id`)");
        }
        $wpdb->query('START TRANSACTION');$ok=$wpdb->update($p.'purchase_orders',$upd,['id'=>$id]);if($ok===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to update PO']);}
        foreach($items as $it){$iid=(int)($it['id']??0);if(!$iid)continue;$db=$wpdb->get_row($wpdb->prepare("SELECT id,po_qty FROM {$p}purchase_order_items WHERE id=%d AND purchase_order_id=%d",$iid,$id),ARRAY_A);if(!$db)continue;$received=$this->po_item_received_qty($iid);$qty=round((float)($it['po_qty']??$db['po_qty']),2);$rate=round((float)($it['rate']??0),2);if($qty+0.009<$received){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'PO Qty cannot be less than already Received Qty']);}if($qty<0||$rate<0){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'PO Qty and Rate cannot be negative']);}$ok=$wpdb->update($p.'purchase_order_items',['po_qty'=>$qty,'rate'=>$rate],['id'=>$iid,'purchase_order_id'=>$id]);if($ok===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to update PO Item']);}}
        $wpdb->query('COMMIT');$this->refresh_purchase_order_status($id);wp_send_json_success(['message'=>'PO updated successfully']);
    }

    public function ajax_purchase_order_pdf(){
        $this->verify_ajax();$id=(int)($_POST['id']??0);if(!$id)wp_send_json_error(['message'=>'Invalid PO']);$url=$this->generate_purchase_order_pdf($id);if(!$url)wp_send_json_error(['message'=>'Unable to generate PO PDF']);$url=add_query_arg(['bsm_pdf'=>self::VERSION,'t'=>time()],$url);wp_send_json_success(['url'=>$url]);
    }


    private function bsm_pdf_theme_rgb($company){
        $hex=trim((string)($company['pdf_theme_color']??''));
        if(!preg_match('/^#?([0-9a-fA-F]{6})$/',$hex,$m)) return [0.02,0.25,0.45];
        $h=$m[1];
        return [hexdec(substr($h,0,2))/255,hexdec(substr($h,2,2))/255,hexdec(substr($h,4,2))/255];
    }

    private function bsm_pdf_image_jpeg($source){
        $source=(string)$source;if($source==='')return ['',0,0];
        $raw='';$mime='';
        if(strpos($source,'data:image/')===0 && strpos($source,',')!==false){
            $head=substr($source,0,strpos($source,','));
            if(preg_match('#^data:([^;]+);base64$#i',$head,$m))$mime=strtolower($m[1]);
            $raw=base64_decode(substr($source,strpos($source,',')+1),true)?:'';
        }elseif(preg_match('#^https?://#i',$source) && function_exists('wp_remote_get')){
            $resp=wp_remote_get($source,['timeout'=>8]);
            if(!is_wp_error($resp) && wp_remote_retrieve_response_code($resp)===200){
                $raw=(string)wp_remote_retrieve_body($resp);
                $mime=strtolower((string)wp_remote_retrieve_header($resp,'content-type'));
            }
        }
        if($raw==='')return ['',0,0];
        $info=@getimagesizefromstring($raw);$w=$info?(int)$info[0]:0;$h=$info?(int)$info[1]:0;
        if($info && !empty($info['mime']))$mime=strtolower((string)$info['mime']);
        if($mime==='image/jpeg' && $w>0 && $h>0)return [$raw,$w,$h];
        if(function_exists('imagecreatefromstring')){
            $im=@imagecreatefromstring($raw);
            if($im){
                $w=imagesx($im);$h=imagesy($im);$bg=imagecreatetruecolor($w,$h);$white=imagecolorallocate($bg,255,255,255);
                imagefill($bg,0,0,$white);imagecopy($bg,$im,0,0,0,0,$w,$h);ob_start();imagejpeg($bg,null,92);$jpg=(string)ob_get_clean();
                imagedestroy($bg);imagedestroy($im);return [$jpg,$w,$h];
            }
        }
        return ['',0,0];
    }

    private function generate_purchase_order_pdf($id){
        global $wpdb;$p=$this->prefix;
        $h=$wpdb->get_row($wpdb->prepare("SELECT po.*,v.vendor_name,v.mobile vendor_mobile,v.address vendor_address,v.gstin vendor_gstin,w.warehouse_name FROM {$p}purchase_orders po LEFT JOIN {$p}vendors v ON v.id=po.vendor_id LEFT JOIN {$p}warehouses w ON w.id=po.warehouse_id WHERE po.id=%d",$id),ARRAY_A);if(!$h)return '';
        $rows=$wpdb->get_results($wpdb->prepare("SELECT poi.*,i.item_name,i.hsn_code,u.unit_name FROM {$p}purchase_order_items poi LEFT JOIN {$p}items i ON i.id=poi.item_id LEFT JOIN {$p}units u ON u.id=poi.unit_id WHERE poi.purchase_order_id=%d ORDER BY poi.id",$id),ARRAY_A)?:[];
        $company=$wpdb->get_row("SELECT * FROM {$p}company_settings ORDER BY id DESC LIMIT 1",ARRAY_A)?:[];
        $theme=$this->bsm_pdf_theme_rgb($company);[$logoJpeg,$logoW,$logoH]=$this->bsm_pdf_image_jpeg($company['logo_url']??'');
        $esc=function($v){$v=(string)$v;$v=function_exists('iconv')?(@iconv('UTF-8','Windows-1252//TRANSLIT',$v)?:$v):$v;return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$v);};
        $txt=function(&$c,$x,$y,$text,$size=9,$bold=false,$color=[0.08,0.15,0.27])use($esc){$font=$bold?'F2':'F1';$c.=sprintf("%.3f %.3f %.3f rg\n",$color[0],$color[1],$color[2]);$c.="BT /$font $size Tf 1 0 0 1 $x $y Tm (".$esc($text).") Tj ET\n";};
        $rect=function(&$c,$x,$y,$w,$hh,$fill=null,$stroke=[0.72,0.82,0.92],$lw=.5){if($fill)$c.=sprintf("%.3f %.3f %.3f rg\n",$fill[0],$fill[1],$fill[2]);$c.=sprintf("%.3f %.3f %.3f RG\n",$stroke[0],$stroke[1],$stroke[2]);$c.="$lw w\n$x $y $w $hh re ".($fill?'B':'S')."\n";};
        $line=function(&$c,$x1,$y1,$x2,$y2,$color=[0.72,0.82,0.92],$lw=.5){$c.=sprintf("%.3f %.3f %.3f RG\n",$color[0],$color[1],$color[2]);$c.="$lw w\n$x1 $y1 m $x2 $y2 l S\n";};
        $wrap=function($text,$max){$text=preg_replace('/\s+/',' ',trim((string)$text));if($text==='')return [''];$out=[];$ln='';foreach(explode(' ',$text) as $w){$t=$ln===''?$w:$ln.' '.$w;if(strlen($t)>$max&&$ln!==''){$out[]=$ln;$ln=$w;}else{$ln=$t;}}if($ln!=='')$out[]=$ln;return $out;};
        $c='';$companyName=trim((string)($company['company_name']??''))?:'BizSkill';
        $txt($c,36,806,$companyName,18,true,$theme);
        $cy=788;
        foreach(array_filter([trim((string)($company['address']??'')),(!empty($company['gstin'])?'GSTIN: '.$company['gstin']:''),implode(' | ',array_filter([(string)($company['phone']??''),(string)($company['email']??'')]))]) as $ln){$txt($c,36,$cy,$ln,7,false);$cy-=11;}
        if($logoJpeg&&$logoW&&$logoH){$scale=min(64/$logoW,54/$logoH);$lw=$logoW*$scale;$lh=$logoH*$scale;$c.=sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im1 Do Q\n",$lw,$lh,559-$lw,780);}
        $rect($c,36,724,523,36,$theme,$theme,0);$txt($c,220,736,'PURCHASE ORDER',17,true,[1,1,1]);

        // PO details use the full printable width; Terms & Conditions are not shown on PO PDFs.
        $rect($c,36,616,523,96,[0.985,0.992,1]);
        $meta=[['PO No.',$h['po_no']??'-'],['Date',!empty($h['po_date'])?date('d/m/Y',strtotime($h['po_date'])):'-'],['Supplier',$h['vendor_name']??'-'],['Mobile',$h['vendor_mobile']??'-'],['Address',$h['vendor_address']??'-'],['GST No.',$h['vendor_gstin']??'-'],['Deliver At',$h['warehouse_name']??'-']];
        $y=696;foreach($meta as $i=>$mv){$txt($c,47,$y,$mv[0],7,true);$txt($c,105,$y,(string)$mv[1],7,false);$y-=12;}
        $top=596;$headers=['Sl','Item','HSN','Qty','Unit','Fixed','Rate','Amount'];$cols=[28,150,48,48,48,60,58,83];$tx=36;
        $rect($c,36,$top-24,523,24,$theme,$theme,0);
        foreach($headers as $i=>$hh){$txt($c,$tx+4,$top-16,$hh,6.6,true,[1,1,1]);$tx+=$cols[$i];}
        $y=$top-24;$grand=0;
        foreach(array_slice($rows,0,15) as $i=>$r){$y-=28;$rect($c,36,$y,523,28,[1,1,1]);$amount=(float)$r['po_qty']*(float)$r['rate'];$grand+=$amount;
            $vals=[strval($i+1),(string)($r['item_name']??''),(string)($r['hsn_code']??''),number_format((float)$r['po_qty'],2),(string)($r['unit_name']??''),number_format((float)$r['fixed_price'],2),number_format((float)$r['rate'],2),number_format($amount,2)];
            $tx=36;foreach($vals as $j=>$v){$txt($c,$tx+4,$y+10,$v,6.5,$j===1);$tx+=$cols[$j];}
        }
        if(!$rows){$y-=28;$rect($c,36,$y,523,28,[1,1,1]);$txt($c,46,$y+10,'No PO items found.',7);}
        $boxY=max(112,$y-94);$rect($c,372,$boxY,187,70,[0.965,0.98,0.995]);$txt($c,386,$boxY+50,'Sub Total',7,true);$txt($c,486,$boxY+50,number_format($grand,2),7,true);
        $line($c,382,$boxY+40,548,$boxY+40);$txt($c,386,$boxY+24,'Total Amount',8,true,$theme);$txt($c,486,$boxY+24,number_format($grand,2),8,true,$theme);
        $txt($c,36,$boxY+50,'Authorized Signatory',7,true,$theme);$line($c,36,$boxY+12,180,$boxY+12,$theme,.6);
        $txt($c,36,34,'For '.$companyName,7,true,$theme);

        $resources="/Font << /F1 5 0 R /F2 6 0 R >>";$objs=[];
        if($logoJpeg&&$logoW&&$logoH)$resources.=" /XObject << /Im1 7 0 R >>";
        $objs=["<< /Type /Catalog /Pages 2 0 R >>","<< /Type /Pages /Kids [3 0 R] /Count 1 >>","<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << $resources >> /Contents 4 0 R >>","<< /Length ".strlen($c)." >>\nstream\n$c\nendstream","<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>","<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"];
        if($logoJpeg&&$logoW&&$logoH)$objs[]="<< /Type /XObject /Subtype /Image /Width $logoW /Height $logoH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($logoJpeg)." >>\nstream\n".$logoJpeg."\nendstream";
        $pdf="%PDF-1.4\n";$offs=[0];foreach($objs as $ix=>$o){$offs[]=strlen($pdf);$pdf.=($ix+1)." 0 obj\n$o\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 ".(count($objs)+1)."\n0000000000 65535 f \n";for($i=1;$i<=count($objs);$i++)$pdf.=sprintf("%010d 00000 n \n",$offs[$i]);$pdf.="trailer\n<< /Size ".(count($objs)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        $up=wp_upload_dir();if(!empty($up['error']))return '';$dir=trailingslashit($up['basedir']).'bizskill-purchase-orders';if(!is_dir($dir)&&!wp_mkdir_p($dir))return '';
        $vendorFile=preg_replace('/[\\\/:*?"<>|]+/','-',trim((string)($h['vendor_name']??'Supplier')));$poFile=preg_replace('/[^A-Za-z0-9_-]+/','',(string)($h['po_no']?:$id));$fn=trim($vendorFile).' - '.$poFile.'.pdf';$path=trailingslashit($dir).$fn;if(@file_put_contents($path,$pdf,LOCK_EX)===false)return '';return trailingslashit($up['baseurl']).'bizskill-purchase-orders/'.rawurlencode($fn);
    }

    public function ajax_purchase_order_cancel(){
        $this->verify_ajax();$this->migrate_purchase_orders();global $wpdb;$id=(int)($_POST['id']??0);if(!$id)wp_send_json_error(['message'=>'PO not found']);$status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$this->prefix}purchase_orders WHERE id=%d",$id));if(!$status)wp_send_json_error(['message'=>'PO not found']);if(in_array($status,['fully_received','cancelled'],true))wp_send_json_error(['message'=>'This PO cannot be cancelled']);$received=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(mii.qty),0) FROM {$this->prefix}material_inward_items mii JOIN {$this->prefix}material_inwards mi ON mi.id=mii.inward_id WHERE mi.purchase_order_id=%d AND LOWER(COALESCE(mi.status,''))<>'cancelled'",$id));if($received>0.009)wp_send_json_error(['message'=>'PO cannot be cancelled after receipt has started']);$wpdb->update($this->prefix.'purchase_orders',['status'=>'cancelled','updated_at'=>current_time('mysql')],['id'=>$id]);wp_send_json_success(['message'=>'PO cancelled']);
    }

    private function migrate_quantity_precision() {
        global $wpdb;
        $p = $this->prefix;
        // Quantities are limited to two decimal places throughout the application.
        // Existing 3-decimal values are rounded before the column precision is reduced.
        $columns = [
            'items' => ['opening_qty','minimum_stock'],
            'site_opening_stock' => ['opening_qty'],
            'material_requisition_items' => ['requested_qty','approved_qty','rejected_qty'],
            'material_dispatch_items' => ['qty'],
            'site_receipt_items' => ['dispatch_qty','received_qty','damaged_qty','short_qty'],
            'site_consumption_items' => ['qty'],
            'material_return_items' => ['qty'],
            'stock_ledger' => ['qty_in','qty_out'],
            'gst_invoice_items' => ['qty'],
        ];
        foreach ($columns as $suffix => $fields) {
            $table = $p . $suffix;
            if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table))) continue;
            foreach ($fields as $field) {
                $col = $wpdb->get_row("SHOW COLUMNS FROM `{$table}` LIKE '{$field}'", ARRAY_A);
                if (!$col) continue;
                $wpdb->query("UPDATE `{$table}` SET `{$field}` = ROUND(COALESCE(`{$field}`,0),2)");
                $wpdb->query("ALTER TABLE `{$table}` MODIFY `{$field}` DECIMAL(18,2) DEFAULT 0");
            }
        }
    }

    private function migrate_vendor_payments_fund_type() {
        global $wpdb;
        $table = $this->prefix . 'vendor_payments';
        if (!$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table))) return;
        $col = $wpdb->get_row("SHOW COLUMNS FROM `{$table}` LIKE 'payment_type'", ARRAY_A);
        if (!$col) $wpdb->query("ALTER TABLE `{$table}` ADD `payment_type` VARCHAR(30) NOT NULL DEFAULT 'head_office_vendor' AFTER `payment_no`");
        // Site petty-cash transfers do not have a vendor, therefore vendor_id must be nullable.
        $wpdb->query("ALTER TABLE `{$table}` MODIFY `vendor_id` BIGINT UNSIGNED NULL");
        // Existing payments remain vendor payments unless explicitly changed.
        $wpdb->query("UPDATE `{$table}` SET payment_type='head_office_vendor' WHERE payment_type IS NULL OR payment_type=''");
    }

    private function migrate_employee_allowance() {
        global $wpdb;
        $vp=$this->prefix.'vendor_payments';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$vp))){
            $cols=[
                'employee_id'=>"BIGINT UNSIGNED NULL AFTER `site_id`",
                'allowance_id'=>"BIGINT UNSIGNED NULL AFTER `employee_id`",
                'allowance_type_id'=>"BIGINT UNSIGNED NULL AFTER `allowance_id`",
            ];
            foreach($cols as $col=>$ddl){ if(!$wpdb->get_var("SHOW COLUMNS FROM `{$vp}` LIKE '{$col}'")) $wpdb->query("ALTER TABLE `{$vp}` ADD `{$col}` {$ddl}"); }
        }
        $ea=$this->prefix.'employee_allowances';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$ea))){
            $cols=[
                'site_id'=>"BIGINT UNSIGNED NULL AFTER `employee_id`",
                'customer_id'=>"BIGINT UNSIGNED NULL AFTER `site_id`",
                'document_url'=>"TEXT NULL AFTER `purpose`",
                'document_name'=>"VARCHAR(255) NULL AFTER `document_url`",
            ];
            foreach($cols as $col=>$ddl){ if(!$wpdb->get_var("SHOW COLUMNS FROM `{$ea}` LIKE '{$col}'")) $wpdb->query("ALTER TABLE `{$ea}` ADD `{$col}` {$ddl}"); }
        }
        $at=$this->prefix.'allowance_types';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$at)) && !(int)$wpdb->get_var("SELECT COUNT(*) FROM `{$at}`")){
            $now=current_time('mysql');
            foreach(['Travel Allowance','Food Allowance','Mobile Allowance','Conveyance','Accommodation','Other Allowance'] as $name){$wpdb->insert($at,['allowance_type_name'=>$name,'status'=>'active','created_at'=>$now,'updated_at'=>$now]);}
        }
    }

    private function migrate_salary_system() {
        global $wpdb;
        $vp=$this->prefix.'vendor_payments';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$vp))){
            if(!$wpdb->get_var("SHOW COLUMNS FROM `{$vp}` LIKE 'salary_slip_id'")) $wpdb->query("ALTER TABLE `{$vp}` ADD `salary_slip_id` BIGINT UNSIGNED NULL AFTER `allowance_type_id`");
        }
        $ss=$this->prefix.'salary_slips';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$ss))){
            if(!$wpdb->get_var("SHOW COLUMNS FROM `{$ss}` LIKE 'pdf_design_version'")) $wpdb->query("ALTER TABLE `{$ss}` ADD `pdf_design_version` INT NOT NULL DEFAULT 1 AFTER `pdf_url`");
        }
    }

    private function migrate_users_table() {
        global $wpdb;
        $table = $this->prefix . 'users';

        // Older builds/sites may already have a bsm_users table where user_id was
        // created as a numeric column. MySQL then converts usernames such as
        // "manash" to 0, causing: Duplicate entry '0' for key 'user_id'.
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;

        $column = $wpdb->get_row("SHOW COLUMNS FROM `{$table}` LIKE 'user_id'", ARRAY_A);
        if ($column) {
            $type = strtolower((string)($column['Type'] ?? ''));
            if (strpos($type, 'varchar') !== 0) {
                $wpdb->query("ALTER TABLE `{$table}` MODIFY `user_id` VARCHAR(60) NOT NULL");
            }
        }

        // Explicitly repair columns introduced after the original User Master.
        // Some hosts/dbDelta versions do not add a new column reliably when an
        // older custom table has drifted from the current CREATE TABLE schema.
        $required_columns = [
            'designation_id' => "BIGINT UNSIGNED NULL AFTER `role`",
            'designation'    => "VARCHAR(150) NULL AFTER `designation_id`",
            'mobile'         => "VARCHAR(30) NULL AFTER `designation`",
            'assigned_sites' => "LONGTEXT NULL AFTER `mobile`",
            'allowed_views'  => "LONGTEXT NULL AFTER `assigned_sites`",
            'attendance_applicable' => "TINYINT(1) NOT NULL DEFAULT 0 AFTER `allowed_views`",
            'salary'                => "DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `attendance_applicable`",
            'salary_effective_date' => "DATE NULL AFTER `salary`",
            'status'         => "VARCHAR(20) DEFAULT 'active' AFTER `salary_effective_date`",
        ];
        foreach ($required_columns as $name => $definition) {
            $has = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", $name));
            if (!$has) {
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$definition}");
            }
        }

        // Repair legacy blank/zero usernames from the linked WordPress account.
        $rows = $wpdb->get_results("SELECT id, wp_user_id, user_id FROM `{$table}` WHERE user_id='' OR user_id='0' OR user_id IS NULL", ARRAY_A);
        foreach ($rows as $row) {
            $wp_user_id = (int)($row['wp_user_id'] ?? 0);
            if (!$wp_user_id) continue;
            $user = get_userdata($wp_user_id);
            if (!$user || !$user->user_login) continue;

            $login = sanitize_user($user->user_login, true);
            if (!$login) continue;
            $duplicate = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM `{$table}` WHERE user_id=%s AND id<>%d LIMIT 1",
                $login,
                (int)$row['id']
            ));
            if (!$duplicate) {
                $wpdb->update($table, ['user_id' => $login], ['id' => (int)$row['id']], ['%s'], ['%d']);
            }
        }
    }

    private function migrate_office_attendance_table() {
        global $wpdb;
        $table=$this->prefix.'office_attendance';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table))!==$table) return;
        $required=[
            'image_url'=>"TEXT NULL AFTER `attendance_status`",
            'image_name'=>"VARCHAR(255) NULL AFTER `image_url`",
        ];
        foreach($required as $name=>$ddl){
            if(!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",$name))){
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$ddl}");
            }
        }
    }

    private function migrate_phase_0122() {
        global $wpdb;
        // Seed Designation/State masters from legacy text values and link existing rows.
        $dtable=$this->prefix.'designations'; $stable=$this->prefix.'states';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$dtable))===$dtable){
            $vals=$wpdb->get_col("SELECT DISTINCT designation FROM {$this->prefix}users WHERE designation IS NOT NULL AND designation<>''");
            foreach($vals as $v){$v=trim((string)$v); if(!$v)continue; $id=$wpdb->get_var($wpdb->prepare("SELECT id FROM $dtable WHERE designation_name=%s",$v)); if(!$id){$wpdb->insert($dtable,['designation_name'=>$v,'status'=>'active','created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);$id=$wpdb->insert_id;} $wpdb->query($wpdb->prepare("UPDATE {$this->prefix}users SET designation_id=%d WHERE (designation_id IS NULL OR designation_id=0) AND designation=%s",$id,$v));}
        }
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$stable))===$stable){
            foreach(['company_settings','customers','vendors','labour_contractors'] as $tbl){
                $full=$this->prefix.$tbl;
                $has=$wpdb->get_var("SHOW COLUMNS FROM $full LIKE 'state_id'"); if(!$has) continue;
                $vals=$wpdb->get_col("SELECT DISTINCT state FROM $full WHERE state IS NOT NULL AND state<>''");
                foreach($vals as $v){$v=trim((string)$v);if(!$v)continue;$sid=$wpdb->get_var($wpdb->prepare("SELECT id FROM $stable WHERE state_name=%s",$v));if(!$sid){$wpdb->insert($stable,['state_name'=>$v,'status'=>'active','created_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')]);$sid=$wpdb->insert_id;}$wpdb->query($wpdb->prepare("UPDATE $full SET state_id=%d WHERE (state_id IS NULL OR state_id=0) AND state=%s",$sid,$v));}
            }
        }
    }

    private function migrate_requisition_table() {
        global $wpdb;
        $table = $this->prefix . 'material_requisitions';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;

        // Explicit schema repair for sites upgraded from builds created before
        // approval remarks were introduced. Some hosts/dbDelta combinations do
        // not reliably add columns to older custom tables.
        $required_columns = [
            'approval_remarks' => "TEXT NULL AFTER `remarks`",
            'approved_by'      => "BIGINT UNSIGNED NULL AFTER `status`",
            'approved_at'      => "DATETIME NULL AFTER `approved_by`",
        ];
        foreach ($required_columns as $name => $definition) {
            $has = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", $name));
            if (!$has) {
                $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$definition}");
            }
        }

        // Item-wise approval stores the unapproved balance explicitly as rejected quantity.
        $item_table = $this->prefix . 'material_requisition_items';
        $item_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $item_table));
        if ($item_exists === $item_table) {
            $has_rejected = $wpdb->get_var("SHOW COLUMNS FROM `{$item_table}` LIKE 'rejected_qty'");
            if (!$has_rejected) {
                $wpdb->query("ALTER TABLE `{$item_table}` ADD COLUMN `rejected_qty` DECIMAL(18,2) DEFAULT 0 AFTER `approved_qty`");
            }
        }
    }

    private function migrate_material_returns_table() {
        global $wpdb;
        $table = $this->prefix . 'material_returns';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;
        $has = $wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'return_by'");
        if (!$has) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `return_by` BIGINT UNSIGNED NULL AFTER `return_date`");
        }
        $item_table = $this->prefix . 'material_return_items';
        $item_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $item_table));
        if ($item_exists === $item_table) {
            $has_rate = $wpdb->get_var("SHOW COLUMNS FROM `{$item_table}` LIKE 'rate'");
            if (!$has_rate) $wpdb->query("ALTER TABLE `{$item_table}` ADD COLUMN `rate` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `unit_id`");
        }
    }

    // Existing customers have no classification until it is explicitly selected.
    private function migrate_customer_gst_type() {
        global $wpdb;
        $table = $this->prefix . 'customers';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) !== $table) return;
        if (!$wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'gst_type'")) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `gst_type` VARCHAR(30) NULL DEFAULT NULL AFTER `gstin`");
        }
    }

    private function migrate_vendor_gst_type() {
        global $wpdb;
        $table = $this->prefix . 'vendors';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;
        $has = $wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'gst_type'");
        if (!$has) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `gst_type` VARCHAR(30) NOT NULL DEFAULT 'no_gst' AFTER `gstin`");
        }
        $wpdb->query("UPDATE `{$table}` SET gst_type='no_gst' WHERE gst_type IS NULL OR gst_type=''");
    }


    private function migrate_material_inward_type() {
        global $wpdb;
        $table = $this->prefix . 'material_inwards';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;
        $cols = [
            'inward_type' => "VARCHAR(20) NOT NULL DEFAULT 'purchase' AFTER `id`",
            'return_id'   => "BIGINT UNSIGNED NULL AFTER `inward_no`",
            'site_id'     => "BIGINT UNSIGNED NULL AFTER `return_id`",
            'received_by' => "BIGINT UNSIGNED NULL AFTER `site_id`",
        ];
        foreach ($cols as $name=>$def) {
            $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",$name));
            if(!$has) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$def}");
        }
        $wpdb->query("UPDATE `{$table}` SET inward_type='purchase' WHERE inward_type IS NULL OR inward_type=''");
        $child = $this->prefix . 'material_inward_items';
        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $child)) === $child) {
            $child_cols = [
                'return_qty' => "DECIMAL(18,2) DEFAULT 0 AFTER `item_id`",
                'remarks'    => "VARCHAR(255) NULL AFTER `rate`",
            ];
            foreach ($child_cols as $name=>$def) {
                $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$child}` LIKE %s",$name));
                if(!$has) $wpdb->query("ALTER TABLE `{$child}` ADD COLUMN `{$name}` {$def}");
            }
        }
    }

    private function migrate_purchase_invoice_extras() {
        global $wpdb;
        $table = $this->prefix . 'material_inwards';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists === $table) {
            $cols = [
                'cgst_amount'      => "DECIMAL(18,2) DEFAULT 0 AFTER `taxable_amount`",
                'sgst_amount'      => "DECIMAL(18,2) DEFAULT 0 AFTER `cgst_amount`",
                'igst_amount'      => "DECIMAL(18,2) DEFAULT 0 AFTER `sgst_amount`",
                'other_expenses'   => "DECIMAL(18,2) DEFAULT 0 AFTER `igst_amount`",
                'other_expense_gst'=> "DECIMAL(18,2) DEFAULT 0 AFTER `other_expenses`",
                'round_off'        => "DECIMAL(18,2) DEFAULT 0 AFTER `other_expense_gst`",
                'bill_type'        => "VARCHAR(20) NULL DEFAULT NULL AFTER `supplier_invoice_date`",
            ];
            foreach ($cols as $name=>$def) {
                $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",$name));
                if(!$has) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$def}");
            }
        }

        // Older installations can have material_inward_items without the GST breakup columns.
        $child=$this->prefix.'material_inward_items';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$child))===$child){
            $child_cols=[
                'item_price'     => "DECIMAL(18,2) DEFAULT 0 AFTER `unit_id`",
                'taxable_amount' => "DECIMAL(18,2) DEFAULT 0 AFTER `remarks`",
                'gst_rate'       => "DECIMAL(8,2) DEFAULT 0 AFTER `taxable_amount`",
                'cgst_amount'    => "DECIMAL(18,2) DEFAULT 0 AFTER `gst_rate`",
                'sgst_amount'    => "DECIMAL(18,2) DEFAULT 0 AFTER `cgst_amount`",
                'igst_amount'    => "DECIMAL(18,2) DEFAULT 0 AFTER `sgst_amount`",
                'total_amount'   => "DECIMAL(18,2) DEFAULT 0 AFTER `igst_amount`",
            ];
            foreach($child_cols as $name=>$def){
                $has=$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$child}` LIKE %s",$name));
                if(!$has) $wpdb->query("ALTER TABLE `{$child}` ADD COLUMN `{$name}` {$def}");
            }
        }
    }


    private function migrate_material_dispatch_courier() {
        global $wpdb;
        $table = $this->prefix . 'material_dispatches';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;
        $cols = [
            'from_site_id' => "BIGINT UNSIGNED NULL",
            'dispatch_from' => "VARCHAR(20) NOT NULL DEFAULT 'warehouse'",
            'courier_no'   => "VARCHAR(100) NULL AFTER `driver_mobile`",
            'courier_name' => "VARCHAR(190) NULL AFTER `courier_no`",
        ];
        foreach ($cols as $name => $def) {
            $has = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", $name));
            if (!$has) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$name}` {$def}");
        }
    }


    private function migrate_contractor_advance() {
        global $wpdb; $p=$this->prefix;
        $payments=$p.'contractor_payments';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$payments))===$payments){
            if(!$wpdb->get_var("SHOW COLUMNS FROM `{$payments}` LIKE 'payment_type'")) $wpdb->query("ALTER TABLE `{$payments}` ADD COLUMN `payment_type` VARCHAR(30) NOT NULL DEFAULT 'bill_payment' AFTER `payment_no`");
            $wpdb->query("UPDATE `{$payments}` SET payment_type='bill_payment' WHERE payment_type IS NULL OR payment_type=''");
        }
        $bills=$p.'contractor_bills';
        if($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$bills))===$bills && !$wpdb->get_var("SHOW COLUMNS FROM `{$bills}` LIKE 'advance_adjustment'")) $wpdb->query("ALTER TABLE `{$bills}` ADD COLUMN `advance_adjustment` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `total_amount`");
        $table=$p.'contractor_advance_adjustments';
        $charset=$wpdb->get_charset_collate();
        require_once ABSPATH.'wp-admin/includes/upgrade.php';
        dbDelta("CREATE TABLE {$table} (id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, advance_payment_id BIGINT UNSIGNED NOT NULL, contractor_bill_id BIGINT UNSIGNED NOT NULL, amount DECIMAL(18,2) DEFAULT 0, created_at DATETIME NULL, PRIMARY KEY(id), KEY advance_payment_id(advance_payment_id), KEY contractor_bill_id(contractor_bill_id)) {$charset};");
    }

    private function contractor_available_advance($contractor_id,$site_id=0,$exclude_bill_id=0){
        global $wpdb; $p=$this->prefix;
        $contractor_id=(int)$contractor_id;$site_id=(int)$site_id;$exclude_bill_id=(int)$exclude_bill_id;
        if(!$contractor_id) return 0;
        $advance_where="cp.contractor_id=%d AND COALESCE(cp.payment_type,'bill_payment')='advance' AND LOWER(COALESCE(cp.status,''))<>'cancelled'";
        $adj_where="ap.contractor_id=%d AND COALESCE(ap.payment_type,'bill_payment')='advance' AND LOWER(COALESCE(ap.status,''))<>'cancelled'";
        $args1=[$contractor_id];$args2=[$contractor_id];
        if($site_id){$advance_where.=' AND cp.site_id=%d';$adj_where.=' AND ap.site_id=%d';$args1[]=$site_id;$args2[]=$site_id;}
        if($exclude_bill_id){$adj_where.=' AND caa.contractor_bill_id<>%d';$args2[]=$exclude_bill_id;}
        $advance_sql="SELECT COALESCE(SUM(cp.amount),0) FROM {$p}contractor_payments cp WHERE $advance_where";
        $adjusted_sql="SELECT COALESCE(SUM(caa.amount),0) FROM {$p}contractor_advance_adjustments caa JOIN {$p}contractor_payments ap ON ap.id=caa.advance_payment_id WHERE $adj_where";
        $advance=(float)$wpdb->get_var($wpdb->prepare($advance_sql,$args1));
        $adjusted=(float)$wpdb->get_var($wpdb->prepare($adjusted_sql,$args2));
        return max(0,$advance-$adjusted);
    }

    private function allocate_contractor_advance($bill_id,$contractor_id,$site_id,$requested){
        global $wpdb; $p=$this->prefix;
        $bill_id=(int)$bill_id;$contractor_id=(int)$contractor_id;$site_id=(int)$site_id;$requested=round(max(0,(float)$requested),2);
        $wpdb->delete($p.'contractor_advance_adjustments',['contractor_bill_id'=>$bill_id]);
        if($requested<=0){$paid=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$p}contractor_payments WHERE contractor_bill_id=%d AND COALESCE(payment_type,'bill_payment')='bill_payment' AND status<>'cancelled'",$bill_id));$wpdb->update($p.'contractor_bills',['advance_adjustment'=>0,'status'=>$paid>0?'partly_paid':'approved','updated_at'=>current_time('mysql')],['id'=>$bill_id]);return 0;}
        $sql="SELECT cp.id,cp.amount,COALESCE((SELECT SUM(a.amount) FROM {$p}contractor_advance_adjustments a WHERE a.advance_payment_id=cp.id),0) adjusted FROM {$p}contractor_payments cp WHERE cp.contractor_id=%d AND cp.site_id=%d AND COALESCE(cp.payment_type,'bill_payment')='advance' AND LOWER(COALESCE(cp.status,''))<>'cancelled' ORDER BY cp.payment_date,cp.id";
        $rows=$wpdb->get_results($wpdb->prepare($sql,$contractor_id,$site_id),ARRAY_A);$left=$requested;$used=0;$now=current_time('mysql');
        foreach($rows as $r){if($left<=0.009)break;$avail=max(0,(float)$r['amount']-(float)$r['adjusted']);if($avail<=0)continue;$take=min($left,$avail);$wpdb->insert($p.'contractor_advance_adjustments',['advance_payment_id'=>(int)$r['id'],'contractor_bill_id'=>$bill_id,'amount'=>$take,'created_at'=>$now]);$used+=$take;$left-=$take;}
        $used=round($used,2);
        $bill_total=(float)$wpdb->get_var($wpdb->prepare("SELECT total_amount FROM {$p}contractor_bills WHERE id=%d",$bill_id));
        $status=$used>0?'partly_paid':'approved';
        if($bill_total>0 && $used>=$bill_total-0.009)$status='paid';
        $wpdb->update($p.'contractor_bills',['advance_adjustment'=>$used,'status'=>$status,'updated_at'=>$now],['id'=>$bill_id]);
        return $used;
    }

    public function ajax_contractor_advance_available(){
        $this->verify_ajax();
        $contractor_id=(int)($_POST['contractor_id']??0);$site_id=(int)($_POST['site_id']??0);$bill_id=(int)($_POST['bill_id']??0);
        wp_send_json_success(['available'=>round($this->contractor_available_advance($contractor_id,$site_id,$bill_id),2)]);
    }

    private function create_app_pages() {
        $pages = $this->app_pages();
        $parent = get_page_by_path(self::PAGE_SLUG);
        if (!$parent) {
            $parent_id = wp_insert_post([
                'post_title' => 'Site Management',
                'post_name' => self::PAGE_SLUG,
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_content' => '[bizskill_site_management]',
            ]);
        } else {
            $parent_id = $parent->ID;
            if (trim((string)$parent->post_content) === '') {
                wp_update_post(['ID' => $parent_id, 'post_content' => '[bizskill_site_management]']);
            }
        }
        if (!$parent_id || is_wp_error($parent_id)) return;

        update_post_meta($parent_id, '_bsm_app_page', '1');
        update_post_meta($parent_id, '_bsm_view_key', 'dashboard');
        update_post_meta($parent_id, '_wp_page_template', 'bsm-blank-template.php');

        foreach ($pages as $key => $def) {
            if ($key === 'dashboard') continue;
            [$title, $slug] = $def;
            $path = self::PAGE_SLUG . '/' . $slug;
            $page = get_page_by_path($path);
            if (!$page) {
                $id = wp_insert_post([
                    'post_title' => $title,
                    'post_name' => $slug,
                    'post_parent' => $parent_id,
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'post_content' => '[bizskill_site_management]',
                ]);
            } else {
                $id = $page->ID;
                $updates = ['ID' => $id];
                $dirty = false;
                if ((int)$page->post_parent !== (int)$parent_id) { $updates['post_parent'] = $parent_id; $dirty = true; }
                if (trim((string)$page->post_content) === '') { $updates['post_content'] = '[bizskill_site_management]'; $dirty = true; }
                if ($dirty) wp_update_post($updates);
            }
            if ($id && !is_wp_error($id)) {
                update_post_meta($id, '_bsm_app_page', '1');
                update_post_meta($id, '_bsm_view_key', $key);
                update_post_meta($id, '_wp_page_template', 'bsm-blank-template.php');
            }
        }
    }

    private function remove_obsolete_app_pages() {
        // Keep historical transaction data intact while cleaning obsolete/renamed front-end pages.
        foreach (['gst-invoices','site-fund-allocation','site-fund-report'] as $slug) {
            $page = get_page_by_path(self::PAGE_SLUG . '/' . $slug);
            if ($page && get_post_meta($page->ID, '_bsm_app_page', true) === '1') wp_trash_post($page->ID);
        }
    }

    public function register_blank_page_template($templates) {
        $templates['bsm-blank-template.php'] = 'Blank Template - Site Management';
        return $templates;
    }

    public function blank_template($template) {
        if (is_page()) {
            global $post;
            if ($post && get_post_meta($post->ID, '_bsm_app_page', true) === '1') {
                // Site Management uses its own full-screen application shell.
                // Hide the WordPress front-end admin toolbar only on these app pages.
                show_admin_bar(false);
                add_filter('show_admin_bar', '__return_false', 999);
                // The app page holds per-user login data and must always be fresh,
                // so stop LiteSpeed / other page caches and the browser from storing it.
                if (!defined('DONOTCACHEPAGE')) define('DONOTCACHEPAGE', true);
                do_action('litespeed_control_set_nocache', 'BizSkill Site Management app page');
                if (!headers_sent()) nocache_headers();
                return plugin_dir_path(__FILE__) . 'templates/app.php';
            }
        }
        return $template;
    }

    private function migrate_dispatch_items_table() {
        global $wpdb;
        $table = $this->prefix . 'material_dispatch_items';
        $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table));
        if ($exists !== $table) return;
        $has = $wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s", 'remarks'));
        if (!$has) {
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `remarks` VARCHAR(255) NULL AFTER `rate`");
        }
    }

    private function create_tables() {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $c = $wpdb->get_charset_collate();
        $p = $this->prefix;
        $sql = [];

        $sql[] = "CREATE TABLE {$p}company_settings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, company_name VARCHAR(190), address TEXT, city VARCHAR(100), state_id BIGINT UNSIGNED NULL, state VARCHAR(100), pin_code VARCHAR(20), gstin VARCHAR(30), gst_type VARCHAR(30) NOT NULL DEFAULT 'no_gst', pan VARCHAR(20), phone VARCHAR(30), email VARCHAR(190), logo_url LONGTEXT, upi_qr_url LONGTEXT, bank_account_holder VARCHAR(190), bank_name VARCHAR(190), bank_branch VARCHAR(190), bank_ifsc VARCHAR(60), bank_account_no VARCHAR(100), pdf_theme_color VARCHAR(20) NULL, financial_year_start DATE NULL, terms_conditions TEXT NULL, updated_at DATETIME NULL, PRIMARY KEY(id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}users (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, wp_user_id BIGINT UNSIGNED NULL, user_id VARCHAR(60) NOT NULL, password_hash VARCHAR(255) NOT NULL, name VARCHAR(190) NOT NULL, email VARCHAR(190), role VARCHAR(80) NOT NULL DEFAULT 'bsm_site_supervisor', designation_id BIGINT UNSIGNED NULL, designation VARCHAR(150), mobile VARCHAR(30), assigned_sites LONGTEXT NULL, allowed_views LONGTEXT NULL, attendance_applicable TINYINT(1) NOT NULL DEFAULT 0, salary DECIMAL(18,2) NOT NULL DEFAULT 0, salary_effective_date DATE NULL, status VARCHAR(20) DEFAULT 'active', created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY user_id(user_id), UNIQUE KEY wp_user_id(wp_user_id), KEY email(email), KEY status(status)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}user_profiles (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, wp_user_id BIGINT UNSIGNED NOT NULL, designation VARCHAR(150), mobile VARCHAR(30), assigned_sites LONGTEXT NULL, allowed_views LONGTEXT NULL, attendance_applicable TINYINT(1) NOT NULL DEFAULT 0, salary DECIMAL(18,2) NOT NULL DEFAULT 0, salary_effective_date DATE NULL, status VARCHAR(20) DEFAULT 'active', created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY wp_user_id (wp_user_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}designations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, designation_name VARCHAR(190) NOT NULL, status VARCHAR(20) DEFAULT 'active', created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY designation_name(designation_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}states (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, state_name VARCHAR(190) NOT NULL, state_code VARCHAR(30), status VARCHAR(20) DEFAULT 'active', created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY state_name(state_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}customers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, customer_name VARCHAR(190) NOT NULL, contact_person VARCHAR(190), mobile VARCHAR(30), email VARCHAR(190), address TEXT, city VARCHAR(100), state_id BIGINT UNSIGNED NULL, state VARCHAR(100), pin_code VARCHAR(20), gstin VARCHAR(30), gst_type VARCHAR(30) NULL DEFAULT NULL, pan VARCHAR(20), status VARCHAR(20) DEFAULT 'active', created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), KEY customer_name(customer_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}sites (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, site_code VARCHAR(50), site_name VARCHAR(190) NOT NULL, customer_id BIGINT UNSIGNED NULL, site_location VARCHAR(255), site_sections TEXT NULL, address TEXT, city VARCHAR(100), state VARCHAR(100), site_manager_user_id BIGINT UNSIGNED NULL, site_supervisor_user_id BIGINT UNSIGNED NULL, start_date DATE NULL, expected_end_date DATE NULL, work_order_value DECIMAL(18,2) DEFAULT 0, status VARCHAR(30) DEFAULT 'active', remarks TEXT, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY site_code(site_code), KEY customer_id(customer_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}vendors (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, vendor_name VARCHAR(190) NOT NULL, contact_person VARCHAR(190), mobile VARCHAR(30), email VARCHAR(190), address TEXT, city VARCHAR(100), state_id BIGINT UNSIGNED NULL, state VARCHAR(100), gstin VARCHAR(30), pan VARCHAR(20), bank_name VARCHAR(150), bank_account VARCHAR(80), ifsc VARCHAR(30), status VARCHAR(20) DEFAULT 'active', created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), KEY vendor_name(vendor_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}labour_contractors (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, contractor_name VARCHAR(190) NOT NULL, contact_person VARCHAR(190), mobile VARCHAR(30), email VARCHAR(190), address TEXT, city VARCHAR(100), state_id BIGINT UNSIGNED NULL, state VARCHAR(100), gstin VARCHAR(30), pan VARCHAR(20), bank_name VARCHAR(150), bank_account VARCHAR(80), ifsc VARCHAR(30), status VARCHAR(20) DEFAULT 'active', created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), KEY contractor_name(contractor_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}labour_attendance (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, attendance_date DATE NOT NULL, site_id BIGINT UNSIGNED NOT NULL, contractor_id BIGINT UNSIGNED NOT NULL, no_of_labour INT UNSIGNED DEFAULT 0, remarks TEXT, created_by BIGINT UNSIGNED, created_at DATETIME, updated_by BIGINT UNSIGNED, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY daily_contractor(attendance_date,site_id,contractor_id), KEY site_date(site_id,attendance_date)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}office_attendance (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, attendance_date DATE NOT NULL, user_id BIGINT UNSIGNED NOT NULL, attendance_status VARCHAR(1) NOT NULL DEFAULT 'P', image_url TEXT NULL, image_name VARCHAR(255) NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY daily_user(attendance_date,user_id), KEY user_date(user_id,attendance_date), KEY attendance_date(attendance_date)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}user_salary_history (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id BIGINT UNSIGNED NOT NULL, monthly_salary DECIMAL(18,2) NOT NULL DEFAULT 0, effective_date DATE NOT NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY user_effective(user_id,effective_date), KEY user_id(user_id), KEY effective_date(effective_date)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}allowance_types (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, allowance_type_name VARCHAR(190) NOT NULL, status VARCHAR(20) DEFAULT 'active', created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY allowance_type_name(allowance_type_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}employee_allowance_limits (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, employee_id BIGINT UNSIGNED NOT NULL, allowance_type_id BIGINT UNSIGNED NOT NULL, limit_amount DECIMAL(18,2) NOT NULL DEFAULT 0, status VARCHAR(20) DEFAULT 'active', created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY employee_allowance(employee_id,allowance_type_id), KEY employee_id(employee_id), KEY allowance_type_id(allowance_type_id), KEY status(status)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}employee_allowances (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, allowance_no VARCHAR(80), employee_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NULL, customer_id BIGINT UNSIGNED NULL, allowance_date DATE NOT NULL, allowance_type_id BIGINT UNSIGNED NOT NULL, amount DECIMAL(18,2) NOT NULL DEFAULT 0, purpose TEXT, document_url TEXT NULL, document_name VARCHAR(255) NULL, status VARCHAR(30) NOT NULL DEFAULT 'submitted', approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, rejected_by BIGINT UNSIGNED NULL, rejected_at DATETIME NULL, payment_id BIGINT UNSIGNED NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY allowance_no(allowance_no), KEY employee_id(employee_id), KEY allowance_type_id(allowance_type_id), KEY status(status), KEY allowance_date(allowance_date)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}salary_slips (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, salary_month VARCHAR(7) NOT NULL, employee_id BIGINT UNSIGNED NOT NULL, employee_code VARCHAR(60), employee_name VARCHAR(190), designation VARCHAR(190), present_days DECIMAL(8,2) DEFAULT 0, basic_salary DECIMAL(18,2) DEFAULT 0, allowance DECIMAL(18,2) DEFAULT 0, overtime DECIMAL(18,2) DEFAULT 0, incentive DECIMAL(18,2) DEFAULT 0, bonus DECIMAL(18,2) DEFAULT 0, other_earnings DECIMAL(18,2) DEFAULT 0, deduction DECIMAL(18,2) DEFAULT 0, advance_deduction DECIMAL(18,2) DEFAULT 0, other_deduction DECIMAL(18,2) DEFAULT 0, gross_salary DECIMAL(18,2) DEFAULT 0, total_deduction DECIMAL(18,2) DEFAULT 0, net_salary_payable DECIMAL(18,2) DEFAULT 0, opening_paid DECIMAL(18,2) DEFAULT 0, net_salary_paid DECIMAL(18,2) DEFAULT 0, balance_payable DECIMAL(18,2) DEFAULT 0, remarks TEXT, status VARCHAR(30) DEFAULT 'pending_payment', pdf_url TEXT NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY employee_month(employee_id,salary_month), KEY salary_month(salary_month), KEY status(status), KEY employee_id(employee_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}item_categories (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, category_name VARCHAR(190) NOT NULL, status VARCHAR(20) DEFAULT 'active', PRIMARY KEY(id), UNIQUE KEY category_name(category_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}units (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, unit_name VARCHAR(100) NOT NULL, status VARCHAR(20) DEFAULT 'active', PRIMARY KEY(id), UNIQUE KEY unit_name(unit_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}gst_rates (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, gst_rate DECIMAL(8,2) NOT NULL DEFAULT 0, status VARCHAR(20) DEFAULT 'active', created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY gst_rate(gst_rate)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, category_id BIGINT UNSIGNED NULL, item_name VARCHAR(190) NOT NULL, unit_id BIGINT UNSIGNED NULL, hsn_code VARCHAR(50), gst_rate DECIMAL(8,2) DEFAULT 0, opening_qty DECIMAL(18,2) DEFAULT 0, opening_rate DECIMAL(18,2) DEFAULT 0, minimum_stock DECIMAL(18,2) DEFAULT 0, status VARCHAR(20) DEFAULT 'active', PRIMARY KEY(id), KEY item_name(item_name), KEY category_id(category_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}warehouses (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, warehouse_name VARCHAR(190) NOT NULL, address TEXT, manager_user_id BIGINT UNSIGNED NULL, status VARCHAR(20) DEFAULT 'active', PRIMARY KEY(id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_opening_stock (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, site_id BIGINT UNSIGNED NOT NULL, opening_date DATE NOT NULL, item_id BIGINT UNSIGNED NOT NULL, unit_id BIGINT UNSIGNED NULL, opening_qty DECIMAL(18,2) DEFAULT 0, opening_rate DECIMAL(18,2) DEFAULT 0, remarks VARCHAR(255) NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY site_item(site_id,item_id), KEY site_id(site_id), KEY item_id(item_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}boqs (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, boq_no VARCHAR(80) NOT NULL, boq_date DATE NOT NULL, site_id BIGINT UNSIGNED NOT NULL, section VARCHAR(190) NOT NULL DEFAULT '', boq_name VARCHAR(190) NOT NULL, remarks TEXT NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY boq_no(boq_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}boq_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, boq_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NOT NULL, section VARCHAR(190) NOT NULL DEFAULT '', unit_id BIGINT UNSIGNED NULL, boq_qty DECIMAL(18,2) NOT NULL DEFAULT 0, PRIMARY KEY(id), UNIQUE KEY boq_item(boq_id,item_id), KEY item_id(item_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_requisitions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, requisition_no VARCHAR(80), site_id BIGINT UNSIGNED NOT NULL, requisition_date DATE NOT NULL, required_date DATE NULL, requested_by BIGINT UNSIGNED, remarks TEXT, approval_remarks TEXT, status VARCHAR(30) DEFAULT 'pending_approval', approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY requisition_no(requisition_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_requisition_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, requisition_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NOT NULL, requested_qty DECIMAL(18,2) DEFAULT 0, approved_qty DECIMAL(18,2) DEFAULT 0, rejected_qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, remarks VARCHAR(255), PRIMARY KEY(id), KEY requisition_id(requisition_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_inwards (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, inward_no VARCHAR(80), site_id BIGINT UNSIGNED NULL, warehouse_id BIGINT UNSIGNED NOT NULL, vendor_id BIGINT UNSIGNED NULL, inward_date DATE NOT NULL, supplier_invoice_no VARCHAR(100), supplier_invoice_date DATE NULL, bill_type VARCHAR(20) NULL DEFAULT NULL, taxable_amount DECIMAL(18,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, other_expenses DECIMAL(18,2) DEFAULT 0, other_expense_gst DECIMAL(18,2) DEFAULT 0, round_off DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, remarks TEXT, status VARCHAR(30) DEFAULT 'draft', created_by BIGINT UNSIGNED, approved_by BIGINT UNSIGNED NULL, approved_at DATETIME NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY inward_no(inward_no), KEY vendor_id(vendor_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_inward_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, inward_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NOT NULL, return_qty DECIMAL(18,2) DEFAULT 0, qty DECIMAL(18,2) DEFAULT 0, approved_qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, item_price DECIMAL(18,2) DEFAULT 0, rate DECIMAL(18,2) DEFAULT 0, remarks VARCHAR(255) NULL, taxable_amount DECIMAL(18,2) DEFAULT 0, gst_rate DECIMAL(8,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, PRIMARY KEY(id), KEY inward_id(inward_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_dispatches (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, dispatch_no VARCHAR(80), dispatch_from VARCHAR(20) NOT NULL DEFAULT 'warehouse', from_site_id BIGINT UNSIGNED NULL, warehouse_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NOT NULL, requisition_id BIGINT UNSIGNED NULL, dispatch_date DATE NOT NULL, vehicle_no VARCHAR(80), driver_name VARCHAR(150), driver_mobile VARCHAR(30), courier_no VARCHAR(100), courier_name VARCHAR(190), transport_details TEXT, remarks TEXT, status VARCHAR(30) DEFAULT 'draft', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY dispatch_no(dispatch_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_dispatch_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, dispatch_id BIGINT UNSIGNED NOT NULL, requisition_item_id BIGINT UNSIGNED NULL, item_id BIGINT UNSIGNED NOT NULL, qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, remarks VARCHAR(255) NULL, PRIMARY KEY(id), KEY dispatch_id(dispatch_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_receipts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, receipt_no VARCHAR(80), dispatch_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NOT NULL, receipt_date DATE NOT NULL, received_by BIGINT UNSIGNED, remarks TEXT, status VARCHAR(30) DEFAULT 'received', created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY receipt_no(receipt_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_receipt_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, receipt_id BIGINT UNSIGNED NOT NULL, dispatch_item_id BIGINT UNSIGNED NULL, item_id BIGINT UNSIGNED NOT NULL, dispatch_qty DECIMAL(18,2) DEFAULT 0, received_qty DECIMAL(18,2) DEFAULT 0, damaged_qty DECIMAL(18,2) DEFAULT 0, short_qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, remarks VARCHAR(255), PRIMARY KEY(id), KEY receipt_id(receipt_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_consumptions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, consumption_no VARCHAR(80), site_id BIGINT UNSIGNED NOT NULL, consumption_date DATE NOT NULL, remarks TEXT, status VARCHAR(30) DEFAULT 'approved', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY consumption_no(consumption_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_consumption_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, consumption_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NOT NULL, qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, remarks VARCHAR(255), document_url TEXT NULL, document_name VARCHAR(255) NULL, PRIMARY KEY(id), KEY consumption_id(consumption_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_returns (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, return_no VARCHAR(80), site_id BIGINT UNSIGNED NOT NULL, warehouse_id BIGINT UNSIGNED NOT NULL, return_date DATE NOT NULL, return_by BIGINT UNSIGNED NULL, reason VARCHAR(255), remarks TEXT, status VARCHAR(30) DEFAULT 'submitted', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY return_no(return_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}material_return_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, return_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NOT NULL, qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, remarks VARCHAR(255), PRIMARY KEY(id), KEY return_id(return_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}stock_ledger (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, transaction_date DATE NOT NULL, location_type VARCHAR(20) NOT NULL, warehouse_id BIGINT UNSIGNED NULL, site_id BIGINT UNSIGNED NULL, item_id BIGINT UNSIGNED NOT NULL, transaction_type VARCHAR(40) NOT NULL, reference_type VARCHAR(40) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, qty_in DECIMAL(18,2) DEFAULT 0, qty_out DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, created_at DATETIME, PRIMARY KEY(id), KEY location_item(location_type,warehouse_id,site_id,item_id), KEY reference(reference_type,reference_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}documents (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, module VARCHAR(60) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, document_type VARCHAR(100), file_url TEXT NOT NULL, original_filename VARCHAR(255), uploaded_by BIGINT UNSIGNED, uploaded_at DATETIME, PRIMARY KEY(id), KEY module_ref(module,reference_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_fund_allocations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, fund_no VARCHAR(80), site_id BIGINT UNSIGNED NOT NULL, fund_date DATE NOT NULL, amount DECIMAL(18,2) DEFAULT 0, payment_mode VARCHAR(50), transaction_reference VARCHAR(150), provided_to_user_id BIGINT UNSIGNED NULL, purpose VARCHAR(255), remarks TEXT, status VARCHAR(30) DEFAULT 'approved', created_by BIGINT UNSIGNED, approved_by BIGINT UNSIGNED NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY fund_no(fund_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}sections (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, section_name VARCHAR(190) NOT NULL, status VARCHAR(20) DEFAULT 'active', PRIMARY KEY(id), UNIQUE KEY section_name(section_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}expense_categories (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, category_name VARCHAR(190) NOT NULL, status VARCHAR(20) DEFAULT 'active', PRIMARY KEY(id), UNIQUE KEY category_name(category_name)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_expenses (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, expense_no VARCHAR(80), site_id BIGINT UNSIGNED NOT NULL, expense_date DATE NOT NULL, category_id BIGINT UNSIGNED NULL, vendor_id BIGINT UNSIGNED NULL, paid_to VARCHAR(190), particulars TEXT, amount DECIMAL(18,2) DEFAULT 0, paid_by VARCHAR(20) DEFAULT 'SITE', payment_mode VARCHAR(50), transaction_reference VARCHAR(150), remarks TEXT, status VARCHAR(30) DEFAULT 'approved', created_by BIGINT UNSIGNED, approved_by BIGINT UNSIGNED NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY expense_no(expense_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}site_fund_ledger (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, site_id BIGINT UNSIGNED NOT NULL, transaction_date DATE NOT NULL, transaction_type VARCHAR(40) NOT NULL, reference_type VARCHAR(40) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, amount_in DECIMAL(18,2) DEFAULT 0, amount_out DECIMAL(18,2) DEFAULT 0, remarks TEXT, created_at DATETIME, PRIMARY KEY(id), KEY site_id(site_id), KEY reference(reference_type,reference_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}bank_accounts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, bank_name VARCHAR(190) NOT NULL, account_holder_name VARCHAR(190) NOT NULL, account_number VARCHAR(100) NOT NULL, ifsc_code VARCHAR(50), branch_name VARCHAR(190), account_type VARCHAR(50), upi_id VARCHAR(190), status VARCHAR(20) DEFAULT 'active', created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), KEY bank_name(bank_name), KEY status(status)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}customer_receipts (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, receipt_no VARCHAR(80), customer_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NOT NULL, gst_invoice_id BIGINT UNSIGNED NULL, receipt_date DATE NOT NULL, amount DECIMAL(18,2) DEFAULT 0, payment_mode VARCHAR(50), bank_account_id BIGINT UNSIGNED NULL, transaction_reference VARCHAR(150), remarks TEXT, status VARCHAR(30) DEFAULT 'received', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY receipt_no(receipt_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}vendor_payments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, payment_no VARCHAR(80), payment_type VARCHAR(30) DEFAULT 'head_office_vendor', vendor_id BIGINT UNSIGNED NULL, material_inward_id BIGINT UNSIGNED NULL, site_id BIGINT UNSIGNED NULL, employee_id BIGINT UNSIGNED NULL, allowance_id BIGINT UNSIGNED NULL, allowance_type_id BIGINT UNSIGNED NULL, salary_slip_id BIGINT UNSIGNED NULL, payment_date DATE NOT NULL, amount DECIMAL(18,2) DEFAULT 0, paid_by VARCHAR(20) DEFAULT 'HEAD_OFFICE', payment_mode VARCHAR(50), bank_account_id BIGINT UNSIGNED NULL, transaction_reference VARCHAR(150), remarks TEXT, status VARCHAR(30) DEFAULT 'paid', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY payment_no(payment_no), KEY vendor_id(vendor_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}contractor_bills (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, bill_no VARCHAR(80), contractor_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NOT NULL, bill_date DATE NOT NULL, from_date DATE NULL, to_date DATE NULL, bill_reference VARCHAR(120), taxable_amount DECIMAL(18,2) DEFAULT 0, gst_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, advance_adjustment DECIMAL(18,2) DEFAULT 0, remarks TEXT, status VARCHAR(30) DEFAULT 'approved', created_by BIGINT UNSIGNED, approved_by BIGINT UNSIGNED NULL, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY bill_no(bill_no), KEY contractor_id(contractor_id), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}contractor_payments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, payment_no VARCHAR(80), payment_type VARCHAR(30) DEFAULT 'bill_payment', contractor_id BIGINT UNSIGNED NOT NULL, contractor_bill_id BIGINT UNSIGNED NULL, site_id BIGINT UNSIGNED NULL, payment_date DATE NOT NULL, amount DECIMAL(18,2) DEFAULT 0, paid_by VARCHAR(20) DEFAULT 'HEAD_OFFICE', payment_mode VARCHAR(50), bank_account_id BIGINT UNSIGNED NULL, transaction_reference VARCHAR(150), remarks TEXT, status VARCHAR(30) DEFAULT 'paid', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY payment_no(payment_no), KEY contractor_id(contractor_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}contractor_advance_adjustments (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, advance_payment_id BIGINT UNSIGNED NOT NULL, contractor_bill_id BIGINT UNSIGNED NOT NULL, amount DECIMAL(18,2) DEFAULT 0, created_at DATETIME NULL, PRIMARY KEY(id), KEY advance_payment_id(advance_payment_id), KEY contractor_bill_id(contractor_bill_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}gst_invoices (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, financial_year VARCHAR(20), invoice_no VARCHAR(80), invoice_date DATE NOT NULL, customer_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NOT NULL, billing_address TEXT, customer_gstin VARCHAR(30), customer_state VARCHAR(100), place_of_supply_state VARCHAR(100), taxable_amount DECIMAL(18,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, round_off DECIMAL(18,2) DEFAULT 0, invoice_total DECIMAL(18,2) DEFAULT 0, status VARCHAR(30) DEFAULT 'issued', created_by BIGINT UNSIGNED, created_at DATETIME, updated_at DATETIME, PRIMARY KEY(id), UNIQUE KEY invoice_no(invoice_no), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}gst_invoice_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, invoice_id BIGINT UNSIGNED NOT NULL, description TEXT, hsn_sac VARCHAR(50), qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, taxable_amount DECIMAL(18,2) DEFAULT 0, gst_rate DECIMAL(8,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, PRIMARY KEY(id), KEY invoice_id(invoice_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}quotations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, quotation_no VARCHAR(80), quotation_date DATE NOT NULL, valid_till DATE NULL, customer_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NULL, billing_address TEXT, customer_gstin VARCHAR(30), billing_state_id BIGINT UNSIGNED NULL, place_of_supply_state_id BIGINT UNSIGNED NULL, taxable_amount DECIMAL(18,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, other_charges DECIMAL(18,2) DEFAULT 0, gst_on_other_charge DECIMAL(18,2) DEFAULT 0, round_off DECIMAL(18,2) DEFAULT 0, grand_total DECIMAL(18,2) DEFAULT 0, terms TEXT, remarks TEXT, pdf_url TEXT NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY quotation_no(quotation_no), KEY customer_id(customer_id), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}quotation_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, quotation_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NULL, description TEXT, hsn_sac VARCHAR(50), length DECIMAL(18,2) DEFAULT 0, height DECIMAL(18,2) DEFAULT 0, nos DECIMAL(18,2) DEFAULT 0, qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, amount DECIMAL(18,2) DEFAULT 0, gst_rate DECIMAL(8,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, PRIMARY KEY(id), KEY quotation_id(quotation_id), KEY item_id(item_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}tax_invoices (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, invoice_no VARCHAR(80), invoice_date DATE NOT NULL, customer_id BIGINT UNSIGNED NOT NULL, site_id BIGINT UNSIGNED NULL, billing_address TEXT, customer_gstin VARCHAR(30), billing_state_id BIGINT UNSIGNED NULL, place_of_supply_state_id BIGINT UNSIGNED NULL, taxable_amount DECIMAL(18,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, other_charges DECIMAL(18,2) DEFAULT 0, gst_on_other_charge DECIMAL(18,2) DEFAULT 0, round_off DECIMAL(18,2) DEFAULT 0, invoice_total DECIMAL(18,2) DEFAULT 0, terms TEXT, remarks TEXT, pdf_url TEXT NULL, created_by BIGINT UNSIGNED NULL, created_at DATETIME NULL, updated_at DATETIME NULL, PRIMARY KEY(id), UNIQUE KEY invoice_no(invoice_no), KEY customer_id(customer_id), KEY site_id(site_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}tax_invoice_items (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, invoice_id BIGINT UNSIGNED NOT NULL, item_id BIGINT UNSIGNED NULL, description TEXT, hsn_sac VARCHAR(50), qty DECIMAL(18,2) DEFAULT 0, unit_id BIGINT UNSIGNED NULL, rate DECIMAL(18,2) DEFAULT 0, amount DECIMAL(18,2) DEFAULT 0, gst_rate DECIMAL(8,2) DEFAULT 0, cgst_amount DECIMAL(18,2) DEFAULT 0, sgst_amount DECIMAL(18,2) DEFAULT 0, igst_amount DECIMAL(18,2) DEFAULT 0, total_amount DECIMAL(18,2) DEFAULT 0, PRIMARY KEY(id), KEY invoice_id(invoice_id), KEY item_id(item_id)
        ) $c;";

        $sql[] = "CREATE TABLE {$p}approvals (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, module VARCHAR(60) NOT NULL, reference_id BIGINT UNSIGNED NOT NULL, submitted_by BIGINT UNSIGNED NULL, submitted_at DATETIME NULL, action_by BIGINT UNSIGNED NULL, action_at DATETIME NULL, status VARCHAR(30) DEFAULT 'pending', remarks TEXT, PRIMARY KEY(id), KEY module_ref(module,reference_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}notifications (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, user_id BIGINT UNSIGNED NOT NULL, notification_type VARCHAR(50), title VARCHAR(190), message TEXT, module VARCHAR(60), reference_id BIGINT UNSIGNED NULL, is_read TINYINT(1) DEFAULT 0, created_at DATETIME, PRIMARY KEY(id), KEY user_id(user_id)
        ) $c;";
        $sql[] = "CREATE TABLE {$p}number_series (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, module VARCHAR(60) NOT NULL, financial_year VARCHAR(20) NOT NULL, prefix VARCHAR(30), last_number BIGINT UNSIGNED DEFAULT 0, PRIMARY KEY(id), UNIQUE KEY module_fy(module,financial_year)
        ) $c;";

        foreach ($sql as $q) dbDelta($q);

        // Defaults
        if (!$wpdb->get_var("SELECT COUNT(*) FROM {$p}company_settings")) {
            $wpdb->insert("{$p}company_settings", ['company_name' => get_bloginfo('name'), 'updated_at' => current_time('mysql')]);
        }
        if (!$wpdb->get_var("SELECT COUNT(*) FROM {$p}warehouses")) {
            $wpdb->insert("{$p}warehouses", ['warehouse_name' => 'Central Warehouse', 'status' => 'active']);
        }
        foreach (['Nos','Kg','Bag','Litre','Metre','Sq Ft'] as $u) {
            $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$p}units(unit_name,status) VALUES(%s,'active')", $u));
        }
        foreach ([0,5,12,18,28] as $r) {
            $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$p}gst_rates(gst_rate,status,created_at,updated_at) VALUES(%f,'active',%s,%s)", $r, current_time('mysql'), current_time('mysql')));
        }
        foreach (['Fuel','Transportation','Food','Local Purchase','Repair','Accommodation','Miscellaneous'] as $e) {
            $wpdb->query($wpdb->prepare("INSERT IGNORE INTO {$p}expense_categories(category_name,status) VALUES(%s,'active')", $e));
        }
    }

    private function page_urls() {
        // Pages are created with fixed slugs, so build URLs directly instead of
        // querying WordPress once for every menu item on every page load.
        // If Site Management is configured as the WordPress static front page,
        // the dashboard URL must be the site root. Pointing it back to
        // /site-management/ lets WordPress canonicalize that page to / and can
        // participate in redirect loops when another redirect is present.
        $urls = [];
        $parent = get_page_by_path(self::PAGE_SLUG);
        $is_front = $parent && (int) get_option('page_on_front') === (int) $parent->ID;
        foreach ($this->app_pages() as $key => $def) {
            if ($key === 'dashboard') {
                $urls[$key] = $is_front ? home_url('/') : home_url('/' . self::PAGE_SLUG . '/');
            } else {
                $urls[$key] = home_url('/' . self::PAGE_SLUG . '/' . $def[1] . '/');
            }
        }
        return $urls;
    }

    public function enqueue_assets() {
        if (!is_page()) return;
        global $post;
        if (!$post || get_post_meta($post->ID, '_bsm_app_page', true) !== '1') return;
        // Stable asset names prevent PHP and filesystem releases becoming mismatched.
        // Use file modification timestamps for cache-busting after each update.
        $asset_path = plugin_dir_path(__FILE__) . 'assets/';
        $asset_url = plugin_dir_url(__FILE__) . 'assets/';
        $css_version = self::VERSION . '-' . (file_exists($asset_path . 'app.css') ? (string) filemtime($asset_path . 'app.css') : '0');
        $js_version = self::VERSION . '-' . (file_exists($asset_path . 'app.js') ? (string) filemtime($asset_path . 'app.js') : '0');
        wp_enqueue_style('bsm-app', $asset_url . 'app.css', [], $css_version);
        wp_enqueue_script('bsm-app', $asset_url . 'app.js', ['jquery'], $js_version, true);
        global $wpdb;
        $plugin_user = is_user_logged_in() ? $wpdb->get_row($wpdb->prepare("SELECT id,role,assigned_sites,allowed_views FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1", get_current_user_id()), ARRAY_A) : null;
        $plugin_user_id = $plugin_user ? (int)$plugin_user['id'] : 0;
        $is_bsm_admin = is_user_logged_in() && (current_user_can('manage_options') || (($plugin_user['role'] ?? '') !== 'bsm_employee'));
        $assigned_sites = $plugin_user && !empty($plugin_user['assigned_sites']) ? json_decode($plugin_user['assigned_sites'], true) : [];
        $allowed_views = $plugin_user && !empty($plugin_user['allowed_views']) ? json_decode($plugin_user['allowed_views'], true) : [];
        if (!is_array($assigned_sites)) $assigned_sites = [];
        if (!is_array($allowed_views)) $allowed_views = [];
        if (!$is_bsm_admin) $allowed_views = array_values(array_filter($allowed_views, static function($v){ return sanitize_key($v) !== 'company_settings'; }));
        wp_localize_script('bsm-app', 'BSM', [
            'ajax' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bsm_nonce'),
            'loggedIn' => is_user_logged_in(),
            'currentUser' => is_user_logged_in() ? [
                'id' => get_current_user_id(), 'plugin_id' => $plugin_user_id, 'name' => wp_get_current_user()->display_name,
                'role' => implode(', ', wp_get_current_user()->roles),
                'is_admin' => $is_bsm_admin,
                'assigned_sites' => array_values(array_map('intval',$assigned_sites)),
                'allowed_views' => array_values(array_map('sanitize_key',$allowed_views))
            ] : null,
            'today' => current_time('Y-m-d'),
            'siteUrl' => (is_front_page() && get_post_meta($post->ID, '_bsm_view_key', true) === 'dashboard') ? home_url('/') : site_url('/' . self::PAGE_SLUG . '/'),
            'initialView' => get_post_meta($post->ID, '_bsm_view_key', true) ?: 'dashboard',
            'pages' => $this->page_urls(),
            'deleteIcon' => plugin_dir_url(__FILE__) . 'assets/delete-icon.png',
            'hiddenViews' => $this->hidden_views(),
        ]);
    }

    public function shortcode($atts = []) {
        ob_start();
        include plugin_dir_path(__FILE__) . 'templates/app-shell.php';
        return ob_get_clean();
    }

    // Views that can never be hidden, so an admin can always reach Settings again.
    private const PROTECTED_VIEWS = ['company_settings', 'view_settings'];

    // Views switched off in Settings > Show / Hide Views (applies to every user).
    private function hidden_views() {
        $hidden = get_option('bsm_hidden_views', []);
        if (!is_array($hidden)) $hidden = [];
        $hidden = array_map('sanitize_key', $hidden);
        return array_values(array_unique(array_diff($hidden, self::PROTECTED_VIEWS)));
    }

    public function ajax_save_view_visibility() {
        $this->verify_ajax();
        global $wpdb;
        $role = (string)$wpdb->get_var($wpdb->prepare("SELECT role FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1", get_current_user_id()));
        $is_admin = current_user_can('manage_options') || ($role !== '' && $role !== 'bsm_employee');
        if (!$is_admin) wp_send_json_error(['message' => 'Only administrators can change view visibility.'], 403);
        $hidden = isset($_POST['hidden']) && is_array($_POST['hidden']) ? wp_unslash($_POST['hidden']) : [];
        $hidden = array_values(array_unique(array_diff(array_filter(array_map('sanitize_key', $hidden)), self::PROTECTED_VIEWS)));
        update_option('bsm_hidden_views', $hidden, false);
        wp_send_json_success(['hidden' => $hidden, 'message' => 'Saved']);
    }

    private function verify_ajax($require_login = true) {
        check_ajax_referer('bsm_nonce', 'nonce');
        if ($require_login && !is_user_logged_in()) wp_send_json_error(['message' => 'Please login.'], 401);
    }

    private function deny_employee_settings($key) {
        if (sanitize_key($key) !== 'company_settings' || !is_user_logged_in() || current_user_can('manage_options')) return;
        global $wpdb;
        $role = $wpdb->get_var($wpdb->prepare("SELECT role FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1", get_current_user_id()));
        if ($role === 'bsm_employee') wp_send_json_error(['message'=>'You do not have permission to access this view.'], 403);
    }

    private function modules() {
        return [
            'company_settings' => ['table'=>'company_settings','label'=>'Company Settings','pk'=>'id'],
            'bank_accounts' => ['table'=>'bank_accounts','label'=>'Bank Master','pk'=>'id'],
            'designations' => ['table'=>'designations','label'=>'Designations','pk'=>'id'],
            'states' => ['table'=>'states','label'=>'States','pk'=>'id'],
            'customers' => ['table'=>'customers','label'=>'Customers','pk'=>'id'],
            'sites' => ['table'=>'sites','label'=>'Sites','pk'=>'id'],
            'vendors' => ['table'=>'vendors','label'=>'Suppliers','pk'=>'id'],
            'labour_contractors' => ['table'=>'labour_contractors','label'=>'Labour Contractors','pk'=>'id'],
            'labour_attendance' => ['table'=>'labour_attendance','label'=>'Daily Labour Attendance','pk'=>'id'],
            'item_categories' => ['table'=>'item_categories','label'=>'Item Categories','pk'=>'id'],
            'sections' => ['table'=>'sections','label'=>'Section','pk'=>'id'],
            'units' => ['table'=>'units','label'=>'Units','pk'=>'id'],
            'gst_rates' => ['table'=>'gst_rates','label'=>'GST Rates','pk'=>'id'],
            'items' => ['table'=>'items','label'=>'Items','pk'=>'id'],
            'warehouses' => ['table'=>'warehouses','label'=>'Warehouses','pk'=>'id'],
            'site_opening_stock' => ['table'=>'site_opening_stock','label'=>'Site Opening Stock','pk'=>'id'],
            'material_requisitions' => ['table'=>'material_requisitions','child'=>'material_requisition_items','fk'=>'requisition_id','label'=>'Material Requisitions','series'=>'REQ'],
            'material_inwards' => ['table'=>'material_inwards','child'=>'material_inward_items','fk'=>'inward_id','label'=>'Material Inward','series'=>'MIN'],
            'material_dispatches' => ['table'=>'material_dispatches','child'=>'material_dispatch_items','fk'=>'dispatch_id','label'=>'Material Dispatch','series'=>'DIS'],
            'site_receipts' => ['table'=>'site_receipts','child'=>'site_receipt_items','fk'=>'receipt_id','label'=>'Site Receipts','series'=>'R'],
            'site_consumptions' => ['table'=>'site_consumptions','child'=>'site_consumption_items','fk'=>'consumption_id','label'=>'Site Consumption','series'=>'C'],
            'material_returns' => ['table'=>'material_returns','child'=>'material_return_items','fk'=>'return_id','label'=>'Material Returns','series'=>'R'],
            'site_fund_allocations' => ['table'=>'site_fund_allocations','label'=>'Site Fund Allocation','series'=>'FND'],
            'expense_categories' => ['table'=>'expense_categories','label'=>'Expense Categories'],
            'allowance_types' => ['table'=>'allowance_types','label'=>'Allowance Types'],
            'employee_allowance_limits' => ['table'=>'employee_allowance_limits','label'=>'Employee Allowance Limit'],
            'site_expenses' => ['table'=>'site_expenses','label'=>'Site Expenses','series'=>'EXP'],
            'customer_receipts' => ['table'=>'customer_receipts','label'=>'Customer Receipts','series'=>'CR'],
            'vendor_payments' => ['table'=>'vendor_payments','label'=>'Payments','series'=>'VP'],
            'contractor_bills' => ['table'=>'contractor_bills','label'=>'Contractor Bills','series'=>'CB'],
            'contractor_payments' => ['table'=>'contractor_payments','label'=>'Contractor Payments','series'=>'CP'],
            'contractor_advances' => ['table'=>'contractor_payments','label'=>'Contractor Payment','series'=>'CP'],
            'quotations' => ['table'=>'quotations','child'=>'quotation_items','fk'=>'quotation_id','label'=>'Quotation','series'=>'QTN'],
            'tax_invoices' => ['table'=>'tax_invoices','child'=>'tax_invoice_items','fk'=>'invoice_id','label'=>'Tax Invoice','series'=>'INV'],
            'gst_invoices' => ['table'=>'gst_invoices','child'=>'gst_invoice_items','fk'=>'invoice_id','label'=>'GST Invoices','series'=>'INV'],
        ];
    }

    private function module($key) {
        $m = $this->modules();
        return isset($m[$key]) ? $m[$key] : null;
    }


    /**
     * Central dependency/locking guard.
     * Masters that are already referenced cannot be deleted; they should be made Inactive.
     * Workflow transactions become immutable once a downstream transaction exists.
     */
    private function record_guard($key, $id) {
        global $wpdb;
        $p=$this->prefix; $id=(int)$id;
        $out=['can_edit'=>true,'can_delete'=>true,'edit_message'=>'','delete_message'=>''];
        if(!$id) return $out;
        $exists=function($sql,...$args) use ($wpdb){
            if($args) $sql=$wpdb->prepare($sql,$args);
            return (int)$wpdb->get_var($sql)>0;
        };
        $lock_delete=function($message) use (&$out){$out['can_delete']=false;$out['delete_message']=$message;};
        $lock_both=function($message) use (&$out){$out['can_edit']=false;$out['can_delete']=false;$out['edit_message']=$message;$out['delete_message']=$message;};

        if($key==='contractor_bills' && $exists("SELECT COUNT(*) FROM {$p}time_entries WHERE bill_id=%d",$id)){
            $lock_both('This calculated bill is locked to approved Time Sheet entries.');return $out;
        }
        switch($key){
            case 'bank_accounts':
                if($exists("SELECT COUNT(*) FROM {$p}customer_receipts WHERE bank_account_id=%d",$id)) $lock_delete('This Bank Account is already used in Site Payment Receipts and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'designations':
                if($exists("SELECT COUNT(*) FROM {$p}users WHERE designation_id=%d",$id)) $lock_delete('This Designation is assigned to one or more Users and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'states':
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}customers WHERE state_id=%d)+(SELECT COUNT(*) FROM {$p}vendors WHERE state_id=%d)",$id,$id)) $lock_delete('This State is already in use and cannot be deleted.');
                break;
            case 'customers':
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}sites WHERE customer_id=%d)+(SELECT COUNT(*) FROM {$p}customer_receipts WHERE customer_id=%d)+(SELECT COUNT(*) FROM {$p}gst_invoices WHERE customer_id=%d)",$id,$id,$id)) $lock_delete('This Customer is already linked with a Site or transaction and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'sites':
                if($exists("SELECT COUNT(*) FROM {$p}material_dispatches WHERE from_site_id=%d",$id)) $lock_delete('This Site has outgoing dispatches and cannot be deleted. Mark it Inactive instead.');
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}material_requisitions WHERE site_id=%d)+(SELECT COUNT(*) FROM {$p}material_dispatches WHERE site_id=%d)+(SELECT COUNT(*) FROM {$p}site_receipts WHERE site_id=%d)+(SELECT COUNT(*) FROM {$p}site_consumptions WHERE site_id=%d)+(SELECT COUNT(*) FROM {$p}material_returns WHERE site_id=%d)+(SELECT COUNT(*) FROM {$p}labour_attendance WHERE site_id=%d)+(SELECT COUNT(*) FROM {$p}site_expenses WHERE site_id=%d)",$id,$id,$id,$id,$id,$id,$id)) $lock_delete('This Site already has transactions and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'vendors':
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}material_inwards WHERE vendor_id=%d)+(SELECT COUNT(*) FROM {$p}vendor_payments WHERE vendor_id=%d)",$id,$id)) $lock_delete('This Supplier is already used in transactions and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'labour_contractors':
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}labour_attendance WHERE contractor_id=%d)+(SELECT COUNT(*) FROM {$p}contractor_bills WHERE contractor_id=%d)+(SELECT COUNT(*) FROM {$p}contractor_payments WHERE contractor_id=%d)",$id,$id,$id)) $lock_delete('This Labour Contractor is already used in transactions and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'sections':
                if($exists("SELECT COUNT(*) FROM {$p}boqs WHERE section=(SELECT section_name FROM {$p}sections WHERE id=%d)",$id)) $lock_delete('This Section is already used in BOQ and cannot be deleted. Mark it Inactive instead.');
                break;
            case 'item_categories':
                if($exists("SELECT COUNT(*) FROM {$p}items WHERE category_id=%d",$id)) $lock_delete('This Item Category is already used in Item Master and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'units':
                if($exists("SELECT COUNT(*) FROM {$p}items WHERE unit_id=%d",$id)) $lock_delete('This Unit is already used in Item Master and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'items':
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}material_requisition_items WHERE item_id=%d)+(SELECT COUNT(*) FROM {$p}material_inward_items WHERE item_id=%d)+(SELECT COUNT(*) FROM {$p}material_dispatch_items WHERE item_id=%d)+(SELECT COUNT(*) FROM {$p}site_receipt_items WHERE item_id=%d)+(SELECT COUNT(*) FROM {$p}site_consumption_items WHERE item_id=%d)+(SELECT COUNT(*) FROM {$p}material_return_items WHERE item_id=%d)",$id,$id,$id,$id,$id,$id)) $lock_delete('This Item has already been used in transactions and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'warehouses':
                if($exists("SELECT (SELECT COUNT(*) FROM {$p}material_inwards WHERE warehouse_id=%d)+(SELECT COUNT(*) FROM {$p}material_dispatches WHERE warehouse_id=%d)+(SELECT COUNT(*) FROM {$p}material_returns WHERE warehouse_id=%d)",$id,$id,$id)) $lock_delete('This Warehouse already has stock or transactions and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'expense_categories':
                if($exists("SELECT COUNT(*) FROM {$p}site_expenses WHERE category_id=%d",$id)) $lock_delete('This Expense Category is already used in transactions and cannot be deleted. You can mark it Inactive instead.');
                break;
            case 'material_requisitions':
                $row=$wpdb->get_row($wpdb->prepare("SELECT status FROM {$p}material_requisitions WHERE id=%d",$id),ARRAY_A);
                $has_dispatch=$exists("SELECT COUNT(*) FROM {$p}material_dispatches WHERE requisition_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",$id);
                if($has_dispatch) $lock_both('This Requisition has already been dispatched and cannot be edited or deleted.');
                elseif($row && !in_array(strtolower((string)$row['status']),['draft','submitted','pending_approval'],true)) $lock_both('This Requisition has already been processed and cannot be edited or deleted.');
                break;
            case 'material_dispatches':
                if($exists("SELECT COUNT(*) FROM {$p}site_receipts WHERE dispatch_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",$id)) $lock_both('A Site Receipt has already been created against this Dispatch. The Dispatch cannot be edited or deleted.');
                break;
            case 'site_receipts':
                $r=$wpdb->get_row($wpdb->prepare("SELECT site_id,receipt_date FROM {$p}site_receipts WHERE id=%d",$id),ARRAY_A);
                if($r){
                    $used=$exists("SELECT COUNT(*) FROM {$p}site_consumptions sc JOIN {$p}site_consumption_items sci ON sci.consumption_id=sc.id JOIN {$p}site_receipt_items sri ON sri.receipt_id=%d AND sri.item_id=sci.item_id WHERE sc.site_id=%d AND sc.consumption_date>=%s",$id,(int)$r['site_id'],$r['receipt_date'])
                      || $exists("SELECT COUNT(*) FROM {$p}material_returns mr JOIN {$p}material_return_items mri ON mri.return_id=mr.id JOIN {$p}site_receipt_items sri ON sri.receipt_id=%d AND sri.item_id=mri.item_id WHERE mr.site_id=%d AND mr.return_date>=%s",$id,(int)$r['site_id'],$r['receipt_date']);
                    $used=$used || $exists("SELECT COUNT(*) FROM {$p}material_dispatches md JOIN {$p}material_dispatch_items mdi ON mdi.dispatch_id=md.id JOIN {$p}site_receipt_items sri ON sri.receipt_id=%d AND sri.item_id=mdi.item_id WHERE md.from_site_id=%d AND LOWER(COALESCE(md.status,''))<>'cancelled'",$id,(int)$r['site_id']);
                    if($used) $lock_both('Stock from this Receipt has already been used. The Receipt cannot be edited or deleted.');
                }
                break;
            case 'material_returns':
                if($exists("SELECT COUNT(*) FROM {$p}material_inwards WHERE return_id=%d AND LOWER(COALESCE(inward_type,''))='return' AND LOWER(COALESCE(status,''))<>'cancelled'",$id)) $lock_both('This Material Return has already been received at the Warehouse and cannot be edited or deleted.');
                break;
            case 'material_inwards':
                $r=$wpdb->get_row($wpdb->prepare("SELECT warehouse_id,inward_date,inward_type,status FROM {$p}material_inwards WHERE id=%d",$id),ARRAY_A);
                // Approved Purchases are final records. The Purchase Register hides Edit/Delete for them;
                // keep the server-side lock as a safeguard against direct/manual requests.
                if($r && strtolower((string)($r['inward_type']??''))==='purchase' && strtolower((string)($r['status']??''))==='approved'){
                    $lock_both('Approved Purchase cannot be edited or deleted.');
                }elseif($r && $exists("SELECT COUNT(*) FROM {$p}material_dispatches d JOIN {$p}material_dispatch_items di ON di.dispatch_id=d.id JOIN {$p}material_inward_items mi ON mi.inward_id=%d AND mi.item_id=di.item_id WHERE d.warehouse_id=%d AND d.dispatch_date>=%s AND LOWER(COALESCE(d.status,''))<>'cancelled'",$id,(int)$r['warehouse_id'],$r['inward_date'])){
                    $lock_both('Stock from this Material Inward has already been used. It cannot be edited or deleted.');
                }
                break;
        }
        return $out;
    }

    private function sanitize_row($table, $data) {
        global $wpdb;
        $columns = $wpdb->get_results("DESCRIBE {$this->prefix}{$table}", ARRAY_A);
        $allowed = [];
        foreach ($columns as $c) $allowed[$c['Field']] = $c['Type'];
        $row = [];
        foreach ($data as $k => $v) {
            if (!isset($allowed[$k]) || $k === 'id') continue;
            if (is_array($v)) continue;
            $type = strtolower($allowed[$k]);
            if ($v === '' && (strpos($type,'date') !== false || strpos($type,'int') !== false || strpos($type,'decimal') !== false)) {
                $row[$k] = null; continue;
            }
            if (strpos($type,'int') !== false) $row[$k] = is_numeric($v) ? intval($v) : 0;
            elseif (strpos($type,'decimal') !== false) $row[$k] = is_numeric($v) ? round((float)$v, 2) : 0;
            elseif (strpos($type,'text') !== false) $row[$k] = sanitize_textarea_field($v);
            elseif (strpos($type,'date') !== false) $row[$k] = sanitize_text_field($v);
            else $row[$k] = sanitize_text_field($v);
        }
        return $row;
    }

    private function fy() {
        $ts = current_time('timestamp');
        $y = (int)date('Y',$ts); $m=(int)date('n',$ts);
        $start = $m >= 4 ? $y : $y-1;
        return substr((string)$start,2) . '-' . substr((string)($start+1),2);
    }

    private function next_site_code() {
        global $wpdb;
        $fy = $this->fy();
        $t = $this->prefix . 'number_series';
        $module = 'site_code';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE module=%s AND financial_year=%s FOR UPDATE", $module, $fy), ARRAY_A);
        if (!$row) {
            $display_fy = str_replace('-', '/', $fy);
            $last_code = $wpdb->get_var($wpdb->prepare("SELECT site_code FROM {$this->prefix}sites WHERE site_code LIKE %s ORDER BY site_code DESC LIMIT 1", 'S-' . $display_fy . '-%'));
            $n = 1;
            if ($last_code && preg_match('/-(\d{4,})$/', (string)$last_code, $m)) $n = ((int)$m[1]) + 1;
            $wpdb->insert($t, ['module'=>$module,'financial_year'=>$fy,'prefix'=>'S','last_number'=>$n]);
        } else {
            $n = (int)$row['last_number'] + 1;
            $wpdb->update($t, ['last_number'=>$n], ['id'=>$row['id']]);
        }
        return 'S-' . str_replace('-', '/', $fy) . '-' . str_pad($n, 4, '0', STR_PAD_LEFT);
    }

    private function next_no($module, $prefix) {
        global $wpdb;
        $fy = $this->fy(); $t = $this->prefix.'number_series';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $t WHERE module=%s AND financial_year=%s FOR UPDATE", $module,$fy), ARRAY_A);
        if (!$row) {
            $wpdb->insert($t,['module'=>$module,'financial_year'=>$fy,'prefix'=>$prefix,'last_number'=>1]);
            $n=1;
        } else {
            $n=(int)$row['last_number']+1;
            $wpdb->update($t,['last_number'=>$n],['id'=>$row['id']]);
        }
        // Requisition and Dispatch numbering: PREFIX-YY/YY-00001 (financial-year wise).
        // Keep the same running counter for the financial year; only the display format differs.
        if (in_array($module, ['quotations','tax_invoices'], true)) {
            $display_prefix = $module==='quotations' ? 'QTN' : 'INV';
            return $display_prefix.'-'.str_replace('-', '/', $fy).'-'.str_pad($n,5,'0',STR_PAD_LEFT);
        }
        if ($module === 'purchase_orders') {
            $display_prefix='PO';
            if (!empty($row) && ($row['prefix'] ?? '') !== $display_prefix) $wpdb->update($t,['prefix'=>$display_prefix],['id'=>$row['id']]);
            return $display_prefix.'-'.str_replace('-', '/', $fy).'-'.str_pad($n,5,'0',STR_PAD_LEFT);
        }
        if (in_array($module, ['material_requisitions','material_dispatches','site_receipts','site_consumptions','material_returns'], true)) {
            $display_prefix = ($module === 'material_dispatches') ? 'DIS' : (($module === 'site_receipts') ? 'R' : (($module === 'site_consumptions') ? 'C' : (($module === 'material_returns') ? 'R' : 'REQ')));
            if (!empty($row) && ($row['prefix'] ?? '') !== $display_prefix) {
                $wpdb->update($t, ['prefix'=>$display_prefix], ['id'=>$row['id']]);
            }
            return $display_prefix.'-'.str_replace('-', '/', $fy).'-'.str_pad($n,5,'0',STR_PAD_LEFT);
        }
        return $prefix.'/'.$fy.'/'.str_pad($n,4,'0',STR_PAD_LEFT);
    }

    /**
     * Contractor bill payments and contractor advances are stored in the same
     * contractor_payments table and therefore MUST share one payment-number
     * sequence. This helper also self-heals the number_series counter from the
     * actual table, so a stale or previously separate advance counter cannot
     * generate a duplicate payment_no.
     */
    private function next_contractor_payment_no() {
        global $wpdb;
        $fy = $this->fy();
        $series_table = $this->prefix . 'number_series';
        $payment_table = $this->prefix . 'contractor_payments';
        $module = 'contractor_payments';
        $prefix = 'CP';

        // Find the highest number already used in the shared contractor payment table.
        $like = $prefix . '/' . $fy . '/%';
        $existing = $wpdb->get_col($wpdb->prepare(
            "SELECT payment_no FROM {$payment_table} WHERE payment_no LIKE %s",
            $like
        ));
        $max_used = 0;
        foreach ((array)$existing as $no) {
            if (preg_match('/\/(\d+)$/', (string)$no, $m)) {
                $max_used = max($max_used, (int)$m[1]);
            }
        }

        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$series_table} WHERE module=%s AND financial_year=%s FOR UPDATE",
            $module, $fy
        ), ARRAY_A);

        $last_series = $row ? (int)$row['last_number'] : 0;
        $n = max($last_series, $max_used) + 1;

        if ($row) {
            $wpdb->update($series_table, ['last_number'=>$n,'prefix'=>$prefix], ['id'=>(int)$row['id']]);
        } else {
            $wpdb->insert($series_table, [
                'module'=>$module,
                'financial_year'=>$fy,
                'prefix'=>$prefix,
                'last_number'=>$n
            ]);
        }

        // Extra collision guard for legacy data.
        do {
            $payment_no = $prefix . '/' . $fy . '/' . str_pad($n, 4, '0', STR_PAD_LEFT);
            $exists = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$payment_table} WHERE payment_no=%s",
                $payment_no
            ));
            if ($exists) {
                $n++;
                if ($row) $wpdb->update($series_table, ['last_number'=>$n], ['id'=>(int)$row['id']]);
            }
        } while ($exists);

        return $payment_no;
    }

    private function employee_site_scope(){
        global $wpdb;
        if(!is_user_logged_in()) return null;
        if(current_user_can('manage_options')) return null;
        $u=$wpdb->get_row($wpdb->prepare("SELECT role,assigned_sites FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()),ARRAY_A);
        if(!$u || ($u['role']??'')!=='bsm_employee') return null;
        $sites=[];
        if(!empty($u['assigned_sites'])){ $sites=json_decode($u['assigned_sites'],true); if(!is_array($sites)) $sites=[]; }
        return array_values(array_unique(array_filter(array_map('intval',$sites))));
    }

    private function enforce_employee_site_value($site_id){
        $scope=$this->employee_site_scope();
        if($scope===null || !$scope) return; // Admin/non-employee, or Employee with no site restriction = all sites.
        if(!$site_id || !in_array((int)$site_id,$scope,true)) wp_send_json_error(['message'=>'You do not have access to the selected Site.']);
    }

    private function boq_access($site_id = 0) {
        $this->verify_ajax();
        if(current_user_can('manage_options')) return;
        global $wpdb;
        $user=$wpdb->get_row($wpdb->prepare("SELECT role,assigned_sites,allowed_views FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()),ARRAY_A);
        if(!$user) wp_send_json_error(['message'=>'Permission denied'],403);
        $allowed=json_decode($user['allowed_views']??'[]',true);
        if(is_array($allowed) && count($allowed) && !array_intersect(['boq_form','boq_master','boq_items','boq_monitoring'],$allowed)) wp_send_json_error(['message'=>'BOQ permission denied'],403);
        $sites=json_decode($user['assigned_sites']??'[]',true);
        if($site_id && is_array($sites) && count($sites) && !in_array((string)$site_id,array_map('strval',$sites),true)) wp_send_json_error(['message'=>'Site permission denied'],403);
    }

    private function boq_xlsx_xml_escape($v) { return htmlspecialchars((string)$v,ENT_XML1|ENT_QUOTES,'UTF-8'); }
    public function ajax_boq_template() {
        $this->boq_access(); global $wpdb;
        if(!class_exists('ZipArchive')) wp_die('ZIP extension is required for the Excel template', '', ['response'=>500]);
        $site=(int)($_POST['site_id']??0);
        if(!$site)wp_die('Select Site first');
        $this->boq_access($site);
        $assigned_raw=(string)$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$this->prefix}sites WHERE id=%d",$site));
        $allowed=array_map('trim',explode(',',$assigned_raw));
        $sections=$wpdb->get_col("SELECT section_name FROM {$this->prefix}sections WHERE status='active' ORDER BY section_name");
        $sections=array_values(array_intersect($sections,$allowed));
        if(!$sections)wp_die('No active Sections assigned to this Site');
        $items=$wpdb->get_results("SELECT id,item_name FROM {$this->prefix}items ORDER BY item_name",ARRAY_A);
        $tmp=wp_tempnam('boq-template.xlsx');$zip=new ZipArchive();if($zip->open($tmp,ZipArchive::OVERWRITE)!==true)wp_die('Could not create template');
        $xmlHeader='<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $zip->addFromString('[Content_Types].xml',$xmlHeader.'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet3.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('_rels/.rels',$xmlHeader.'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/_rels/workbook.xml.rels',$xmlHeader.'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet3.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml',$xmlHeader.'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="BOQ Upload" sheetId="1" r:id="rId1"/><sheet name="Item List" sheetId="2" state="hidden" r:id="rId2"/><sheet name="Section List" sheetId="3" state="hidden" r:id="rId3"/></sheets><definedNames><definedName name="BOQItemNames">&apos;Item List&apos;!$A$2:$A$'.max(2,count($items)+1).'</definedName><definedName name="BOQSectionNames">&apos;Section List&apos;!$A$2:$A$'.max(2,count($sections)+1).'</definedName></definedNames></workbook>');
        $sheet=$xmlHeader.'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols><col min="1" max="1" width="45" customWidth="1"/><col min="2" max="2" width="20" customWidth="1"/><col min="3" max="3" width="30" customWidth="1"/></cols><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Item</t></is></c><c r="B1" t="inlineStr"><is><t>BOQ Qty</t></is></c><c r="C1" t="inlineStr"><is><t>Section</t></is></c></row></sheetData><dataValidations count="3"><dataValidation type="list" allowBlank="1" showErrorMessage="1" error="Select an item from the list" sqref="A2:A1001"><formula1>BOQItemNames</formula1></dataValidation><dataValidation type="decimal" operator="greaterThanOrEqual" allowBlank="1" showErrorMessage="1" error="Quantity must be zero or more" sqref="B2:B1001"><formula1>0</formula1></dataValidation><dataValidation type="list" allowBlank="0" showErrorMessage="1" error="Select a Section" sqref="C2:C1001"><formula1>BOQSectionNames</formula1></dataValidation></dataValidations></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml',$sheet);
        $list=$xmlHeader.'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Item</t></is></c></row>';
        foreach($items as $i=>$item){$row=$i+2;$name=$this->boq_xlsx_xml_escape($item['item_name']);$list.='<row r="'.$row.'"><c r="A'.$row.'" t="inlineStr"><is><t>'.$name.'</t></is></c></row>';}
        $list.='</sheetData></worksheet>';$zip->addFromString('xl/worksheets/sheet2.xml',$list);
        $secXml=$xmlHeader.'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>Section</t></is></c></row>';
        foreach($sections as $i=>$s){$row=$i+2;$secXml.='<row r="'.$row.'"><c r="A'.$row.'" t="inlineStr"><is><t>'.$this->boq_xlsx_xml_escape($s).'</t></is></c></row>';}
        $secXml.='</sheetData></worksheet>';$zip->addFromString('xl/worksheets/sheet3.xml',$secXml);$zip->close();
        nocache_headers();header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="BOQ-Upload-Template.xlsx"');header('Content-Length: '.filesize($tmp));readfile($tmp);unlink($tmp);exit;
    }
    public function ajax_boq_import() {
        $this->boq_access();global $wpdb;
        $site=(int)($_POST['site_id']??0);if(!$site)wp_send_json_error(['message'=>'Select Site first']);$this->boq_access($site);
        $assigned_raw=(string)$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$this->prefix}sites WHERE id=%d",$site));
        $assigned=array_map('trim',explode(',',$assigned_raw));
        if(empty($_FILES['file']['tmp_name'])||!is_uploaded_file($_FILES['file']['tmp_name']))wp_send_json_error(['message'=>'Choose an Excel template']);
        $file=$_FILES['file'];if(($file['size']??0)>2*1024*1024)wp_send_json_error(['message'=>'File exceeds 2 MB']);
        if(strtolower(pathinfo($file['name'],PATHINFO_EXTENSION))!=='xlsx')wp_send_json_error(['message'=>'Upload the .xlsx template']);
        if(!class_exists('ZipArchive'))wp_send_json_error(['message'=>'ZIP extension required']);
        $zip=new ZipArchive();if($zip->open($file['tmp_name'])!==true)wp_send_json_error(['message'=>'Invalid Excel workbook']);
        $raw=$zip->getFromName('xl/worksheets/sheet1.xml');$shared=$zip->getFromName('xl/sharedStrings.xml');$zip->close();
        if($raw===false||strlen($raw)>2500000)wp_send_json_error(['message'=>'Invalid or oversized worksheet']);
        libxml_use_internal_errors(true);$xml=simplexml_load_string($raw);if(!$xml)wp_send_json_error(['message'=>'Could not read worksheet']);
        $ss=[];if($shared){$sx=simplexml_load_string($shared);if($sx){foreach($sx->si as $si){$v=(string)$si->t;foreach($si->r as $r)$v.=(string)$r->t;$ss[]=$v;}}}
        $lookup=[];$masters=$wpdb->get_results("SELECT id,item_name FROM {$this->prefix}items",ARRAY_A);
        foreach($masters as $m)$lookup[mb_strtolower(trim($m['item_name']))][]=$m;
        $out=[];$errors=[];$used=[];
        foreach($xml->sheetData->row as $row){$n=(int)$row['r'];if($n===1)continue;if($n>1001){$errors[]='Maximum 1000 items allowed';break;}$values=['A'=>'','B'=>'','C'=>''];
            foreach($row->c as $c){$col=preg_replace('/[0-9]/','',(string)$c['r']);if(!isset($values[$col]))continue;$v=(string)$c->v;if((string)$c['t']==='s')$v=$ss[(int)$v]??'';elseif((string)$c['t']==='inlineStr'){$v=(string)$c->is->t;foreach($c->is->r as $rr)$v.=(string)$rr->t;}$values[$col]=trim($v);}
            if($values['A']===''&&$values['B']===''&&$values['C']==='')continue;
            $matches=$lookup[mb_strtolower($values['A'])]??[];
            if(count($matches)!==1){$errors[]='Row '.$n.': Item is missing, invalid or ambiguous';continue;}
            if($values['B']===''||!is_numeric($values['B'])||(float)$values['B']<0){$errors[]='Row '.$n.': Invalid BOQ Qty';continue;}
            $sec=$values['C'];
            if(!$sec||!in_array($sec,$assigned,true)||!(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}sections WHERE section_name=%s AND status='active'",$sec))){$errors[]='Row '.$n.': Invalid Site Section';continue;}
            $id=(int)$matches[0]['id'];$key=$sec.'|'.$id;if(isset($used[$key])){$errors[]='Row '.$n.': Duplicate Section + Item';continue;}$used[$key]=true;
            $out[]=['item_id'=>$id,'section'=>$sec,'boq_qty'=>(float)$values['B']];
        }
        if($errors)wp_send_json_error(['message'=>implode('; ',array_slice($errors,0,8))]);
        if(!$out)wp_send_json_error(['message'=>'No filled item rows found']);wp_send_json_success(['items'=>$out]);
    }

    public function ajax_boq_site_sections() {
        $this->verify_ajax(); global $wpdb;
        $site=(int)($_POST['site_id']??0);
        if(!$site)wp_send_json_error(['message'=>'Select a Site']);
        $site_sections=$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$this->prefix}sites WHERE id=%d",$site));
        if($site_sections===null)wp_send_json_error(['message'=>'Site not found']);
        $assigned=array_unique(array_filter(array_map('trim',explode(',',(string)$site_sections))));
        $all=$wpdb->get_results("SELECT section_name FROM {$this->prefix}sections WHERE status='active' ORDER BY section_name",ARRAY_A);
        $out=[];foreach($all as $r){if(in_array($r['section_name'],$assigned,true))$out[]=['label'=>$r['section_name']];}
        wp_send_json_success(['sections'=>$out]);
    }

    public function ajax_boq_save() {
        global $wpdb; $site=(int)($_POST['site_id']??0); $this->boq_access($site);
        $id=(int)($_POST['id']??0);$date=sanitize_text_field($_POST['boq_date']??'');
        $items=json_decode(wp_unslash($_POST['items']??'[]'),true);
        if(!$site||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}sites WHERE id=%d",$site))||!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||!is_array($items)||!count($items)) wp_send_json_error(['message'=>'Site, date and at least one item are required']);
        if (!$this->ensure_boq_item_section_column()) wp_send_json_error(['message'=>'BOQ item Section database update failed']);
        $assigned_raw=(string)$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$this->prefix}sites WHERE id=%d",$site));
        $assigned=array_map('trim',explode(',',$assigned_raw));
        $active=$wpdb->get_col("SELECT section_name FROM {$this->prefix}sections WHERE status='active'");
        $clean=[];
        foreach($items as $item){
            $iid=(int)($item['item_id']??0);
            $section=substr(sanitize_text_field($item['section']??''),0,190);
            $q=isset($item['boq_qty'])&&is_numeric($item['boq_qty'])?(float)$item['boq_qty']:-1;
            $key=$section.'|'.$iid;
            if(!$iid||!is_finite($q)||$q<0||isset($clean[$key])) wp_send_json_error(['message'=>'Invalid quantity or duplicate Section + Item combination']);
            if($section===''||!in_array($section,$assigned,true)||!in_array($section,$active,true)) wp_send_json_error(['message'=>'Select an active Section assigned to the Site for each item']);
            $unit=$wpdb->get_var($wpdb->prepare("SELECT unit_id FROM {$this->prefix}items WHERE id=%d",$iid));
            if($unit===null) wp_send_json_error(['message'=>'Invalid Item Master selection']);
            $clean[$key]=['item_id'=>$iid,'section'=>$section,'unit_id'=>(int)$unit,'boq_qty'=>$q];
        }
        $table=$this->prefix.'boqs';$it=$this->prefix.'boq_items';
        if (!$this->ensure_boq_section_column()) wp_send_json_error(['message'=>'BOQ Section database update failed']);
        if($id){
            $old=$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id),ARRAY_A);
            if(!$old)wp_send_json_error(['message'=>'BOQ not found']);
            $this->boq_access((int)$old['site_id']);
        }
        // Old two-column UNIQUE index prevents repeated items in different Sections.
        $indexes=$wpdb->get_results("SHOW INDEX FROM `{$it}` WHERE Key_name='boq_item'",ARRAY_A);
        if($indexes && count($indexes)===2 && (int)($indexes[0]['Non_unique']??1)===0)
            $wpdb->query("ALTER TABLE `{$it}` DROP INDEX `boq_item`, ADD KEY `boq_item` (`boq_id`,`item_id`)");
        $groups=[];
        foreach($clean as $r)$groups[$r['section']][]=$r;
        $site_name=$wpdb->get_var($wpdb->prepare("SELECT site_name FROM {$this->prefix}sites WHERE id=%d",$site));
        $wpdb->query('START TRANSACTION');
        $created=[];$index=0;
        foreach($groups as $section=>$rows){
            // On edit, retain the original BOQ number for the first Section only.
            $reuse=$id && $index===0;
            $data=['boq_date'=>$date,'site_id'=>$site,'section'=>$section,'remarks'=>'','updated_at'=>current_time('mysql')];
            if($reuse){
                $target=$id;$data['boq_name']=$old['boq_no'].' ('.$site_name.')';
                $ok=$wpdb->update($table,$data,['id'=>$target]);
                if($ok!==false)$ok=$wpdb->delete($it,['boq_id'=>$target]);
            } else {
                $data['created_by']=get_current_user_id();$data['created_at']=current_time('mysql');
                $data['boq_no']='TEMP-'.wp_generate_uuid4();$data['boq_name']='Pending';
                $ok=$wpdb->insert($table,$data);
                $target=(int)$wpdb->insert_id;
                if($ok!==false){
                    $number='BOQ-'.wp_date('y').'/'.wp_date('y',strtotime('+1 year')).'-'.str_pad($target,5,'0',STR_PAD_LEFT);
                    $ok=$wpdb->update($table,['boq_no'=>$number,'boq_name'=>$number.' ('.$site_name.')'],['id'=>$target]);
                }
            }
            if($ok===false){$wpdb->query('ROLLBACK');error_log('[Site Management BOQ] Section header save failed: '.$wpdb->last_error);wp_send_json_error(['message'=>'Could not save BOQ header for Section '.$section]);}
            foreach($rows as $r){
                if($wpdb->insert($it,['boq_id'=>$target]+$r)===false){
                    $wpdb->query('ROLLBACK');error_log('[Site Management BOQ] Item save failed: '.$wpdb->last_error);
                    wp_send_json_error(['message'=>'Could not save BOQ items for Section '.$section]);
                }
            }
            $created[]=$target;$index++;
        }
        $wpdb->query('COMMIT');wp_send_json_success(['ids'=>$created,'count'=>count($created)]);
    }

    /** One-time conversion: retain original BOQ number for first Section, create separate headers for others. */
    private function split_existing_mixed_section_boqs() {
        global $wpdb;
        $table=$this->prefix.'boqs';$items=$this->prefix.'boq_items';
        $mixed=$wpdb->get_col("SELECT boq_id FROM {$items} WHERE section IS NOT NULL AND TRIM(section)<>'' GROUP BY boq_id HAVING COUNT(DISTINCT section)>1");
        foreach((array)$mixed as $id){
            $boq=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d",(int)$id),ARRAY_A);
            if(!$boq)continue;
            $sections=$wpdb->get_col($wpdb->prepare("SELECT DISTINCT section FROM {$items} WHERE boq_id=%d AND TRIM(section)<>'' ORDER BY id",(int)$id));
            if(count($sections)<2)continue;
            $site_name=$wpdb->get_var($wpdb->prepare("SELECT site_name FROM {$this->prefix}sites WHERE id=%d",(int)$boq['site_id']));
            $wpdb->query('START TRANSACTION');$ok=true;
            foreach(array_slice($sections,1) as $section){
                $data=['boq_no'=>'TEMP-'.wp_generate_uuid4(),'boq_date'=>$boq['boq_date'],'site_id'=>$boq['site_id'],'section'=>$section,'boq_name'=>'Pending','remarks'=>$boq['remarks']??'','created_by'=>$boq['created_by']??0,'created_at'=>$boq['created_at']??current_time('mysql'),'updated_at'=>current_time('mysql')];
                if($wpdb->insert($table,$data)===false){$ok=false;break;}
                $new_id=(int)$wpdb->insert_id;
                $number='BOQ-'.wp_date('y').'/'.wp_date('y',strtotime('+1 year')).'-'.str_pad($new_id,5,'0',STR_PAD_LEFT);
                if($wpdb->update($table,['boq_no'=>$number,'boq_name'=>$number.' ('.$site_name.')'],['id'=>$new_id])===false){$ok=false;break;}
                if($wpdb->query($wpdb->prepare("UPDATE {$items} SET boq_id=%d WHERE boq_id=%d AND section=%s",$new_id,(int)$id,$section))===false){$ok=false;break;}
            }
            if($ok && $wpdb->update($table,['section'=>$sections[0]],['id'=>(int)$id])!==false)$wpdb->query('COMMIT');
            else{$wpdb->query('ROLLBACK');error_log('[Site Management BOQ] Could not split legacy BOQ '.(int)$id.': '.$wpdb->last_error);}
        }
    }
    public function ajax_boq_delete() {
        global $wpdb;$id=(int)($_POST['id']??0);$b=$wpdb->get_row($wpdb->prepare("SELECT site_id FROM {$this->prefix}boqs WHERE id=%d",$id));if(!$b)wp_send_json_error(['message'=>'BOQ not found']);$this->boq_access((int)$b->site_id);
        $wpdb->delete($this->prefix.'boq_items',['boq_id'=>$id]);$wpdb->delete($this->prefix.'boqs',['id'=>$id]);wp_send_json_success();
    }
    public function ajax_boq_get() {
        global $wpdb;$id=(int)($_POST['id']??0);$b=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}boqs WHERE id=%d",$id),ARRAY_A);if(!$b)wp_send_json_error(['message'=>'BOQ not found']);$this->boq_access((int)$b['site_id']);
        $this->ensure_boq_item_section_column();$b['items']=$wpdb->get_results($wpdb->prepare("SELECT item_id,section,boq_qty FROM {$this->prefix}boq_items WHERE boq_id=%d ORDER BY id",$id),ARRAY_A);wp_send_json_success($b);
    }
    public function ajax_requisition_boq_items() {
        $this->verify_ajax();
        global $wpdb;
        $site=(int)($_POST['site_id']??0);
        if(!$site) wp_send_json_success(['items'=>[]]);
        $this->boq_access($site);
        $p=$this->prefix;
        $sql=$wpdb->prepare("SELECT bi.item_id, SUM(bi.boq_qty) boq_qty FROM {$p}boq_items bi INNER JOIN {$p}boqs b ON b.id=bi.boq_id WHERE b.site_id=%d GROUP BY bi.item_id ORDER BY bi.item_id",$site);
        $items=$wpdb->get_results($sql,ARRAY_A)?:[];
        // Already Req. Qty excludes rejected/cancelled headers. For partially
        // rejected item lines, use the remaining requested quantity.
        // This is informational and does not modify any requisition records.
        $already_sql=$wpdb->prepare("SELECT ri.item_id, SUM(GREATEST(0, COALESCE(ri.requested_qty,0)-COALESCE(ri.rejected_qty,0))) AS already_req_qty
            FROM {$p}material_requisition_items ri
            INNER JOIN {$p}material_requisitions r ON r.id=ri.requisition_id
            WHERE r.site_id=%d AND LOWER(COALESCE(r.status,'')) NOT IN ('rejected','cancelled','canceled')
            GROUP BY ri.item_id",$site);
        $already_rows=$wpdb->get_results($already_sql,ARRAY_A)?:[];
        $already_map=[];
        foreach($already_rows as $r) $already_map[(int)$r['item_id']]=(float)$r['already_req_qty'];
        foreach($items as &$item) $item['already_req_qty']=$already_map[(int)$item['item_id']]??0;
        unset($item);
        wp_send_json_success(['items'=>$items]);
    }
    public function ajax_boq_pdf() {
        global $wpdb;
        $id=(int)($_POST['id']??0);
        if(!$id) wp_send_json_error(['message'=>'Invalid BOQ']);
        $p=$this->prefix;
        $boq=$wpdb->get_row($wpdb->prepare("SELECT b.*,s.site_name FROM {$p}boqs b LEFT JOIN {$p}sites s ON s.id=b.site_id WHERE b.id=%d",$id),ARRAY_A);
        if(!$boq) wp_send_json_error(['message'=>'BOQ not found']);
        $this->boq_access((int)$boq['site_id']);
        $items=$wpdb->get_results($wpdb->prepare("SELECT bi.boq_qty,bi.section,i.item_name,u.unit_name FROM {$p}boq_items bi LEFT JOIN {$p}items i ON i.id=bi.item_id LEFT JOIN {$p}units u ON u.id=bi.unit_id WHERE bi.boq_id=%d ORDER BY bi.id",$id),ARRAY_A)?:[];
        $esc=function($v){$v=(string)$v;$v=function_exists('iconv')?(@iconv('UTF-8','Windows-1252//TRANSLIT',$v)?:$v):$v;return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$v);};
        $text=function(&$c,$x,$y,$value,$size=9,$bold=false)use($esc){$font=$bold?'F2':'F1';$c.="BT /$font $size Tf 1 0 0 1 $x $y Tm (".$esc($value).") Tj ET\n";};
        $pages=[];$chunks=array_chunk($items,23);if(!$chunks)$chunks=[[]];
        foreach($chunks as $pageIdx=>$chunk){
            $c="0.08 0.20 0.38 rg\n";
            $c.="0.06 0.29 0.56 rg\n36 749 523 38 re f\n1 1 1 rg\n";$text($c,260,763,'BOQ',17,true);
            $c.="0 0 0 rg\n";
            $text($c,36,724,'BOQ No.: '.($boq['boq_no']??''),9,true);
            $text($c,335,724,'Date: '.(!empty($boq['boq_date'])?date('d/m/Y',strtotime($boq['boq_date'])):'-'),9);
            $text($c,36,702,'Site: '.($boq['site_name']??'-'),9);
            
            $c.="0.06 0.29 0.56 rg\n36 665 523 26 re f\n1 1 1 rg\n";
            foreach([[43,'Sl.'],[75,'Section'],[175,'Item'],[390,'Unit'],[474,'BOQ Qty']] as $h)$text($c,$h[0],674,$h[1],9,true);
            $y=665;foreach($chunk as $i=>$r){$y-=24;
                $c.="0.72 0.79 0.87 RG 0.4 w\n36 $y 523 24 re S\n0 0 0 rg\n";
                $text($c,43,$y+8,(string)($pageIdx*23+$i+1),8);
                $text($c,75,$y+8,substr((string)($r['section']??'-'),0,18),8);
                $text($c,175,$y+8,substr((string)($r['item_name']??'-'),0,35),8);
                $text($c,390,$y+8,substr((string)($r['unit_name']??'-'),0,12),8);
                $text($c,474,$y+8,number_format((float)$r['boq_qty'],2,'.',''),8);
            }
            $text($c,470,34,'Page '.($pageIdx+1).' of '.count($chunks),8);
            $pages[]=$c;
        }
        $count=count($pages);$fontRegular=3+$count*2;$fontBold=$fontRegular+1;
        $objs=["<< /Type /Catalog /Pages 2 0 R >>", "<< /Type /Pages /Kids [".implode(' ',array_map(fn($i)=>(3+$i*2).' 0 R',range(0,$count-1)))."] /Count $count >>"];
        foreach($pages as $i=>$c){$objPage=3+$i*2;$objContent=$objPage+1;
            // Substitute fixed font resources into the content stream per page.
            $objs[]="<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 $fontRegular 0 R /F2 $fontBold 0 R >> >> /Contents $objContent 0 R >>";
            $objs[]="<< /Length ".strlen($c)." >>\nstream\n$c\nendstream";
        }
        $objs[]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objs[]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $pdf="%PDF-1.4\n";$offs=[0];foreach($objs as $i=>$obj){$offs[]=strlen($pdf);$pdf.=($i+1)." 0 obj\n$obj\nendobj\n";}
        $xref=strlen($pdf);$pdf.="xref\n0 ".(count($objs)+1)."\n0000000000 65535 f \n";
        for($i=1;$i<=count($objs);$i++)$pdf.=sprintf("%010d 00000 n \n",$offs[$i]);
        $pdf.="trailer\n<< /Size ".(count($objs)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        $up=wp_upload_dir();if(!empty($up['error']))wp_send_json_error(['message'=>'Unable to access PDF storage']);
        $dir=trailingslashit($up['basedir']).'site-management-boq';if(!is_dir($dir)&&!wp_mkdir_p($dir))wp_send_json_error(['message'=>'Unable to create PDF directory']);
        $file=sanitize_file_name(($boq['boq_no']??('BOQ-'.$id)).'-'.time().'.pdf');
        if(@file_put_contents(trailingslashit($dir).$file,$pdf,LOCK_EX)===false)wp_send_json_error(['message'=>'Unable to write PDF']);
        wp_send_json_success(['url'=>trailingslashit($up['baseurl']).'site-management-boq/'.$file,'filename'=>$file]);
    }

    public function ajax_boq_list() {
        global $wpdb;$site=(int)($_POST['site_id']??0);$this->boq_access($site);$type=sanitize_key($_POST['type']??'boq_master');$p=$this->prefix;$where='1=1';$args=[];
        if(!current_user_can('manage_options')){$u=$wpdb->get_var($wpdb->prepare("SELECT assigned_sites FROM {$p}users WHERE wp_user_id=%d",get_current_user_id()));$assigned=json_decode($u??'[]',true);if(is_array($assigned)&&count($assigned)){$ids=array_values(array_filter(array_map('intval',$assigned)));if($ids)$where.=' AND b.site_id IN ('.implode(',',$ids).')';}}
        if($site){$where.=' AND b.site_id=%d';$args[]=$site;}
        $search=sanitize_text_field(wp_unslash($_POST['search']??''));if($search){$where.=' AND (b.boq_no LIKE %s OR b.boq_name LIKE %s OR bi.section LIKE %s OR s.site_name LIKE %s OR i.item_name LIKE %s)';$term='%'.$wpdb->esc_like($search).'%';array_push($args,$term,$term,$term,$term,$term);}
        $this->ensure_boq_item_section_column();
        $sql="SELECT b.id,b.boq_no,b.boq_date,b.site_id,bi.section,b.boq_name,b.remarks,s.site_name,bi.id boq_item_id,bi.item_id,i.item_name,u.unit_name,bi.boq_qty FROM {$p}boqs b JOIN {$p}boq_items bi ON bi.boq_id=b.id LEFT JOIN {$p}sites s ON s.id=b.site_id LEFT JOIN {$p}items i ON i.id=bi.item_id LEFT JOIN {$p}units u ON u.id=bi.unit_id WHERE $where ORDER BY b.id DESC,bi.id";
        $rows=$wpdb->get_results($args?$wpdb->prepare($sql,$args):$sql,ARRAY_A);
        // Site/item aggregates are shared across BOQs. Do not double-count or sum different BOQs.
        foreach($rows as &$r){$sid=(int)$r['site_id'];$iid=(int)$r['item_id'];
            $r['requisition_qty']=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ri.approved_qty),0) FROM {$p}material_requisition_items ri JOIN {$p}material_requisitions h ON h.id=ri.requisition_id WHERE h.site_id=%d AND ri.item_id=%d AND LOWER(COALESCE(h.status,'')) NOT IN ('rejected','cancelled','pending_approval')",$sid,$iid));
            $r['site_qty']=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ri.received_qty),0) FROM {$p}site_receipt_items ri JOIN {$p}site_receipts h ON h.id=ri.receipt_id WHERE h.site_id=%d AND ri.item_id=%d AND LOWER(COALESCE(h.status,'')) NOT IN ('cancelled','rejected')",$sid,$iid));
            $r['consumption_qty']=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ci.qty),0) FROM {$p}site_consumption_items ci JOIN {$p}site_consumptions h ON h.id=ci.consumption_id WHERE h.site_id=%d AND ci.item_id=%d AND LOWER(COALESCE(h.status,'')) NOT IN ('cancelled','rejected')",$sid,$iid));
            $r['return_qty']=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ri.qty),0) FROM {$p}material_return_items ri JOIN {$p}material_returns h ON h.id=ri.return_id WHERE h.site_id=%d AND ri.item_id=%d AND LOWER(COALESCE(h.status,'')) NOT IN ('cancelled','rejected')",$sid,$iid));
            $r['boq_name']=$r['boq_no'].' ('.$r['site_name'].')';
            $r['balance']=round((float)$r['boq_qty']-$r['consumption_qty'],2);
        }unset($r);
        wp_send_json_success(['rows'=>$rows]);
    }
    public function ajax_list() {
        $this->verify_ajax(); global $wpdb;
        $key = sanitize_key($_POST['module'] ?? ''); $this->deny_employee_settings($key); $m=$this->module($key);
        if(!$m) wp_send_json_error(['message'=>'Invalid module']);
        $table=$this->prefix.$m['table']; $page=max(1,intval($_POST['page']??1)); $export_all=!empty($_POST['export_all']); $per=$export_all?0:20; $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');

        // Unified Payments view: show Supplier Payments and Contractor Payments together.
        // Contractor payments created from Pending Payment therefore appear here as a new row after every Update.
        if($key==='vendor_payments'){
            $site_id=(int)($_POST['site_id']??0);
            $payment_type=sanitize_key($_POST['payment_type']??'');
            $from=sanitize_text_field($_POST['date_from']??'');
            $to=sanitize_text_field($_POST['date_to']??'');
            $vendor_where="LOWER(COALESCE(vp.status,''))<>'cancelled'";
            $contractor_where="LOWER(COALESCE(cp.status,''))<>'cancelled'";
            $vargs=[];$cargs=[];
            if($site_id){$vendor_where.=' AND vp.site_id=%d';$vargs[]=$site_id;$contractor_where.=' AND cp.site_id=%d';$cargs[]=$site_id;}
            if($payment_type!==''){
                if(in_array($payment_type,['contractor_payment','contractor_advance'],true)){ $vendor_where.=' AND 1=0'; $contractor_where.=" AND COALESCE(cp.payment_type,'bill_payment')=%s";$cargs[]=($payment_type==='contractor_advance'?'advance':'bill_payment'); }
                elseif(strpos($payment_type,'allowance_')===0){$aid=(int)substr($payment_type,10);$vendor_where.=" AND vp.payment_type='employee_allowance' AND vp.allowance_type_id=%d";$vargs[]=$aid;$contractor_where.=' AND 1=0';}
                else { $vendor_where.=' AND COALESCE(vp.payment_type,\'head_office_vendor\')=%s';$vargs[]=$payment_type;$contractor_where.=' AND 1=0'; }
            }
            if($from!==''){$vendor_where.=' AND vp.payment_date>=%s';$vargs[]=$from;$contractor_where.=' AND cp.payment_date>=%s';$cargs[]=$from;}
            if($to!==''){$vendor_where.=' AND vp.payment_date<=%s';$vargs[]=$to;$contractor_where.=' AND cp.payment_date<=%s';$cargs[]=$to;}
            if($search!==''){
                $needle='%'.$wpdb->esc_like($search).'%';
                $vendor_where.=" AND (vp.payment_no LIKE %s OR v.vendor_name LIKE %s OR s.site_name LIKE %s OR eu.name LIKE %s OR at.allowance_type_name LIKE %s OR ss.salary_month LIKE %s OR CAST(vp.amount AS CHAR) LIKE %s OR vp.paid_by LIKE %s OR vp.payment_type LIKE %s)";
                $contractor_where.=" AND (cp.payment_no LIKE %s OR c.contractor_name LIKE %s OR s.site_name LIKE %s OR CAST(cp.amount AS CHAR) LIKE %s OR cp.paid_by LIKE %s)";
                foreach(range(1,9) as $_)$vargs[]=$needle;
                foreach(range(1,5) as $_)$cargs[]=$needle;
            }
            $vendor_sql="SELECT vp.id,'vendor' _source,vp.payment_no,vp.payment_date,COALESCE(vp.payment_type,'head_office_vendor') payment_type,CASE WHEN vp.payment_type='employee_allowance' THEN COALESCE(at.allowance_type_name,'Employee Allowance') WHEN vp.payment_type='salary' THEN 'Salary' ELSE NULL END payment_type_label,CASE WHEN COALESCE(vp.payment_type,'head_office_vendor')='site_fund' THEN CONCAT(COALESCE(s.site_name,'Site'),' - Petty Cash') WHEN vp.payment_type IN ('employee_allowance','salary') THEN eu.name ELSE v.vendor_name END party_name,s.site_name,vp.amount,vp.paid_by,(SELECT d.file_url FROM {$this->prefix}documents d WHERE d.module='vendor_payments' AND d.reference_id=vp.id AND d.document_type='Payment Document' ORDER BY d.id DESC LIMIT 1) document_url,vp.created_at FROM {$this->prefix}vendor_payments vp LEFT JOIN {$this->prefix}vendors v ON v.id=vp.vendor_id LEFT JOIN {$this->prefix}sites s ON s.id=vp.site_id LEFT JOIN {$this->prefix}users eu ON eu.id=vp.employee_id LEFT JOIN {$this->prefix}allowance_types at ON at.id=vp.allowance_type_id LEFT JOIN {$this->prefix}salary_slips ss ON ss.id=vp.salary_slip_id WHERE $vendor_where";
            $contractor_sql="SELECT cp.id,'contractor' _source,cp.payment_no,cp.payment_date,CASE WHEN COALESCE(cp.payment_type,'bill_payment')='advance' THEN 'contractor_advance' ELSE 'contractor_payment' END payment_type,CASE WHEN COALESCE(cp.payment_type,'bill_payment')='advance' THEN 'Labour Contractor Advance' ELSE 'Labour Contractor Payment' END payment_type_label,c.contractor_name party_name,s.site_name,cp.amount,cp.paid_by,(SELECT d.file_url FROM {$this->prefix}documents d WHERE d.module='contractor_payments' AND d.reference_id=cp.id AND d.document_type='Payment Document' ORDER BY d.id DESC LIMIT 1) document_url,cp.created_at FROM {$this->prefix}contractor_payments cp LEFT JOIN {$this->prefix}labour_contractors c ON c.id=cp.contractor_id LEFT JOIN {$this->prefix}sites s ON s.id=cp.site_id WHERE $contractor_where";
            if($vargs)$vendor_sql=$wpdb->prepare($vendor_sql,$vargs);
            if($cargs)$contractor_sql=$wpdb->prepare($contractor_sql,$cargs);
            $union="$vendor_sql UNION ALL $contractor_sql";
            $total=(int)$wpdb->get_var("SELECT COUNT(*) FROM ($union) p");
            $dataSql="SELECT * FROM ($union) p ORDER BY payment_date DESC, created_at DESC, id DESC";
            if(!$export_all)$dataSql.=$wpdb->prepare(" LIMIT %d OFFSET %d",20,$offset);
            $rows=$wpdb->get_results($dataSql,ARRAY_A);
            foreach($rows as &$lr){
                if(($lr['_source']??'')==='vendor'){
                    if(in_array(($lr['payment_type']??''),['employee_allowance','salary'],true)){
                        $linked=(($lr['payment_type']??'')==='salary')?'Salary':'Employee Allowance';$lr['_can_edit']=0;$lr['_can_delete']=0;$lr['_edit_message']=$linked.' payment is linked to its source record.';$lr['_delete_message']=$linked.' payment is linked to its source record.';
                    }else{
                        $g=$this->record_guard('vendor_payments',(int)$lr['id']);
                        $lr['_can_edit']=$g['can_edit']?1:0;$lr['_can_delete']=$g['can_delete']?1:0;$lr['_edit_message']=$g['edit_message'];$lr['_delete_message']=$g['delete_message'];
                    }
                }else{
                    $lr['_can_edit']=0;$lr['_can_delete']=0;$lr['_edit_message']='Contractor payment was created from Pending Payment.';$lr['_delete_message']='Contractor payment was created from Pending Payment.';
                }
            }unset($lr);
            wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,ceil($total/20))]);
        }
        $where='1=1'; $args=[];
        if($key==='site_fund_allocations') $where.=" AND COALESCE(purpose,'')='Opening Petty Cash'";
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){
            $has_site=(bool)$wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'site_id'");
            if($has_site){ $ph=implode(',',array_fill(0,count($site_scope),'%d')); $where.=" AND `site_id` IN ($ph)"; foreach($site_scope as $sid)$args[]=$sid; }
        }
        if($search!=='') {
            $needle='%'.$wpdb->esc_like($search).'%';
            $cols=$wpdb->get_results("DESCRIBE $table",ARRAY_A); $colmap=[]; foreach($cols as $c) $colmap[$c['Field']]=$c['Type'];
            $visible=json_decode(wp_unslash($_POST['visible_columns']??'[]'),true); if(!is_array($visible)) $visible=[];
            $parts=[];
            $refmap=[
                'site_id'=>['sites','site_name'],'customer_id'=>['customers','customer_name'],'vendor_id'=>['vendors','vendor_name'],
                'contractor_id'=>['labour_contractors','contractor_name'],'warehouse_id'=>['warehouses','warehouse_name'],
                'item_id'=>['items','item_name'],'unit_id'=>['units','unit_name'],'state_id'=>['states','state_name'],
                'designation_id'=>['designations','designation_name'],'requisition_id'=>['material_requisitions','requisition_no'],
                'dispatch_id'=>['material_dispatches','dispatch_no'],'inward_id'=>['material_inwards','inward_no'],
                'contractor_bill_id'=>['contractor_bills','bill_no'],'gst_invoice_id'=>['gst_invoices','invoice_no']
            ];
            foreach($visible as $vc){
                $f=sanitize_key($vc['key']??''); if(!$f || !isset($colmap[$f])) continue; $type=(string)($vc['type']??'text');
                if($type==='date' || stripos($colmap[$f],'date')!==false){$parts[]="DATE_FORMAT(`$f`,'%%d/%%m/%%Y') LIKE %s";$args[]=$needle;}
                else {$parts[]="CAST(`$f` AS CHAR) LIKE %s";$args[]=$needle;}
                $refType=''; if(strpos($type,'ref:')===0) $refType=substr($type,4);
                $ref=$refmap[$f]??null;
                if($f==='category_id') $ref=($key==='items')?['item_categories','category_name']:(($key==='site_expenses')?['expense_categories','category_name']:null);
                if(in_array($f,['site_manager_user_id','site_supervisor_user_id','manager_user_id','requested_by','provided_to_user_id','received_by','return_by'],true)) $ref=['users','name'];
                if($ref){[$rt,$label]=$ref;$parts[]="EXISTS (SELECT 1 FROM {$this->prefix}{$rt} sr WHERE sr.id=`$f` AND CAST(sr.`$label` AS CHAR) LIKE %s)";$args[]=$needle;}
            }
            // Fallback for older clients: search every stored column, including numbers and dates.
            if(!$parts){foreach($colmap as $f=>$type){if(stripos($type,'date')!==false){$parts[]="DATE_FORMAT(`$f`,'%%d/%%m/%%Y') LIKE %s";}else{$parts[]="CAST(`$f` AS CHAR) LIKE %s";}$args[]=$needle;}}
            if($parts) $where.=' AND ('.implode(' OR ',$parts).')';
        }
        foreach(['site_id','vendor_id','contractor_id','customer_id','warehouse_id','status','inward_type'] as $f){ if(isset($_POST[$f]) && $_POST[$f]!==''){ $where.=" AND `$f`=%s"; $args[]=sanitize_text_field($_POST[$f]); } }
        if($key==='material_inwards' && !empty($_POST['invoice_missing'])){
            $where .= " AND NOT EXISTS (SELECT 1 FROM {$this->prefix}documents d WHERE d.module='material_inwards' AND d.reference_id=`{$m['table']}`.`id` AND d.document_type='Supplier Invoice')";
        }
        // Pending Material Receipt must never show a Return that has already been received through Material Inward.
        // This NOT EXISTS check also repairs the view for legacy rows whose return status was not updated correctly.
        if($key==='material_returns' && (string)($_POST['status']??'')==='submitted'){
            $where .= " AND NOT EXISTS (SELECT 1 FROM {$this->prefix}material_inwards mi WHERE mi.return_id=`{$m['table']}`.`id` AND LOWER(COALESCE(mi.inward_type,''))='return' AND LOWER(COALESCE(mi.status,''))<>'cancelled')";
        }
        if(!empty($_POST['date_from'])){ foreach(['attendance_date','requisition_date','inward_date','dispatch_date','receipt_date','consumption_date','return_date','opening_date','fund_date','expense_date','receipt_date','payment_date','bill_date','invoice_date'] as $df){ if($wpdb->get_var("SHOW COLUMNS FROM $table LIKE '$df'")){ $where.=" AND `$df` >= %s"; $args[]=sanitize_text_field($_POST['date_from']); break; } } }
        if(!empty($_POST['date_to'])){ foreach(['attendance_date','requisition_date','inward_date','dispatch_date','receipt_date','consumption_date','return_date','opening_date','fund_date','expense_date','receipt_date','payment_date','bill_date','invoice_date'] as $df){ if($wpdb->get_var("SHOW COLUMNS FROM $table LIKE '$df'")){ $where.=" AND `$df` <= %s"; $args[]=sanitize_text_field($_POST['date_to']); break; } } }
        $countSql="SELECT COUNT(*) FROM $table WHERE $where"; $dataSql="SELECT * FROM $table WHERE $where ORDER BY id DESC"; if(!$export_all) $dataSql.=" LIMIT 20 OFFSET $offset";
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);} 
        $total=(int)$wpdb->get_var($countSql); $rows=$wpdb->get_results($dataSql,ARRAY_A);
        if($key==='labour_attendance'){
            $updater_names=[];
            foreach($rows as &$attendance){
                $updater=(int)($attendance['updated_by']?:($attendance['created_by']??0));
                if(!array_key_exists($updater,$updater_names)){
                    $name=$updater?(string)$wpdb->get_var($wpdb->prepare("SELECT name FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$updater)):'';
                    if($name===''&&$updater){$wp_user=get_userdata($updater);if($wp_user)$name=$wp_user->display_name;}
                    $updater_names[$updater]=$name;
                }
                $attendance['updated_by_name']=$updater_names[$updater];
            }unset($attendance);
        }

        if($key==='site_consumptions'){
            foreach($rows as &$cr){
                $created_by=(int)($cr['created_by']??0);$cr['created_by_name']='';
                if($created_by){
                    $cr['created_by_name']=(string)$wpdb->get_var($wpdb->prepare("SELECT name FROM {$this->prefix}users WHERE wp_user_id=%d OR id=%d ORDER BY (wp_user_id=%d) DESC LIMIT 1",$created_by,$created_by,$created_by));
                    if($cr['created_by_name']===''){ $wu=get_userdata($created_by); if($wu)$cr['created_by_name']=$wu->display_name; }
                }
            }unset($cr);
        }
        foreach($rows as &$lr){
            if(!empty($lr['id'])){
                $g=$this->record_guard($key,(int)$lr['id']);
                $lr['_can_edit']=$g['can_edit']?1:0;
                $lr['_can_delete']=$g['can_delete']?1:0;
                $lr['_edit_message']=$g['edit_message'];
                $lr['_delete_message']=$g['delete_message'];
        if($key==='contractor_bills'){
                    $lr['document_url']=$wpdb->get_var($wpdb->prepare("SELECT file_url FROM {$this->prefix}documents WHERE module='contractor_bills' AND reference_id=%d ORDER BY id DESC LIMIT 1",(int)$lr['id']));
                    $lr['paid_amount']=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$this->prefix}contractor_payments WHERE contractor_bill_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",(int)$lr['id']));
                    $lr['advance_adjustment']=(float)($lr['advance_adjustment']??0);$lr['pending_amount']=max(0,(float)($lr['total_amount']??0)-(float)$lr['advance_adjustment']-(float)$lr['paid_amount']);
                }
                if($key==='material_dispatches'){
                    $lr['dispatch_photo_url']=$wpdb->get_var($wpdb->prepare("SELECT file_url FROM {$this->prefix}documents WHERE module='material_dispatches' AND reference_id=%d AND document_type='Dispatch Photo' ORDER BY id DESC LIMIT 1",(int)$lr['id']));
                }
                if($key==='material_inwards'){
                    $lr['gst_type']=!empty($lr['vendor_id'])?$wpdb->get_var($wpdb->prepare("SELECT gst_type FROM {$this->prefix}vendors WHERE id=%d",(int)$lr['vendor_id'])):null;
                    $lr['invoice_url']=$wpdb->get_var($wpdb->prepare("SELECT file_url FROM {$this->prefix}documents WHERE module='material_inwards' AND reference_id=%d AND document_type='Supplier Invoice' ORDER BY id DESC LIMIT 1",(int)$lr['id']));
                }
            }
        } unset($lr);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,ceil($total/20))]);
    }

    public function ajax_purchase_items_register() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $vendor_id=(int)($_POST['vendor_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where="LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase'";
        $args=[];
        if($date_from!==''){$where.=' AND mi.inward_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND mi.inward_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND mi.site_id=%d';$args[]=$site_id;}
        if($vendor_id){$where.=' AND mi.vendor_id=%d';$args[]=$vendor_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND mii.item_id=%d';$args[]=$item_id;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (mi.inward_no LIKE %s OR COALESCE(mi.supplier_invoice_no,'') LIKE %s OR COALESCE(v.vendor_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s)";
            for($n=0;$n<6;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}material_inward_items mii
            INNER JOIN {$p}material_inwards mi ON mi.id=mii.inward_id
            LEFT JOIN {$p}vendors v ON v.id=mi.vendor_id
            LEFT JOIN {$p}items i ON i.id=mii.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=mii.unit_id
            LEFT JOIN {$p}users ap ON ap.id=mi.approved_by
            LEFT JOIN {$p}users apwp ON apwp.wp_user_id=mi.approved_by
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT mii.id,mi.id inward_id,mi.inward_no,mi.inward_date,mi.supplier_invoice_no,mi.vendor_id,v.vendor_name,mi.approved_by,COALESCE(ap.name,apwp.name) approved_by_name,i.category_id,c.category_name,mii.item_id,i.item_name,mii.item_price master_price,mii.qty,mii.approved_qty,mii.unit_id,u.unit_name,mii.rate,mii.taxable_amount,mii.gst_rate,mii.cgst_amount,mii.sgst_amount,mii.igst_amount,mii.total_amount $from ORDER BY mi.inward_date DESC,mi.id DESC,mii.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }

    public function ajax_material_requisition_item() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where='1=1';$args=[];
        if($date_from!==''){$where.=' AND r.requisition_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND r.requisition_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND r.site_id=%d';$args[]=$site_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND ri.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND r.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (r.requisition_no LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s OR COALESCE(ri.remarks,'') LIKE %s OR COALESCE(r.status,'') LIKE %s)";
            for($n=0;$n<7;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}material_requisition_items ri
            INNER JOIN {$p}material_requisitions r ON r.id=ri.requisition_id
            LEFT JOIN {$p}sites s ON s.id=r.site_id
            LEFT JOIN {$p}users ru ON ru.id=r.requested_by
            LEFT JOIN {$p}users au ON au.id=r.approved_by
            LEFT JOIN {$p}users auwp ON auwp.wp_user_id=r.approved_by
            LEFT JOIN {$p}items i ON i.id=ri.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=ri.unit_id
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT ri.id,r.id requisition_id,r.requisition_no,r.requisition_date,r.required_date,r.site_id,s.site_name,r.requested_by,ru.name requested_by_name,r.approved_by,COALESCE(au.name,auwp.name,'') approved_by_name,r.status,i.category_id,c.category_name,ri.item_id,i.item_name,ri.requested_qty,ri.approved_qty,ri.rejected_qty,ri.unit_id,u.unit_name,ri.remarks $from ORDER BY r.requisition_date DESC,r.id DESC,ri.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);$rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }


    public function ajax_dispatch_items_register() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where='1=1';$args=[];
        if($date_from!==''){$where.=' AND d.dispatch_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND d.dispatch_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND d.site_id=%d';$args[]=$site_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND di.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND d.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (d.dispatch_no LIKE %s OR COALESCE(r.requisition_no,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(w.warehouse_name,'') LIKE %s OR COALESCE(fs.site_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s OR COALESCE(d.status,'') LIKE %s OR COALESCE(di.remarks,'') LIKE %s OR COALESCE(d.vehicle_no,'') LIKE %s OR COALESCE(d.driver_name,'') LIKE %s OR COALESCE(d.driver_mobile,'') LIKE %s OR COALESCE(d.courier_no,'') LIKE %s OR COALESCE(d.courier_name,'') LIKE %s)";
            for($n=0;$n<15;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}material_dispatch_items di
            INNER JOIN {$p}material_dispatches d ON d.id=di.dispatch_id
            LEFT JOIN {$p}material_requisitions r ON r.id=d.requisition_id
            LEFT JOIN {$p}sites s ON s.id=d.site_id
            LEFT JOIN {$p}sites fs ON fs.id=d.from_site_id
            LEFT JOIN {$p}warehouses w ON w.id=d.warehouse_id
            LEFT JOIN {$p}items i ON i.id=di.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=di.unit_id
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT di.id,d.id dispatch_id,d.dispatch_no,d.dispatch_date,d.status,d.site_id,s.site_name,d.warehouse_id,w.warehouse_name,d.from_site_id,fs.site_name from_site_name,d.requisition_id,r.requisition_no,d.vehicle_no,d.driver_name,d.driver_mobile,d.courier_no,d.courier_name,i.category_id,c.category_name,di.item_id,i.item_name,di.qty,di.unit_id,u.unit_name,di.rate,di.remarks $from ORDER BY d.dispatch_date DESC,d.id DESC,di.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }


    public function ajax_site_material_item_receipt() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where="LOWER(COALESCE(sr.status,''))<>'cancelled'";$args=[];
        if($date_from!==''){$where.=' AND sr.receipt_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND sr.receipt_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND sr.site_id=%d';$args[]=$site_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND sri.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND sr.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (sr.receipt_no LIKE %s OR COALESCE(d.dispatch_no,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s OR COALESCE(usr.name,'') LIKE %s)";
            for($n=0;$n<7;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}site_receipt_items sri
            INNER JOIN {$p}site_receipts sr ON sr.id=sri.receipt_id
            LEFT JOIN {$p}material_dispatches d ON d.id=sr.dispatch_id
            LEFT JOIN {$p}sites s ON s.id=sr.site_id
            LEFT JOIN {$p}items i ON i.id=sri.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=sri.unit_id
            LEFT JOIN {$p}users usr ON usr.id=sr.received_by
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT sri.id,sr.id receipt_id,sr.receipt_no,sr.receipt_date,sr.dispatch_id,d.dispatch_no,sr.site_id,s.site_name,sr.received_by,usr.name received_by_name,i.category_id,c.category_name,sri.item_id,i.item_name,sri.dispatch_qty,sri.received_qty,sri.damaged_qty,sri.short_qty,sri.unit_id,u.unit_name $from ORDER BY sr.receipt_date DESC,sr.id DESC,sri.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }


    public function ajax_consumption_items_register() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where="LOWER(COALESCE(sc.status,''))<>'cancelled'";$args=[];
        if($date_from!==''){$where.=' AND sc.consumption_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND sc.consumption_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND sc.site_id=%d';$args[]=$site_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND sci.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND sc.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (sc.consumption_no LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s OR COALESCE(sci.remarks,'') LIKE %s OR COALESCE((SELECT name FROM {$p}users cu WHERE cu.wp_user_id=sc.created_by OR cu.id=sc.created_by ORDER BY (cu.wp_user_id=sc.created_by) DESC LIMIT 1),'') LIKE %s)";
            for($n=0;$n<7;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}site_consumption_items sci
            INNER JOIN {$p}site_consumptions sc ON sc.id=sci.consumption_id
            LEFT JOIN {$p}sites s ON s.id=sc.site_id
            LEFT JOIN {$p}items i ON i.id=sci.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=sci.unit_id
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT sci.id,sc.id consumption_id,sc.consumption_no,sc.consumption_date,sc.site_id,s.site_name,sc.status,sc.created_by,COALESCE((SELECT name FROM {$p}users cu WHERE cu.wp_user_id=sc.created_by OR cu.id=sc.created_by ORDER BY (cu.wp_user_id=sc.created_by) DESC LIMIT 1),'') created_by_name,i.category_id,c.category_name,sci.item_id,i.item_name,sci.qty,sci.unit_id,u.unit_name,sci.remarks,sci.document_url,sci.document_name $from ORDER BY sc.consumption_date DESC,sc.id DESC,sci.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }


    public function ajax_return_receipt_master() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $warehouse_id=(int)($_POST['warehouse_id']??0);
        $where="LOWER(COALESCE(mi.inward_type,''))='return' AND LOWER(COALESCE(mi.status,''))<>'cancelled'";
        $args=[];
        if($date_from!==''){$where.=' AND mi.inward_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND mi.inward_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND mi.site_id=%d';$args[]=$site_id;}
        if($warehouse_id){$where.=' AND mi.warehouse_id=%d';$args[]=$warehouse_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND mi.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (mi.inward_no LIKE %s OR COALESCE(mr.return_no,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(w.warehouse_name,'') LIKE %s OR COALESCE(usr.name,'') LIKE %s)";
            for($n=0;$n<5;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}material_inwards mi
            LEFT JOIN {$p}material_returns mr ON mr.id=mi.return_id
            LEFT JOIN {$p}sites s ON s.id=mi.site_id
            LEFT JOIN {$p}warehouses w ON w.id=mi.warehouse_id
            LEFT JOIN {$p}users usr ON usr.id=mi.received_by
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT mi.id,mi.inward_no,mi.inward_date,mi.return_id,mr.return_no,mi.site_id,s.site_name,mi.warehouse_id,w.warehouse_name,mi.received_by,COALESCE(usr.name,'') received_by_name,mi.status,
            COALESCE((SELECT SUM(mii.qty) FROM {$p}material_inward_items mii WHERE mii.inward_id=mi.id),0) received_qty
            $from ORDER BY mi.inward_date DESC,mi.id DESC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }

    public function ajax_return_receipt_item_master() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $warehouse_id=(int)($_POST['warehouse_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where="LOWER(COALESCE(mi.inward_type,''))='return' AND LOWER(COALESCE(mi.status,''))<>'cancelled'";
        $args=[];
        if($date_from!==''){$where.=' AND mi.inward_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND mi.inward_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND mi.site_id=%d';$args[]=$site_id;}
        if($warehouse_id){$where.=' AND mi.warehouse_id=%d';$args[]=$warehouse_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND mii.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND mi.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (mi.inward_no LIKE %s OR COALESCE(mr.return_no,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(w.warehouse_name,'') LIKE %s OR COALESCE(usr.name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s)";
            for($n=0;$n<8;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}material_inward_items mii
            INNER JOIN {$p}material_inwards mi ON mi.id=mii.inward_id
            LEFT JOIN {$p}material_returns mr ON mr.id=mi.return_id
            LEFT JOIN {$p}sites s ON s.id=mi.site_id
            LEFT JOIN {$p}warehouses w ON w.id=mi.warehouse_id
            LEFT JOIN {$p}users usr ON usr.id=mi.received_by
            LEFT JOIN {$p}items i ON i.id=mii.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=mii.unit_id
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT mii.id,mi.id inward_id,mi.inward_no,mi.inward_date,mi.return_id,mr.return_no,mi.site_id,s.site_name,mi.warehouse_id,w.warehouse_name,mi.received_by,COALESCE(usr.name,'') received_by_name,
            i.category_id,c.category_name,mii.item_id,i.item_name,COALESCE(mii.return_qty,0) return_qty,mii.qty received_qty,mii.unit_id,u.unit_name
            $from ORDER BY mi.inward_date DESC,mi.id DESC,mii.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }

    public function ajax_material_returns_item_master() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $per=$export_all?0:20;
        $offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $where="LOWER(COALESCE(mr.status,''))<>'cancelled'";$args=[];
        if($date_from!==''){$where.=' AND mr.return_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND mr.return_date<=%s';$args[]=$date_to;}
        if($site_id){$where.=' AND mr.site_id=%d';$args[]=$site_id;}
        if($category_id){$where.=' AND i.category_id=%d';$args[]=$category_id;}
        if($item_id){$where.=' AND mri.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND mr.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (mr.return_no LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(w.warehouse_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s OR COALESCE(mri.remarks,'') LIKE %s OR COALESCE((SELECT name FROM {$p}users ru WHERE ru.wp_user_id=mr.return_by OR ru.id=mr.return_by ORDER BY (ru.wp_user_id=mr.return_by) DESC LIMIT 1),'') LIKE %s OR COALESCE((SELECT u2.name FROM {$p}material_inwards mi LEFT JOIN {$p}users u2 ON (u2.id=mi.received_by OR u2.wp_user_id=mi.received_by) WHERE mi.return_id=mr.id AND LOWER(COALESCE(mi.inward_type,''))='return' AND LOWER(COALESCE(mi.status,''))<>'cancelled' ORDER BY mi.id DESC LIMIT 1),'') LIKE %s)";
            for($n=0;$n<9;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}material_return_items mri
            INNER JOIN {$p}material_returns mr ON mr.id=mri.return_id
            LEFT JOIN {$p}sites s ON s.id=mr.site_id
            LEFT JOIN {$p}warehouses w ON w.id=mr.warehouse_id
            LEFT JOIN {$p}items i ON i.id=mri.item_id
            LEFT JOIN {$p}item_categories c ON c.id=i.category_id
            LEFT JOIN {$p}units u ON u.id=mri.unit_id
            WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT mri.id,mr.id return_id,mr.return_no,mr.return_date,mr.site_id,s.site_name,mr.warehouse_id,w.warehouse_name,mr.status,mr.return_by,
            COALESCE((SELECT name FROM {$p}users ru WHERE ru.wp_user_id=mr.return_by OR ru.id=mr.return_by ORDER BY (ru.wp_user_id=mr.return_by) DESC LIMIT 1),'') return_by_name,
            COALESCE((SELECT u2.name FROM {$p}material_inwards mi LEFT JOIN {$p}users u2 ON (u2.id=mi.received_by OR u2.wp_user_id=mi.received_by) WHERE mi.return_id=mr.id AND LOWER(COALESCE(mi.inward_type,''))='return' AND LOWER(COALESCE(mi.status,''))<>'cancelled' ORDER BY mi.id DESC LIMIT 1),'') received_by_name,
            i.category_id,c.category_name,mri.item_id,i.item_name,mri.qty,mri.unit_id,u.unit_name,mri.rate,mri.remarks $from ORDER BY mr.return_date DESC,mr.id DESC,mri.id ASC";
        if(!$export_all)$dataSql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset);
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }


    public function ajax_daily_outward_summary() {
        $this->verify_ajax();
        global $wpdb; $p=$this->prefix;
        $from=sanitize_text_field($_POST['date_from']??'');
        $to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $item_id=(int)($_POST['item_id']??0);
        $search=sanitize_text_field($_POST['search']??'');
        if($from==='') $from=current_time('Y-m-d');
        if($to==='') $to=$from;
        $where="LOWER(COALESCE(d.status,''))<>'cancelled' AND d.dispatch_date>=%s AND d.dispatch_date<=%s";
        $args=[$from,$to];
        if($site_id){$where.=' AND d.site_id=%d';$args[]=$site_id;}
        if($item_id){$where.=' AND di.item_id=%d';$args[]=$item_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND d.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (d.dispatch_no LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(i.item_name,'') LIKE %s OR COALESCE(u.unit_name,'') LIKE %s)";
            for($n=0;$n<4;$n++)$args[]=$needle;
        }
        $sql="SELECT d.id dispatch_id,d.dispatch_no,d.dispatch_date,d.site_id,s.site_name,di.item_id,i.item_name,di.qty,di.unit_id,u.unit_name
              FROM {$p}material_dispatches d
              INNER JOIN {$p}material_dispatch_items di ON di.dispatch_id=d.id
              LEFT JOIN {$p}sites s ON s.id=d.site_id
              LEFT JOIN {$p}items i ON i.id=di.item_id
              LEFT JOIN {$p}units u ON u.id=di.unit_id
              WHERE $where
              ORDER BY s.site_name ASC,d.dispatch_date ASC,d.id ASC,di.id ASC";
        $sql=$wpdb->prepare($sql,$args);
        wp_send_json_success(['rows'=>$wpdb->get_results($sql,ARRAY_A),'date_from'=>$from,'date_to'=>$to]);
    }


    public function ajax_daily_expense_summary() {
        $this->verify_ajax();
        global $wpdb; $p=$this->prefix;
        $from=sanitize_text_field($_POST['date_from']??'');
        $to=sanitize_text_field($_POST['date_to']??'');
        $site_id=(int)($_POST['site_id']??0);
        $category_id=(int)($_POST['category_id']??0);
        $vendor_id=(int)($_POST['vendor_id']??0);
        $search=sanitize_text_field($_POST['search']??'');
        if($from==='') $from=current_time('Y-m-d');
        if($to==='') $to=$from;
        $where="LOWER(COALESCE(e.status,''))<>'cancelled' AND e.expense_date>=%s AND e.expense_date<=%s";
        $args=[$from,$to];
        if($site_id){$where.=' AND e.site_id=%d';$args[]=$site_id;}
        if($category_id){$where.=' AND e.category_id=%d';$args[]=$category_id;}
        if($vendor_id){$where.=' AND e.vendor_id=%d';$args[]=$vendor_id;}
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND e.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (e.expense_no LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(c.category_name,'') LIKE %s OR COALESCE(v.vendor_name,'') LIKE %s OR COALESCE(e.paid_to,'') LIKE %s OR COALESCE(e.particulars,'') LIKE %s OR COALESCE(e.payment_mode,'') LIKE %s OR COALESCE(e.transaction_reference,'') LIKE %s OR COALESCE(e.remarks,'') LIKE %s)";
            for($n=0;$n<9;$n++)$args[]=$needle;
        }
        $sql="SELECT e.id,e.expense_no,e.expense_date,e.site_id,s.site_name,e.category_id,c.category_name,e.vendor_id,v.vendor_name,e.paid_to,e.particulars,e.amount,e.payment_mode,e.transaction_reference,e.remarks
              FROM {$p}site_expenses e
              LEFT JOIN {$p}sites s ON s.id=e.site_id
              LEFT JOIN {$p}expense_categories c ON c.id=e.category_id
              LEFT JOIN {$p}vendors v ON v.id=e.vendor_id
              WHERE $where
              ORDER BY s.site_name ASC,e.expense_date ASC,e.id ASC";
        $sql=$wpdb->prepare($sql,$args);
        wp_send_json_success(['rows'=>$wpdb->get_results($sql,ARRAY_A),'date_from'=>$from,'date_to'=>$to]);
    }

    public function ajax_pending_site_receive_challans() {
        $this->verify_ajax();
        global $wpdb; $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $offset=($page-1)*20;
        $search=sanitize_text_field($_POST['search']??'');
        $where="LOWER(COALESCE(sr.status,'received'))<>'cancelled' AND NOT EXISTS (
            SELECT 1 FROM {$p}documents dc
            WHERE dc.module='site_receipts' AND dc.reference_id=sr.id
              AND LOWER(TRIM(COALESCE(dc.document_type,'')))='receiving challan'
              AND TRIM(COALESCE(dc.file_url,''))<>''
        )";
        $args=[];
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND sr.site_id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (sr.receipt_no LIKE %s OR COALESCE(d.dispatch_no,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s)";
            for($n=0;$n<3;$n++)$args[]=$needle;
        }
        $from=" FROM {$p}site_receipts sr LEFT JOIN {$p}material_dispatches d ON d.id=sr.dispatch_id LEFT JOIN {$p}sites s ON s.id=sr.site_id WHERE $where";
        $count="SELECT COUNT(*) $from";
        $data="SELECT sr.id,sr.receipt_no,sr.receipt_date,sr.dispatch_id,d.dispatch_no,sr.site_id,s.site_name $from ORDER BY sr.receipt_date DESC,sr.id DESC LIMIT 20 OFFSET %d";
        if($args){$count=$wpdb->prepare($count,$args);$prepared_args=array_merge($args,[$offset]);$data=$wpdb->prepare($data,$prepared_args);}else{$data=$wpdb->prepare($data,$offset);}
        $total=(int)$wpdb->get_var($count);
        $rows=$wpdb->get_results($data,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/20))]);
    }


    public function ajax_pending_material_returns() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $search=sanitize_text_field($_POST['search']??'');
        $where="LOWER(COALESCE(r.status,'')) NOT IN ('received','cancelled')";
        $args=[];
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){
            $ph=implode(',',array_fill(0,count($site_scope),'%d'));
            $where.=" AND r.site_id IN ($ph)";
            foreach($site_scope as $sid)$args[]=(int)$sid;
        }

        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (r.return_no LIKE %s OR DATE_FORMAT(r.return_date,'%%d/%%m/%%Y') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(w.warehouse_name,'') LIKE %s OR COALESCE(u.name,'') LIKE %s)";
            for($i=0;$i<5;$i++)$args[]=$needle;
        }
        $sql="SELECT r.id,r.return_no,r.return_date,r.site_id,r.warehouse_id,r.return_by,r.status,s.site_name,w.warehouse_name,u.name return_by_name,COUNT(ri.id) item_count
              FROM {$p}material_returns r
              LEFT JOIN {$p}sites s ON s.id=r.site_id
              LEFT JOIN {$p}warehouses w ON w.id=r.warehouse_id
              LEFT JOIN {$p}users u ON u.id=r.return_by
              LEFT JOIN {$p}material_return_items ri ON ri.return_id=r.id
              WHERE $where
              GROUP BY r.id
              ORDER BY r.id DESC";
        if($args)$sql=$wpdb->prepare($sql,$args);
        $rows=$wpdb->get_results($sql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_pending_invoice_copy() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $export_all=!empty($_POST['export_all']);
        $offset=($page-1)*20;
        $search=sanitize_text_field($_POST['search']??'');
        $where="LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase'";
        $args=[];

        // A purchase is pending only when there is no actual Supplier Invoice file uploaded.
        // Ignore empty/stale document rows so older purchases are still shown correctly.
        $where .= " AND NOT EXISTS (SELECT 1 FROM {$p}documents d WHERE d.module='material_inwards' AND d.reference_id=mi.id AND LOWER(TRIM(COALESCE(d.document_type,'')))='supplier invoice' AND TRIM(COALESCE(d.file_url,''))<>'')";

        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){
            $ph=implode(',',array_fill(0,count($site_scope),'%d'));
            $where.=" AND mi.site_id IN ($ph)";
            foreach($site_scope as $sid)$args[]=(int)$sid;
        }
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (mi.inward_no LIKE %s OR DATE_FORMAT(mi.inward_date,'%%d/%%m/%%Y') LIKE %s OR COALESCE(v.vendor_name,'') LIKE %s OR COALESCE(mi.supplier_invoice_no,'') LIKE %s OR CAST(mi.total_amount AS CHAR) LIKE %s)";
            foreach(range(1,5) as $_)$args[]=$needle;
        }

        $from=" FROM {$p}material_inwards mi LEFT JOIN {$p}vendors v ON v.id=mi.vendor_id WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT mi.id,mi.inward_no,mi.inward_date,mi.vendor_id,v.vendor_name,v.gst_type,mi.supplier_invoice_no,mi.bill_type,mi.total_amount $from ORDER BY mi.inward_date DESC,mi.id DESC".($export_all?"":" LIMIT 20 OFFSET $offset");
        if($args){
            $countSql=$wpdb->prepare($countSql,$args);
            $dataSql=$wpdb->prepare($dataSql,$args);
        }
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }


    public function ajax_pending_contractor_bill_copy() {
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));
        $offset=($page-1)*20;
        $search=sanitize_text_field($_POST['search']??'');
        $where="LOWER(COALESCE(b.status,''))<>'cancelled'";
        $args=[];
        $where .= " AND NOT EXISTS (SELECT 1 FROM {$p}documents d WHERE d.module='contractor_bills' AND d.reference_id=b.id AND TRIM(COALESCE(d.file_url,''))<>'')";
        $site_scope=$this->employee_site_scope();
        if($site_scope!==null && $site_scope){
            $ph=implode(',',array_fill(0,count($site_scope),'%d'));
            $where.=" AND b.site_id IN ($ph)";
            foreach($site_scope as $sid)$args[]=(int)$sid;
        }
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (b.bill_no LIKE %s OR DATE_FORMAT(b.bill_date,'%%d/%%m/%%Y') LIKE %s OR COALESCE(c.contractor_name,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(b.bill_reference,'') LIKE %s OR CAST(b.total_amount AS CHAR) LIKE %s)";
            foreach(range(1,6) as $_)$args[]=$needle;
        }
        $from=" FROM {$p}contractor_bills b LEFT JOIN {$p}labour_contractors c ON c.id=b.contractor_id LEFT JOIN {$p}sites s ON s.id=b.site_id WHERE $where";
        $countSql="SELECT COUNT(*) $from";
        $dataSql="SELECT b.id,b.bill_no,b.bill_date,b.bill_reference,b.contractor_id,c.contractor_name,b.site_id,s.site_name,b.total_amount $from ORDER BY b.bill_date DESC,b.id DESC LIMIT 20 OFFSET $offset";
        if($args){$countSql=$wpdb->prepare($countSql,$args);$dataSql=$wpdb->prepare($dataSql,$args);}
        $total=(int)$wpdb->get_var($countSql);
        $rows=$wpdb->get_results($dataSql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>max(1,(int)ceil($total/20))]);
    }

    public function ajax_get() {
        $this->verify_ajax(); global $wpdb;
        $key=sanitize_key($_POST['module']??''); $this->deny_employee_settings($key); $id=intval($_POST['id']??0); $m=$this->module($key);
        if(!$m||!$id) wp_send_json_error(['message'=>'Invalid request']);
        $row=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}{$m['table']} WHERE id=%d",$id),ARRAY_A);
        if(!$row) wp_send_json_error(['message'=>'Record not found']);
        if(array_key_exists('site_id',$row)) $this->enforce_employee_site_value((int)$row['site_id']);
        if($key==='labour_contractors'){
            $row['_employees']=$this->ops_admin()?$wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->prefix}contractor_employees WHERE contractor_id=%d AND status='active' ORDER BY id",$id),ARRAY_A):[];
        }
        if($key==='site_consumptions'){
            $created_by=(int)($row['created_by']??0);
            $row['created_by_name']='';
            if($created_by){
                $row['created_by_name']=(string)$wpdb->get_var($wpdb->prepare("SELECT name FROM {$this->prefix}users WHERE wp_user_id=%d OR id=%d ORDER BY (wp_user_id=%d) DESC LIMIT 1",$created_by,$created_by,$created_by));
                if($row['created_by_name']===''){
                    $wu=get_userdata($created_by); if($wu) $row['created_by_name']=$wu->display_name;
                }
            }
        }
        if(!empty($m['child'])){
            if($key==='material_returns'){
                $row['_items']=$wpdb->get_results($wpdb->prepare(
                    "SELECT mri.*,
                            COALESCE(NULLIF(mri.unit_id,0),i.unit_id) resolved_unit_id,
                            COALESCE(u1.unit_name,u2.unit_name,'') unit_name
                     FROM {$this->prefix}material_return_items mri
                     LEFT JOIN {$this->prefix}items i ON i.id=mri.item_id
                     LEFT JOIN {$this->prefix}units u1 ON u1.id=mri.unit_id
                     LEFT JOIN {$this->prefix}units u2 ON u2.id=i.unit_id
                     WHERE mri.return_id=%d
                     ORDER BY mri.id",
                    $id
                ),ARRAY_A);
                foreach($row['_items'] as &$rit){
                    if(empty($rit['unit_id']) && !empty($rit['resolved_unit_id'])) $rit['unit_id']=$rit['resolved_unit_id'];
                }
                unset($rit);
            } else {
                $row['_items']=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->prefix}{$m['child']} WHERE {$m['fk']}=%d ORDER BY id",$id),ARRAY_A);
            }
        }
        if($key==='material_returns' && !empty($row['_items'])){
            foreach($row['_items'] as &$rit){
                $received=(float)$wpdb->get_var($wpdb->prepare(
                    "SELECT COALESCE(SUM(mii.qty),0)
                     FROM {$this->prefix}material_inward_items mii
                     JOIN {$this->prefix}material_inwards mi ON mi.id=mii.inward_id
                     WHERE mi.return_id=%d
                       AND mii.item_id=%d
                       AND LOWER(COALESCE(mi.inward_type,''))='return'
                       AND LOWER(COALESCE(mi.status,''))<>'cancelled'",
                    $id,(int)$rit['item_id']
                ));
                $rit['received_qty']=round($received,2);
                $rit['pending_qty']=max(0,round((float)$rit['qty']-$received,2));
            }
            unset($rit);
        }
        $row['_documents']=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->prefix}documents WHERE module=%s AND reference_id=%d ORDER BY id DESC",$key,$id),ARRAY_A);
        wp_send_json_success($row);
    }

    public function ajax_save() {
        $this->verify_ajax(); global $wpdb;
        $key=sanitize_key($_POST['module']??''); $this->deny_employee_settings($key); $m=$this->module($key); if(!$m) wp_send_json_error(['message'=>'Invalid module']);
        if(in_array($key,['site_consumptions','material_returns','material_dispatches'],true)){
            try{$ops_stock_lock=$this->ops_lock('stock_dispatch');}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
            register_shutdown_function(function()use($wpdb,$ops_stock_lock){$wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)',$ops_stock_lock));});
        }
        $data=json_decode(stripslashes($_POST['data']??'{}'),true);
        if($key==='sites'){
            if (!$this->ensure_site_sections_column()) wp_send_json_error(['message'=>'Section database column could not be created. Check database permissions.']);
            $raw=(string)($data['site_sections']??'');
            $names=array_values(array_unique(array_filter(array_map('trim',explode(',',$raw)))));
            if(count($names)>100) wp_send_json_error(['message'=>'Too many Sections selected']);
            $verified=[];
            foreach($names as $name){
                $name=substr(sanitize_text_field($name),0,190);
                $valid=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}sections WHERE section_name=%s AND status='active'",$name));
                if(!$valid){
                    $existing=(int)($data['id']??0);
                    $stored=$existing?(string)$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$this->prefix}sites WHERE id=%d",$existing)):'';
                    if(!in_array($name,array_map('trim',explode(',',$stored)),true)) wp_send_json_error(['message'=>'Invalid or inactive Section: '.$name]);
                }
                $verified[]=$name;
            }
            $data['site_sections']=implode(', ',$verified);
        }
        if($key==='sections'){
            $name=substr(sanitize_text_field(wp_unslash($data['section_name']??'')),0,190);
            if($name==='') wp_send_json_error(['message'=>'Section Name is required']);
            $data['section_name']=$name;
            $duplicate=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}sections WHERE section_name=%s AND id<>%d LIMIT 1",$name,(int)($data['id']??0)));
            if($duplicate) wp_send_json_error(['message'=>'Section already exists']);
        }
        if($key==='contractor_advances' && empty($data['site_id'])){
            wp_send_json_error(['message'=>'Site is required']);
        }
        if(!is_array($data)) $data=[];
        if(in_array($key,['vendor_payments','contractor_payments','contractor_advances','customer_receipts'],true)){
            $mode=strtolower(trim((string)($data['payment_mode']??'')));
            if($mode==='bank' && empty($data['bank_account_id'])) wp_send_json_error(['message'=>'Bank is required when Payment Mode is Bank']);
            if($mode!=='bank') $data['bank_account_id']=null;
        }
        $items=isset($data['_items']) && is_array($data['_items']) ? $data['_items'] : []; unset($data['_items']);
        $id=intval($data['id']??0); unset($data['id']);
        $contractor_employees_input=null;
        if($key==='labour_contractors' && array_key_exists('_employees',$data)){
            if(!is_array($data['_employees']))wp_send_json_error(['message'=>'Invalid Employee rows.']);
            $contractor_employees_input=$data['_employees'];unset($data['_employees']);
        }
        $requested_contractor_advance_adjustment=0;
        if($key==='customers' && array_key_exists('gst_type',$data)) {
            $gst_type=trim((string)$data['gst_type']);
            if($gst_type!=='' && !in_array($gst_type,['intra_state','inter_state','no_gst'],true)) {
                wp_send_json_error(['message'=>'Invalid Customer GST Type']);
            }
            $data['gst_type']=$gst_type==='' ? null : $gst_type;
        }
        if($key==='contractor_advances'){ $data['payment_type']='contractor_payment';$data['contractor_bill_id']=null;$data['paid_by']='HEAD_OFFICE';$data['status']='paid'; }
        if($key==='contractor_bills'){
            $requested_contractor_advance_adjustment=round((float)($data['advance_adjustment']??0),2);
            unset($data['available_advance']);
            $available_adv=$this->contractor_available_advance((int)($data['contractor_id']??0),(int)($data['site_id']??0),$id);
            if($requested_contractor_advance_adjustment<0) wp_send_json_error(['message'=>'Advance Adjustment cannot be negative.']);
            if($requested_contractor_advance_adjustment>$available_adv+0.009) wp_send_json_error(['message'=>'Advance Adjustment cannot exceed Available Advance.']);
        }
        if(array_key_exists('site_id',$data) && $data['site_id']!=='') $this->enforce_employee_site_value((int)$data['site_id']);
        $table=$this->prefix.$m['table']; $now=current_time('mysql'); $uid=get_current_user_id();
        if($key==='employee_allowance_limits'){
            if(!$this->allowance_admin_allowed()) wp_send_json_error(['message'=>'Only HO/Admin can manage Employee Allowance Limits'],403);
            $employee_id=(int)($data['employee_id']??0);$allowance_type_id=(int)($data['allowance_type_id']??0);$limit_amount=(float)($data['limit_amount']??0);
            if(!$employee_id) wp_send_json_error(['message'=>'Employee is required']);
            if(!$allowance_type_id) wp_send_json_error(['message'=>'Allowance Type is required']);
            if($limit_amount<0) wp_send_json_error(['message'=>'Limit Amount cannot be negative']);
            $dup=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}employee_allowance_limits WHERE employee_id=%d AND allowance_type_id=%d AND id<>%d LIMIT 1",$employee_id,$allowance_type_id,$id));
            if($dup) wp_send_json_error(['message'=>'Allowance limit already exists for this Employee and Allowance Type']);
        }
        if($key==='labour_contractors' && isset($data['payment_basis']) && !in_array($data['payment_basis'],['direct','employee','time'],true)) wp_send_json_error(['message'=>'Invalid Payment Basis']);
        if($key==='contractor_bills'){
            if($id && (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}time_entries WHERE bill_id=%d",$id))) wp_send_json_error(['message'=>'Calculated bills are locked to their approved Time Sheet entries.']);
            $data['payment_basis']='direct';
        }
        if($id){ $g=$this->record_guard($key,$id); if(!$g['can_edit']) wp_send_json_error(['message'=>$g['edit_message']?:'This record is locked and cannot be edited.']); }
        if($key==='company_settings'){ $this->migrate_company_payment_details(); }
        if($key==='company_settings' && !$id){ $existing_id=(int)$wpdb->get_var("SELECT id FROM $table ORDER BY id DESC LIMIT 1"); if($existing_id) $id=$existing_id; }
        // Keep both the normalized state_id and the legacy state text in sync for Company Settings.
        // This makes the selected State persist reliably across older installations and after reload.
        if($key==='company_settings'){
            $sid=(int)($data['state_id']??0);
            if($sid>0){
                $state_name=(string)$wpdb->get_var($wpdb->prepare("SELECT state_name FROM {$this->prefix}states WHERE id=%d",$sid));
                if($state_name!=='') $data['state']=$state_name;
            }else{
                $data['state']=null;
            }
        }
        // Keep customer/vendor/contractor State ID and State Name synchronized.
        // This ensures the selected State remains visible after save and on older databases.
        if(in_array($key,['customers','vendors','labour_contractors'],true)){
            $sid=(int)($data['state_id']??0);
            if($sid>0){
                $state_name=(string)$wpdb->get_var($wpdb->prepare("SELECT state_name FROM {$this->prefix}states WHERE id=%d",$sid));
                if($state_name!=='') $data['state']=$state_name;
            }elseif(array_key_exists('state_id',$data)){
                $data['state']=null;
            }
        }
        if($wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'updated_at'")) $data['updated_at']=$now;
        if(!$id && $wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'created_at'")) $data['created_at']=$now;
        if(!$id && $wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'created_by'")) $data['created_by']=$uid;
        if(!$id && $key==='site_consumptions'){
            $plugin_uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$uid));
            if($plugin_uid) $data['created_by']=$plugin_uid;
        }
        if($key==='labour_attendance'){ if(!$id) $data['created_by']=$uid; $data['updated_by']=$uid; $data['updated_at']=$now; }
        if($key==='sites' && !$id && empty($data['site_code'])) $data['site_code']=$this->next_site_code();
        if(!empty($m['series'])) {
            $noField=['material_requisitions'=>'requisition_no','material_inwards'=>'inward_no','material_dispatches'=>'dispatch_no','site_receipts'=>'receipt_no','site_consumptions'=>'consumption_no','material_returns'=>'return_no','site_fund_allocations'=>'fund_no','site_expenses'=>'expense_no','customer_receipts'=>'receipt_no','vendor_payments'=>'payment_no','contractor_payments'=>'payment_no','contractor_advances'=>'payment_no','quotations'=>'quotation_no','tax_invoices'=>'invoice_no','gst_invoices'=>'invoice_no'][$key]??'';
            if(!$id && $noField && empty($data[$noField])) {
                if(in_array($key,['contractor_payments','contractor_advances'],true)) $data[$noField]=$this->next_contractor_payment_no();
                else $data[$noField]=$this->next_no($key,$m['series']);
            }
        }
        if($key==='customer_receipts'){
            $site_id=(int)($data['site_id']??0);
            if(!$site_id) wp_send_json_error(['message'=>'Site is required']);
            $customer_id=(int)$wpdb->get_var($wpdb->prepare("SELECT customer_id FROM {$this->prefix}sites WHERE id=%d",$site_id));
            if(!$customer_id) wp_send_json_error(['message'=>'Selected Site is not linked with a Customer']);
            $data['customer_id']=$customer_id;
            $data['gst_invoice_id']=null;
            $data['status']='received';
        }
        if($key==='site_fund_allocations'){
            $site_id=(int)($data['site_id']??0);
            if(!$site_id) wp_send_json_error(['message'=>'Site is required']);
            if(empty($data['fund_date'])) wp_send_json_error(['message'=>'Opening Date is required']);
            if((float)($data['amount']??0)<0) wp_send_json_error(['message'=>'Opening Petty Cash cannot be negative']);
            $exists_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}site_fund_allocations WHERE site_id=%d AND id<>%d AND purpose='Opening Petty Cash' LIMIT 1",$site_id,$id));
            if($exists_id) wp_send_json_error(['message'=>'Opening petty cash is already entered for this Site. Please edit the existing Site Opening entry.']);
            $data['status']='approved';
            $data['purpose']='Opening Petty Cash';
        }
        if($key==='site_expenses'){
            if(empty($data['site_id'])) wp_send_json_error(['message'=>'Site is required']);
            if(empty($data['expense_date'])) wp_send_json_error(['message'=>'Date is required']);
            if((float)($data['amount']??0)<=0) wp_send_json_error(['message'=>'Amount must be greater than zero']);
            $data['paid_by']='SITE';
            $data['status']='approved';
        }
        if($key==='vendor_payments'){
            $this->migrate_vendor_payments_fund_type();
            $ptype=sanitize_key((string)($data['payment_type']??'head_office_vendor'));
            if(!in_array($ptype,['head_office_vendor','site_vendor','site_fund','employee_allowance','salary'],true)) $ptype='head_office_vendor';
            $data['payment_type']=$ptype;
            $data['paid_by']='HEAD_OFFICE';
            $data['status']='paid';
            if(empty($data['payment_date'])) wp_send_json_error(['message'=>'Payment Date is required']);
            if((float)($data['amount']??0)<=0) wp_send_json_error(['message'=>'Amount must be greater than zero']);
            if($ptype==='head_office_vendor'){
                if(empty($data['vendor_id'])) wp_send_json_error(['message'=>'Supplier is required']);
                $data['site_id']=null;
            } elseif($ptype==='site_vendor'){
                if(empty($data['vendor_id'])) wp_send_json_error(['message'=>'Supplier is required']);
                if(empty($data['site_id'])) wp_send_json_error(['message'=>'Site is required']);
            } elseif($ptype==='site_fund') {
                if(empty($data['site_id'])) wp_send_json_error(['message'=>'Site is required']);
                $data['vendor_id']=null;
                $data['material_inward_id']=null;
                $data['employee_id']=null;$data['allowance_id']=null;$data['allowance_type_id']=null;
            } elseif($ptype==='employee_allowance') {
                if(!$this->allowance_admin_allowed()) wp_send_json_error(['message'=>'Only HO/Admin can make Employee Allowance payments'],403);
                $allowance_id=(int)($data['allowance_id']??0);
                $employee_id=(int)($data['employee_id']??0);
                $allowance_type_id=(int)($data['allowance_type_id']??0);
                if(!$allowance_id || !$employee_id || !$allowance_type_id) wp_send_json_error(['message'=>'Invalid Employee Allowance payment']);
                $a=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}employee_allowances WHERE id=%d",$allowance_id),ARRAY_A);
                if(!$a || ($a['status']??'')!=='approved') wp_send_json_error(['message'=>'Allowance is not pending for payment']);
                if((int)$a['employee_id']!==$employee_id || (int)$a['allowance_type_id']!==$allowance_type_id) wp_send_json_error(['message'=>'Allowance details do not match']);
                $data['amount']=(float)$a['amount'];
                $data['vendor_id']=null;$data['material_inward_id']=null;$data['site_id']=null;$data['salary_slip_id']=null;
            } else {
                if(!$this->allowance_admin_allowed()) wp_send_json_error(['message'=>'Only HO/Admin can make Salary payments'],403);
                $salary_slip_id=(int)($data['salary_slip_id']??0);$employee_id=(int)($data['employee_id']??0);
                $slip=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}salary_slips WHERE id=%d",$salary_slip_id),ARRAY_A);
                if(!$slip || !in_array(($slip['status']??''),['pending_payment','partly_paid'],true)) wp_send_json_error(['message'=>'Salary slip is not pending for payment']);
                if((int)$slip['employee_id']!==$employee_id) wp_send_json_error(['message'=>'Employee does not match Salary Slip']);
                $amt=(float)($data['amount']??0);$bal=(float)$slip['balance_payable'];
                if($amt<=0 || $amt>$bal+0.01) wp_send_json_error(['message'=>'Payment Amount cannot exceed Salary Balance']);
                $data['vendor_id']=null;$data['material_inward_id']=null;$data['site_id']=null;$data['allowance_id']=null;$data['allowance_type_id']=null;
            }
        }
        // Phase 1 master validations. Front-end required attributes are mirrored here
        // so incomplete records cannot be inserted through a direct request.
        if($key==='site_opening_stock'){
            $site_id=(int)($data['site_id']??0);$item_id=(int)($data['item_id']??0);
            if(!$site_id) wp_send_json_error(['message'=>'Site is required']);
            if(empty($data['opening_date'])) wp_send_json_error(['message'=>'Opening Date is required']);
            if(!$item_id) wp_send_json_error(['message'=>'Item is required']);
            if((float)($data['opening_qty']??0)<0) wp_send_json_error(['message'=>'Opening Qty cannot be negative']);
            if((float)($data['opening_rate']??0)<0) wp_send_json_error(['message'=>'Opening Rate cannot be negative']);
            $unit_id=(int)$wpdb->get_var($wpdb->prepare("SELECT unit_id FROM {$this->prefix}items WHERE id=%d",$item_id));
            if($unit_id) $data['unit_id']=$unit_id;
            $dup=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}site_opening_stock WHERE site_id=%d AND item_id=%d AND id<>%d LIMIT 1",$site_id,$item_id,$id));
            if($dup) wp_send_json_error(['message'=>'Opening stock for this Item is already entered for this Site. Please edit the existing entry.']);
        }
        if($key==='designations' && empty(trim((string)($data['designation_name']??'')))) wp_send_json_error(['message'=>'Designation Name is required']);
        if($key==='states' && empty(trim((string)($data['state_name']??'')))) wp_send_json_error(['message'=>'State Name is required']);
        if($key==='sites'){
            foreach(['site_name'=>'Site Name','customer_id'=>'Customer','site_location'=>'Site Location','site_manager_user_id'=>'Site Manager','site_supervisor_user_id'=>'Site Supervisor','start_date'=>'Start Date','expected_end_date'=>'Expected Completion Date','work_order_value'=>'Work Order Value'] as $f=>$label){
                if(!isset($data[$f]) || $data[$f]==='') wp_send_json_error(['message'=>$label.' is required']);
            }
        }
        if(in_array($key,['vendors','labour_contractors'],true)){
            $name_field=$key==='vendors'?'vendor_name':'contractor_name';
            $name_label=$key==='vendors'?'Supplier Name':'Contractor Name';
            foreach([$name_field=>$name_label,'contact_person'=>'Contact Person','mobile'=>'Mobile','address'=>'Address','city'=>'City','state_id'=>'State'] as $f=>$label){
                if(!isset($data[$f]) || trim((string)$data[$f])==='') wp_send_json_error(['message'=>$label.' is required']);
            }
        }
        if($key==='vendors'){
            $this->migrate_customer_gst_type();
            $this->migrate_vendor_gst_type();
            $gst_type=sanitize_key((string)($data['gst_type']??''));
            if(!in_array($gst_type,['intra_state','inter_state','no_gst'],true)) wp_send_json_error(['message'=>'GST Type is required','field'=>'gst_type']);
            $data['gst_type']=$gst_type;
        }
        if($key==='material_inwards'){
            $this->migrate_customer_gst_type();
            $this->migrate_vendor_gst_type();
            $this->migrate_material_inward_type();
            $this->migrate_purchase_invoice_extras();
            $this->migrate_purchase_approval();
            $inward_type=sanitize_key((string)($data['inward_type']??'purchase'));
            if(!in_array($inward_type,['purchase','return'],true)) $inward_type='purchase';
            $data['inward_type']=$inward_type;
            $data['status']=$inward_type==='purchase'?'pending_approval':'approved';
            if($inward_type==='purchase'){
                if(empty($data['warehouse_id'])) wp_send_json_error(['message'=>'Please select Warehouse','field'=>'warehouse_id']);
            }
            if($inward_type==='return'){
                $return_id=(int)($data['return_id']??0);
                if(!$return_id) wp_send_json_error(['message'=>'Return No. is required','field'=>'return_id']);
                $ret=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}material_returns WHERE id=%d",$return_id),ARRAY_A);
                if(!$ret) wp_send_json_error(['message'=>'Material Return not found']);
                $plugin_uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$uid));
                if(!$plugin_uid) wp_send_json_error(['message'=>'Logged-in user is not available in User Master']);
                $data['received_by']=$plugin_uid;
                $data['site_id']=(int)$ret['site_id'];
                $data['warehouse_id']=(int)$ret['warehouse_id'];
                $data['inward_date']=current_time('Y-m-d');
                $data['vendor_id']=null;
                $data['supplier_invoice_no']=null;
                $data['supplier_invoice_date']=null;
                $data['remarks']=null;
                $data['other_expenses']=0;
                $data['other_expense_gst']=0;
                $data['round_off']=0;
                $return_items=$wpdb->get_results($wpdb->prepare("SELECT mri.item_id,mri.qty,COALESCE(NULLIF(mri.unit_id,0),i.unit_id,0) unit_id,mri.rate,mri.remarks FROM {$this->prefix}material_return_items mri LEFT JOIN {$this->prefix}items i ON i.id=mri.item_id WHERE mri.return_id=%d ORDER BY mri.id",$return_id),ARRAY_A);
                if(!$return_items) wp_send_json_error(['message'=>'Selected Return has no items']);
                $submitted=[];
                foreach($items as $si){ if(is_array($si) && !empty($si['item_id'])) $submitted[(int)$si['item_id']]=$si; }
                $items=[];
                $current_receipt_total=0.0;
                foreach($return_items as $ri){
                    $item_id=(int)$ri['item_id'];
                    $return_qty=round((float)$ri['qty'],2);
                    $si=$submitted[$item_id]??[];

                    $exclude=$id?" AND mi.id<>".(int)$id:'';
                    $already=(float)$wpdb->get_var($wpdb->prepare(
                        "SELECT COALESCE(SUM(mii.qty),0)
                         FROM {$this->prefix}material_inward_items mii
                         JOIN {$this->prefix}material_inwards mi ON mi.id=mii.inward_id
                         WHERE mi.return_id=%d
                           AND mii.item_id=%d
                           AND LOWER(COALESCE(mi.inward_type,''))='return'
                           AND LOWER(COALESCE(mi.status,''))<>'cancelled' $exclude",
                        $return_id,$item_id
                    ));
                    $pending=max(0,round($return_qty-$already,2));
                    $received_qty=round((float)($si['qty']??0),2);

                    if($received_qty<0) wp_send_json_error(['message'=>'Received Qty cannot be negative']);
                    if($received_qty>$pending+0.001) wp_send_json_error(['message'=>'Received Qty cannot exceed Pending Return Qty']);
                    $current_receipt_total+=$received_qty;

                    $rate=round((float)($ri['rate']??0),2);
                    $items[]=['item_id'=>$item_id,'return_qty'=>$return_qty,'qty'=>$received_qty,'unit_id'=>(int)$ri['unit_id'],'rate'=>$rate,'remarks'=>'','taxable_amount'=>round($received_qty*$rate,2),'gst_rate'=>0,'cgst_amount'=>0,'sgst_amount'=>0,'igst_amount'=>0,'total_amount'=>round($received_qty*$rate,2)];
                }
                if($current_receipt_total<=0.0001) wp_send_json_error(['message'=>'Enter Received Qty for at least one item']);
            } else {
                $data['return_id']=null;
                $data['approved_by']=null;
                $data['approved_at']=null;
                $data['site_id']=null;
                $data['received_by']=null;
            }
            // Every new Purchase must be linked to a Purchase Order.
            $po_id=(int)($data['purchase_order_id']??0);
            if($inward_type==='purchase' && !$po_id && !$id){
                wp_send_json_error(['message'=>'PO No. is required. Purchase cannot be entered without a Purchase Order.','field'=>'purchase_order_id']);
            }
            // PO-linked purchase receipt validation and linkage.
            if($inward_type==='purchase' && $po_id){
                $po=$wpdb->get_row($wpdb->prepare("SELECT id,vendor_id,warehouse_id,status FROM {$this->prefix}purchase_orders WHERE id=%d",$po_id),ARRAY_A);
                if(!$po || strtolower((string)($po['status']??''))==='cancelled') wp_send_json_error(['message'=>'Purchase Order is not available']);
                $data['vendor_id']=(int)$po['vendor_id'];
                $data['warehouse_id']=(int)$po['warehouse_id'];
                foreach($items as &$pit){
                    $poi=(int)($pit['purchase_order_item_id']??0);
                    if(!$poi) wp_send_json_error(['message'=>'Invalid PO Item']);
                    $dbpoi=$wpdb->get_row($wpdb->prepare("SELECT id,item_id,unit_id,fixed_price,rate,po_qty FROM {$this->prefix}purchase_order_items WHERE id=%d AND purchase_order_id=%d",$poi,$po_id),ARRAY_A);
                    if(!$dbpoi) wp_send_json_error(['message'=>'PO Item not found']);
                    $pit['item_id']=(int)$dbpoi['item_id'];
                    $pit['unit_id']=(int)$dbpoi['unit_id'];
                    $pit['item_price']=round((float)$dbpoi['fixed_price'],2);
                    if(!isset($pit['rate']) || $pit['rate']==='') $pit['rate']=round((float)$dbpoi['rate'],2);
                    $exclude=$id?" AND mi.id<>".(int)$id:'';
                    $already=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(mii.qty),0) FROM {$this->prefix}material_inward_items mii JOIN {$this->prefix}material_inwards mi ON mi.id=mii.inward_id WHERE mii.purchase_order_item_id=%d AND LOWER(COALESCE(mi.status,''))<>'cancelled' $exclude",$poi));
                    $pending=max(0,round((float)$dbpoi['po_qty']-$already,2));
                    $rq=round((float)($pit['qty']??0),2);
                    if($rq<0) wp_send_json_error(['message'=>'Receipt Qty cannot be negative']);
                    if($rq>$pending+0.009) wp_send_json_error(['message'=>'Receipt Qty cannot exceed Pending Receipt Qty']);
                } unset($pit);
            }
            if($inward_type==='purchase'){
                $bill_type=sanitize_key((string)($data['bill_type']??''));
                if($bill_type!=='' && !in_array($bill_type,['gst_bill','non_gst_bill'],true)) wp_send_json_error(['message'=>'Invalid Bill Type','field'=>'bill_type']);
                $data['bill_type']=$bill_type?:null;
            }
            $vendor_id=(int)($data['vendor_id']??0);
            $gst_type='no_gst';
            if($vendor_id){
                $gst_type=(string)$wpdb->get_var($wpdb->prepare("SELECT gst_type FROM {$this->prefix}vendors WHERE id=%d",$vendor_id));
                if(!in_array($gst_type,['intra_state','inter_state','no_gst'],true)) $gst_type='no_gst';
            }
            if($inward_type==='purchase' && ($data['bill_type']??'')==='non_gst_bill') $gst_type='no_gst';
            $sum_taxable=0.0; $sum_cgst=0.0; $sum_sgst=0.0; $sum_igst=0.0; $sum_total=0.0;
            foreach($items as &$it){
                $item_id=(int)($it['item_id']??0);
                // Price is a fixed purchase-item snapshot, not a live/virtual Item Master value.
                // For a new line (or legacy request without item_price), freeze the current Item Master Price now.
                if($inward_type==='purchase'){
                    $submittedPrice=isset($it['item_price']) && $it['item_price']!=='' ? (float)$it['item_price'] : null;
                    if($submittedPrice===null){
                        $submittedPrice=(float)$wpdb->get_var($wpdb->prepare("SELECT opening_rate FROM {$this->prefix}items WHERE id=%d",$item_id));
                    }
                    $it['item_price']=round(max(0,$submittedPrice),2);
                }
                $q=max(0,(float)($it['qty']??0)); $rate=max(0,(float)($it['rate']??0));
                $taxable=round($q*$rate,2);
                // Purchase GST % is controlled by Item Master. Never depend on a disabled/read-only
                // browser field for the value that is stored in the backend.
                $gst=0.0;
                if($inward_type==='purchase' && $item_id){
                    $gst=max(0,(float)$wpdb->get_var($wpdb->prepare("SELECT gst_rate FROM {$this->prefix}items WHERE id=%d",$item_id)));
                }else{
                    $gst=max(0,(float)($it['gst_rate']??0));
                }
                $cgst=$sgst=$igst=0.0;
                if($gst_type==='intra_state'){ $cgst=round($taxable*$gst/200,2); $sgst=$cgst; }
                elseif($gst_type==='inter_state'){ $igst=round($taxable*$gst/100,2); }
                else { $gst=0.0; }
                $total=round($taxable+$cgst+$sgst+$igst,2);
                $it['taxable_amount']=$taxable; $it['gst_rate']=$gst; $it['cgst_amount']=$cgst; $it['sgst_amount']=$sgst; $it['igst_amount']=$igst; $it['total_amount']=$total;
                $sum_taxable+=$taxable; $sum_cgst+=$cgst; $sum_sgst+=$sgst; $sum_igst+=$igst; $sum_total+=$total;
            } unset($it);
            $data['taxable_amount']=round($sum_taxable,2); $data['cgst_amount']=round($sum_cgst,2); $data['sgst_amount']=round($sum_sgst,2); $data['igst_amount']=round($sum_igst,2);
            $other_expenses=round((float)($data['other_expenses']??0),2);
            $other_expense_gst=round((float)($data['other_expense_gst']??0),2);
            $round_off=round((float)($data['round_off']??0),2);
            if($other_expenses<0) $other_expenses=0;
            if($other_expense_gst<0) $other_expense_gst=0;
            $data['other_expenses']=$other_expenses;
            if($inward_type==='purchase' && ($data['bill_type']??'')==='non_gst_bill') $other_expense_gst=0.0;
            $data['other_expense_gst']=$other_expense_gst;
            $data['round_off']=$round_off;
            $data['total_amount']=round($sum_total+$other_expenses+$other_expense_gst+$round_off,2);
        }
        if($key==='contractor_bills'){
            $data['bill_no']=trim(sanitize_text_field((string)($data['bill_no']??'')));
            if($data['bill_no']==='') wp_send_json_error(['message'=>'Bill No. is required','field'=>'bill_no']);
            $duplicate_bill=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}contractor_bills WHERE bill_no=%s AND id<>%d LIMIT 1",$data['bill_no'],$id));
            if($duplicate_bill) wp_send_json_error(['message'=>'This Bill No. already exists. Please enter a different Bill No.','field'=>'bill_no']);
            if(empty($data['from_date'])) wp_send_json_error(['message'=>'From Date is required','field'=>'from_date']);
            if(empty($data['to_date'])) wp_send_json_error(['message'=>'To Date is required','field'=>'to_date']);
            if(!isset($data['taxable_amount']) || $data['taxable_amount']==='' || !is_numeric($data['taxable_amount'])) wp_send_json_error(['message'=>'Amount is required','field'=>'taxable_amount']);
            if(strtotime((string)$data['to_date']) < strtotime((string)$data['from_date'])) wp_send_json_error(['message'=>'To Date cannot be before From Date','field'=>'to_date']);
            $amount=round(max(0,(float)($data['taxable_amount']??0)),2);
            $gst=round(max(0,(float)($data['gst_amount']??0)),2);
            $data['taxable_amount']=$amount;
            $data['gst_amount']=$gst;
            $data['total_amount']=round($amount+$gst,2);
            if($requested_contractor_advance_adjustment>$data['total_amount']+0.009) wp_send_json_error(['message'=>'Advance Adjustment cannot exceed Total Amount.','field'=>'advance_adjustment']);
            if($id){$already_bill_paid=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$this->prefix}contractor_payments WHERE contractor_bill_id=%d AND COALESCE(payment_type,'bill_payment')='bill_payment' AND LOWER(COALESCE(status,''))<>'cancelled'",$id));if($data['total_amount']<$already_bill_paid+$requested_contractor_advance_adjustment-0.009) wp_send_json_error(['message'=>'Total Amount cannot be less than payments and advance already adjusted.']);}
        }
        if($key==='contractor_advances'){
            if(empty($data['contractor_id'])) wp_send_json_error(['message'=>'Labour Contractor is required','field'=>'contractor_id']);
            if(empty($data['payment_date'])) wp_send_json_error(['message'=>'Date is required','field'=>'payment_date']);
            if(!isset($data['amount']) || !is_numeric($data['amount']) || (float)$data['amount']<=0) wp_send_json_error(['message'=>'Payment Amount should be greater than zero','field'=>'amount']);
            if(empty($data['site_id'])) $data['site_id']=null;
            $data['amount']=round((float)$data['amount'],2);
        }
        // Mobile fields: when present, they must contain exactly 10 digits.
        foreach(['mobile'=>'Mobile Number','driver_mobile'=>'Driver Mobile Number'] as $mobile_field=>$mobile_label){
            if(isset($data[$mobile_field])){
                $mobile_value=trim((string)$data[$mobile_field]);
                if($mobile_value!=='' && !preg_match('/^[0-9]{10}$/',$mobile_value)){
                    wp_send_json_error(['message'=>$mobile_label.' should be a 10 digit number only','field'=>$mobile_field]);
                }
            }
        }
        if($key==='material_requisitions'){
            // Requisitions go directly to Pending Approval after Add. Status is workflow-controlled.
            if(!$id){
                $data['status']='pending_approval';
                $plugin_uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$uid));
                if(!$plugin_uid) wp_send_json_error(['message'=>'Logged-in user is not available in User Master']);
                $data['requested_by']=$plugin_uid;
            } else {
                unset($data['status']);
                // Requested By is fixed after creation.
                unset($data['requested_by']);
            }
            foreach(['site_id'=>'Site','requisition_date'=>'Requisition Date','required_date'=>'Required Date'] as $f=>$label){
                if(!isset($data[$f]) || $data[$f]==='') wp_send_json_error(['message'=>$label.' is required']);
            }
            $today=current_time('Y-m-d');
            foreach(['requisition_date'=>'Requisition Date','required_date'=>'Required Date'] as $f=>$label){
                if((string)$data[$f] < $today) wp_send_json_error(['message'=>$label.' cannot be before today','field'=>$f]);
            }
            if(empty($items)) wp_send_json_error(['message'=>'At least one Item is required']);
            foreach($items as $it){
                if(empty($it['item_id'])) wp_send_json_error(['message'=>'Item is required']);
                if(!isset($it['requested_qty']) || (float)$it['requested_qty']<=0) wp_send_json_error(['message'=>'Requested Qty must be greater than zero']);
                if(empty($it['unit_id'])) wp_send_json_error(['message'=>'Unit is required']);
                // Approved Qty is controlled only by Pending Approval view.
                unset($it['approved_qty']);
            }
        }
        if($key==='site_receipts'){
            $this->migrate_site_receipt_items_table();
            $dispatch_id=(int)($data['dispatch_id']??0);
            if(!$dispatch_id) wp_send_json_error(['message'=>'Dispatch is required']);
            $dispatch=$wpdb->get_row($wpdb->prepare("SELECT id,site_id,status FROM {$this->prefix}material_dispatches WHERE id=%d",$dispatch_id),ARRAY_A);
            if(!$dispatch) wp_send_json_error(['message'=>'Dispatch not found']);
            $dup_receipt=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}site_receipts WHERE dispatch_id=%d AND LOWER(COALESCE(status,''))<>'cancelled' AND id<>%d",$dispatch_id,$id));
            if($dup_receipt) wp_send_json_error(['message'=>'A Site Receipt has already been created against this Dispatch.']);
            $plugin_uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$uid));
            if(!$plugin_uid) wp_send_json_error(['message'=>'Logged-in user is not available in User Master']);
            $data['site_id']=(int)$dispatch['site_id'];
            $data['received_by']=$plugin_uid;
            $data['status']='received';
            unset($data['remarks']);
            if(empty($data['receipt_date'])) wp_send_json_error(['message'=>'Receipt Date is required']);
            if(empty($items)) wp_send_json_error(['message'=>'Dispatch items are required']);
            $dispatch_items=$wpdb->get_results($wpdb->prepare("SELECT id,item_id,qty,unit_id,rate FROM {$this->prefix}material_dispatch_items WHERE dispatch_id=%d ORDER BY id",$dispatch_id),ARRAY_A);
            $by_item=[]; foreach($dispatch_items as $di){$by_item[(int)$di['item_id']]=$di;}
            foreach($items as &$it){
                $iid=(int)($it['item_id']??0); if(!$iid || empty($by_item[$iid])) wp_send_json_error(['message'=>'Invalid dispatch item']);
                $di=$by_item[$iid]; $it['dispatch_qty']=$di['qty']; $it['unit_id']=$di['unit_id']; $it['rate']=$di['rate'];
                $recv=(float)($it['received_qty']??0); $dmg=(float)($it['damaged_qty']??0); $short=(float)($it['short_qty']??0);
                if($recv<0||$dmg<0||$short<0) wp_send_json_error(['message'=>'Receipt quantities cannot be negative']);
                if($recv+$dmg+$short > (float)$di['qty']+0.0001) wp_send_json_error(['message'=>'Received + Damaged + Short Qty cannot exceed Dispatch Qty']);
            } unset($it);
        }

        if($key==='site_consumptions'){
            $this->migrate_consumption_item_documents();
            // Consumption number and status are workflow-controlled and hidden from the form.
            $data['status']='approved';
            unset($data['remarks']);
            if(empty($data['site_id'])) wp_send_json_error(['message'=>'Site is required','field'=>'site_id']);
            if(empty($data['consumption_date'])) wp_send_json_error(['message'=>'Date is required','field'=>'consumption_date']);
            if(empty($items)) wp_send_json_error(['message'=>'At least one Item is required']);
            $used=[];
            foreach($items as $it){
                $item_id=(int)($it['item_id']??0);
                $qty=(float)($it['qty']??0);
                $unit_id=(int)($it['unit_id']??0);
                if(!$item_id) wp_send_json_error(['message'=>'Item is required']);
                if(isset($used[$item_id])) wp_send_json_error(['message'=>'Same Item cannot be repeated in Consumption Form']);
                if($qty<=0) wp_send_json_error(['message'=>'Qty must be greater than zero']);
                if(!$unit_id) wp_send_json_error(['message'=>'Unit is required']);
                // Source-of-truth stock calculation from saved receipt/consumption/return details.
                // Receipt matching deliberately falls back to the linked Dispatch because older receipt
                // rows can have missing site_id/item_id values even though the Dispatch relation is valid.
                $site_id=(int)$data['site_id'];
                $stock=$this->site_available_stock($site_id,$item_id,$id);
                $available=(float)$stock['available_qty'];
                $used[$item_id]=$qty;
                if($used[$item_id] > $available + 0.0001) wp_send_json_error(['message'=>'Consumption Qty cannot exceed Available Qty']);
            }
        }

        if($key==='material_returns'){
            $this->migrate_material_returns_table();
            // Return number/status/user are workflow-controlled and hidden from the form.
            $plugin_uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$uid));
            if(!$plugin_uid) wp_send_json_error(['message'=>'Logged-in user is not available in User Master']);
            if(!$id) $data['return_by']=$plugin_uid;
            else unset($data['return_by']);
            $data['status']='submitted';
            unset($data['reason'],$data['remarks']);
            if(empty($data['site_id'])) wp_send_json_error(['message'=>'Site is required','field'=>'site_id']);
            if(empty($data['warehouse_id'])) wp_send_json_error(['message'=>'Warehouse is required','field'=>'warehouse_id']);
            if(empty($data['return_date'])) wp_send_json_error(['message'=>'Return Date is required','field'=>'return_date']);
            if(empty($items)) wp_send_json_error(['message'=>'At least one Item is required']);
            $used=[];
            foreach($items as &$it){
                $item_id=(int)($it['item_id']??0);
                $qty=round((float)($it['qty']??0),2);
                if(!$item_id) wp_send_json_error(['message'=>'Item is required']);
                if($qty<=0) wp_send_json_error(['message'=>'Return Qty must be greater than zero']);
                $stock=$this->site_available_stock((int)$data['site_id'],$item_id,0,$id);
                $available=(float)$stock['available_qty'];
                $used[$item_id]=round(($used[$item_id]??0)+$qty,2);
                if($used[$item_id] > $available + 0.001) wp_send_json_error(['message'=>'Return Qty cannot exceed Available Qty']);
                $it['qty']=$qty;
                if(!empty($stock['unit_id'])) $it['unit_id']=(int)$stock['unit_id'];
                if(empty($it['unit_id'])) wp_send_json_error(['message'=>'Unit is required']);
                $it['rate']=round((float)($stock['rate']??0),2);
                unset($it['available_qty']);
            }
            unset($it);
        }

        if($key==='material_dispatches'){
            $source=sanitize_key($data['dispatch_from']??'warehouse');
            if(!in_array($source,['warehouse','site'],true))wp_send_json_error(['message'=>'Select Dispatch From']);
            $data['dispatch_from']=$source;
            $location=(int)($data[$source==='site'?'from_site_id':'warehouse_id']??0);
            if(!$location)wp_send_json_error(['message'=>$source==='site'?'From Site is required':'Warehouse is required']);
            if($source==='site'){
                try{$this->ops_site($location);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
                if($location===(int)($data['site_id']??0))wp_send_json_error(['message'=>'From Site and To Site must be different']);
                $data['warehouse_id']=0;
            }else{
                if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}warehouses WHERE id=%d",$location)))wp_send_json_error(['message'=>'Select a valid Warehouse']);
                $data['from_site_id']=null;
            }
            $used=[];$clean=[];
            foreach($items as $it){
                $q=round((float)($it['qty']??0),2);
                if(!is_finite($q)||$q<0)wp_send_json_error(['message'=>'Invalid Dispatch Qty']);
                if($q==0)continue;
                $iid=(int)($it['item_id']??0);if(!$iid)wp_send_json_error(['message'=>'Item is required']);
                $stock=$this->dispatch_source_stock($source,$location,$iid,$id);
                $used[$iid]=round(($used[$iid]??0)+$q,2);
                if($used[$iid]>(float)$stock['available_qty']+0.0001)wp_send_json_error(['message'=>'Dispatch Qty cannot exceed Available Stock']);
                $it['qty']=$q;$it['rate']=$stock['rate']??0;unset($it['available_qty']);$clean[]=$it;
            }
            if(!$clean)wp_send_json_error(['message'=>'Enter Dispatch Qty for at least one item']);
            $items=$clean;$data['status']='dispatched';
        }

        if($key==='material_dispatches' && !empty($data['requisition_id'])){
            // Dispatch status is workflow-controlled and is not shown on the form.
            $data['status']='dispatched';
            unset($data['transport_details']);
            $rid=(int)$data['requisition_id'];
            $req_site=(int)$wpdb->get_var($wpdb->prepare("SELECT site_id FROM {$this->prefix}material_requisitions WHERE id=%d",$rid));
            if($req_site!==(int)($data['site_id']??0))wp_send_json_error(['message'=>'To Site must match the requisition']);
            $seen_req=[];
            $req_status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$this->prefix}material_requisitions WHERE id=%d",$rid));
            if(!in_array($req_status,['approved','partially_dispatched'],true)) wp_send_json_error(['message'=>'This requisition is not approved for dispatch']);
            foreach($items as &$it){
                $ri=(int)($it['requisition_item_id']??0); if(isset($seen_req[$ri]))wp_send_json_error(['message'=>'Duplicate requisition item']);$seen_req[$ri]=true; if(!$ri) wp_send_json_error(['message'=>'Invalid requisition item']);
                $req_item=$wpdb->get_row($wpdb->prepare("SELECT item_id,unit_id,approved_qty FROM {$this->prefix}material_requisition_items WHERE id=%d AND requisition_id=%d",$ri,$rid),ARRAY_A);
                if(!$req_item) wp_send_json_error(['message'=>'Requisition item not found']);
                if((int)$it['item_id']!==(int)$req_item['item_id'])wp_send_json_error(['message'=>'Invalid requisition item']);
                $it['item_id']=(int)$req_item['item_id'];
                $it['unit_id']=(int)$req_item['unit_id'];
                // Source stock validation has already assigned the source valuation rate.
                $exclude=$id?" AND d.id<>".(int)$id:'';
                $already=(float)$wpdb->get_var("SELECT COALESCE(SUM(di.qty),0) FROM {$this->prefix}material_dispatch_items di JOIN {$this->prefix}material_dispatches d ON d.id=di.dispatch_id WHERE di.requisition_item_id=".$ri." AND d.requisition_id=".$rid." AND d.status<>'cancelled'".$exclude);
                $pending=max(0,(float)$req_item['approved_qty']-$already);
                if((float)($it['qty']??0)<=0) wp_send_json_error(['message'=>'Dispatch Qty must be greater than zero']);
                if((float)$it['qty']>$pending+0.0001) wp_send_json_error(['message'=>'Dispatch Qty cannot exceed Pending Approved Qty']);
            }
            unset($it);
        }
        if($key==='labour_attendance'){
            $site_id=(int)($data['site_id']??0);
            $attendance_date=sanitize_text_field((string)($data['attendance_date']??''));
            if(!$site_id) wp_send_json_error(['message'=>'Please select a Site.','field'=>'site_id']);
            if(!$attendance_date) wp_send_json_error(['message'=>'Date is required.','field'=>'attendance_date']);
            if(empty($items)) wp_send_json_error(['message'=>'Please add at least one Labour Contractor.']);
            $seen=[]; $clean_rows=[];
            foreach($items as $idx=>$it){
                if(!is_array($it)) continue;
                $contractor_id=(int)($it['contractor_id']??0);
                if(!$contractor_id) wp_send_json_error(['message'=>'Please select a Labour Contractor.']);
                $employee_hours=[];$worker_seen=[];$attend=0;
                foreach((array)($it['employee_hours']??[]) as $hours){
                    $worker_id=(int)($hours['worker_id']??0);$raw=$hours['working_hours']??'';
                    if(!$worker_id||isset($worker_seen[$worker_id])||!is_numeric($raw)||(float)$raw<0||(float)$raw>24)wp_send_json_error(['message'=>'Enter valid Working Hours between 0 and 24 for each employee.']);
                    $worker=$wpdb->get_row($wpdb->prepare("SELECT id,employee_name,status FROM {$this->prefix}contractor_employees WHERE id=%d AND contractor_id=%d",$worker_id,$contractor_id),ARRAY_A);
                    if(!$worker)wp_send_json_error(['message'=>'Employee does not belong to the selected Labour Contractor.']);
                    if($worker['status']!=='active'&&!$id)wp_send_json_error(['message'=>'Select active Contractor Employees.']);
                    $worker_seen[$worker_id]=true;$value=round((float)$raw,2);if($value>0)$attend++;
                    $employee_hours[]=['worker_id'=>$worker_id,'employee_name'=>$worker['employee_name'],'working_hours'=>$value];
                }
                if(!$attend)wp_send_json_error(['message'=>'Enter Working Hours for at least one employee.']);
                $hours_json=wp_json_encode(['employee_hours'=>$employee_hours]);
                if(isset($seen[$contractor_id])) wp_send_json_error(['message'=>'This Labour Contractor has already been selected.']);
                $seen[$contractor_id]=1;
                $exclude_id=($id && $idx===0)?$id:0;
                $dup=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}labour_attendance WHERE attendance_date=%s AND site_id=%d AND contractor_id=%d AND id<>%d",$attendance_date,$site_id,$contractor_id,$exclude_id));
                if($dup) wp_send_json_error(['message'=>'Attendance for this Labour Contractor already exists for the selected Site and Date.']);
                $clean_rows[]=['contractor_id'=>$contractor_id,'no_of_labour'=>$attend,'remarks'=>$hours_json];
            }
            if(!$clean_rows) wp_send_json_error(['message'=>'Please add at least one Labour Contractor.']);
            if($id){
                $first=array_shift($clean_rows);
                $ok=$wpdb->update($table,[
                    'attendance_date'=>$attendance_date,'site_id'=>$site_id,'contractor_id'=>$first['contractor_id'],'no_of_labour'=>$first['no_of_labour'],
                    'remarks'=>$first['remarks'],'updated_by'=>$uid,'updated_at'=>$now
                ],['id'=>$id]);
                if($ok===false) wp_send_json_error(['message'=>$wpdb->last_error?:'Save failed']);
            }
            foreach($clean_rows as $lr){
                $ok=$wpdb->insert($table,[
                    'attendance_date'=>$attendance_date,'site_id'=>$site_id,'contractor_id'=>$lr['contractor_id'],'no_of_labour'=>$lr['no_of_labour'],
                    'remarks'=>$lr['remarks'],'created_by'=>$uid,'created_at'=>$now,'updated_by'=>$uid,'updated_at'=>$now
                ]);
                if(!$ok){
                    if(stripos((string)$wpdb->last_error,'Duplicate')!==false) wp_send_json_error(['message'=>'Attendance for this Labour Contractor already exists for the selected Site and Date.']);
                    wp_send_json_error(['message'=>$wpdb->last_error?:'Save failed']);
                }
                if(!$id) $id=(int)$wpdb->insert_id;
            }
            wp_send_json_success(['id'=>$id,'message'=>'Attendance saved successfully']);
        }

        // Sales values are always derived on the server, not trusted from the browser.
                if(in_array($key,['quotations','tax_invoices'],true)){
            $is_quote=$key==='quotations';
            $date_field=$is_quote?'quotation_date':'invoice_date';
            if(empty($data[$date_field])) wp_send_json_error(['message'=>($is_quote?'Quotation':'Invoice').' Date is required','field'=>$date_field]);
            if(empty($data['customer_id'])) wp_send_json_error(['message'=>'Customer is required','field'=>'customer_id']);
            if(empty($items)) wp_send_json_error(['message'=>'At least one Item is required']);
            $company_state=(int)$wpdb->get_var("SELECT state_id FROM {$this->prefix}company_settings ORDER BY id DESC LIMIT 1");
            $pos=(int)($data['place_of_supply_state_id']??0);
            if(!$pos) $pos=(int)($data['billing_state_id']??0);
            $intra=($company_state>0 && $pos>0 && $company_state===$pos);
            $customer_gst_type='';
            if(!$is_quote){
                $customer_gst_type=(string)$wpdb->get_var($wpdb->prepare("SELECT gst_type FROM {$this->prefix}customers WHERE id=%d",(int)$data['customer_id']));
                if(!in_array($customer_gst_type,['intra_state','inter_state','no_gst'],true)) wp_send_json_error(['message'=>'Please set GST Type in Customers Master before saving the Invoice','field'=>'customer_id']);
            }
            $sum_amount=$sum_cgst=$sum_sgst=$sum_igst=$sum_total=0.0;
            $clean_items=[];
            foreach($items as $it){
                if(!is_array($it)) continue;
                $item_id=(int)($it['item_id']??0);
                $description=trim(sanitize_text_field((string)($it['description']??'')));
                if(!$item_id && $description==='') continue;
                $qty=round((float)($it['qty']??0),2);$rate=round((float)($it['rate']??0),2);$gst=max(0,round((float)($it['gst_rate']??0),2));
                if($qty<=0) wp_send_json_error(['message'=>'Qty must be greater than zero']);
                if($rate<0) wp_send_json_error(['message'=>'Rate cannot be negative']);
                $amount=round($qty*$rate,2);$cgst=$sgst=$igst=0.0;
                if($is_quote ? $intra : $customer_gst_type==='intra_state'){$cgst=round($amount*$gst/200,2);$sgst=$cgst;}elseif($is_quote || $customer_gst_type==='inter_state'){$igst=round($amount*$gst/100,2);}
                $total=round($amount+$cgst+$sgst+$igst,2);
                if($item_id){
                    $master=$wpdb->get_row($wpdb->prepare("SELECT item_name,unit_id,hsn_code,gst_rate FROM {$this->prefix}items WHERE id=%d",$item_id),ARRAY_A);
                    if($master){if($description==='')$description=(string)$master['item_name'];if(empty($it['unit_id']))$it['unit_id']=(int)$master['unit_id'];if(empty($it['hsn_sac']))$it['hsn_sac']=$master['hsn_code'];}
                }
                $clean_items[]=['item_id'=>$item_id?:null,'description'=>$description,'hsn_sac'=>sanitize_text_field((string)($it['hsn_sac']??'')),'qty'=>$qty,'unit_id'=>(int)($it['unit_id']??0)?:null,'rate'=>$rate,'amount'=>$amount,'gst_rate'=>$gst,'cgst_amount'=>$cgst,'sgst_amount'=>$sgst,'igst_amount'=>$igst,'total_amount'=>$total];
                $sum_amount+=$amount;$sum_cgst+=$cgst;$sum_sgst+=$sgst;$sum_igst+=$igst;$sum_total+=$total;
            }
            if(!$clean_items) wp_send_json_error(['message'=>'At least one valid Item is required']);
            $items=$clean_items;
            $other=round((float)($data['other_charges']??0),2);$otherGst=round((float)($data['gst_on_other_charge']??0),2);if(!$is_quote&&$customer_gst_type==='no_gst')$otherGst=0.0;$round=round((float)($data['round_off']??0),2);
            $data['taxable_amount']=round($sum_amount,2);$data['cgst_amount']=round($sum_cgst,2);$data['sgst_amount']=round($sum_sgst,2);$data['igst_amount']=round($sum_igst,2);$data['other_charges']=$other;$data['gst_on_other_charge']=$otherGst;$data['round_off']=$round;
            $data[$is_quote?'grand_total':'invoice_total']=round($sum_total+$other+$otherGst+$round,2);
            unset($data['pdf_url']);
        }
        if($key==='material_dispatches'){
            if($wpdb->query('START TRANSACTION')===false)wp_send_json_error(['message'=>'Unable to start dispatch transaction']);
            $dispatch_committed=false;
            register_shutdown_function(function()use($wpdb,&$dispatch_committed){if(!$dispatch_committed)$wpdb->query('ROLLBACK');});
        }
        $row=$this->sanitize_row($m['table'],$data);
        if($key==='labour_contractors' && $contractor_employees_input!==null){
            try{$id=$this->ops_save_contractor_employees($id,$row,$contractor_employees_input);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}
        }elseif($id){ $ok=$wpdb->update($table,$row,['id'=>$id]); if($ok===false) wp_send_json_error(['message'=>$wpdb->last_error]); }
        else { $ok=$wpdb->insert($table,$row); if(!$ok) wp_send_json_error(['message'=>$wpdb->last_error?:'Save failed']); $id=(int)$wpdb->insert_id; }
        if($key==='sites' && array_key_exists('site_sections',$data)) {
            $stored=(string)$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$table} WHERE id=%d", $id));
            if($stored !== (string)$data['site_sections']) {
                wp_send_json_error(['message'=>'Sections could not be saved. Please retry or contact the administrator.']);
            }
        }
        if(!empty($m['child'])) {
            $child=$this->prefix.$m['child']; $deleted=$wpdb->delete($child,[$m['fk']=>$id]);
            if($key==='material_dispatches' && $deleted===false)wp_send_json_error(['message'=>'Unable to replace dispatch items']);
            foreach($items as $it){ if(!is_array($it)) continue; $it[$m['fk']]=$id; $clean=$this->sanitize_row($m['child'],$it); if(!empty($clean['item_id']) || in_array($key,['gst_invoices','quotations','tax_invoices'],true) || !empty($clean['description'])) $ok=$wpdb->insert($child,$clean); if($key==='material_dispatches' && $ok===false)wp_send_json_error(['message'=>'Unable to save dispatch items']); }
        }
        if($key==='material_dispatches' && !empty($data['requisition_id'])) $this->update_requisition_dispatch_status((int)$data['requisition_id']);
        $this->sync_ledgers($key,$id);
        if($key==='material_dispatches'){
            if($wpdb->last_error || $wpdb->query('COMMIT')===false)wp_send_json_error(['message'=>'Unable to save dispatch stock']);
            $dispatch_committed=true;
        }
        if($key==='items') $this->sync_item_opening_stock($id);
        // Keep the two Pending Receipt workflow views in sync with completed receipts.
        if($key==='material_inwards' && (($data['inward_type']??'')==='return') && !empty($data['return_id'])){
            $rid=(int)$data['return_id'];
            $return_total=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(qty),0) FROM {$this->prefix}material_return_items WHERE return_id=%d",$rid));
            $received_total=(float)$wpdb->get_var($wpdb->prepare(
                "SELECT COALESCE(SUM(mii.qty),0)
                 FROM {$this->prefix}material_inward_items mii
                 JOIN {$this->prefix}material_inwards mi ON mi.id=mii.inward_id
                 WHERE mi.return_id=%d
                   AND LOWER(COALESCE(mi.inward_type,''))='return'
                   AND LOWER(COALESCE(mi.status,''))<>'cancelled'",
                $rid
            ));
            $return_status=($return_total>0 && $received_total+0.001>=$return_total)?'received':'partly_received';
            $wpdb->update($this->prefix.'material_returns',['status'=>$return_status,'updated_at'=>$now],['id'=>$rid]);
        }
        if($key==='site_receipts' && !empty($data['dispatch_id'])){
            $wpdb->update($this->prefix.'material_dispatches',['status'=>'received','updated_at'=>$now],['id'=>(int)$data['dispatch_id']]);
        }
        if($key==='vendor_payments' && ($data['payment_type']??'')==='employee_allowance' && !empty($data['allowance_id'])){
            $wpdb->update($this->prefix.'employee_allowances',['status'=>'paid','payment_id'=>$id,'updated_at'=>$now],['id'=>(int)$data['allowance_id'],'status'=>'approved']);
        }
        if($key==='vendor_payments' && ($data['payment_type']??'')==='salary' && !empty($data['salary_slip_id'])){
            $sid=(int)$data['salary_slip_id'];
            $slip=$wpdb->get_row($wpdb->prepare("SELECT net_salary_payable FROM {$this->prefix}salary_slips WHERE id=%d",$sid),ARRAY_A);
            if($slip){
                $payments=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$this->prefix}vendor_payments WHERE payment_type='salary' AND salary_slip_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",$sid));
                $net=(float)$slip['net_salary_payable'];$paid=min($net,max(0,$payments));$bal=max(0,$net-$paid);$st=$bal<=0.01?'paid':($paid>0?'partly_paid':'pending_payment');
                $wpdb->update($this->prefix.'salary_slips',['opening_paid'=>0,'net_salary_paid'=>$paid,'balance_payable'=>$bal,'status'=>$st,'updated_at'=>$now],['id'=>$sid]);
                $this->generate_salary_pdf($sid);
            }
        }
        if($key==='contractor_bills') $this->allocate_contractor_advance($id,(int)($data['contractor_id']??0),(int)($data['site_id']??0),$requested_contractor_advance_adjustment);
        if(in_array($key,['quotations','tax_invoices'],true)) $this->generate_sales_pdf($key,$id);
        wp_send_json_success(['id'=>$id,'message'=>'Saved successfully']);
    }

    private function sync_item_opening_stock($item_id){
        global $wpdb; $p=$this->prefix; $item_id=(int)$item_id;
        if(!$item_id) return;
        $wpdb->delete($p.'stock_ledger',['reference_type'=>'items','reference_id'=>$item_id]);
        $item=$wpdb->get_row($wpdb->prepare("SELECT id,unit_id,opening_qty,opening_rate FROM {$p}items WHERE id=%d",$item_id),ARRAY_A);
        if(!$item) return;
        $qty=max(0,(float)($item['opening_qty']??0));
        if($qty<=0.0001) return;
        $warehouse_id=(int)$wpdb->get_var("SELECT id FROM {$p}warehouses WHERE status='active' ORDER BY id ASC LIMIT 1");
        if(!$warehouse_id) $warehouse_id=(int)$wpdb->get_var("SELECT id FROM {$p}warehouses ORDER BY id ASC LIMIT 1");
        if(!$warehouse_id) return;
        $opening_date=(string)$wpdb->get_var("SELECT financial_year_start FROM {$p}company_settings ORDER BY id DESC LIMIT 1");
        if(!$opening_date || $opening_date==='0000-00-00') $opening_date=current_time('Y-m-d');
        $wpdb->insert($p.'stock_ledger',[
            'transaction_date'=>$opening_date,
            'location_type'=>'WAREHOUSE',
            'warehouse_id'=>$warehouse_id,
            'item_id'=>$item_id,
            'transaction_type'=>'OPENING_STOCK',
            'reference_type'=>'items',
            'reference_id'=>$item_id,
            'qty_in'=>$qty,
            'qty_out'=>0,
            'unit_id'=>(int)($item['unit_id']??0),
            'rate'=>max(0,(float)($item['opening_rate']??0)),
            'created_at'=>current_time('mysql')
        ]);
    }

    private function warehouse_item_rate($warehouse_id,$item_id){
        global $wpdb; $p=$this->prefix; $warehouse_id=(int)$warehouse_id; $item_id=(int)$item_id;
        if(!$warehouse_id || !$item_id) return 0.0;
        $row=$wpdb->get_row($wpdb->prepare("SELECT COALESCE(SUM((qty_in-qty_out)*rate),0) stock_value,COALESCE(SUM(qty_in-qty_out),0) stock_qty FROM {$p}stock_ledger WHERE location_type='WAREHOUSE' AND warehouse_id=%d AND item_id=%d",$warehouse_id,$item_id),ARRAY_A);
        $qty=(float)($row['stock_qty']??0); $value=(float)($row['stock_value']??0);
        if(abs($qty)>0.0001 && $value>0) return round($value/$qty,2);
        $rate=(float)$wpdb->get_var($wpdb->prepare("SELECT opening_rate FROM {$p}items WHERE id=%d",$item_id));
        if($rate>0) return round($rate,2);
        $rate=(float)$wpdb->get_var($wpdb->prepare("SELECT mii.rate FROM {$p}material_inward_items mii JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mi.warehouse_id=%d AND mii.item_id=%d AND mi.status<>'cancelled' ORDER BY mi.inward_date DESC,mii.id DESC LIMIT 1",$warehouse_id,$item_id));
        return round(max(0,$rate),2);
    }

    private function sync_ledgers($key,$id){
        global $wpdb; $p=$this->prefix; $now=current_time('mysql');
        $wpdb->delete($p.'stock_ledger',['reference_type'=>$key,'reference_id'=>$id]);
        $wpdb->delete($p.'site_fund_ledger',['reference_type'=>$key,'reference_id'=>$id]);
        $m=$this->module($key); if(!$m) return;
        $r=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}{$m['table']} WHERE id=%d",$id),ARRAY_A); if(!$r) return;
        $status=strtolower($r['status']??'');
        $activeStatus=in_array($status,['approved','closed','received','paid','issued','dispatched','in transit','in_transit','submitted']);
        if(!$activeStatus) return;

        if($key==='material_inwards'){
            $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}material_inward_items WHERE inward_id=%d",$id),ARRAY_A);
            foreach($items as $it){
                $isPurchase=(($r['inward_type']??'purchase')==='purchase');
                $ledgerQty=$isPurchase?(float)($it['approved_qty']??0):(float)$it['qty'];
                if($ledgerQty<=0.0001) continue;
                $wpdb->insert($p.'stock_ledger',['transaction_date'=>$r['inward_date'],'location_type'=>'WAREHOUSE','warehouse_id'=>$r['warehouse_id'],'item_id'=>$it['item_id'],'transaction_type'=>$isPurchase?'PURCHASE_INWARD':'RETURN_INWARD','reference_type'=>$key,'reference_id'=>$id,'qty_in'=>$ledgerQty,'qty_out'=>0,'unit_id'=>$it['unit_id'],'rate'=>$it['rate'],'created_at'=>$now]);
            }
        }
        if($key==='material_dispatches'){
            $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}material_dispatch_items WHERE dispatch_id=%d",$id),ARRAY_A);
            foreach($items as $it) { $ok=$wpdb->insert($p.'stock_ledger',['transaction_date'=>$r['dispatch_date'],'location_type'=>!empty($r['from_site_id'])?'SITE':'WAREHOUSE','site_id'=>$r['from_site_id']??null,'warehouse_id'=>$r['warehouse_id'],'item_id'=>$it['item_id'],'transaction_type'=>'SITE_DISPATCH','reference_type'=>$key,'reference_id'=>$id,'qty_in'=>0,'qty_out'=>$it['qty'],'unit_id'=>$it['unit_id'],'rate'=>$it['rate'],'created_at'=>$now]); if($ok===false)wp_send_json_error(['message'=>'Unable to save dispatch stock ledger']); }
        }
        if($key==='site_receipts'){
            $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}site_receipt_items WHERE receipt_id=%d",$id),ARRAY_A);
            foreach($items as $it) $wpdb->insert($p.'stock_ledger',['transaction_date'=>$r['receipt_date'],'location_type'=>'SITE','site_id'=>$r['site_id'],'item_id'=>$it['item_id'],'transaction_type'=>'SITE_RECEIPT','reference_type'=>$key,'reference_id'=>$id,'qty_in'=>$it['received_qty'],'qty_out'=>0,'unit_id'=>$it['unit_id'],'rate'=>$it['rate'],'created_at'=>$now]);
        }
        if($key==='site_consumptions'){
            $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}site_consumption_items WHERE consumption_id=%d",$id),ARRAY_A);
            foreach($items as $it) $wpdb->insert($p.'stock_ledger',['transaction_date'=>$r['consumption_date'],'location_type'=>'SITE','site_id'=>$r['site_id'],'item_id'=>$it['item_id'],'transaction_type'=>'SITE_CONSUMPTION','reference_type'=>$key,'reference_id'=>$id,'qty_in'=>0,'qty_out'=>$it['qty'],'unit_id'=>$it['unit_id'],'created_at'=>$now]);
        }
        if($key==='material_returns'){
            $items=$wpdb->get_results($wpdb->prepare("SELECT * FROM {$p}material_return_items WHERE return_id=%d",$id),ARRAY_A);
            foreach($items as $it){
                $wpdb->insert($p.'stock_ledger',['transaction_date'=>$r['return_date'],'location_type'=>'SITE','site_id'=>$r['site_id'],'item_id'=>$it['item_id'],'transaction_type'=>'SITE_RETURN','reference_type'=>$key,'reference_id'=>$id,'qty_in'=>0,'qty_out'=>$it['qty'],'unit_id'=>$it['unit_id'],'rate'=>($it['rate']??0),'created_at'=>$now]);
                if(in_array($status,['received','closed'])) $wpdb->insert($p.'stock_ledger',['transaction_date'=>$r['return_date'],'location_type'=>'WAREHOUSE','warehouse_id'=>$r['warehouse_id'],'item_id'=>$it['item_id'],'transaction_type'=>'RETURN_RECEIVED','reference_type'=>$key,'reference_id'=>$id,'qty_in'=>$it['qty'],'qty_out'=>0,'unit_id'=>$it['unit_id'],'rate'=>($it['rate']??0),'created_at'=>$now]);
            }
        }
        if($key==='site_fund_allocations') $wpdb->insert($p.'site_fund_ledger',['site_id'=>$r['site_id'],'transaction_date'=>$r['fund_date'],'transaction_type'=>'OPENING_BALANCE','reference_type'=>$key,'reference_id'=>$id,'amount_in'=>$r['amount'],'amount_out'=>0,'remarks'=>$r['remarks'],'created_at'=>$now]);
        if($key==='site_expenses' && strtoupper($r['paid_by'])==='SITE') $wpdb->insert($p.'site_fund_ledger',['site_id'=>$r['site_id'],'transaction_date'=>$r['expense_date'],'transaction_type'=>'SITE_EXPENSE','reference_type'=>$key,'reference_id'=>$id,'amount_in'=>0,'amount_out'=>$r['amount'],'remarks'=>$r['particulars'],'created_at'=>$now]);
        if($key==='vendor_payments' && !empty($r['site_id'])) {
            $ptype=sanitize_key((string)($r['payment_type']??''));
            if($ptype==='site_fund') $wpdb->insert($p.'site_fund_ledger',['site_id'=>$r['site_id'],'transaction_date'=>$r['payment_date'],'transaction_type'=>'SITE_FUND_TRANSFER','reference_type'=>$key,'reference_id'=>$id,'amount_in'=>$r['amount'],'amount_out'=>0,'remarks'=>$r['remarks'],'created_at'=>$now]);
            elseif(strtoupper((string)$r['paid_by'])==='SITE') $wpdb->insert($p.'site_fund_ledger',['site_id'=>$r['site_id'],'transaction_date'=>$r['payment_date'],'transaction_type'=>'VENDOR_PAYMENT','reference_type'=>$key,'reference_id'=>$id,'amount_in'=>0,'amount_out'=>$r['amount'],'remarks'=>$r['remarks'],'created_at'=>$now]);
        }
        if($key==='contractor_payments' && strtoupper($r['paid_by'])==='SITE' && !empty($r['site_id'])) $wpdb->insert($p.'site_fund_ledger',['site_id'=>$r['site_id'],'transaction_date'=>$r['payment_date'],'transaction_type'=>'CONTRACTOR_PAYMENT','reference_type'=>$key,'reference_id'=>$id,'amount_in'=>0,'amount_out'=>$r['amount'],'remarks'=>$r['remarks'],'created_at'=>$now]);
    }


    private function update_requisition_dispatch_status($requisition_id){
        global $wpdb; $p=$this->prefix;
        $approved=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(approved_qty),0) FROM {$p}material_requisition_items WHERE requisition_id=%d",$requisition_id));
        $dispatched=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(di.qty),0) FROM {$p}material_dispatch_items di JOIN {$p}material_dispatches d ON d.id=di.dispatch_id WHERE d.requisition_id=%d AND d.status<>'cancelled'",$requisition_id));
        $status=$dispatched<=0.0001?'approved':(($dispatched+0.0001 >= $approved)?'fully_dispatched':'partially_dispatched');
        $wpdb->update($p.'material_requisitions',['status'=>$status,'updated_at'=>current_time('mysql')],['id'=>$requisition_id]);
    }


    public function ajax_purchase_pending_approval(){
        $this->verify_ajax();
        $this->migrate_purchase_approval();
        global $wpdb; $p=$this->prefix;
        $rows=$wpdb->get_results("SELECT mi.id,mi.inward_no,mi.inward_date,mi.vendor_id,v.vendor_name,mi.warehouse_id,w.warehouse_name,mi.total_amount,COUNT(mii.id) item_count FROM {$p}material_inwards mi LEFT JOIN {$p}vendors v ON v.id=mi.vendor_id LEFT JOIN {$p}warehouses w ON w.id=mi.warehouse_id LEFT JOIN {$p}material_inward_items mii ON mii.inward_id=mi.id WHERE LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase' AND mi.status='pending_approval' GROUP BY mi.id ORDER BY mi.id DESC",ARRAY_A);
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_purchase_approval_get(){
        $this->verify_ajax();
        $this->migrate_purchase_approval();
        global $wpdb; $p=$this->prefix; $id=(int)($_POST['id']??0);
        $row=$wpdb->get_row($wpdb->prepare("SELECT mi.*,v.vendor_name,w.warehouse_name FROM {$p}material_inwards mi LEFT JOIN {$p}vendors v ON v.id=mi.vendor_id LEFT JOIN {$p}warehouses w ON w.id=mi.warehouse_id WHERE mi.id=%d AND LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase'",$id),ARRAY_A);
        if(!$row) wp_send_json_error(['message'=>'Purchase not found']);
        if(($row['status']??'')!=='pending_approval') wp_send_json_error(['message'=>'Purchase is no longer pending approval']);
        $row['_items']=$wpdb->get_results($wpdb->prepare("SELECT mii.*,i.item_name,mii.item_price AS master_price,u.unit_name FROM {$p}material_inward_items mii LEFT JOIN {$p}items i ON i.id=mii.item_id LEFT JOIN {$p}units u ON u.id=mii.unit_id WHERE mii.inward_id=%d ORDER BY mii.id",$id),ARRAY_A);
        wp_send_json_success($row);
    }

    public function ajax_purchase_approval_save(){
        $this->verify_ajax();
        $this->migrate_purchase_approval();
        global $wpdb; $p=$this->prefix; $id=(int)($_POST['id']??0);
        $head=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}material_inwards WHERE id=%d AND LOWER(TRIM(COALESCE(inward_type,'')))='purchase'",$id),ARRAY_A);
        if(!$head) wp_send_json_error(['message'=>'Purchase not found']);
        if(($head['status']??'')!=='pending_approval') wp_send_json_error(['message'=>'Purchase is no longer pending approval']);
        $items=json_decode(stripslashes($_POST['items']??'[]'),true);
        if(!is_array($items)||!$items) wp_send_json_error(['message'=>'No items found']);
        $gst_type='no_gst';
        if(!empty($head['vendor_id'])){
            $gst_type=(string)$wpdb->get_var($wpdb->prepare("SELECT gst_type FROM {$p}vendors WHERE id=%d",(int)$head['vendor_id']));
            if(!in_array($gst_type,['intra_state','inter_state','no_gst'],true)) $gst_type='no_gst';
        }
        $sum_taxable=$sum_cgst=$sum_sgst=$sum_igst=$sum_total=0.0; $totalApproved=0.0;
        foreach($items as $it){
            $iid=(int)($it['id']??0); $aq=round((float)($it['approved_qty']??0),2); $submittedRate=isset($it['rate'])?round((float)$it['rate'],2):null;
            $db=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}material_inward_items WHERE id=%d AND inward_id=%d",$iid,$id),ARRAY_A);
            if(!$db) wp_send_json_error(['message'=>'Purchase item not found']);
            $q=(float)$db['qty'];
            if($aq<0 || $aq>$q+0.0001) wp_send_json_error(['message'=>'Approved Qty cannot exceed Qty']);
            $rate=$submittedRate===null?max(0,(float)$db['rate']):max(0,$submittedRate); $gst=max(0,(float)$db['gst_rate']);
            $taxable=round($aq*$rate,2); $cgst=$sgst=$igst=0.0;
            if($gst_type==='intra_state'){ $cgst=round($taxable*$gst/200,2); $sgst=$cgst; }
            elseif($gst_type==='inter_state'){ $igst=round($taxable*$gst/100,2); }
            else { $gst=0.0; }
            $total=round($taxable+$cgst+$sgst+$igst,2);
            $ok=$wpdb->update($p.'material_inward_items',['approved_qty'=>$aq,'rate'=>$rate,'taxable_amount'=>$taxable,'gst_rate'=>$gst,'cgst_amount'=>$cgst,'sgst_amount'=>$sgst,'igst_amount'=>$igst,'total_amount'=>$total],['id'=>$iid,'inward_id'=>$id]);
            if($ok===false) wp_send_json_error(['message'=>'Unable to save Approved Qty: '.$wpdb->last_error]);
            $totalApproved+=$aq; $sum_taxable+=$taxable; $sum_cgst+=$cgst; $sum_sgst+=$sgst; $sum_igst+=$igst; $sum_total+=$total;
        }
        if($totalApproved<=0.0001) wp_send_json_error(['message'=>'Please approve at least one item quantity']);
        $other=round((float)($head['other_expenses']??0),2); $otherGst=round((float)($head['other_expense_gst']??0),2); $round=round((float)($head['round_off']??0),2);
        $grand=round($sum_total+$other+$otherGst+$round,2);
        $wp_uid=get_current_user_id();
        $plugin_uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE wp_user_id=%d LIMIT 1",$wp_uid));
        if(!$plugin_uid) $plugin_uid=$wp_uid;
        $ok=$wpdb->update($p.'material_inwards',['taxable_amount'=>round($sum_taxable,2),'cgst_amount'=>round($sum_cgst,2),'sgst_amount'=>round($sum_sgst,2),'igst_amount'=>round($sum_igst,2),'total_amount'=>$grand,'status'=>'approved','approved_by'=>$plugin_uid,'approved_at'=>current_time('mysql'),'updated_at'=>current_time('mysql')],['id'=>$id]);
        if($ok===false) wp_send_json_error(['message'=>'Unable to approve Purchase: '.$wpdb->last_error]);
        $this->sync_ledgers('material_inwards',$id);
        if(!empty($head['purchase_order_id'])) $this->refresh_purchase_order_status((int)$head['purchase_order_id']);
        wp_send_json_success(['id'=>$id,'status'=>'approved','message'=>'Purchase approved successfully']);
    }

    public function ajax_vendor_outstanding(){
        $this->verify_ajax();
        $this->migrate_vendor_payment_allocations();
        global $wpdb; $p=$this->prefix;
        $vendor_id=(int)($_POST['vendor_id']??0);
        $date_from=sanitize_text_field($_POST['date_from']??'');
        $date_to=sanitize_text_field($_POST['date_to']??'');
        $status=sanitize_key($_POST['status']??'');
        $search=sanitize_text_field($_POST['search']??'');
        $summary=!empty($_POST['summary']);
        $where="LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase' AND LOWER(COALESCE(mi.status,''))<>'cancelled'";
        $args=[];
        if($vendor_id){$where.=' AND mi.vendor_id=%d';$args[]=$vendor_id;}
        if($date_from!==''){$where.=' AND mi.inward_date>=%s';$args[]=$date_from;}
        if($date_to!==''){$where.=' AND mi.inward_date<=%s';$args[]=$date_to;}
        if($search!==''){
            $n='%'.$wpdb->esc_like($search).'%';
            $where.=" AND (mi.inward_no LIKE %s OR COALESCE(mi.supplier_invoice_no,'') LIKE %s OR COALESCE(v.vendor_name,'') LIKE %s)";
            array_push($args,$n,$n,$n);
        }
        $sql="SELECT mi.id,mi.inward_no,mi.inward_date,mi.vendor_id,v.vendor_name,mi.supplier_invoice_no,mi.supplier_invoice_date,mi.total_amount purchase_amount,
            COALESCE((SELECT SUM(a.amount) FROM {$p}vendor_payment_allocations a JOIN {$p}vendor_payments vp ON vp.id=a.payment_id WHERE a.material_inward_id=mi.id AND LOWER(COALESCE(vp.status,'paid'))<>'cancelled'),0)
            +COALESCE((SELECT SUM(vp2.amount) FROM {$p}vendor_payments vp2 WHERE vp2.material_inward_id=mi.id AND LOWER(COALESCE(vp2.status,'paid'))<>'cancelled' AND NOT EXISTS(SELECT 1 FROM {$p}vendor_payment_allocations aa WHERE aa.payment_id=vp2.id)),0) paid_amount
            FROM {$p}material_inwards mi LEFT JOIN {$p}vendors v ON v.id=mi.vendor_id WHERE $where ORDER BY mi.inward_date DESC,mi.id DESC";
        if($args)$sql=$wpdb->prepare($sql,$args);
        $rows=$wpdb->get_results($sql,ARRAY_A)?:[];
        foreach($rows as &$r){
            $r['purchase_amount']=round((float)$r['purchase_amount'],2);
            $r['paid_amount']=round((float)$r['paid_amount'],2);
            $r['outstanding']=max(0,round($r['purchase_amount']-$r['paid_amount'],2));
            $r['payment_status']=$r['paid_amount']<=0.009?'unpaid':($r['outstanding']<=0.009?'paid':'partly_paid');
        } unset($r);
        if($summary){
            $grouped=[];
            foreach($rows as $r){
                $vid=(int)$r['vendor_id'];
                if(!isset($grouped[$vid]))$grouped[$vid]=[
                    'vendor_id'=>$vid,
                    'vendor_name'=>$r['vendor_name']??'',
                    'purchase_count'=>0,
                    'purchase_amount'=>0.0,
                    'paid_amount'=>0.0,
                    'outstanding'=>0.0,
                ];
                $grouped[$vid]['purchase_count']++;
                $grouped[$vid]['purchase_amount']+=round((float)$r['purchase_amount'],2);
                $grouped[$vid]['paid_amount']+=round((float)$r['paid_amount'],2);
                $grouped[$vid]['outstanding']+=round((float)$r['outstanding'],2);
            }
            $rows=array_values($grouped);
            foreach($rows as &$r){
                $r['purchase_amount']=round((float)$r['purchase_amount'],2);
                $r['paid_amount']=round((float)$r['paid_amount'],2);
                $r['outstanding']=max(0,round((float)$r['outstanding'],2));
                $r['payment_status']=$r['paid_amount']<=0.009?'unpaid':($r['outstanding']<=0.009?'paid':'partly_paid');
            } unset($r);
            usort($rows,fn($a,$b)=>strcasecmp((string)$a['vendor_name'],(string)$b['vendor_name']));
        }
        if($status!=='')$rows=array_values(array_filter($rows,fn($r)=>$r['payment_status']===$status));
        else $rows=array_values(array_filter($rows,fn($r)=>(float)$r['outstanding']>0.009));
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_vendor_payment_save(){
        $this->verify_ajax();
        $this->migrate_vendor_payment_allocations();
        global $wpdb; $p=$this->prefix;
        $vendor_id=(int)($_POST['vendor_id']??0);
        $payment_date=sanitize_text_field($_POST['payment_date']??'');
        $payment_mode=sanitize_text_field($_POST['payment_mode']??'');
        $bank_account_id=(int)($_POST['bank_account_id']??0);
        $reference=sanitize_text_field($_POST['transaction_reference']??'');
        $remarks=sanitize_textarea_field($_POST['remarks']??'');
        $total=round((float)($_POST['amount']??0),2);
        if(!$vendor_id)wp_send_json_error(['message'=>'Please select Supplier']);
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$payment_date))wp_send_json_error(['message'=>'Payment Date is required']);
        if($total<=0)wp_send_json_error(['message'=>'Payment Amount must be greater than zero']);
        if(strtolower($payment_mode)==='bank' && !$bank_account_id)wp_send_json_error(['message'=>'Please select Bank']);
        if(strtolower($payment_mode)!=='bank')$bank_account_id=0;

        // Allocate a single vendor payment automatically against the vendor's oldest approved
        // purchase outstanding first. This keeps the form simple while preserving PO-wise history.
        $pos=$wpdb->get_results($wpdb->prepare("SELECT mi.id,mi.total_amount
            FROM {$p}material_inwards mi
            WHERE mi.vendor_id=%d
              AND LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase'
              AND LOWER(COALESCE(mi.status,''))<>'cancelled'
            ORDER BY mi.inward_date ASC,mi.id ASC",$vendor_id),ARRAY_A)?:[];
        $clean=[];$remaining=$total;$vendor_outstanding=0.0;
        foreach($pos as $po){
            $mid=(int)$po['id'];
            $paid=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(a.amount),0) FROM {$p}vendor_payment_allocations a JOIN {$p}vendor_payments vp ON vp.id=a.payment_id WHERE a.material_inward_id=%d AND LOWER(COALESCE(vp.status,'paid'))<>'cancelled'",$mid));
            $legacy=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(vp.amount),0) FROM {$p}vendor_payments vp WHERE vp.material_inward_id=%d AND LOWER(COALESCE(vp.status,'paid'))<>'cancelled' AND NOT EXISTS(SELECT 1 FROM {$p}vendor_payment_allocations aa WHERE aa.payment_id=vp.id)",$mid));
            $out=max(0,round((float)$po['total_amount']-$paid-$legacy,2));
            $vendor_outstanding+=$out;
            if($remaining>0.009 && $out>0.009){
                $amt=min($remaining,$out);
                $clean[]=['material_inward_id'=>$mid,'amount'=>round($amt,2)];
                $remaining=round($remaining-$amt,2);
            }
        }
        $vendor_outstanding=round($vendor_outstanding,2);
        if($vendor_outstanding<=0.009)wp_send_json_error(['message'=>'No Supplier Outstanding available']);
        if($total>$vendor_outstanding+0.009)wp_send_json_error(['message'=>'Payment Amount cannot exceed Supplier Outstanding']);
        if(!$clean||$remaining>0.009)wp_send_json_error(['message'=>'Unable to allocate Payment against Supplier Outstanding']);

        $payment_no=$this->next_no('vendor_payments','VP');$now=current_time('mysql');$wp_uid=get_current_user_id();
        // DDL auto-commits; migrate the legacy unique index BEFORE starting a transaction.
        $indexes=$wpdb->get_results("SHOW INDEX FROM `{$it}` WHERE Key_name='boq_item'",ARRAY_A);
        if($indexes && count($indexes)===2 && ($indexes[0]['Non_unique']??0)==0) {
            $wpdb->query("ALTER TABLE `{$it}` DROP INDEX `boq_item`, ADD KEY `boq_item` (`boq_id`,`item_id`)");
        }
        $wpdb->query('START TRANSACTION');
        $ok=$wpdb->insert($p.'vendor_payments',['payment_no'=>$payment_no,'payment_type'=>'head_office_vendor','vendor_id'=>$vendor_id,'material_inward_id'=>count($clean)===1?$clean[0]['material_inward_id']:null,'payment_date'=>$payment_date,'amount'=>$total,'paid_by'=>'HEAD_OFFICE','payment_mode'=>$payment_mode,'bank_account_id'=>$bank_account_id?:null,'transaction_reference'=>$reference,'remarks'=>$remarks,'status'=>'paid','created_by'=>$wp_uid,'created_at'=>$now,'updated_at'=>$now]);
        if(!$ok){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to save Payment: '.$wpdb->last_error]);}
        $pid=(int)$wpdb->insert_id;
        foreach($clean as $a){$ok=$wpdb->insert($p.'vendor_payment_allocations',['payment_id'=>$pid,'material_inward_id'=>$a['material_inward_id'],'amount'=>$a['amount'],'created_at'=>$now]);if(!$ok){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to allocate Payment: '.$wpdb->last_error]);}}
        $wpdb->query('COMMIT');
        wp_send_json_success(['id'=>$pid,'payment_no'=>$payment_no,'amount'=>$total,'message'=>'Payment saved successfully']);
    }

    public function ajax_pending_approval(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;
        // Normalize legacy unsent statuses into the single workflow status used by this view.
        $wpdb->query("UPDATE {$p}material_requisitions SET status='pending_approval' WHERE status IN ('draft','submitted')");
        // This view must contain ONLY requisitions still awaiting a decision.
        $rows=$wpdb->get_results("SELECT r.id,r.requisition_no,r.requisition_date,r.required_date,r.site_id,s.site_name,u.name requested_by_name,COUNT(ri.id) item_count FROM {$p}material_requisitions r LEFT JOIN {$p}sites s ON s.id=r.site_id LEFT JOIN {$p}users u ON u.id=r.requested_by LEFT JOIN {$p}material_requisition_items ri ON ri.requisition_id=r.id WHERE r.status='pending_approval' GROUP BY r.id ORDER BY r.id DESC",ARRAY_A);
        $scope=$this->employee_site_scope(); if($scope!==null && $scope) $rows=array_values(array_filter($rows,fn($x)=>in_array((int)$x['site_id'],$scope,true)));
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_requisition_approval_get(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;$id=intval($_POST['id']??0);
        $row=$wpdb->get_row($wpdb->prepare("SELECT r.*,s.site_name FROM {$p}material_requisitions r LEFT JOIN {$p}sites s ON s.id=r.site_id WHERE r.id=%d",$id),ARRAY_A);
        if(!$row) wp_send_json_error(['message'=>'Requisition not found']);
        $this->enforce_employee_site_value((int)$row['site_id']);
        $row['_items']=$wpdb->get_results($wpdb->prepare("SELECT ri.*,i.item_name,u.unit_name unit_name,
            ROUND(
                COALESCE(i.opening_qty,0)
                + COALESCE((SELECT SUM(mii.qty) FROM {$p}material_inward_items mii INNER JOIN {$p}material_inwards mi ON mi.id=mii.inward_id WHERE mii.item_id=ri.item_id AND LOWER(COALESCE(mi.status,''))<>'cancelled'),0)
                + COALESCE((SELECT SUM(mri.qty) FROM {$p}material_return_items mri INNER JOIN {$p}material_returns mr ON mr.id=mri.return_id WHERE mri.item_id=ri.item_id AND LOWER(COALESCE(mr.status,''))<>'cancelled'),0)
                - COALESCE((SELECT SUM(mdi.qty) FROM {$p}material_dispatch_items mdi INNER JOIN {$p}material_dispatches md ON md.id=mdi.dispatch_id WHERE mdi.item_id=ri.item_id AND LOWER(COALESCE(md.status,''))<>'cancelled'),0)
            ,2) current_ho_stock
            FROM {$p}material_requisition_items ri
            LEFT JOIN {$p}items i ON i.id=ri.item_id
            LEFT JOIN {$p}units u ON u.id=ri.unit_id
            WHERE ri.requisition_id=%d ORDER BY ri.id",$id),ARRAY_A);
        // Other sites only: use the same available-stock calculation as Site Inventory.
        // Supply both the total and a per-site breakdown so the popup always agrees with its clickable quantity.
        $other_sites=$wpdb->get_results($wpdb->prepare("SELECT id,site_name FROM {$p}sites WHERE id<>%d AND status='active' ORDER BY site_name",(int)$row['site_id']),ARRAY_A);
        foreach($row['_items'] as &$req_item){
            $breakdown=[]; $total=0.0;
            foreach($other_sites as $other_site){
                $stock=$this->site_available_stock((int)$other_site['id'],(int)$req_item['item_id']);
                $available=max(0.0,round((float)($stock['available_qty']??0),2));
                $breakdown[]=['site_id'=>(int)$other_site['id'],'site_name'=>$other_site['site_name'],'available_qty'=>$available];
                $total+=$available;
            }
            $req_item['other_site_stock']=round($total,2);
            $req_item['other_site_stock_breakdown']=$breakdown;
        }
        unset($req_item);
        wp_send_json_success($row);
    }

    public function ajax_requisition_approval_save(){
        $this->verify_ajax();
        $this->migrate_requisition_table();
        global $wpdb; $p=$this->prefix; $id=intval($_POST['id']??0);
        $status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$p}material_requisitions WHERE id=%d",$id));
        if(!$status) wp_send_json_error(['message'=>'Requisition not found']);
        if(!in_array($status,['pending_approval','submitted','draft'],true)) wp_send_json_error(['message'=>'Requisition is no longer pending approval']);

        $items=json_decode(stripslashes($_POST['items']??'[]'),true);
        if(!is_array($items)||!count($items)) wp_send_json_error(['message'=>'No items found']);

        $total_approved=0.0;
        foreach($items as $it){
            $iid=(int)($it['id']??0); $aq=(float)($it['approved_qty']??0);
            $rq=(float)$wpdb->get_var($wpdb->prepare("SELECT requested_qty FROM {$p}material_requisition_items WHERE id=%d AND requisition_id=%d",$iid,$id));
            if($aq<0 || $aq>$rq+0.0001) wp_send_json_error(['message'=>'Approved Qty cannot exceed Requested Qty']);
            $rejected=max(0,$rq-$aq);
            $ok=$wpdb->update($p.'material_requisition_items',['approved_qty'=>$aq,'rejected_qty'=>$rejected],['id'=>$iid,'requisition_id'=>$id]);
            if($ok===false) wp_send_json_error(['message'=>'Unable to save item approval: '.$wpdb->last_error]);
            $total_approved += $aq;
        }

        $final_status = $total_approved > 0.0001 ? 'approved' : 'rejected';
        $updated=$wpdb->update($p.'material_requisitions',[
            'status'=>$final_status,
            'approval_remarks'=>'',
            'approved_by'=>get_current_user_id(),
            'approved_at'=>current_time('mysql'),
            'updated_at'=>current_time('mysql')
        ],['id'=>$id]);
        if($updated===false) wp_send_json_error(['message'=>'Unable to update requisition status: '.$wpdb->last_error]);
        $saved_status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$p}material_requisitions WHERE id=%d",$id));
        if($saved_status!==$final_status) wp_send_json_error(['message'=>'Requisition status was not updated. Please try again.']);

        wp_send_json_success([
            'message'=>$final_status==='approved'?'Requisition approved':'Requisition rejected',
            'status'=>$final_status,
            'id'=>$id
        ]);
    }

    public function ajax_pending_dispatch(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;
        $rows=$wpdb->get_results("SELECT r.id,r.requisition_no,r.required_date,r.site_id,s.site_name,ROUND(SUM(ri.approved_qty),2) approved_qty,ROUND(COALESCE(SUM(ds.dispatched_qty),0),2) dispatched_qty,ROUND(SUM(ri.approved_qty)-COALESCE(SUM(ds.dispatched_qty),0),2) pending_qty FROM {$p}material_requisitions r JOIN {$p}material_requisition_items ri ON ri.requisition_id=r.id LEFT JOIN {$p}sites s ON s.id=r.site_id LEFT JOIN (SELECT di.requisition_item_id,SUM(di.qty) dispatched_qty FROM {$p}material_dispatch_items di JOIN {$p}material_dispatches d ON d.id=di.dispatch_id WHERE d.status<>'cancelled' GROUP BY di.requisition_item_id) ds ON ds.requisition_item_id=ri.id WHERE r.status IN ('approved','partially_dispatched') GROUP BY r.id HAVING pending_qty>0.0001 ORDER BY r.required_date,r.id",ARRAY_A);
        $scope=$this->employee_site_scope(); if($scope!==null && $scope) $rows=array_values(array_filter($rows,fn($x)=>in_array((int)$x['site_id'],$scope,true)));
        wp_send_json_success(['rows'=>$rows]);
    }

    private function dispatch_source_stock($source,$location,$item,$exclude=0){
        global $wpdb;$p=$this->prefix;
        $stock=$source==='site'?$this->site_available_stock($location,$item):['available_qty'=>$this->warehouse_item_stock($location,$item),'rate'=>$this->warehouse_item_rate($location,$item)];
        if($exclude){
            $column=$source==='site'?'from_site_id':'warehouse_id';
            $stock['available_qty']+=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(di.qty),0) FROM {$p}material_dispatch_items di JOIN {$p}material_dispatches d ON d.id=di.dispatch_id WHERE d.id=%d AND d.$column=%d AND di.item_id=%d AND LOWER(COALESCE(d.status,''))<>'cancelled'",$exclude,$location,$item));
        }
        return $stock;
    }

    public function ajax_dispatch_source_stock(){
        $this->verify_ajax();
        $source=sanitize_key($_POST['dispatch_from']??'warehouse');
        $location=(int)($_POST['location_id']??0);$rows=[];
        if(!in_array($source,['site','warehouse'],true))wp_send_json_error(['message'=>'Invalid dispatch source']);
        if($source==='site' && $location){try{$this->ops_site($location);}catch(Throwable $e){wp_send_json_error(['message'=>$e->getMessage()]);}}
        foreach(array_slice((array)($_POST['item_ids']??[]),0,500) as $item){$item=(int)$item;$rows[$item]=$this->dispatch_source_stock($source,$location,$item,(int)($_POST['dispatch_id']??0));}
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_dispatch_prefill(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;$id=intval($_POST['id']??0);
        $row=$wpdb->get_row($wpdb->prepare("SELECT r.id,r.site_id,r.requisition_no,r.status FROM {$p}material_requisitions r WHERE r.id=%d",$id),ARRAY_A);
        if(!$row || !in_array($row['status'],['approved','partially_dispatched'],true)) wp_send_json_error(['message'=>'No approved requisition found']);
        $this->enforce_employee_site_value((int)$row['site_id']);
        $items=$wpdb->get_results($wpdb->prepare("SELECT ri.id,ri.item_id,ri.unit_id,ri.approved_qty,COALESCE(ds.dispatched_qty,0) dispatched_qty,(ri.approved_qty-COALESCE(ds.dispatched_qty,0)) pending_qty FROM {$p}material_requisition_items ri LEFT JOIN (SELECT di.requisition_item_id,SUM(di.qty) dispatched_qty FROM {$p}material_dispatch_items di JOIN {$p}material_dispatches d ON d.id=di.dispatch_id WHERE d.status<>'cancelled' GROUP BY di.requisition_item_id) ds ON ds.requisition_item_id=ri.id WHERE ri.requisition_id=%d HAVING pending_qty>0.0001 ORDER BY ri.id",$id),ARRAY_A);
        if(!$items) wp_send_json_error(['message'=>'No material is pending dispatch']);
        $row['_items']=$items;wp_send_json_success($row);
    }


    private function migrate_site_receipt_items_table(){
        global $wpdb;
        $table=$this->prefix.'site_receipt_items';
        $exists=$wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s",$table));
        if($exists!==$table) return;
        $col=$wpdb->get_row("SHOW COLUMNS FROM `{$table}` LIKE 'rate'",ARRAY_A);
        if(!$col) $wpdb->query("ALTER TABLE `{$table}` ADD `rate` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `unit_id`");
    }

    public function ajax_site_receipt_prefill(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;
        $id=intval($_POST['id']??0);
        if(!$id) wp_send_json_error(['message'=>'Invalid Dispatch']);
        $row=$wpdb->get_row($wpdb->prepare("SELECT d.id,d.dispatch_no,d.site_id,d.status,s.site_name FROM {$p}material_dispatches d LEFT JOIN {$p}sites s ON s.id=d.site_id WHERE d.id=%d",$id),ARRAY_A);
        if(!$row || strtolower((string)($row['status']??''))==='cancelled') wp_send_json_error(['message'=>'Dispatch not found']);
        $items=$wpdb->get_results($wpdb->prepare("SELECT di.id dispatch_item_id,di.item_id,di.qty dispatch_qty,di.unit_id,di.rate,di.remarks FROM {$p}material_dispatch_items di WHERE di.dispatch_id=%d ORDER BY di.id",$id),ARRAY_A);
        if(!$items) wp_send_json_error(['message'=>'No items found in this Dispatch']);
        $row['_items']=$items;
        wp_send_json_success($row);
    }

    private function site_available_stock($site_id,$item_id,$consumption_id=0,$return_id=0){
        global $wpdb; $p=$this->prefix;
        $site_id=(int)$site_id; $item_id=(int)$item_id; $consumption_id=(int)$consumption_id; $return_id=(int)$return_id;
        if(!$site_id || !$item_id) return ['available_qty'=>0,'unit_id'=>0,'received'=>0,'consumed'=>0,'returned'=>0];

        $opening=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(opening_qty),0) FROM {$p}site_opening_stock WHERE site_id=%d AND item_id=%d",$site_id,$item_id));

        // Receipts: use receipt header/detail first, but fall back to the linked Dispatch for site/item/unit.
        // This keeps older receipts valid even when a legacy save did not persist one of those IDs correctly.
        $received=(float)$wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(SUM(COALESCE(ri.received_qty,0)),0)
            FROM {$p}site_receipt_items ri
            INNER JOIN {$p}site_receipts r ON r.id=ri.receipt_id
            LEFT JOIN {$p}material_dispatch_items di ON di.id=ri.dispatch_item_id
            LEFT JOIN {$p}material_dispatches d ON d.id=r.dispatch_id
            WHERE COALESCE(NULLIF(r.site_id,0),d.site_id)=%d
              AND COALESCE(NULLIF(ri.item_id,0),di.item_id)=%d
              AND LOWER(COALESCE(r.status,''))<>'cancelled'
        ",$site_id,$item_id));

        $consumed=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ci.qty),0) FROM {$p}site_consumption_items ci INNER JOIN {$p}site_consumptions c ON c.id=ci.consumption_id WHERE c.site_id=%d AND ci.item_id=%d AND LOWER(COALESCE(c.status,''))<>'cancelled'",$site_id,$item_id));
        $returned=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(ri.qty),0) FROM {$p}material_return_items ri INNER JOIN {$p}material_returns r ON r.id=ri.return_id WHERE r.site_id=%d AND ri.item_id=%d AND LOWER(COALESCE(r.status,''))<>'cancelled'",$site_id,$item_id));

        $unit_id=(int)$wpdb->get_var($wpdb->prepare("
            SELECT COALESCE(NULLIF(ri.unit_id,0),di.unit_id)
            FROM {$p}site_receipt_items ri
            INNER JOIN {$p}site_receipts r ON r.id=ri.receipt_id
            LEFT JOIN {$p}material_dispatch_items di ON di.id=ri.dispatch_item_id
            LEFT JOIN {$p}material_dispatches d ON d.id=r.dispatch_id
            WHERE COALESCE(NULLIF(r.site_id,0),d.site_id)=%d
              AND COALESCE(NULLIF(ri.item_id,0),di.item_id)=%d
              AND LOWER(COALESCE(r.status,''))<>'cancelled'
            ORDER BY r.receipt_date DESC,ri.id DESC LIMIT 1
        ",$site_id,$item_id));

        if(!$unit_id) $unit_id=(int)$wpdb->get_var($wpdb->prepare("SELECT unit_id FROM {$p}site_opening_stock WHERE site_id=%d AND item_id=%d ORDER BY id DESC LIMIT 1",$site_id,$item_id));

        // Legacy-safe fallback: older transactions may point to a different Item ID
        // even though the visible Item Name is the same (for example, duplicate master
        // rows created during testing). In that case calculate stock by normalized Item
        // Name so historical receipts remain usable in Site Consumption.
        $item_name=(string)$wpdb->get_var($wpdb->prepare("SELECT item_name FROM {$p}items WHERE id=%d",$item_id));
        if($item_name!==''){
            $norm=mb_strtolower(trim($item_name),'UTF-8');
            $received_by_name=(float)$wpdb->get_var($wpdb->prepare("
                SELECT COALESCE(SUM(COALESCE(ri.received_qty,0)),0)
                FROM {$p}site_receipt_items ri
                INNER JOIN {$p}site_receipts r ON r.id=ri.receipt_id
                LEFT JOIN {$p}material_dispatch_items di ON di.id=ri.dispatch_item_id
                LEFT JOIN {$p}material_dispatches d ON d.id=r.dispatch_id
                LEFT JOIN {$p}items ii ON ii.id=COALESCE(NULLIF(ri.item_id,0),di.item_id)
                WHERE COALESCE(NULLIF(r.site_id,0),d.site_id)=%d
                  AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                  AND LOWER(COALESCE(r.status,''))<>'cancelled'
            ",$site_id,$norm));
            if($received_by_name>$received+0.0001){
                $received=$received_by_name;
                $consumed=(float)$wpdb->get_var($wpdb->prepare("
                    SELECT COALESCE(SUM(ci.qty),0)
                    FROM {$p}site_consumption_items ci
                    INNER JOIN {$p}site_consumptions c ON c.id=ci.consumption_id
                    LEFT JOIN {$p}items ii ON ii.id=ci.item_id
                    WHERE c.site_id=%d AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                      AND LOWER(COALESCE(c.status,''))<>'cancelled'
                ",$site_id,$norm));
                $returned=(float)$wpdb->get_var($wpdb->prepare("
                    SELECT COALESCE(SUM(ri.qty),0)
                    FROM {$p}material_return_items ri
                    INNER JOIN {$p}material_returns r ON r.id=ri.return_id
                    LEFT JOIN {$p}items ii ON ii.id=ri.item_id
                    WHERE r.site_id=%d AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                      AND LOWER(COALESCE(r.status,''))<>'cancelled'
                ",$site_id,$norm));
                $unit_id=(int)$wpdb->get_var($wpdb->prepare("
                    SELECT COALESCE(NULLIF(ri.unit_id,0),di.unit_id)
                    FROM {$p}site_receipt_items ri
                    INNER JOIN {$p}site_receipts r ON r.id=ri.receipt_id
                    LEFT JOIN {$p}material_dispatch_items di ON di.id=ri.dispatch_item_id
                    LEFT JOIN {$p}material_dispatches d ON d.id=r.dispatch_id
                    LEFT JOIN {$p}items ii ON ii.id=COALESCE(NULLIF(ri.item_id,0),di.item_id)
                    WHERE COALESCE(NULLIF(r.site_id,0),d.site_id)=%d
                      AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                      AND LOWER(COALESCE(r.status,''))<>'cancelled'
                    ORDER BY r.receipt_date DESC,ri.id DESC LIMIT 1
                ",$site_id,$norm));
            }
        }

        $available=$opening+$received-$consumed-$returned;
        if($consumption_id){
            if(!empty($norm)){
                $available += (float)$wpdb->get_var($wpdb->prepare("
                    SELECT COALESCE(SUM(ci.qty),0)
                    FROM {$p}site_consumption_items ci
                    LEFT JOIN {$p}items ii ON ii.id=ci.item_id
                    WHERE ci.consumption_id=%d AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                ",$consumption_id,$norm));
            } else {
                $available += (float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(qty),0) FROM {$p}site_consumption_items WHERE consumption_id=%d AND item_id=%d",$consumption_id,$item_id));
            }
        }
        if($return_id){
            if(!empty($norm)){
                $available += (float)$wpdb->get_var($wpdb->prepare("
                    SELECT COALESCE(SUM(ri.qty),0)
                    FROM {$p}material_return_items ri
                    LEFT JOIN {$p}items ii ON ii.id=ri.item_id
                    WHERE ri.return_id=%d AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                ",$return_id,$norm));
            } else {
                $available += (float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(qty),0) FROM {$p}material_return_items WHERE return_id=%d AND item_id=%d",$return_id,$item_id));
            }
        }

        // Weighted average receipt rate for the selected Site + Item. This is used
        // for Material Return and remains stable across historical receipt batches.
        $rate=0.0;
        if(!empty($norm)){
            $rate=(float)$wpdb->get_var($wpdb->prepare("
                SELECT COALESCE(SUM(COALESCE(ri.received_qty,0)*COALESCE(NULLIF(ri.rate,0),di.rate,0))/NULLIF(SUM(COALESCE(ri.received_qty,0)),0),0)
                FROM {$p}site_receipt_items ri
                INNER JOIN {$p}site_receipts r ON r.id=ri.receipt_id
                LEFT JOIN {$p}material_dispatch_items di ON di.id=ri.dispatch_item_id
                LEFT JOIN {$p}material_dispatches d ON d.id=r.dispatch_id
                LEFT JOIN {$p}items ii ON ii.id=COALESCE(NULLIF(ri.item_id,0),di.item_id)
                WHERE COALESCE(NULLIF(r.site_id,0),d.site_id)=%d
                  AND LOWER(TRIM(COALESCE(ii.item_name,'')))=%s
                  AND LOWER(COALESCE(r.status,''))<>'cancelled'
            ",$site_id,$norm));
        }

        if($rate<=0){
            $rate=(float)$wpdb->get_var($wpdb->prepare("SELECT opening_rate FROM {$p}site_opening_stock WHERE site_id=%d AND item_id=%d ORDER BY id DESC LIMIT 1",$site_id,$item_id));
        }

        $available += $this->ops_stock_delta($site_id,$item_id);
        if(!$unit_id) $unit_id=(int)$wpdb->get_var($wpdb->prepare("SELECT unit_id FROM {$p}items WHERE id=%d",$item_id));
        return [
            'available_qty'=>round($available,2),
            'unit_id'=>$unit_id,
            'rate'=>round($rate,2),
            'received'=>round($received,2),
            'consumed'=>round($consumed,2),
            'returned'=>round($returned,2),
        ];
    }

    public function ajax_site_stock_items(){
        $this->verify_ajax();
        global $wpdb; $p=$this->prefix;
        $site_id=intval($_POST['site_id']??0);
        $consumption_id=intval($_POST['consumption_id']??0);
        if(!$site_id) wp_send_json_success(['rows'=>[]]);

        // IMPORTANT: Use the exact same transaction sources and balance logic as
        // Materials > Stock. This is now the single source of truth for Site Consumption.
        $map=[];
        $add=function($item_id,$unit_id,$item_name,$qty) use (&$map){
            $item_id=(int)$item_id; $unit_id=(int)$unit_id;
            if(!$item_id) return;
            $key=$item_id.'|'.$unit_id;
            if(!isset($map[$key])) $map[$key]=[
                'id'=>$item_id,
                'label'=>(string)$item_name,
                'unit_id'=>$unit_id,
                'received'=>0.0,
                'consumed'=>0.0,
                'returned'=>0.0,
                'available_qty'=>0.0,
            ];
            $map[$key]['received']+=(float)$qty;
        };

        // Site opening stock = opening stock in.
        $sql=$wpdb->prepare("SELECT so.item_id,so.unit_id,i.item_name,SUM(so.opening_qty) qty
            FROM {$p}site_opening_stock so
            LEFT JOIN {$p}items i ON i.id=so.item_id
            WHERE so.site_id=%d
            GROUP BY so.item_id,so.unit_id,i.item_name",$site_id);
        foreach($wpdb->get_results($sql,ARRAY_A) as $x){
            $key=((int)$x['item_id']).'|'.((int)$x['unit_id']);
            $map[$key]=[
                'id'=>(int)$x['item_id'],'label'=>(string)$x['item_name'],'unit_id'=>(int)$x['unit_id'],
                'received'=>(float)$x['qty'],'consumed'=>0.0,'returned'=>0.0,'available_qty'=>0.0,
            ];
        }

        // Actual Site Receipts = stock in.
        $sql=$wpdb->prepare("SELECT d.item_id,d.unit_id,i.item_name,SUM(d.received_qty) qty
            FROM {$p}site_receipts h
            INNER JOIN {$p}site_receipt_items d ON d.receipt_id=h.id
            LEFT JOIN {$p}items i ON i.id=d.item_id
            WHERE h.site_id=%d AND LOWER(COALESCE(h.status,''))<>'cancelled'
            GROUP BY d.item_id,d.unit_id,i.item_name",$site_id);
        foreach($wpdb->get_results($sql,ARRAY_A) as $x){
            $key=((int)$x['item_id']).'|'.((int)$x['unit_id']);
            if(!isset($map[$key])) $map[$key]=[
                'id'=>(int)$x['item_id'],'label'=>(string)$x['item_name'],'unit_id'=>(int)$x['unit_id'],
                'received'=>0.0,'consumed'=>0.0,'returned'=>0.0,'available_qty'=>0.0,
            ];
            $map[$key]['received']+=(float)$x['qty'];
        }

        // Consumption = stock out.
        $sql=$wpdb->prepare("SELECT d.item_id,d.unit_id,SUM(d.qty) qty
            FROM {$p}site_consumptions h
            INNER JOIN {$p}site_consumption_items d ON d.consumption_id=h.id
            WHERE h.site_id=%d AND LOWER(COALESCE(h.status,''))<>'cancelled'
            GROUP BY d.item_id,d.unit_id",$site_id);
        foreach($wpdb->get_results($sql,ARRAY_A) as $x){
            $key=((int)$x['item_id']).'|'.((int)$x['unit_id']);
            if(!isset($map[$key])){
                $name=(string)$wpdb->get_var($wpdb->prepare("SELECT item_name FROM {$p}items WHERE id=%d",(int)$x['item_id']));
                $map[$key]=['id'=>(int)$x['item_id'],'label'=>$name,'unit_id'=>(int)$x['unit_id'],'received'=>0.0,'consumed'=>0.0,'returned'=>0.0,'available_qty'=>0.0];
            }
            $map[$key]['consumed']+=(float)$x['qty'];
        }

        // Material Return = stock out from Site.
        $sql=$wpdb->prepare("SELECT d.item_id,d.unit_id,SUM(d.qty) qty
            FROM {$p}material_returns h
            INNER JOIN {$p}material_return_items d ON d.return_id=h.id
            WHERE h.site_id=%d AND LOWER(COALESCE(h.status,''))<>'cancelled'
            GROUP BY d.item_id,d.unit_id",$site_id);
        foreach($wpdb->get_results($sql,ARRAY_A) as $x){
            $key=((int)$x['item_id']).'|'.((int)$x['unit_id']);
            if(!isset($map[$key])){
                $name=(string)$wpdb->get_var($wpdb->prepare("SELECT item_name FROM {$p}items WHERE id=%d",(int)$x['item_id']));
                $map[$key]=['id'=>(int)$x['item_id'],'label'=>$name,'unit_id'=>(int)$x['unit_id'],'received'=>0.0,'consumed'=>0.0,'returned'=>0.0,'available_qty'=>0.0];
            }
            $map[$key]['returned']+=(float)$x['qty'];
        }

        foreach($this->ops_transfer_movements($site_id) as $x){
            $key=$x['item_id'].'|'.$x['unit_id'];
            if(!isset($map[$key])) $map[$key]=['id'=>$x['item_id'],'label'=>$x['item_name'],'unit_id'=>$x['unit_id'],'received'=>0,'consumed'=>0,'returned'=>0,'available_qty'=>0];
            $map[$key]['received']+=$x['in'];$map[$key]['returned']+=$x['out'];
        }

        // When editing a consumption, add that record's own quantity back to availability.
        if($consumption_id){
            $sql=$wpdb->prepare("SELECT item_id,unit_id,SUM(qty) qty FROM {$p}site_consumption_items WHERE consumption_id=%d GROUP BY item_id,unit_id",$consumption_id);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x){
                $key=((int)$x['item_id']).'|'.((int)$x['unit_id']);
                if(isset($map[$key])) $map[$key]['consumed']-=(float)$x['qty'];
            }
        }

        $rows=[];
        foreach($map as $x){
            $x['available_qty']=round((float)$x['received']-(float)$x['consumed']-(float)$x['returned'],2);
            if($x['available_qty']<=0.0001) continue;
            if($x['label']==='') $x['label']=(string)$wpdb->get_var($wpdb->prepare("SELECT item_name FROM {$p}items WHERE id=%d",$x['id']));
            if($x['label']==='') continue;
            $rows[]=['id'=>$x['id'],'label'=>$x['label'],'available_qty'=>$x['available_qty'],'unit_id'=>$x['unit_id']];
        }
        usort($rows,function($a,$b){return strcasecmp($a['label'],$b['label']);});
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_site_stock_available(){
        $this->verify_ajax();
        $site_id=intval($_POST['site_id']??0); $item_id=intval($_POST['item_id']??0); $consumption_id=intval($_POST['consumption_id']??0); $return_id=intval($_POST['return_id']??0);
        wp_send_json_success($this->site_available_stock($site_id,$item_id,$consumption_id,$return_id));
    }

    public function ajax_delete(){
        global $wpdb;
        $this->verify_ajax(); global $wpdb; $key=sanitize_key($_POST['module']??'');$this->deny_employee_settings($key);$id=intval($_POST['id']??0);$m=$this->module($key);if(!$m||!$id)wp_send_json_error(['message'=>'Invalid request']);
        if($key==='contractor_bills' && (int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$this->prefix}time_entries WHERE bill_id=%d",$id))) wp_send_json_error(['message'=>'A calculated Contractor Bill cannot be deleted.']);
        if($key==='sites' && $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}site_transfers WHERE from_site_id=%d OR to_site_id=%d LIMIT 1",$id,$id))) wp_send_json_error(['message'=>'This Site is linked to a Site Transfer. Mark it inactive instead.']);
        if($key==='items' && $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}site_transfer_items WHERE item_id=%d LIMIT 1",$id))) wp_send_json_error(['message'=>'This Item is linked to a Site Transfer. Mark it inactive instead.']);
        if($key==='labour_contractors' && $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}contractor_employees WHERE contractor_id=%d LIMIT 1",$id))) wp_send_json_error(['message'=>'This Contractor has linked Employees. Mark it inactive instead.']);
        $deleted_salary_slip_id=0;
        if($key==='vendor_payments') $deleted_salary_slip_id=(int)$wpdb->get_var($wpdb->prepare("SELECT salary_slip_id FROM {$this->prefix}vendor_payments WHERE id=%d AND payment_type='salary'",$id));
        if($key==='company_settings') wp_send_json_error(['message'=>'Company Settings cannot be deleted.']);
        if($key==='employee_allowance_limits' && !$this->allowance_admin_allowed()) wp_send_json_error(['message'=>'Only HO/Admin can delete Employee Allowance Limits'],403);
        $g=$this->record_guard($key,$id);
        if(!$g['can_delete']) wp_send_json_error(['message'=>$g['delete_message']?:'This record is already in use and cannot be deleted.']);
        if(!empty($m['child'])) $wpdb->delete($this->prefix.$m['child'],[$m['fk']=>$id]);
        $wpdb->delete($this->prefix.'stock_ledger',['reference_type'=>$key,'reference_id'=>$id]);
        $wpdb->delete($this->prefix.'site_fund_ledger',['reference_type'=>$key,'reference_id'=>$id]);
        $wpdb->delete($this->prefix.'documents',['module'=>$key,'reference_id'=>$id]);
        $wpdb->delete($this->prefix.$m['table'],['id'=>$id]);
        if($deleted_salary_slip_id){
            $net=(float)$wpdb->get_var($wpdb->prepare("SELECT net_salary_payable FROM {$this->prefix}salary_slips WHERE id=%d",$deleted_salary_slip_id));
            $paid=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$this->prefix}vendor_payments WHERE payment_type='salary' AND salary_slip_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",$deleted_salary_slip_id));
            $paid=min($net,max(0,$paid));$bal=max(0,$net-$paid);$st=$bal<=0.01?'paid':($paid>0?'partly_paid':'pending_payment');
            $wpdb->update($this->prefix.'salary_slips',['opening_paid'=>0,'net_salary_paid'=>$paid,'balance_payable'=>$bal,'status'=>$st,'updated_at'=>current_time('mysql')],['id'=>$deleted_salary_slip_id]);
            $this->generate_salary_pdf($deleted_salary_slip_id);
        }
        wp_send_json_success(['message'=>'Deleted']);
    }

    private function option_rows($type) {
        global $wpdb; $rows=[];
        switch($type){
            case 'banks':$rows=$wpdb->get_results("SELECT id,CONCAT(bank_name,' - ',account_number) label,bank_name,account_holder_name,account_number,ifsc_code,branch_name,account_type,upi_id FROM {$this->prefix}bank_accounts WHERE status='active' ORDER BY bank_name,account_number",ARRAY_A);break;
            case 'designations':$rows=$wpdb->get_results("SELECT id,designation_name label FROM {$this->prefix}designations WHERE status='active' ORDER BY designation_name",ARRAY_A);break;
            case 'states':$rows=$wpdb->get_results("SELECT id,state_name label FROM {$this->prefix}states WHERE status='active' ORDER BY state_name",ARRAY_A);break;
            case 'sites':$rows=$wpdb->get_results("SELECT id,site_name label,customer_id FROM {$this->prefix}sites WHERE status='active' ORDER BY site_name",ARRAY_A);break;
            case 'customers':$rows=$wpdb->get_results("SELECT id,customer_name label FROM {$this->prefix}customers WHERE status='active' ORDER BY customer_name",ARRAY_A);break;
            case 'vendors':$rows=$wpdb->get_results("SELECT id,vendor_name label FROM {$this->prefix}vendors WHERE status='active' ORDER BY vendor_name",ARRAY_A);break;
            case 'contractors':$rows=$wpdb->get_results("SELECT id,contractor_name label FROM {$this->prefix}labour_contractors WHERE status='active' ORDER BY contractor_name",ARRAY_A);break;
            case 'categories':$rows=$wpdb->get_results("SELECT id,category_name label FROM {$this->prefix}item_categories WHERE status='active' ORDER BY category_name",ARRAY_A);break;
            case 'sections':$rows=$wpdb->get_results("SELECT id,section_name label FROM {$this->prefix}sections WHERE status='active' ORDER BY section_name",ARRAY_A);break;
            case 'expense_categories':$rows=$wpdb->get_results("SELECT id,category_name label FROM {$this->prefix}expense_categories WHERE status='active' ORDER BY category_name",ARRAY_A);break;
            case 'allowance_types':$rows=$wpdb->get_results("SELECT id,allowance_type_name label FROM {$this->prefix}allowance_types WHERE status='active' ORDER BY allowance_type_name",ARRAY_A);break;
            case 'units':$rows=$wpdb->get_results("SELECT id,unit_name label FROM {$this->prefix}units WHERE status='active' ORDER BY unit_name",ARRAY_A);break;
            case 'gst_rates':$rows=$wpdb->get_results("SELECT CAST(gst_rate AS CHAR) id,CONCAT(TRIM(TRAILING '.00' FROM CAST(gst_rate AS CHAR)),'%') label FROM {$this->prefix}gst_rates WHERE status='active' ORDER BY gst_rate",ARRAY_A);break;
            case 'items':$rows=$wpdb->get_results("SELECT id,item_name label,unit_id,category_id,hsn_code,gst_rate,opening_rate FROM {$this->prefix}items WHERE status='active' ORDER BY item_name",ARRAY_A);break;
            case 'warehouses':$rows=$wpdb->get_results("SELECT id,warehouse_name label FROM {$this->prefix}warehouses WHERE status='active' ORDER BY warehouse_name",ARRAY_A);break;
            case 'users':$rows=$wpdb->get_results("SELECT id,name AS label,wp_user_id,role,designation FROM {$this->prefix}users WHERE status='active' ORDER BY name,user_id",ARRAY_A);break;
            case 'requisitions':$rows=$wpdb->get_results("SELECT r.id,CONCAT(r.requisition_no,' - ',COALESCE(s.site_name,'')) label FROM {$this->prefix}material_requisitions r LEFT JOIN {$this->prefix}sites s ON s.id=r.site_id WHERE r.status IN ('approved','partially_dispatched') ORDER BY r.id DESC LIMIT 500",ARRAY_A);break;
            case 'dispatches':$rows=$wpdb->get_results("SELECT id,dispatch_no label,site_id FROM {$this->prefix}material_dispatches ORDER BY id DESC LIMIT 500",ARRAY_A);break;
            case 'inwards':$rows=$wpdb->get_results("SELECT id,CONCAT(inward_no,IF(supplier_invoice_no IS NULL OR supplier_invoice_no='', '', CONCAT(' / ',supplier_invoice_no))) label,vendor_id,total_amount FROM {$this->prefix}material_inwards ORDER BY id DESC LIMIT 500",ARRAY_A);break;
            case 'returns':$rows=$wpdb->get_results("SELECT r.id,CONCAT(r.return_no,' - ',COALESCE(s.site_name,'')) label,r.warehouse_id,r.return_by
                FROM {$this->prefix}material_returns r
                LEFT JOIN {$this->prefix}sites s ON s.id=r.site_id
                WHERE LOWER(COALESCE(r.status,''))<>'cancelled'
                  AND NOT EXISTS (
                    SELECT 1 FROM {$this->prefix}material_inwards mi
                    WHERE mi.return_id=r.id
                      AND LOWER(COALESCE(mi.inward_type,''))='return'
                      AND LOWER(COALESCE(mi.status,''))<>'cancelled'
                  )
                ORDER BY r.id DESC LIMIT 500",ARRAY_A);break;
            case 'contractor_bills':$rows=$wpdb->get_results("SELECT id,CONCAT(bill_no,' / ',COALESCE(bill_reference,'')) label,contractor_id,site_id,total_amount FROM {$this->prefix}contractor_bills ORDER BY id DESC LIMIT 500",ARRAY_A);break;
            case 'gst_invoices':$rows=$wpdb->get_results("SELECT id,invoice_no label,customer_id,site_id,invoice_total FROM {$this->prefix}gst_invoices ORDER BY id DESC LIMIT 500",ARRAY_A);break;
        }
        return $rows;
    }

    public function ajax_options(){
        $this->verify_ajax();
        $types_raw = sanitize_text_field($_POST['types'] ?? '');
        if ($types_raw !== '') {
            $allowed = ['sites','customers','vendors','contractors','categories','expense_categories','sections','allowance_types','units','gst_rates','items','warehouses','users','designations','states','banks','requisitions','dispatches','inwards','returns','contractor_bills','gst_invoices'];
            $out = [];
            foreach (array_unique(array_filter(array_map('sanitize_key', explode(',', $types_raw)))) as $type) {
                if (in_array($type, $allowed, true)) $out[$type] = $this->option_rows($type);
            }
            wp_send_json_success($out);
        }
        $type=sanitize_key($_POST['type']??'');
        wp_send_json_success($this->option_rows($type));
    }


    private function migrate_quotation_layout(){
        global $wpdb;
        $table=$this->prefix.'quotation_items';
        foreach ([
            'length'=>"DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER hsn_sac",
            'height'=>"DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER length",
            'nos'=>"DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER height"
        ] as $col=>$def){
            if(!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$table}` LIKE %s",$col))) $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `{$col}` {$def}");
        }
        foreach(['quotations','tax_invoices'] as $head){
            $headTable=$this->prefix.$head;
            if(!$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$headTable}` LIKE %s",'gst_on_other_charge'))){
                $wpdb->query("ALTER TABLE `{$headTable}` ADD COLUMN `gst_on_other_charge` DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER `other_charges`");
            }
        }
    }

    public function ajax_sales_register(){
        $this->verify_ajax();
        global $wpdb;$p=$this->prefix;
        $kind=sanitize_key($_POST['kind']??'quotation_master');
        $page=max(1,(int)($_POST['page']??1));$export=!empty($_POST['export_all']);$offset=($page-1)*20;
        $search=sanitize_text_field($_POST['search']??'');$from=sanitize_text_field($_POST['date_from']??'');$to=sanitize_text_field($_POST['date_to']??'');
        $customer=(int)($_POST['customer_id']??0);$site=(int)($_POST['site_id']??0);$category=(int)($_POST['category_id']??0);$item=(int)($_POST['item_id']??0);
        $is_quote=strpos($kind,'quotation')===0;
        $head=$is_quote?'quotations':'tax_invoices';$detail=$is_quote?'quotation_items':'tax_invoice_items';$fk=$is_quote?'quotation_id':'invoice_id';
        $no=$is_quote?'quotation_no':'invoice_no';$date=$is_quote?'quotation_date':'invoice_date';$total=$is_quote?'grand_total':'invoice_total';
        $items_mode=(substr($kind,-5)==='items');
        $where='1=1';$args=[];
        if($from!==''){$where.=" AND h.$date>=%s";$args[]=$from;} if($to!==''){$where.=" AND h.$date<=%s";$args[]=$to;}
        if($customer){$where.=' AND h.customer_id=%d';$args[]=$customer;} if($site){$where.=' AND h.site_id=%d';$args[]=$site;}
        if($items_mode){if($category){$where.=' AND it.category_id=%d';$args[]=$category;}if($item){$where.=' AND d.item_id=%d';$args[]=$item;}}
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            if($items_mode){$where.=" AND (h.$no LIKE %s OR COALESCE(c.customer_name,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s OR COALESCE(i.item_name,d.description,'') LIKE %s OR COALESCE(d.hsn_sac,'') LIKE %s)";for($i=0;$i<5;$i++)$args[]=$needle;}
            else{$where.=" AND (h.$no LIKE %s OR COALESCE(c.customer_name,'') LIKE %s OR COALESCE(s.site_name,'') LIKE %s)";for($i=0;$i<3;$i++)$args[]=$needle;}
        }
        if($items_mode){
            $fromSql=" FROM {$p}{$detail} d INNER JOIN {$p}{$head} h ON h.id=d.$fk LEFT JOIN {$p}customers c ON c.id=h.customer_id LEFT JOIN {$p}sites s ON s.id=h.site_id LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}item_categories it ON it.id=i.category_id LEFT JOIN {$p}units u ON u.id=d.unit_id WHERE $where";
            $quoteExtra=$is_quote?'d.length,d.height,d.nos,':'0 length,0 height,0 nos,';
            $select="SELECT d.id,h.id head_id,h.$no document_no,h.$date document_date,h.customer_id,c.customer_name,h.site_id,s.site_name,i.category_id,it.category_name,d.item_id,COALESCE(i.item_name,d.description) item_name,d.description,d.hsn_sac,$quoteExtra d.qty,d.unit_id,u.unit_name,d.rate,d.amount,d.gst_rate,d.cgst_amount,d.sgst_amount,d.igst_amount,d.total_amount";
            $order=" ORDER BY h.$date DESC,h.id DESC,d.id ASC";
        }else{
            $fromSql=" FROM {$p}{$head} h LEFT JOIN {$p}customers c ON c.id=h.customer_id LEFT JOIN {$p}sites s ON s.id=h.site_id WHERE $where";
            $quoteMasterExtra=$is_quote?',h.valid_till':'';
            $select="SELECT h.id,h.$no document_no,h.$date document_date{$quoteMasterExtra},h.customer_id,c.customer_name,h.site_id,s.site_name,h.taxable_amount,h.cgst_amount,h.sgst_amount,h.igst_amount,h.other_charges,h.gst_on_other_charge,h.round_off,h.$total document_total,h.pdf_url";
            $order=" ORDER BY h.$date DESC,h.id DESC";
        }
        $count="SELECT COUNT(*) $fromSql";$sql="$select $fromSql $order";if(!$export)$sql.=$wpdb->prepare(' LIMIT %d OFFSET %d',20,$offset);
        if($args){$count=$wpdb->prepare($count,$args);$sql=$wpdb->prepare($sql,$args);} $totalRows=(int)$wpdb->get_var($count);$rows=$wpdb->get_results($sql,ARRAY_A);
        // PDF is regenerated on demand from the PDF button. Avoid rebuilding every row
        // while the register loads; this prevents simultaneous writes and keeps the master fast.
        wp_send_json_success(['rows'=>$rows,'total'=>$totalRows,'page'=>$page,'pages'=>$export?1:max(1,(int)ceil($totalRows/20))]);
    }

    public function ajax_dispatch_pdf(){
        $this->verify_ajax();
        $id=(int)($_POST['id']??0);
        if($id<=0) wp_send_json_error(['message'=>'Invalid dispatch.']);
        $url=$this->generate_dispatch_pdf($id);
        if(!$url) wp_send_json_error(['message'=>'Unable to generate Dispatch Slip.']);
        $url=add_query_arg(['bsm_pdf'=>self::VERSION,'t'=>time()],$url);
        wp_send_json_success(['url'=>$url]);
    }

    private function generate_dispatch_pdf($id){
        global $wpdb;$p=$this->prefix;
        $h=$wpdb->get_row($wpdb->prepare("SELECT d.*,w.warehouse_name,fs.site_name from_site_name,s.site_name,r.requisition_no FROM {$p}material_dispatches d LEFT JOIN {$p}warehouses w ON w.id=d.warehouse_id LEFT JOIN {$p}sites fs ON fs.id=d.from_site_id LEFT JOIN {$p}sites s ON s.id=d.site_id LEFT JOIN {$p}material_requisitions r ON r.id=d.requisition_id WHERE d.id=%d",$id),ARRAY_A);if(!$h)return '';
        $rows=$wpdb->get_results($wpdb->prepare("SELECT di.*,i.item_name,i.hsn_code,u.unit_name FROM {$p}material_dispatch_items di LEFT JOIN {$p}items i ON i.id=di.item_id LEFT JOIN {$p}units u ON u.id=di.unit_id WHERE di.dispatch_id=%d ORDER BY di.id",$id),ARRAY_A)?:[];
        $company=$wpdb->get_row("SELECT * FROM {$p}company_settings ORDER BY id DESC LIMIT 1",ARRAY_A)?:[];
        $theme=$this->bsm_pdf_theme_rgb($company);[$logoJpeg,$logoW,$logoH]=$this->bsm_pdf_image_jpeg($company['logo_url']??'');
        $esc=function($v){$v=(string)$v;$v=function_exists('iconv')?(@iconv('UTF-8','Windows-1252//TRANSLIT',$v)?:$v):$v;return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$v);};
        $txt=function(&$c,$x,$y,$text,$size=9,$bold=false,$color=[0.08,0.15,0.27])use($esc){$font=$bold?'F2':'F1';$c.=sprintf("%.3f %.3f %.3f rg\n",$color[0],$color[1],$color[2]);$c.="BT /$font $size Tf 1 0 0 1 $x $y Tm (".$esc($text).") Tj ET\n";};
        $rect=function(&$c,$x,$y,$w,$hh,$fill=null,$stroke=[0.72,0.82,0.92],$lw=.5){if($fill)$c.=sprintf("%.3f %.3f %.3f rg\n",$fill[0],$fill[1],$fill[2]);$c.=sprintf("%.3f %.3f %.3f RG\n",$stroke[0],$stroke[1],$stroke[2]);$c.="$lw w\n$x $y $w $hh re ".($fill?'B':'S')."\n";};
        $line=function(&$c,$x1,$y1,$x2,$y2,$color=[0.72,0.82,0.92],$lw=.5){$c.=sprintf("%.3f %.3f %.3f RG\n",$color[0],$color[1],$color[2]);$c.="$lw w\n$x1 $y1 m $x2 $y2 l S\n";};
        $c='';$companyName=trim((string)($company['company_name']??''))?:'BizSkill';
        $txt($c,36,806,$companyName,18,true,$theme);$cy=788;
        foreach(array_filter([trim((string)($company['address']??'')),(!empty($company['gstin'])?'GSTIN: '.$company['gstin']:''),implode(' | ',array_filter([(string)($company['phone']??''),(string)($company['email']??'')]))]) as $ln){$txt($c,36,$cy,$ln,7,false);$cy-=11;}
        if($logoJpeg&&$logoW&&$logoH){$scale=min(64/$logoW,54/$logoH);$lw=$logoW*$scale;$lh=$logoH*$scale;$c.=sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im1 Do Q\n",$lw,$lh,559-$lw,780);}
        $rect($c,36,724,523,36,$theme,$theme,0);$txt($c,225,736,'DISPATCH SLIP',17,true,[1,1,1]);

        $rect($c,36,626,330,86,[0.985,0.992,1]);$rect($c,376,626,183,86,[0.965,0.98,0.995]);
        $left=[['Dispatch No.',$h['dispatch_no']??'-'],['Date',!empty($h['dispatch_date'])?date('d/m/Y',strtotime($h['dispatch_date'])):'-'],['Requisition',$h['requisition_no']??'-'],['From',!empty($h['from_site_id'])?($h['from_site_name']??'-'):($h['warehouse_name']??'-')],['To Site',$h['site_name']??'-']];
        $y=696;foreach($left as $mv){$txt($c,47,$y,$mv[0],7,true);$txt($c,112,$y,(string)$mv[1],7);$y-=14;}
        $right=[['Vehicle No.',$h['vehicle_no']??'-'],['Driver Name',$h['driver_name']??'-'],['Driver Mobile',$h['driver_mobile']??'-'],['Courier No.',$h['courier_no']??'-'],['Courier Name',$h['courier_name']??'-']];
        $y=696;foreach($right as $mv){$txt($c,388,$y,$mv[0],7,true);$txt($c,455,$y,(string)$mv[1],7);$y-=14;}

        $top=604;$headers=['Sl','Item','HSN','Dispatch Qty','Unit','Remarks'];$cols=[30,220,60,85,55,73];$tx=36;
        $rect($c,36,$top-24,523,24,$theme,$theme,0);foreach($headers as $i=>$hh){$txt($c,$tx+4,$top-16,$hh,6.7,true,[1,1,1]);$tx+=$cols[$i];}
        $y=$top-24;
        foreach(array_slice($rows,0,17) as $i=>$r){$y-=29;$rect($c,36,$y,523,29,[1,1,1]);$vals=[strval($i+1),(string)($r['item_name']??''),(string)($r['hsn_code']??''),number_format((float)($r['qty']??0),2),(string)($r['unit_name']??''),(string)($r['remarks']??'')];$tx=36;foreach($vals as $j=>$v){$txt($c,$tx+4,$y+10,$v,6.6,$j===1);$tx+=$cols[$j];}}
        if(!$rows){$y-=29;$rect($c,36,$y,523,29,[1,1,1]);$txt($c,46,$y+10,'No dispatch items found.',7);}
        $remarksY=max(100,$y-86);$txt($c,36,$remarksY+55,'Remarks:',8,true,$theme);$rect($c,36,$remarksY,523,45,[0.965,0.98,0.995]);$txt($c,47,$remarksY+25,trim((string)($h['remarks']??''))?:'Material dispatched as per requisition.',7);
        $txt($c,388,50,'For '.$companyName,7,true,$theme);$line($c,388,28,540,28,$theme,.6);$txt($c,414,16,'Authorized Signatory',7);

        $resources="/Font << /F1 5 0 R /F2 6 0 R >>";$objs=[];
        if($logoJpeg&&$logoW&&$logoH)$resources.=" /XObject << /Im1 7 0 R >>";
        $objs=["<< /Type /Catalog /Pages 2 0 R >>","<< /Type /Pages /Kids [3 0 R] /Count 1 >>","<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << $resources >> /Contents 4 0 R >>","<< /Length ".strlen($c)." >>\nstream\n$c\nendstream","<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>","<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"];
        if($logoJpeg&&$logoW&&$logoH)$objs[]="<< /Type /XObject /Subtype /Image /Width $logoW /Height $logoH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($logoJpeg)." >>\nstream\n".$logoJpeg."\nendstream";
        $pdf="%PDF-1.4\n";$offs=[0];foreach($objs as $ix=>$o){$offs[]=strlen($pdf);$pdf.=($ix+1)." 0 obj\n$o\nendobj\n";}$xref=strlen($pdf);$pdf.="xref\n0 ".(count($objs)+1)."\n0000000000 65535 f \n";for($i=1;$i<=count($objs);$i++)$pdf.=sprintf("%010d 00000 n \n",$offs[$i]);$pdf.="trailer\n<< /Size ".(count($objs)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
        $up=wp_upload_dir();if(!empty($up['error']))return '';$dir=trailingslashit($up['basedir']).'bizskill-dispatch-slips';if(!is_dir($dir)&&!wp_mkdir_p($dir))return '';
        $fn=sanitize_file_name('dispatch-slip-'.($h['dispatch_no']?:$id).'-v'.str_replace('.','',self::VERSION).'.pdf');$path=trailingslashit($dir).$fn;if(@file_put_contents($path,$pdf,LOCK_EX)===false)return '';return trailingslashit($up['baseurl']).'bizskill-dispatch-slips/'.$fn;
    }

    public function ajax_sales_pdf(){
        $this->verify_ajax();
        $key=sanitize_key($_POST['module']??'');
        $id=(int)($_POST['id']??0);
        if(!in_array($key,['quotations','tax_invoices'],true) || $id<=0){
            wp_send_json_error(['message'=>'Invalid document.']);
        }
        $url=$this->generate_sales_pdf($key,$id);
        if(!$url) wp_send_json_error(['message'=>'Unable to generate PDF.']);
        // Add a cache-busting query string so the browser never reuses an older PDF tab/cache.
        $url=add_query_arg(['bsm_pdf'=>self::VERSION,'t'=>time()],$url);
        wp_send_json_success(['url'=>$url]);
    }

    private function generate_sales_pdf($key,$id){
        global $wpdb;$p=$this->prefix;$quote=$key==='quotations';
        $head=$quote?'quotations':'tax_invoices';$detail=$quote?'quotation_items':'tax_invoice_items';$fk=$quote?'quotation_id':'invoice_id';
        // Load the document header first with a simple query. Earlier versions used one large
        // JOIN here; one missing/legacy column in a customer/settings table could make the whole
        // PDF query fail and ajax_sales_pdf returned "Unable to generate PDF". Enrich the
        // header in small, fault-tolerant lookups instead.
        $h=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}{$head} WHERE id=%d",$id),ARRAY_A);
        if(!$h)return '';
        $h=array_merge([
            'customer_name'=>'','customer_mobile'=>'','customer_address'=>'','customer_city'=>'','customer_pin'=>'',
            'customer_master_gstin'=>'','customer_state'=>'','site_name'=>'','billing_state'=>'','pos_state'=>''
        ],$h);
        if(!empty($h['customer_id'])){
            $cust=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}customers WHERE id=%d",(int)$h['customer_id']),ARRAY_A)?:[];
            $h['customer_name']=$cust['customer_name']??'';
            $h['customer_mobile']=$cust['mobile']??'';
            $h['customer_address']=$cust['address']??'';
            $h['customer_city']=$cust['city']??'';
            $h['customer_pin']=$cust['pin_code']??($cust['pincode']??'');
            $h['customer_master_gstin']=$cust['gstin']??'';
            $h['customer_state']='';
            if(!empty($cust['state_id'])){
                $h['customer_state']=(string)$wpdb->get_var($wpdb->prepare("SELECT state_name FROM {$p}states WHERE id=%d",(int)$cust['state_id']));
            }
            // Fallback for legacy/new customer records where State is stored as text.
            if(trim((string)$h['customer_state'])===''){
                $h['customer_state']=trim((string)($cust['state']??($cust['state_name']??'')));
            }
        }
        if(!empty($h['site_id'])) $h['site_name']=(string)$wpdb->get_var($wpdb->prepare("SELECT site_name FROM {$p}sites WHERE id=%d",(int)$h['site_id']));
        if(!empty($h['billing_state_id'])) $h['billing_state']=(string)$wpdb->get_var($wpdb->prepare("SELECT state_name FROM {$p}states WHERE id=%d",(int)$h['billing_state_id']));
        if(!empty($h['place_of_supply_state_id'])) $h['pos_state']=(string)$wpdb->get_var($wpdb->prepare("SELECT state_name FROM {$p}states WHERE id=%d",(int)$h['place_of_supply_state_id']));

        $rows=$wpdb->get_results($wpdb->prepare("SELECT d.*,COALESCE(i.item_name,d.description) item_name,u.unit_name FROM {$p}{$detail} d LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id WHERE d.$fk=%d ORDER BY d.id",$id),ARRAY_A)?:[];
        $company=$wpdb->get_row("SELECT * FROM {$p}company_settings ORDER BY id DESC LIMIT 1",ARRAY_A)?:[];
        $company['state_name']='';
        if(!empty($company['state_id'])) $company['state_name']=(string)$wpdb->get_var($wpdb->prepare("SELECT state_name FROM {$p}states WHERE id=%d",(int)$company['state_id']));
        $title=$quote?'QUOTATION':'INVOICE';$no=$quote?$h['quotation_no']:$h['invoice_no'];$date=$quote?$h['quotation_date']:$h['invoice_date'];$grand=$quote?$h['grand_total']:$h['invoice_total'];
        $madeBy='';if(!empty($h['created_by'])){$wu=get_userdata((int)$h['created_by']);if($wu)$madeBy=$wu->display_name;}
        if(!$madeBy)$madeBy='BizSkill';
        $esc=function($v){$v=(string)$v;$v=function_exists('iconv')?(@iconv('UTF-8','Windows-1252//TRANSLIT',$v)?:$v):$v;return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$v);};
        $money=function($v){return number_format((float)$v,2);};
        $wrap=function($text,$max){$text=preg_replace('/\s+/',' ',trim((string)$text));if($text==='')return [''];$words=explode(' ',$text);$out=[];$line='';foreach($words as $w){$test=$line===''?$w:$line.' '.$w;if(strlen($test)>$max&&$line!==''){$out[]=$line;$line=$w;}else{$line=$test;}}if($line!=='')$out[]=$line;return $out;};
        $txt=function(&$c,$x,$y,$text,$size=9,$bold=false,$color=[0.08,0.15,0.27])use($esc){$font=$bold?'F2':'F1';$c.=sprintf("%.3f %.3f %.3f rg\n",$color[0],$color[1],$color[2]);$c.="BT /$font $size Tf 1 0 0 1 $x $y Tm (".$esc($text).") Tj ET\n";};
        $rect=function(&$c,$x,$y,$w,$h,$fill,$stroke=null,$lw=0.5){if($fill){$c.=sprintf("%.3f %.3f %.3f rg\n",$fill[0],$fill[1],$fill[2]);}$c.=$stroke?sprintf("%.3f %.3f %.3f RG\n",$stroke[0],$stroke[1],$stroke[2]):"0.85 0.89 0.94 RG\n";$c.="$lw w\n$x $y $w $h re ".($fill?'B':'S')."\n";};
        $line=function(&$c,$x1,$y1,$x2,$y2,$color=[0.80,0.86,0.94],$lw=0.5){$c.=sprintf("%.3f %.3f %.3f RG\n",$color[0],$color[1],$color[2]);$c.="$lw w\n$x1 $y1 m $x2 $y2 l S\n";};
        // Prepare the Company Settings logo as a JPEG PDF image. The logo may be stored
        // as a Base64 data URI (current setting) or as a normal URL (legacy setting).
        // JPEG files can be embedded directly without GD. PNG/WEBP are converted with GD or
        // Imagick when available so PDF logo rendering does not depend on one PHP extension.
        $logoJpeg='';$logoW=0;$logoH=0;$logoRaw='';
        if(!empty($company['logo_url'])){
            $logoSource=(string)$company['logo_url'];$logoMime='';
            if(strpos($logoSource,'data:image/')===0 && strpos($logoSource,',')!==false){
                $headPart=substr($logoSource,0,strpos($logoSource,','));
                if(preg_match('#^data:([^;]+);base64$#i',$headPart,$mm))$logoMime=strtolower($mm[1]);
                $logoRaw=base64_decode(substr($logoSource,strpos($logoSource,',')+1),true)?:'';
            }elseif(preg_match('#^https?://#i',$logoSource) && function_exists('wp_remote_get')){
                $resp=wp_remote_get($logoSource,['timeout'=>8]);
                if(!is_wp_error($resp) && wp_remote_retrieve_response_code($resp)===200){
                    $logoRaw=(string)wp_remote_retrieve_body($resp);
                    $logoMime=strtolower((string)wp_remote_retrieve_header($resp,'content-type'));
                }
            }
            if($logoRaw!==''){
                $info=@getimagesizefromstring($logoRaw);
                if($info){$logoW=(int)$info[0];$logoH=(int)$info[1];if(!empty($info['mime']))$logoMime=strtolower((string)$info['mime']);}
                if($logoMime==='image/jpeg' && $logoW>0 && $logoH>0){
                    $logoJpeg=$logoRaw;
                }else{
                    $im=false;
                    if(function_exists('imagecreatefromstring'))$im=@imagecreatefromstring($logoRaw);
                    if($im){
                        $logoW=imagesx($im);$logoH=imagesy($im);$bg=imagecreatetruecolor($logoW,$logoH);$white=imagecolorallocate($bg,255,255,255);imagefill($bg,0,0,$white);imagecopy($bg,$im,0,0,0,0,$logoW,$logoH);ob_start();imagejpeg($bg,null,92);$logoJpeg=(string)ob_get_clean();imagedestroy($bg);imagedestroy($im);
                    }elseif(class_exists('Imagick')){
                        try{$ig=new \Imagick();$ig->readImageBlob($logoRaw);$ig->setImageBackgroundColor('white');if(method_exists($ig,'setImageAlphaChannel'))@$ig->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);$ig->setImageFormat('jpeg');$ig->setImageCompressionQuality(92);$logoW=(int)$ig->getImageWidth();$logoH=(int)$ig->getImageHeight();$logoJpeg=(string)$ig->getImagesBlob();$ig->clear();$ig->destroy();}catch(\Throwable $e){$logoJpeg='';}
                    }
                }
            }
        }
        $drawLogo=function(&$c,$x,$y,$maxW,$maxH)use(&$logoJpeg,$logoW,$logoH){if(!$logoJpeg||!$logoW||!$logoH)return false;$scale=min($maxW/$logoW,$maxH/$logoH);$w=$logoW*$scale;$h=$logoH*$scale;$dx=$x+($maxW-$w);$dy=$y+($maxH-$h)/2;$c.=sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im1 Do Q\n",$w,$h,$dx,$dy);return true;};
        $qrJpeg='';$qrW=0;$qrH=0;
        if(!empty($company['upi_qr_url'])){
            $qrSource=(string)$company['upi_qr_url'];$qrRaw='';$qrMime='';
            if(strpos($qrSource,'data:image/')===0 && strpos($qrSource,',')!==false){
                $hp=substr($qrSource,0,strpos($qrSource,','));
                if(preg_match('#^data:([^;]+);base64$#i',$hp,$mm))$qrMime=strtolower($mm[1]);
                $qrRaw=base64_decode(substr($qrSource,strpos($qrSource,',')+1),true)?:'';
            }elseif(preg_match('#^https?://#i',$qrSource) && function_exists('wp_remote_get')){
                $resp=wp_remote_get($qrSource,['timeout'=>8]);
                if(!is_wp_error($resp) && wp_remote_retrieve_response_code($resp)===200){$qrRaw=(string)wp_remote_retrieve_body($resp);$qrMime=strtolower((string)wp_remote_retrieve_header($resp,'content-type'));}
            }
            if($qrRaw!==''){
                $info=@getimagesizefromstring($qrRaw);if($info){$qrW=(int)$info[0];$qrH=(int)$info[1];if(!empty($info['mime']))$qrMime=strtolower((string)$info['mime']);}
                if($qrMime==='image/jpeg' && $qrW>0 && $qrH>0){$qrJpeg=$qrRaw;}
                else if(function_exists('imagecreatefromstring')){ $im=@imagecreatefromstring($qrRaw); if($im){$qrW=imagesx($im);$qrH=imagesy($im);$bg=imagecreatetruecolor($qrW,$qrH);$white=imagecolorallocate($bg,255,255,255);imagefill($bg,0,0,$white);imagecopy($bg,$im,0,0,0,0,$qrW,$qrH);ob_start();imagejpeg($bg,null,94);$qrJpeg=(string)ob_get_clean();imagedestroy($bg);imagedestroy($im);} }
            }
        }
        $drawQr=function(&$c,$x,$y,$maxW,$maxH)use(&$qrJpeg,$qrW,$qrH){if(!$qrJpeg||!$qrW||!$qrH)return false;$scale=min($maxW/$qrW,$maxH/$qrH);$w=$qrW*$scale;$h=$qrH*$scale;$dx=$x+($maxW-$w)/2;$dy=$y+($maxH-$h)/2;$c.=sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im2 Do Q
",$w,$h,$dx,$dy);return true;};

        $c='';
        // Shared professional layout. Quotation colors follow the company logo/theme.
        $defaultNavy=[0.03,0.18,0.36];$defaultBlue=[0.05,0.45,0.78];
        $navy=$defaultNavy;$blue=$defaultBlue;$light=[0.94,0.97,1.00];$light2=[0.975,0.987,1.00];$dark=[0.07,0.13,0.23];$muted=[0.30,0.38,0.48];
        {
            $themeRgb=null;
            $themeHex=trim((string)($company['pdf_theme_color']??''));
            if(preg_match('/^#?([0-9a-fA-F]{6})$/',$themeHex,$tm)){
                $hx=$tm[1];$themeRgb=[hexdec(substr($hx,0,2)),hexdec(substr($hx,2,2)),hexdec(substr($hx,4,2))];
            }elseif($logoRaw!=='' && function_exists('imagecreatefromstring')){
                // Auto-detect a representative non-white/non-black logo color.
                $ti=@imagecreatefromstring($logoRaw);
                if($ti){
                    $tw=imagesx($ti);$th=imagesy($ti);$step=max(1,(int)floor(max($tw,$th)/70));
                    $sr=$sg=$sb=$sw=0.0;
                    for($py=0;$py<$th;$py+=$step){for($px=0;$px<$tw;$px+=$step){
                        $col=imagecolorat($ti,$px,$py);$r=($col>>16)&255;$g=($col>>8)&255;$b=$col&255;
                        $mx=max($r,$g,$b);$mn=min($r,$g,$b);$avg=($r+$g+$b)/3;
                        if($avg>238 || $avg<38 || ($mx-$mn)<8) continue;
                        $sat=($mx-$mn)/255; $wgt=1.0+($sat*3.0);
                        $sr+=$r*$wgt;$sg+=$g*$wgt;$sb+=$b*$wgt;$sw+=$wgt;
                    }}
                    if($sw>0)$themeRgb=[(int)round($sr/$sw),(int)round($sg/$sw),(int)round($sb/$sw)];
                    imagedestroy($ti);
                }
            }
            if($themeRgb){
                $r=max(0,min(255,$themeRgb[0]));$g=max(0,min(255,$themeRgb[1]));$b=max(0,min(255,$themeRgb[2]));
                // Keep headers dark enough for white text while retaining the logo hue.
                $lum=(0.299*$r+0.587*$g+0.114*$b);
                $factor=$lum>145?0.68:($lum>105?0.80:0.94);
                $navy=[($r*$factor)/255,($g*$factor)/255,($b*$factor)/255];
                $blue=[$r/255,$g/255,$b/255];
                // Avoid near-black borders if logo color is extremely dark.
                if($lum<65)$blue=[min(1,$r/255+0.18),min(1,$g/255+0.18),min(1,$b/255+0.18)];
            }
        }

        // Quotation uses a detailed commercial header with customer, UPI QR and bank details.
        $companyName=trim((string)($company['company_name']??''))?:'BizSkill';
        $dateText=$date?date('d-m-Y',strtotime($date)):'-';
        $custText=$h['customer_name']?:'-';
        $siteText=$h['site_name']?:'-';

        {
            // Shared commercial header for Quotation and Invoice.
            $docTitle=$quote?'Quotation':'Invoice';
            $docNoLabel=$quote?'Quotation No.':'Invoice No.';
            $docBorder=$blue;$docHeaderFill=$navy;

            // Build customer/bank rows first so the lower header band can auto-size.
            $customerRows=[];
            if(trim((string)$h['customer_name'])!=='')$customerRows[]=['Name',(string)$h['customer_name']];
            if(trim((string)$h['customer_mobile'])!=='')$customerRows[]=['Mobile',(string)$h['customer_mobile']];
            if(trim((string)$h['customer_address'])!=='')$customerRows[]=['Address',(string)$h['customer_address']];
            if(trim((string)$h['customer_state'])!=='')$customerRows[]=['State',(string)$h['customer_state']];
            $customerGst=trim((string)($h['customer_master_gstin']?:($h['customer_gstin']??'')));
            if($customerGst!=='')$customerRows[]=['GST',$customerGst];
            if(!$quote && $siteText!=='-')$customerRows[]=['Site',$siteText];

            $bankRows=[['Acc Holder',$company['bank_account_holder']??''],['Bank Name',$company['bank_name']??''],['Branch',$company['bank_branch']??''],['IFSC Code',$company['bank_ifsc']??''],['Acc No',$company['bank_account_no']??'']];

            $customerLineCount=0;
            foreach($customerRows as $cr){
                $customerLineCount+=max(1,min(2,count($wrap((string)$cr[1],34))));
            }
            $bankLineCount=0;
            foreach($bankRows as $br){
                if(trim((string)$br[1])==='')continue;
                $bankLineCount+=max(1,min(2,count($wrap((string)$br[1],24))));
            }

            $bandH=max(108,34+($customerLineCount*13),34+($bankLineCount*12));
            $bandH=min(154,$bandH);
            $bandTop=716;
            $bandY=$bandTop-$bandH;
            $rect($c,36,$bandY,523,810-$bandY,[1,1,1],$docBorder,0.9);

            // Company block (left, top).
            $logoShown=$drawLogo($c,44,718,94,82);
            $companyX=$logoShown?146:48;
            $txt($c,$companyX,786,$companyName,14,true,$dark);
            $addr=trim((string)($company['address']??''));
            $companyLines=[];
            if($addr!=='') $companyLines=array_slice($wrap($addr,52),0,2);
            $yy=768;foreach($companyLines as $al){$txt($c,$companyX,$yy,$al,7,false,$dark);$yy-=11;}
            if(!empty($company['phone'])){$txt($c,$companyX,$yy,'Mob. : '.$company['phone'],7,true,$blue);$yy-=11;}
            if(!empty($company['email'])){$txt($c,$companyX,$yy,'Email : '.$company['email'],7,true,$blue);$yy-=11;}
            if(!empty($company['gstin'])){$txt($c,$companyX,$yy,'GST : '.$company['gstin'],7,true,$dark);}

            // Document summary box (right, top).
            $qx=365;$qw=194;$qy=716;$qh=94;
            $rect($c,$qx,$qy,$qw,$qh,[1,1,1],$docBorder,0.9);
            $rect($c,$qx,$qy+$qh-24,$qw,24,$docHeaderFill,$docBorder,0.9);
            $titleEstimate=strlen($docTitle)*12*0.48;
            $txt($c,$qx+($qw-$titleEstimate)/2,$qy+$qh-16,$docTitle,12,true,[1,1,1]);
            if($quote){
                $qrows=[[$docNoLabel,$no],['Date',$dateText],['Valid Till',!empty($h['valid_till'])?date('d-m-Y',strtotime($h['valid_till'])):'-']];
            }else{
                // Invoice PDF should show only Invoice No. and Date.
                $qrows=[[$docNoLabel,$no],['Date',$dateText]];
            }
            $qyrow=$qy+$qh-42;
            foreach($qrows as $qr){
                $txt($c,$qx+6,$qyrow,$qr[0],7.6,true,$dark);
                $valueLines=array_slice($wrap((string)$qr[1],24),0,2);
                $txt($c,$qx+104,$qyrow,$valueLines[0]??'',7.6,true,$dark);
                if(!empty($valueLines[1]))$txt($c,$qx+104,$qyrow-9,$valueLines[1],7.0,true,$dark);
                $line($c,$qx,$qyrow-7,$qx+$qw,$qyrow-7,$docBorder,0.6);
                $qyrow-=22;
            }

            // Customer / QR / Bank detail band. Height adjusts to actual content.
            $custW=250;$qrWBox=82;
            $line($c,36,$bandTop,559,$bandTop,$docBorder,0.9);
            $line($c,36+$custW,$bandY,36+$custW,$bandTop,$docBorder,0.9);
            $line($c,36+$custW+$qrWBox,$bandY,36+$custW+$qrWBox,$bandTop,$docBorder,0.9);
            $txt($c,44,$bandTop-18,'Customer Details',12,true,$dark);

            $cy=$bandTop-38;
            foreach($customerRows as $cr){
                $txt($c,44,$cy,$cr[0],7.3,true,$dark);
                $wrapped=array_slice($wrap((string)$cr[1],34),0,2);
                $txt($c,91,$cy,': '.($wrapped[0]??''),7.1,false,$dark);
                if(!empty($wrapped[1])){$cy-=9;$txt($c,91,$cy,'  '.$wrapped[1],6.9,false,$dark);}
                $cy-=13;
            }

            $txt($c,310,$bandTop-12,'UPI QR',8.0,true,$dark);
            $qrAvailH=max(68,$bandH-30);
            $drawQr($c,289,$bandY+8,76,$qrAvailH);

            $bx=380;$txt($c,$bx,$bandTop-18,'Bank Details',12,true,$dark);
            $by=$bandTop-34;
            foreach($bankRows as $br){
                if(trim((string)$br[1])==='')continue;
                $txt($c,$bx,$by,$br[0],7.2,true,$dark);
                $wrapped=array_slice($wrap((string)$br[1],24),0,2);
                $txt($c,$bx+64,$by,': '.($wrapped[0]??''),7.0,false,$dark);
                if(!empty($wrapped[1])){$by-=10;$txt($c,$bx+64,$by,'  '.$wrapped[1],7.0,false,$dark);}
                $by-=12;
            }

            $tableTop=$bandY-12;
        }

        // Item table.
        // Decide visible invoice tax columns from the saved item allocations, preserving
        // historical invoice presentation if Customer Master GST Type changes later.
        $invoiceGstType=strtolower(trim((string)($cust['gst_type']??'')));
        if(!$quote){
            $hasIgst=false;$hasCgstSgst=false;
            foreach($rows as $taxRow){
                if(abs((float)($taxRow['igst_amount']??0))>0.005)$hasIgst=true;
                if(abs((float)($taxRow['cgst_amount']??0))+abs((float)($taxRow['sgst_amount']??0))>0.005)$hasCgstSgst=true;
            }
            if($hasIgst&&!$hasCgstSgst)$invoiceGstType='inter_state';
            elseif($hasCgstSgst&&!$hasIgst)$invoiceGstType='intra_state';
            elseif($hasIgst&&$hasCgstSgst)$invoiceGstType='mixed';
        }
        $tableX=36;$rowH=29;
        $cols=$quote?[18,85,95,50,35,35,48,40,50,67]:[18,84,94,50,35,36,52];
        $headers=$quote?['#','Item','Description','HSN','Qty','Unit','Rate','GST %','GST','Amount']:['#','Item','Description','HSN','Qty','Unit','Rate'];
        if(!$quote){
            if($invoiceGstType!=='no_gst'){$cols[]=43;$headers[]='GST %';}
            if($invoiceGstType==='intra_state'){$cols=array_merge($cols,[52,52]);$headers=array_merge($headers,['CGST','SGST']);}
            elseif($invoiceGstType==='inter_state'){$cols[]=94;$headers[]='IGST';}
            elseif($invoiceGstType==='mixed'||!in_array($invoiceGstType,['no_gst','intra_state','inter_state'],true)){$cols=array_merge($cols,[31,31,32]);$headers=array_merge($headers,['IGST','CGST','SGST']);}
            // Reserve a readable Amount column BEFORE sizing the other fields.
            // Previously 523 - used width could leave only 17pt for Amount,
            // which clipped the entire last PDF column on inter-state invoices.
            $amountWidth=77;
            $availableForOtherColumns=523-$amountWidth;
            $otherTotal=array_sum($cols);
            if($otherTotal>0){
                $cols=array_map(static fn($w)=>(int)round($w*$availableForOtherColumns/$otherTotal),$cols);
                $cols[count($cols)-1]+=$availableForOtherColumns-array_sum($cols);
            }
            $cols[]=$amountWidth;$headers[]='Amount';
        }
        $tableBorder=$blue;
        $headerBorder=$blue;
        $x=$tableX;$rect($c,$tableX,$tableTop-24,523,24,$navy,$navy,0);
        foreach($headers as $i=>$hh){$txt($c,$x+4,$tableTop-15,$hh,7,true,[1,1,1]);$line($c,$x,$tableTop-24,$x,$tableTop,$headerBorder,0.55);$x+=$cols[$i];}
        $line($c,$tableX+523,$tableTop-24,$tableX+523,$tableTop,$headerBorder,0.55);
        $y=$tableTop-24;
        $maxRows=min($quote?8:8,count($rows));
        for($ri=0;$ri<$maxRows;$ri++){
            $r=$rows[$ri];$y-=$rowH;$fill=$ri%2===0?[1,1,1]:$light2;$rect($c,$tableX,$y,523,$rowH,$fill,$tableBorder,0.65);
            $gstTotal=(float)$r['igst_amount']+(float)$r['cgst_amount']+(float)$r['sgst_amount'];
            $pdfItemName=(string)$r['item_name'];
            $vals=[strval($ri+1),$pdfItemName,(string)$r['description'],(string)($r['hsn_sac']??''),number_format((float)$r['qty'],2),$r['unit_name'],$money($r['rate'])];
            if($quote){$vals[]=number_format((float)$r['gst_rate'],2).'%';$vals[]=$money($gstTotal);}
            else{
                if($invoiceGstType!=='no_gst')$vals[]=number_format((float)$r['gst_rate'],2).'%';
                if($invoiceGstType==='intra_state'){$vals[]=$money($r['cgst_amount']);$vals[]=$money($r['sgst_amount']);}
                elseif($invoiceGstType==='inter_state')$vals[]=$money($r['igst_amount']);
                elseif($invoiceGstType==='mixed'||!in_array($invoiceGstType,['no_gst','intra_state','inter_state'],true)){$vals[]=$money($r['igst_amount']);$vals[]=$money($r['cgst_amount']);$vals[]=$money($r['sgst_amount']);}
            }
            $vals[]=$money($r['total_amount']);
            $limits=$quote?[2,14,16,9,6,6,8,6,8,10]:array_map(static fn($w)=>max(4,(int)floor($w/6)),$cols);$x=$tableX;
            foreach($vals as $ci=>$v){
                $lines=$wrap($v,$limits[$ci]);
                $isItem=$ci===1;
                $txt($c,$x+3,$y+18,$lines[0]??'',(!$quote && $ci>=8 && $ci<=10)?5.4:6.6,$isItem,$dark);
                if(($lines[1]??'')!=='')$txt($c,$x+3,$y+8,$lines[1],(!$quote && $ci>=8 && $ci<=10)?5.4:6.6,$isItem,$dark);
                $x+=$cols[$ci];
            }
            // Draw full cell borders for every column.
            $vx=$tableX;
            foreach($cols as $cw){$line($c,$vx,$y,$vx,$y+$rowH,$tableBorder,0.65);$vx+=$cw;}
            $line($c,$tableX+523,$y,$tableX+523,$y+$rowH,$tableBorder,0.65);
        }

        {
            // Shared bottom section for Quotation and Invoice.
            $bottomTop=$y-10;
            $amountBoxX=500;
            $totalRowH=21;
            $gstTotal=(float)$h['cgst_amount']+(float)$h['sgst_amount']+(float)$h['igst_amount'];
            $otherCharge=(float)($h['other_charges']??0);
            $otherChargeGst=(float)($h['gst_on_other_charge']??0);
            $docTotals=[
                ['Total:', $money($h['taxable_amount'])],
                ...(!$quote ? ($invoiceGstType==='no_gst'?[]:($invoiceGstType==='intra_state'?[['CGST:', $money($h['cgst_amount'])],['SGST:', $money($h['sgst_amount'])]]:($invoiceGstType==='inter_state'?[['IGST:', $money($h['igst_amount'])]]:[['IGST:', $money($h['igst_amount'])],['CGST:', $money($h['cgst_amount'])],['SGST:', $money($h['sgst_amount'])]]))) : [['Total GST:', $money($gstTotal)]]),
                ['Other Charge:', $money($otherCharge)],
                ...($quote || $invoiceGstType!=='no_gst' ? [['GST on Other Charge:', $money($otherChargeGst)]] : []),
                ['Grand Total:', $money($grand)],
            ];
            $rowY=$bottomTop-$totalRowH;
            foreach($docTotals as $sr){
                $rect($c,36,$rowY,523,$totalRowH,[1,1,1],$blue,0.65);
                $line($c,$amountBoxX,$rowY,$amountBoxX,$rowY+$totalRowH,$blue,0.65);
                $label=(string)$sr[0];
                $estimate=strlen($label)*7.7*0.48;
                $txt($c,max(300,$amountBoxX-7-$estimate),$rowY+7,$label,7.7,false,$dark);
                $val='Rs. '.(string)$sr[1];
                $vest=strlen($val)*7.8*0.49;
                $txt($c,max($amountBoxX+4,555-$vest),$rowY+7,$val,7.8,true,$dark);
                $rowY-=$totalRowH;
            }

            // Terms / Authorised Signatory box.
            $boxY=max(64,$rowY-86);
            $boxH=max(72,$rowY-$boxY);
            $splitX=360;
            $rect($c,36,$boxY,523,$boxH,[1,1,1],$blue,0.7);
            $line($c,$splitX,$boxY,$splitX,$boxY+$boxH,$blue,0.7);
            $txt($c,42,$boxY+$boxH-16,'Terms and Conditions:',8.4,true,$dark);
            $terms=trim(strip_tags((string)($h['terms']??'')));
            if($terms!==''){
                $termLines=preg_split('/\r\n|\r|\n/',$terms);
                $ty=$boxY+$boxH-31;
                foreach(array_slice(array_values(array_filter(array_map('trim',$termLines))),0,6) as $tl){
                    foreach(array_slice($wrap($tl,55),0,2) as $wi=>$wl){
                        $prefix=$wi===0?'- ':'  ';
                        $txt($c,42,$ty,$prefix.$wl,7,false,$dark);
                        $ty-=10;
                        if($ty<$boxY+8)break 2;
                    }
                }
            }
            $sigCenterX=459;
            $txt($c,407,$boxY+$boxH-32,'Authorised Signatory',8.2,false,$dark);
            $nameEst=strlen($companyName)*8.5*0.49;
            $txt($c,max($splitX+12,$sigCenterX-$nameEst/2),$boxY+31,$companyName,8.5,true,$dark);
            $dateLine='Date: '.$dateText;
            $dateEst=strlen($dateLine)*7.5*0.49;
            $txt($c,max($splitX+12,$sigCenterX-$dateEst/2),$boxY+15,$dateLine,7.5,false,$dark);
            $summaryTop=$bottomTop;
        }

        // Footer intentionally removed. Build the PDF objects here without drawing footer text.
        $xobjs=[];
        if($logoJpeg)$xobjs[]='/Im1 7 0 R';
        if($qrJpeg)$xobjs[]='/Im2 '.($logoJpeg?8:7).' 0 R';
        $pageResources="/Font << /F1 5 0 R /F2 6 0 R >>".($xobjs?' /XObject << '.implode(' ',$xobjs).' >>':'');
        $objs=[
            "<< /Type /Catalog /Pages 2 0 R >>",
            "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << {$pageResources} >> /Contents 4 0 R >>",
            "<< /Length ".strlen($c)." >>\nstream\n$c\nendstream",
            "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
            "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>"
        ];
        if($logoJpeg)$objs[]="<< /Type /XObject /Subtype /Image /Width {$logoW} /Height {$logoH} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($logoJpeg)." >>\nstream\n".$logoJpeg."\nendstream";
        if($qrJpeg)$objs[]="<< /Type /XObject /Subtype /Image /Width {$qrW} /Height {$qrH} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($qrJpeg)." >>\nstream\n".$qrJpeg."\nendstream";

        $pdf="%PDF-1.4\n";
        $offs=[0];
        foreach($objs as $ix=>$o){
            $offs[]=strlen($pdf);
            $n=$ix+1;
            $pdf.="$n 0 obj\n$o\nendobj\n";
        }
        $xref=strlen($pdf);
        $pdf.="xref\n0 ".(count($objs)+1)."\n0000000000 65535 f \n";
        for($i=1;$i<=count($objs);$i++)$pdf.=sprintf("%010d 00000 n \n",$offs[$i]);
        $pdf.="trailer\n<< /Size ".(count($objs)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";

        $up=wp_upload_dir();
        if(!empty($up['error'])) return '';
        $folder=$quote?'bizskill-quotations':'bizskill-tax-invoices';
        $dir=trailingslashit($up['basedir']).$folder;
        if(!is_dir($dir) && !wp_mkdir_p($dir)) return '';
        $fn=sanitize_file_name(strtolower(str_replace(' ','-',$title)).'-'.$no.'-v'.str_replace('.','',self::VERSION).'.pdf');
        $path=trailingslashit($dir).$fn;
        // Write atomically where possible. This avoids a partial/locked PDF when the same
        // document is regenerated from the master and the PDF button at nearly the same time.
        $tmp=$path.'.tmp-'.wp_generate_password(8,false,false);
        $written=@file_put_contents($tmp,$pdf,LOCK_EX);
        if($written===false){
            $written=@file_put_contents($path,$pdf,LOCK_EX);
        }else{
            if(!@rename($tmp,$path)){
                @copy($tmp,$path);@unlink($tmp);
            }
        }
        if($written===false || !is_file($path) || filesize($path)<100) return '';
        $url=trailingslashit($up['baseurl']).$folder.'/'.$fn;
        $wpdb->update($p.$head,['pdf_url'=>$url],['id'=>$id]);
        return $url;
    }

    public function ajax_dashboard(){
        $this->verify_ajax();
        global $wpdb;
        $p=$this->prefix;
        $today=current_time('Y-m-d');
        $site_id=(int)($_POST['site_id']??0);

        $scope=$this->employee_site_scope();
        if($site_id) $this->enforce_employee_site_value($site_id);

        $site_where="s.status='active'";
        $site_args=[];
        if($site_id){$site_where.=' AND s.id=%d';$site_args[]=$site_id;}
        if($scope!==null && $scope){
            $ph=implode(',',array_fill(0,count($scope),'%d'));
            $site_where.=" AND s.id IN ($ph)";
            foreach($scope as $sid)$site_args[]=(int)$sid;
        }

        $sql="SELECT s.id site_id,s.site_name,s.work_order_value,
            COALESCE((SELECT SUM(cr.amount) FROM {$p}customer_receipts cr WHERE cr.site_id=s.id AND LOWER(COALESCE(cr.status,''))<>'cancelled'),0) received,
            COALESCE((SELECT SUM(l.amount_in-l.amount_out) FROM {$p}site_fund_ledger l WHERE l.site_id=s.id),0) petty_cash_balance,
            COALESCE((SELECT SUM(e.amount) FROM {$p}site_expenses e WHERE e.site_id=s.id AND e.expense_date=%s AND LOWER(COALESCE(e.status,''))<>'cancelled'),0) today_expense,
            COALESCE((SELECT SUM(la.no_of_labour) FROM {$p}labour_attendance la WHERE la.site_id=s.id AND la.attendance_date=%s),0) today_labour,
            (SELECT COUNT(*) FROM {$p}material_requisitions r WHERE r.site_id=s.id AND r.status='pending_approval') pending_approval,
            (SELECT COUNT(*) FROM {$p}material_requisitions r WHERE r.site_id=s.id AND r.status IN ('approved','partially_dispatched','partially dispatched')
                AND EXISTS(SELECT 1 FROM {$p}material_requisition_items ri WHERE ri.requisition_id=r.id AND ri.approved_qty > COALESCE((SELECT SUM(di.qty) FROM {$p}material_dispatch_items di INNER JOIN {$p}material_dispatches d2 ON d2.id=di.dispatch_id WHERE di.requisition_item_id=ri.id AND LOWER(COALESCE(d2.status,''))<>'cancelled'),0)+0.0001)
            ) pending_dispatch,
            (SELECT COUNT(*) FROM {$p}material_dispatches d WHERE d.site_id=s.id AND LOWER(COALESCE(d.status,''))='dispatched'
                AND NOT EXISTS(SELECT 1 FROM {$p}site_receipts sr WHERE sr.dispatch_id=d.id AND LOWER(COALESCE(sr.status,''))<>'cancelled')
            ) pending_receipt,
            (SELECT COUNT(*) FROM {$p}site_receipts sr WHERE sr.site_id=s.id AND LOWER(COALESCE(sr.status,'received'))<>'cancelled'
                AND NOT EXISTS(SELECT 1 FROM {$p}documents dc WHERE dc.module='site_receipts' AND dc.reference_id=sr.id AND LOWER(TRIM(COALESCE(dc.document_type,'')))='receiving challan' AND TRIM(COALESCE(dc.file_url,''))<>'')
            ) pending_challan,
            (SELECT COUNT(*) FROM {$p}contractor_bills cb WHERE cb.site_id=s.id AND LOWER(COALESCE(cb.status,''))<>'cancelled'
                AND NOT EXISTS(SELECT 1 FROM {$p}documents dc WHERE dc.module='contractor_bills' AND dc.reference_id=cb.id AND TRIM(COALESCE(dc.file_url,''))<>'')
            ) pending_contractor_bill_copy,
            (SELECT COUNT(*) FROM {$p}contractor_bills cb WHERE cb.site_id=s.id AND LOWER(COALESCE(cb.status,''))<>'cancelled'
                AND cb.total_amount > COALESCE(cb.advance_adjustment,0)+COALESCE((SELECT SUM(cp.amount) FROM {$p}contractor_payments cp WHERE cp.contractor_bill_id=cb.id AND COALESCE(cp.payment_type,'bill_payment')='bill_payment' AND LOWER(COALESCE(cp.status,''))<>'cancelled'),0)+0.01
            ) pending_contractor_payment,
            CASE WHEN EXISTS(SELECT 1 FROM {$p}labour_attendance la WHERE la.site_id=s.id AND la.attendance_date=%s) THEN 0 ELSE 1 END attendance_pending
            FROM {$p}sites s WHERE $site_where ORDER BY s.site_name";
        $args=[$today,$today,$today];
        foreach($site_args as $a)$args[]=$a;
        $site_rows=$wpdb->get_results($wpdb->prepare($sql,$args),ARRAY_A);

        $totals=[
            'active_sites'=>count($site_rows),'work_order_value'=>0.0,'received'=>0.0,'outstanding'=>0.0,'petty_cash_balance'=>0.0,
            'today_expense'=>0.0,'today_labour'=>0,'pending_approval'=>0,'pending_dispatch'=>0,'pending_receipt'=>0,'pending_challan'=>0,
            'pending_contractor_bill_copy'=>0,'attendance_pending'=>0,'total_pending'=>0,'sites_needing_attention'=>0,'sites_clear'=>0
        ];
        foreach($site_rows as &$x){
            $x['work_order_value']=(float)$x['work_order_value'];
            $x['received']=(float)$x['received'];
            $x['outstanding']=$x['work_order_value']-$x['received'];
            $x['received_pct']=$x['work_order_value']>0 ? ($x['received']/$x['work_order_value']*100) : 0;
            $x['outstanding_pct']=$x['work_order_value']>0 ? ($x['outstanding']/$x['work_order_value']*100) : 0;
            $x['petty_cash_balance']=(float)$x['petty_cash_balance'];
            $x['today_expense']=(float)$x['today_expense'];
            foreach(['pending_approval','pending_dispatch','pending_receipt','pending_challan','pending_contractor_bill_copy','attendance_pending'] as $f)$x[$f]=(int)$x[$f];
            $x['total_pending']=$x['pending_approval']+$x['pending_dispatch']+$x['pending_receipt']+$x['pending_challan']+$x['pending_contractor_bill_copy']+$x['attendance_pending'];
            foreach(['work_order_value','received','outstanding','petty_cash_balance','today_expense'] as $f)$totals[$f]+=(float)$x[$f];
            $totals['today_labour']+=(int)$x['today_labour'];
            foreach(['pending_approval','pending_dispatch','pending_receipt','pending_challan','pending_contractor_bill_copy','attendance_pending','total_pending'] as $f)$totals[$f]+=(int)$x[$f];
            if($x['total_pending']>0)$totals['sites_needing_attention']++;else $totals['sites_clear']++;
        } unset($x);
        // Dashboard Contractor Outstanding: use the same bill-minus-payment logic as
        // the Sitewise Contractor Outstanding report, limited to the Sites visible
        // to this user and the selected Dashboard Site. Include negative balances.
        $totals['contractor_outstanding']=0.0;
        $visible_site_ids=array_values(array_unique(array_map('intval', array_column($site_rows,'site_id'))));
        if($visible_site_ids){
            $site_placeholders=implode(',',array_fill(0,count($visible_site_ids),'%d'));
            $bill_sql="SELECT COALESCE(SUM(total_amount),0) FROM {$p}contractor_bills WHERE LOWER(COALESCE(status,''))<>'cancelled' AND site_id IN ($site_placeholders)";
            $paid_sql="SELECT COALESCE(SUM(amount),0) FROM {$p}contractor_payments WHERE LOWER(COALESCE(status,''))<>'cancelled' AND site_id IN ($site_placeholders)";
            $total_bills=(float)$wpdb->get_var($wpdb->prepare($bill_sql,$visible_site_ids));
            $total_paid=(float)$wpdb->get_var($wpdb->prepare($paid_sql,$visible_site_ids));
            $totals['contractor_outstanding']=$total_bills-$total_paid;
        }
        $totals['received_pct']=$totals['work_order_value']>0 ? ($totals['received']/$totals['work_order_value']*100) : 0;
        $totals['outstanding_pct']=$totals['work_order_value']>0 ? ($totals['outstanding']/$totals['work_order_value']*100) : 0;

        // Pending document copies. Supplier invoice copies are mainly an HO control and may not always carry a Site.
        $invoice_where="LOWER(TRIM(COALESCE(mi.inward_type,'')))='purchase' AND NOT EXISTS (SELECT 1 FROM {$p}documents d WHERE d.module='material_inwards' AND d.reference_id=mi.id AND LOWER(TRIM(COALESCE(d.document_type,'')))='supplier invoice' AND TRIM(COALESCE(d.file_url,''))<>'')";
        $invoice_args=[];
        if($site_id){$invoice_where.=' AND mi.site_id=%d';$invoice_args[]=$site_id;}
        $invoice_sql="SELECT COUNT(*) FROM {$p}material_inwards mi WHERE $invoice_where";
        if($invoice_args)$invoice_sql=$wpdb->prepare($invoice_sql,$invoice_args);
        $pending_invoice_copy=(int)$wpdb->get_var($invoice_sql);

        // Today's activity.
        $site_cond='';$activity_args=[];
        if($site_id){$site_cond=' AND site_id=%d';$activity_args[]=$site_id;}
        $q=function($sql,$args=[]) use($wpdb){return $args?$wpdb->get_var($wpdb->prepare($sql,$args)):$wpdb->get_var($sql);};
        $today_activity=[
            'purchases'=>(int)$q("SELECT COUNT(*) FROM {$p}material_inwards WHERE LOWER(TRIM(COALESCE(inward_type,'')))='purchase' AND inward_date=%s".($site_id?' AND site_id=%d':''),$site_id?[$today,$site_id]:[$today]),
            'dispatches'=>(int)$q("SELECT COUNT(*) FROM {$p}material_dispatches WHERE dispatch_date=%s AND LOWER(COALESCE(status,''))<>'cancelled'".($site_id?' AND site_id=%d':''),$site_id?[$today,$site_id]:[$today]),
            'receipts'=>(int)$q("SELECT COUNT(*) FROM {$p}site_receipts WHERE receipt_date=%s AND LOWER(COALESCE(status,''))<>'cancelled'".($site_id?' AND site_id=%d':''),$site_id?[$today,$site_id]:[$today]),
            'consumptions'=>(int)$q("SELECT COUNT(*) FROM {$p}site_consumptions WHERE consumption_date=%s AND LOWER(COALESCE(status,''))<>'cancelled'".($site_id?' AND site_id=%d':''),$site_id?[$today,$site_id]:[$today]),
            'site_expenses'=>(float)$q("SELECT COALESCE(SUM(amount),0) FROM {$p}site_expenses WHERE expense_date=%s AND LOWER(COALESCE(status,''))<>'cancelled'".($site_id?' AND site_id=%d':''),$site_id?[$today,$site_id]:[$today])
        ];

        $office=[
            'present'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$p}office_attendance WHERE attendance_date=%s AND attendance_status='P'",$today)),
            'absent'=>(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$p}office_attendance WHERE attendance_date=%s AND attendance_status='A'",$today))
        ];

        wp_send_json_success([
            'cards'=>$totals,
            'sites'=>$site_rows,
            'pending_documents'=>[
                'supplier_invoices'=>$pending_invoice_copy,
                'contractor_bills'=>$totals['pending_contractor_bill_copy'],
                'site_challans'=>$totals['pending_challan']
            ],
            'today_activity'=>$today_activity,
            'office_attendance'=>$office,
            'today'=>$today
        ]);
    }

    public function ajax_report(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix; $report=sanitize_key($_POST['report']??'');
        $site=intval($_POST['site_id']??0);$warehouse=intval($_POST['warehouse_id']??0);$item=intval($_POST['item_id']??0);$category=intval($_POST['category_id']??0);$from=sanitize_text_field($_POST['date_from']??'');$to=sanitize_text_field($_POST['date_to']??'');$contractor=intval($_POST['contractor_id']??0);$stock_scope=sanitize_key($_POST['stock_scope']??'');
        $rows=[];
        if($report==='material_stock'){
            // Materials > Stock movement summary. Warehouse rows use:
            // Balance = Inward + Return - Dispatch.
            // Site rows use: Balance = Receipt - Consumption - Return.
            // Dispatch is also shown on Site rows as movement visibility, but is not
            // counted into Site Balance until the Site Receipt is actually recorded.
            $map=[];
            $add=function($type,$location_id,$location,$item_id,$item_name,$unit_id,$unit,$field,$qty) use (&$map){
                $key=$type.'|'.(int)$location_id.'|'.(int)$item_id.'|'.(int)$unit_id;
                if(!isset($map[$key])) $map[$key]=[
                    'location_type'=>$type,'location_id'=>(int)$location_id,'location'=>$location,
                    'item_id'=>(int)$item_id,'item_name'=>$item_name,'unit_id'=>(int)$unit_id,'unit'=>$unit,
                    'opening'=>0.0,'inward'=>0.0,'receipt'=>0.0,'dispatch'=>0.0,'consumption'=>0.0,'return_qty'=>0.0,'site_transfer'=>0.0,'balance'=>0.0
                ];
                $map[$key][$field]+=(float)$qty;
            };
            $dateWhere=function($alias,$field) use ($from,$to){
                $w='';$a=[];
                if($from){$w.=" AND {$alias}.{$field}>=%s";$a[]=$from;}
                if($to){$w.=" AND {$alias}.{$field}<=%s";$a[]=$to;}
                return [$w,$a];
            };

            // Item opening stock is held against the first/default active Warehouse.
            $ow="i.opening_qty>0";$oa=[];
            if($warehouse){$ow.=' AND w.id=%d';$oa[]=$warehouse;}
            if($item){$ow.=' AND i.id=%d';$oa[]=$item;}
            $sql="SELECT w.id location_id,w.warehouse_name location,i.id item_id,i.item_name,i.unit_id,u.unit_name unit,i.opening_qty qty
                  FROM {$p}items i
                  LEFT JOIN {$p}units u ON u.id=i.unit_id
                  JOIN {$p}warehouses w ON w.id=(SELECT id FROM {$p}warehouses ORDER BY (status='active') DESC,id ASC LIMIT 1)
                  WHERE $ow";
            if($oa)$sql=$wpdb->prepare($sql,$oa);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x)$add('Warehouse',$x['location_id'],$x['location'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'opening',$x['qty']);

            // Warehouse Material Inward.
            [$dw,$da]=$dateWhere('h','inward_date');
            $w="h.status<>'cancelled'".$dw;$a=$da;
            if($warehouse){$w.=' AND h.warehouse_id=%d';$a[]=$warehouse;}
            if($item){$w.=' AND d.item_id=%d';$a[]=$item;}
            $sql="SELECT h.warehouse_id location_id,w.warehouse_name location,d.item_id,i.item_name,d.unit_id,u.unit_name unit,SUM(d.qty) qty
                  FROM {$p}material_inwards h JOIN {$p}material_inward_items d ON d.inward_id=h.id
                  LEFT JOIN {$p}warehouses w ON w.id=h.warehouse_id LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id
                  WHERE $w GROUP BY h.warehouse_id,d.item_id,d.unit_id";
            if($a)$sql=$wpdb->prepare($sql,$a);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x)$add('Warehouse',$x['location_id'],$x['location'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'inward',$x['qty']);

            // Dispatch: decreases warehouse stock and is displayed against destination Site.
            [$dw,$da]=$dateWhere('h','dispatch_date');
            $w="h.status<>'cancelled' AND COALESCE(h.from_site_id,0)=0".$dw;$a=$da;
            if($warehouse){$w.=' AND h.warehouse_id=%d';$a[]=$warehouse;}
            if($site){$w.=' AND h.site_id=%d';$a[]=$site;}
            if($item){$w.=' AND d.item_id=%d';$a[]=$item;}
            $sql="SELECT h.warehouse_id,h.site_id,w.warehouse_name,s.site_name,d.item_id,i.item_name,d.unit_id,u.unit_name unit,SUM(d.qty) qty
                  FROM {$p}material_dispatches h JOIN {$p}material_dispatch_items d ON d.dispatch_id=h.id
                  LEFT JOIN {$p}warehouses w ON w.id=h.warehouse_id LEFT JOIN {$p}sites s ON s.id=h.site_id
                  LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id
                  WHERE $w GROUP BY h.warehouse_id,h.site_id,d.item_id,d.unit_id";
            if($a)$sql=$wpdb->prepare($sql,$a);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x){
                $add('Warehouse',$x['warehouse_id'],$x['warehouse_name'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'dispatch',$x['qty']);
                $add('Site',$x['site_id'],$x['site_name'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'dispatch',$x['qty']);
            }

            // Site opening stock entered item-wise for each Site.
            $ow="so.opening_qty>0";$oa=[];
            if($site){$ow.=' AND so.site_id=%d';$oa[]=$site;}
            if($item){$ow.=' AND so.item_id=%d';$oa[]=$item;}
            $sql="SELECT so.site_id location_id,s.site_name location,so.item_id,i.item_name,so.unit_id,u.unit_name unit,SUM(so.opening_qty) qty
                  FROM {$p}site_opening_stock so
                  LEFT JOIN {$p}sites s ON s.id=so.site_id LEFT JOIN {$p}items i ON i.id=so.item_id LEFT JOIN {$p}units u ON u.id=so.unit_id
                  WHERE $ow GROUP BY so.site_id,so.item_id,so.unit_id";
            if($oa)$sql=$wpdb->prepare($sql,$oa);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x)$add('Site',$x['location_id'],$x['location'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'opening',$x['qty']);

            // Actual Site Receipts.
            [$dw,$da]=$dateWhere('h','receipt_date');
            $w="h.status<>'cancelled'".$dw;$a=$da;
            if($site){$w.=' AND h.site_id=%d';$a[]=$site;}
            if($item){$w.=' AND d.item_id=%d';$a[]=$item;}
            $sql="SELECT h.site_id,s.site_name,d.item_id,i.item_name,d.unit_id,u.unit_name unit,SUM(d.received_qty) qty
                  FROM {$p}site_receipts h JOIN {$p}site_receipt_items d ON d.receipt_id=h.id
                  LEFT JOIN {$p}sites s ON s.id=h.site_id LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id
                  WHERE $w GROUP BY h.site_id,d.item_id,d.unit_id";
            if($a)$sql=$wpdb->prepare($sql,$a);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x)$add('Site',$x['site_id'],$x['site_name'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'receipt',$x['qty']);

            // Site Consumption.
            [$dw,$da]=$dateWhere('h','consumption_date');
            $w="h.status<>'cancelled'".$dw;$a=$da;
            if($site){$w.=' AND h.site_id=%d';$a[]=$site;}
            if($item){$w.=' AND d.item_id=%d';$a[]=$item;}
            $sql="SELECT h.site_id,s.site_name,d.item_id,i.item_name,d.unit_id,u.unit_name unit,SUM(d.qty) qty
                  FROM {$p}site_consumptions h JOIN {$p}site_consumption_items d ON d.consumption_id=h.id
                  LEFT JOIN {$p}sites s ON s.id=h.site_id LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id
                  WHERE $w GROUP BY h.site_id,d.item_id,d.unit_id";
            if($a)$sql=$wpdb->prepare($sql,$a);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x)$add('Site',$x['site_id'],$x['site_name'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'consumption',$x['qty']);

            // Material Return: decreases Site stock and increases Warehouse stock.
            [$dw,$da]=$dateWhere('h','return_date');
            $w="h.status<>'cancelled'".$dw;$a=$da;
            if($site){$w.=' AND h.site_id=%d';$a[]=$site;}
            if($warehouse){$w.=' AND h.warehouse_id=%d';$a[]=$warehouse;}
            if($item){$w.=' AND d.item_id=%d';$a[]=$item;}
            $sql="SELECT h.site_id,h.warehouse_id,s.site_name,w.warehouse_name,d.item_id,i.item_name,d.unit_id,u.unit_name unit,SUM(d.qty) qty
                  FROM {$p}material_returns h JOIN {$p}material_return_items d ON d.return_id=h.id
                  LEFT JOIN {$p}sites s ON s.id=h.site_id LEFT JOIN {$p}warehouses w ON w.id=h.warehouse_id
                  LEFT JOIN {$p}items i ON i.id=d.item_id LEFT JOIN {$p}units u ON u.id=d.unit_id
                  WHERE $w GROUP BY h.site_id,h.warehouse_id,d.item_id,d.unit_id";
            if($a)$sql=$wpdb->prepare($sql,$a);
            foreach($wpdb->get_results($sql,ARRAY_A) as $x){
                $add('Site',$x['site_id'],$x['site_name'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'return_qty',$x['qty']);
                $add('Warehouse',$x['warehouse_id'],$x['warehouse_name'],$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'return_qty',$x['qty']);
            }

            foreach($this->ops_transfer_movements($site,$item,$from,$to) as $x){
                $site_name=(string)$wpdb->get_var($wpdb->prepare("SELECT site_name FROM {$p}sites WHERE id=%d",$x['site_id']));
                if($x['in'])$add('Site',$x['site_id'],$site_name,$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'receipt',$x['in']);
                if($x['out'])$add('Site',$x['site_id'],$site_name,$x['item_id'],$x['item_name'],$x['unit_id'],$x['unit'],'site_transfer',$x['out']);
            }

            foreach($map as &$x){
                if($x['location_type']==='Warehouse') $x['balance']=$x['opening']+$x['inward']+$x['return_qty']-$x['dispatch'];
                else $x['balance']=$x['opening']+$x['receipt']-$x['consumption']-$x['return_qty']-$x['site_transfer'];
                foreach(['opening','inward','receipt','dispatch','consumption','return_qty','site_transfer','balance'] as $f)$x[$f]=round((float)$x[$f],2);
            }
            unset($x);
            $rows=array_values($map);
            // Add Item Category to every inventory row and optionally filter by category.
            $catMap=[];
            $itemIds=array_values(array_unique(array_filter(array_map(function($r){return (int)($r['item_id']??0);},$rows))));
            if($itemIds){
                $ph=implode(',',array_fill(0,count($itemIds),'%d'));
                $sql=$wpdb->prepare("SELECT i.id item_id,i.category_id,c.category_name FROM {$p}items i LEFT JOIN {$p}item_categories c ON c.id=i.category_id WHERE i.id IN ($ph)",$itemIds);
                foreach($wpdb->get_results($sql,ARRAY_A) as $ci)$catMap[(int)$ci['item_id']]=['category_id'=>(int)$ci['category_id'],'category_name'=>(string)($ci['category_name']??'')];
            }
            foreach($rows as &$r){$cm=$catMap[(int)($r['item_id']??0)]??['category_id'=>0,'category_name'=>''];$r['category_id']=$cm['category_id'];$r['category_name']=$cm['category_name'];}
            unset($r);
            if($category)$rows=array_values(array_filter($rows,function($r) use ($category){return (int)($r['category_id']??0)===$category;}));
            usort($rows,function($a,$b){$c=strcmp($a['location_type'],$b['location_type']);if($c)return $c;$c=strcasecmp((string)$a['location'],(string)$b['location']);if($c)return $c;$c=strcasecmp((string)($a['category_name']??''),(string)($b['category_name']??''));if($c)return $c;return strcasecmp((string)$a['item_name'],(string)$b['item_name']);});
            if($stock_scope==='site_options'){
                $rows=array_values(array_filter($rows,function($x){return strcasecmp((string)($x['location_type']??''),'Site')===0 && (float)($x['balance']??0)>0.0001;}));
            }
        } elseif($report==='stock'){
            $where='1=1';$args=[];if($site){$where.=" AND l.site_id=%d";$args[]=$site;}
            $sql="SELECT l.location_type,COALESCE(w.warehouse_name,s.site_name) location,i.item_name,u.unit_name unit,ROUND(SUM(l.qty_in-l.qty_out),2) stock FROM {$p}stock_ledger l LEFT JOIN {$p}warehouses w ON l.warehouse_id=w.id LEFT JOIN {$p}sites s ON l.site_id=s.id LEFT JOIN {$p}items i ON l.item_id=i.id LEFT JOIN {$p}units u ON l.unit_id=u.id WHERE $where GROUP BY l.location_type,l.warehouse_id,l.site_id,l.item_id HAVING ABS(stock)>0.0001 ORDER BY location,item_name";
            if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A);
        } elseif($report==='site_fund'){
            $where='1=1';$args=[];if($site){$where.=" AND s.id=%d";$args[]=$site;}
            $sql="SELECT s.site_name,
                COALESCE(SUM(CASE WHEN l.transaction_type='OPENING_BALANCE' THEN l.amount_in ELSE 0 END),0) opening_amount,
                COALESCE(SUM(CASE WHEN l.transaction_type IN ('SITE_FUND_TRANSFER','FUND_RECEIVED') THEN l.amount_in ELSE 0 END),0) funds_received,
                COALESCE(SUM(l.amount_out),0) site_expenses,
                COALESCE(SUM(l.amount_in-l.amount_out),0) balance
                FROM {$p}sites s LEFT JOIN {$p}site_fund_ledger l ON l.site_id=s.id WHERE $where GROUP BY s.id ORDER BY s.site_name";if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A);
        } elseif($report==='site_cost'){
            $where='1=1';$args=[];if($site){$where.=' AND s.id=%d';$args[]=$site;}
            $sql="SELECT s.site_name,COALESCE((SELECT SUM(e.amount) FROM {$p}site_expenses e WHERE e.site_id=s.id AND e.status<>'cancelled'),0) direct_expenses,COALESCE((SELECT SUM(b.total_amount) FROM {$p}contractor_bills b WHERE b.site_id=s.id AND b.status<>'cancelled'),0) contractor_cost,COALESCE((SELECT SUM(e.amount) FROM {$p}site_expenses e WHERE e.site_id=s.id AND e.paid_by='HEAD_OFFICE' AND e.status<>'cancelled'),0) ho_paid_expenses FROM {$p}sites s WHERE $where ORDER BY s.site_name";if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A);foreach($rows as &$x)$x['total_site_cost']=(float)$x['direct_expenses']+(float)$x['contractor_cost'];
        } elseif($report==='labour'){
            $where='1=1';$args=[];if($site){$where.=' AND a.site_id=%d';$args[]=$site;}if($contractor){$where.=' AND a.contractor_id=%d';$args[]=$contractor;}if($from){$where.=' AND a.attendance_date>=%s';$args[]=$from;}if($to){$where.=' AND a.attendance_date<=%s';$args[]=$to;}
            $sql="SELECT a.attendance_date,s.site_name,c.contractor_name,a.no_of_labour,a.remarks FROM {$p}labour_attendance a JOIN {$p}sites s ON a.site_id=s.id JOIN {$p}labour_contractors c ON a.contractor_id=c.id WHERE $where ORDER BY a.attendance_date DESC,s.site_name,c.contractor_name";if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A);
        } elseif($report==='labour_summary'){
            $where='1=1';$args=[];if($site){$where.=' AND a.site_id=%d';$args[]=$site;}if($contractor){$where.=' AND a.contractor_id=%d';$args[]=$contractor;}if($from){$where.=' AND a.attendance_date>=%s';$args[]=$from;}if($to){$where.=' AND a.attendance_date<=%s';$args[]=$to;}
            $sql="SELECT s.site_name,c.contractor_name,SUM(a.no_of_labour) labour_days,ROUND(AVG(a.no_of_labour),2) avg_labour,MAX(a.no_of_labour) max_labour,COUNT(DISTINCT a.attendance_date) days_reported FROM {$p}labour_attendance a JOIN {$p}sites s ON a.site_id=s.id JOIN {$p}labour_contractors c ON a.contractor_id=c.id WHERE $where GROUP BY a.site_id,a.contractor_id ORDER BY s.site_name,c.contractor_name";if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A);
        } elseif($report==='customer_outstanding'){
            $where='1=1';$args=[];
            if($site){$where.=' AND s.id=%d';$args[]=$site;}
            $site_scope=$this->employee_site_scope();
            if($site_scope!==null && $site_scope){$ph=implode(',',array_fill(0,count($site_scope),'%d'));$where.=" AND s.id IN ($ph)";foreach($site_scope as $sid)$args[]=(int)$sid;}
            $sql="SELECT s.site_name,s.work_order_value,
                COALESCE((SELECT SUM(r.amount) FROM {$p}customer_receipts r WHERE r.site_id=s.id AND LOWER(COALESCE(r.status,'received'))<>'cancelled'),0) received,
                (s.work_order_value-COALESCE((SELECT SUM(r.amount) FROM {$p}customer_receipts r WHERE r.site_id=s.id AND LOWER(COALESCE(r.status,'received'))<>'cancelled'),0)) outstanding,
                CASE WHEN COALESCE(s.work_order_value,0)>0 THEN ROUND(COALESCE((SELECT SUM(r.amount) FROM {$p}customer_receipts r WHERE r.site_id=s.id AND LOWER(COALESCE(r.status,'received'))<>'cancelled'),0)*100/s.work_order_value,2) ELSE 0 END received_percentage,
                CASE WHEN COALESCE(s.work_order_value,0)>0 THEN ROUND((s.work_order_value-COALESCE((SELECT SUM(r.amount) FROM {$p}customer_receipts r WHERE r.site_id=s.id AND LOWER(COALESCE(r.status,'received'))<>'cancelled'),0))*100/s.work_order_value,2) ELSE 0 END outstanding_percentage
                FROM {$p}sites s WHERE $where ORDER BY s.site_name";
            if($args)$sql=$wpdb->prepare($sql,$args);
            $rows=$wpdb->get_results($sql,ARRAY_A);
        } elseif($report==='vendor_outstanding'){
            $rows=$wpdb->get_results("SELECT v.vendor_name,COALESCE((SELECT SUM(i.total_amount) FROM {$p}material_inwards i WHERE i.vendor_id=v.id AND i.status<>'cancelled'),0) bills,COALESCE((SELECT SUM(pay.amount) FROM {$p}vendor_payments pay WHERE pay.vendor_id=v.id AND pay.status<>'cancelled'),0) payments,(COALESCE((SELECT SUM(i.total_amount) FROM {$p}material_inwards i WHERE i.vendor_id=v.id AND i.status<>'cancelled'),0)-COALESCE((SELECT SUM(pay.amount) FROM {$p}vendor_payments pay WHERE pay.vendor_id=v.id AND pay.status<>'cancelled'),0)) outstanding FROM {$p}vendors v ORDER BY v.vendor_name",ARRAY_A);
        } elseif($report==='contractor_outstanding'){
            // Contractor Outstanding is site-wise: one row for each Site + Contractor combination.
            // Include combinations appearing in either Bills or Payments so the ledger stays complete.
            $rows=$wpdb->get_results("SELECT pair.contractor_id,pair.site_id,c.contractor_name,COALESCE(s.site_name,'-') site_name,
                COALESCE((SELECT SUM(b.total_amount) FROM {$p}contractor_bills b WHERE b.contractor_id=pair.contractor_id AND COALESCE(b.site_id,0)=COALESCE(pair.site_id,0) AND LOWER(COALESCE(b.status,''))<>'cancelled'),0) bills,
                COALESCE((SELECT SUM(pay.amount) FROM {$p}contractor_payments pay WHERE pay.contractor_id=pair.contractor_id AND COALESCE(pay.site_id,0)=COALESCE(pair.site_id,0) AND LOWER(COALESCE(pay.status,''))<>'cancelled'),0) payments,
                (COALESCE((SELECT SUM(b.total_amount) FROM {$p}contractor_bills b WHERE b.contractor_id=pair.contractor_id AND COALESCE(b.site_id,0)=COALESCE(pair.site_id,0) AND LOWER(COALESCE(b.status,''))<>'cancelled'),0)
                -COALESCE((SELECT SUM(pay.amount) FROM {$p}contractor_payments pay WHERE pay.contractor_id=pair.contractor_id AND COALESCE(pay.site_id,0)=COALESCE(pair.site_id,0) AND LOWER(COALESCE(pay.status,''))<>'cancelled'),0)) outstanding
                FROM (
                    SELECT contractor_id,site_id FROM {$p}contractor_bills WHERE LOWER(COALESCE(status,''))<>'cancelled'
                    UNION
                    SELECT contractor_id,site_id FROM {$p}contractor_payments WHERE LOWER(COALESCE(status,''))<>'cancelled'
                ) pair
                LEFT JOIN {$p}labour_contractors c ON c.id=pair.contractor_id
                LEFT JOIN {$p}sites s ON s.id=pair.site_id
                WHERE pair.contractor_id IS NOT NULL"
                .($site?$wpdb->prepare(" AND pair.site_id=%d",$site):"")
                .($contractor?$wpdb->prepare(" AND pair.contractor_id=%d",$contractor):"")
                ." ORDER BY COALESCE(s.site_name,''),c.contractor_name",ARRAY_A);
        } elseif($report==='contractor_advance_register'){
            // Contractor Payment Master must show EVERY non-cancelled payment made to a Labour Contractor,
            // irrespective of whether it was created as a contractor-level payment, an older advance,
            // or a legacy bill-linked payment. This gives one complete contractor payment ledger.
            $where="LOWER(COALESCE(cp.status,''))<>'cancelled'";$args=[];
            if($site){$where.=' AND cp.site_id=%d';$args[]=$site;} if($contractor){$where.=' AND cp.contractor_id=%d';$args[]=$contractor;}
            $sql="SELECT cp.id,cp.payment_date,c.contractor_name,s.site_name,cp.amount payment_amount,cp.payment_mode,cp.bank_account_id,cp.transaction_reference,cp.remarks FROM {$p}contractor_payments cp LEFT JOIN {$p}labour_contractors c ON c.id=cp.contractor_id LEFT JOIN {$p}sites s ON s.id=cp.site_id WHERE $where ORDER BY cp.payment_date DESC,cp.id DESC";
            if($args)$sql=$wpdb->prepare($sql,$args);$rows=$wpdb->get_results($sql,ARRAY_A);
        } elseif($report==='pending_contractor_payment'){
            $where="b.status NOT IN ('cancelled','paid')";$args=[];
            if($site){$where.=' AND b.site_id=%d';$args[]=$site;}
            if($contractor){$where.=' AND b.contractor_id=%d';$args[]=$contractor;}
            $sql="SELECT b.id,b.bill_no,b.bill_date,b.contractor_id,b.site_id,c.contractor_name,s.site_name,b.total_amount,b.status payment_status,b.remarks,(SELECT d.file_url FROM {$p}documents d WHERE d.module='contractor_bills' AND d.reference_id=b.id ORDER BY d.id DESC LIMIT 1) document_url,COALESCE((SELECT SUM(pay.amount) FROM {$p}contractor_payments pay WHERE pay.contractor_bill_id=b.id AND COALESCE(pay.payment_type,'bill_payment')='bill_payment' AND pay.status<>'cancelled'),0) paid_amount,COALESCE(b.advance_adjustment,0) advance_adjusted,(b.total_amount-COALESCE(b.advance_adjustment,0)-COALESCE((SELECT SUM(pay.amount) FROM {$p}contractor_payments pay WHERE pay.contractor_bill_id=b.id AND COALESCE(pay.payment_type,'bill_payment')='bill_payment' AND pay.status<>'cancelled'),0)) pending_amount FROM {$p}contractor_bills b LEFT JOIN {$p}labour_contractors c ON c.id=b.contractor_id LEFT JOIN {$p}sites s ON s.id=b.site_id WHERE $where HAVING pending_amount>0.009 OR payment_status='partly_paid' ORDER BY b.bill_date DESC,b.id DESC";
            if($args)$sql=$wpdb->prepare($sql,$args);
            $rows=$wpdb->get_results($sql,ARRAY_A);
            foreach($rows as &$row){$row['available_advance']=round($this->contractor_available_advance((int)$row['contractor_id'],(int)$row['site_id'],0),2);}unset($row);
        }
        wp_send_json_success(['rows'=>$rows]);
    }

    public function ajax_update_contractor_payment_status(){
        $this->verify_ajax();
        global $wpdb;
        $id=(int)($_POST['id']??0);
        $status=sanitize_key($_POST['status']??'');
        $remarks=sanitize_textarea_field(wp_unslash($_POST['remarks']??''));
        if(!$id) wp_send_json_error(['message'=>'Invalid Contractor Bill.']);
        if(!in_array($status,['paid','partly_paid'],true)) wp_send_json_error(['message'=>'Please select Payment Status.']);
        $table=$this->prefix.'contractor_bills';
        $exists=(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE id=%d",$id));
        if(!$exists) wp_send_json_error(['message'=>'Contractor Bill not found.']);
        $ok=$wpdb->update($table,['status'=>$status,'remarks'=>$remarks,'updated_at'=>current_time('mysql')],['id'=>$id]);
        if($ok===false) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to update payment status.']);
        wp_send_json_success(['message'=>'Payment status updated successfully.']);
    }


    public function ajax_update_contractor_paid_amount(){
        $this->verify_ajax();
        global $wpdb;
        $bill_id=(int)($_POST['id']??0);
        $paid_amount=round((float)($_POST['paid_amount']??0),2);
        $new_advance_adjustment=round((float)($_POST['advance_adjustment']??0),2);
        if(!$bill_id) wp_send_json_error(['message'=>'Invalid Contractor Bill.']);
        if($paid_amount<=0 && $new_advance_adjustment<=0) wp_send_json_error(['message'=>'Please enter Advance Adjustment or Paid Amount.']);
        if($paid_amount<0 || $new_advance_adjustment<0) wp_send_json_error(['message'=>'Amount cannot be negative.']);
        $p=$this->prefix;
        $bill=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}contractor_bills WHERE id=%d",$bill_id),ARRAY_A);
        if(!$bill) wp_send_json_error(['message'=>'Contractor Bill not found.']);
        $already_paid=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$p}contractor_payments WHERE contractor_bill_id=%d AND COALESCE(payment_type,'bill_payment')='bill_payment' AND status<>'cancelled'",$bill_id));
        $existing_adjustment=round((float)($bill['advance_adjustment']??0),2);
        $balance_before=round(max(0,(float)$bill['total_amount']-$existing_adjustment-$already_paid),2);
        if($new_advance_adjustment>$balance_before+0.009) wp_send_json_error(['message'=>'Advance Adjustment cannot be more than Balance Amount.']);
        if($new_advance_adjustment>0){
            $available=round($this->contractor_available_advance((int)$bill['contractor_id'],(int)$bill['site_id'],0),2);
            if($new_advance_adjustment>$available+0.009) wp_send_json_error(['message'=>'Advance Adjustment cannot exceed Available Advance of '.number_format($available,2).'.']);
            $target_adjustment=round($existing_adjustment+$new_advance_adjustment,2);
            $final_adjustment=$this->allocate_contractor_advance($bill_id,(int)$bill['contractor_id'],(int)$bill['site_id'],$target_adjustment);
        } else {
            $final_adjustment=$existing_adjustment;
        }
        $balance_after_advance=round(max(0,(float)$bill['total_amount']-$final_adjustment-$already_paid),2);
        if($paid_amount>$balance_after_advance+0.009) wp_send_json_error(['message'=>'Paid Amount cannot be more than Balance Amount after Advance Adjustment.']);
        $now=current_time('mysql');
        $final_paid=$already_paid;
        if($paid_amount>0){
            $ok=$wpdb->insert($p.'contractor_payments',[
                'payment_no'=>$this->next_contractor_payment_no(),
                'payment_type'=>'bill_payment',
                'contractor_id'=>(int)$bill['contractor_id'],
                'contractor_bill_id'=>$bill_id,
                'site_id'=>(int)$bill['site_id'],
                'payment_date'=>current_time('Y-m-d'),
                'amount'=>$paid_amount,
                'paid_by'=>'HEAD_OFFICE',
                'payment_mode'=>'Other',
                'transaction_reference'=>'PENDING_PAYMENT_UPDATE',
                'remarks'=>'Payment entered from Pending Payment',
                'status'=>'paid',
                'created_by'=>get_current_user_id(),
                'created_at'=>$now,
                'updated_at'=>$now
            ]);
            if(!$ok) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to save payment.']);
            $payment_id=(int)$wpdb->insert_id;
            $this->sync_ledgers('contractor_payments',$payment_id);
            $final_paid=round($already_paid+$paid_amount,2);
        }
        $bill_status=($final_paid+$final_adjustment >= (float)$bill['total_amount'] - 0.009)?'paid':'partly_paid';
        $wpdb->update($p.'contractor_bills',['status'=>$bill_status,'advance_adjustment'=>$final_adjustment,'updated_at'=>$now],['id'=>$bill_id]);
        $balance=round(max(0,(float)$bill['total_amount']-$final_adjustment-$final_paid),2);
        wp_send_json_success(['message'=>'Contractor payment updated successfully.','advance_adjusted'=>$final_adjustment,'already_paid'=>$final_paid,'balance'=>$balance]);
    }

    private function sync_existing_wp_users_to_bsm(){
        global $wpdb;
        $table=$this->prefix.'users';
        // Migrate existing WordPress users once so the plugin User Master starts with current logins.
        $profiles=[];
        foreach($wpdb->get_results("SELECT wp_user_id,designation,mobile,status FROM {$this->prefix}user_profiles",ARRAY_A) as $p){$profiles[(int)$p['wp_user_id']]=$p;}
        foreach(get_users(['orderby'=>'ID']) as $u){
            $exists=$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE wp_user_id=%d OR user_id=%s LIMIT 1",$u->ID,$u->user_login));
            if($exists) continue;
            $profile=$profiles[(int)$u->ID]??[];
            $wpdb->insert($table,[
                'wp_user_id'=>$u->ID,
                'user_id'=>$u->user_login,
                'password_hash'=>$u->user_pass,
                'name'=>$u->display_name ?: $u->user_login,
                'email'=>$u->user_email,
                'role'=>!empty($u->roles)?reset($u->roles):'subscriber',
                'designation'=>$profile['designation']??'',
                'mobile'=>$profile['mobile']??'',
                'status'=>$profile['status']??'active',
                'created_at'=>current_time('mysql'),
                'updated_at'=>current_time('mysql'),
            ]);
        }
    }

    public function enforce_bsm_user_status($user,$username,$password){
        if(is_wp_error($user) || !$user || empty($user->ID)) return $user;
        global $wpdb;
        $status=$wpdb->get_var($wpdb->prepare("SELECT status FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",$user->ID));
        if($status && strtolower($status)!=='active') return new WP_Error('bsm_inactive_user','This user is inactive. Please contact the administrator.');
        return $user;
    }

    public function ajax_user_list(){
        $this->verify_ajax(); global $wpdb;

        // Keep the User Master backward-compatible with databases created by
        // earlier plugin versions. dbDelta/create_tables is safe here and only
        // repairs missing columns/indexes; it does not remove existing data.
        $this->create_tables();
        $this->migrate_users_table();
        $this->migrate_phase_0122();

        $table = $this->prefix . 'users';
        $dtable = $this->prefix . 'designations';

        // If the plugin User Master is empty for any reason, restore it from
        // the linked WordPress users so existing logins never disappear from
        // the frontend User view.
        $count = (int)$wpdb->get_var("SELECT COUNT(*) FROM `{$table}`");
        if ($count === 0) {
            $this->sync_existing_wp_users_to_bsm();
        }

        $has_designation_id = (bool)$wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'designation_id'");
        $has_designation = (bool)$wpdb->get_var("SHOW COLUMNS FROM `{$table}` LIKE 'designation'");
        $designation_table_exists = ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $dtable)) === $dtable);

        $select_designation_id = $has_designation_id ? 'u.designation_id' : '0 AS designation_id';
        if ($designation_table_exists && $has_designation_id) {
            $legacy = $has_designation ? "u.designation" : "''";
            $designation_expr = "COALESCE(d.designation_name, {$legacy}, '') AS designation";
            $join = " LEFT JOIN `{$dtable}` d ON d.id=u.designation_id ";
        } else {
            $designation_expr = $has_designation ? "COALESCE(u.designation,'') AS designation" : "'' AS designation";
            $join = '';
        }

        $search=sanitize_text_field($_POST['search']??'');
        $where=''; $params=[];
        if($search!==''){
            $needle='%'.$wpdb->esc_like($search).'%';
            $parts=["u.user_id LIKE %s","u.name LIKE %s","u.email LIKE %s","u.mobile LIKE %s","u.status LIKE %s","REPLACE(REPLACE(u.role,'bsm_',''),'_',' ') LIKE %s"];
            $params=array_fill(0,6,$needle);
            if($designation_table_exists && $has_designation_id){$parts[]="d.designation_name LIKE %s";$params[]=$needle;}
            elseif($has_designation){$parts[]="u.designation LIKE %s";$params[]=$needle;}
            $where=' WHERE ('.implode(' OR ',$parts).')';
        }
        $sql = "SELECT u.id,u.wp_user_id,u.user_id,u.name,u.email,u.role,{$select_designation_id},{$designation_expr},u.mobile,u.assigned_sites,u.allowed_views,u.attendance_applicable,u.salary,u.salary_effective_date,u.status FROM `{$table}` u{$join}{$where} ORDER BY u.name,u.user_id";
        if($params) $sql=$wpdb->prepare($sql,$params);
        $rows = $wpdb->get_results($sql, ARRAY_A);

        if ($rows === null || $wpdb->last_error) {
            wp_send_json_error(['message' => $wpdb->last_error ?: 'Unable to load users']);
        }
        wp_send_json_success(['rows'=>$rows ?: []]);
    }

    public function ajax_user_delete(){
        $this->verify_ajax();
        if(!current_user_can('manage_options') && !in_array('bsm_owner', wp_get_current_user()->roles, true)) {
            wp_send_json_error(['message'=>'Only Administrator/Owner can delete users'],403);
        }
        global $wpdb;
        $id = intval($_POST['id'] ?? 0);
        if(!$id) wp_send_json_error(['message'=>'Invalid user']);
        $table = $this->prefix . 'users';
        $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d", $id), ARRAY_A);
        if(!$row) wp_send_json_error(['message'=>'User record not found']);
        $wp_user_id = intval($row['wp_user_id'] ?? 0);
        if($wp_user_id && $wp_user_id === get_current_user_id()) {
            wp_send_json_error(['message'=>'You cannot delete the currently logged-in user']);
        }
        if($wp_user_id){
            $wp_user = get_userdata($wp_user_id);
            if($wp_user && in_array('administrator', (array)$wp_user->roles, true)){
                $admins = get_users(['role'=>'administrator','fields'=>'ID']);
                if(count($admins) <= 1) wp_send_json_error(['message'=>'The last Administrator cannot be deleted']);
            }
        }

        // Prevent orphaning direct site responsibility assignments.
        $used = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->prefix}sites WHERE site_manager_user_id=%d OR site_supervisor_user_id=%d",
            $id, $id
        ));
        if($used > 0) wp_send_json_error(['message'=>'This user is assigned to a Site. Reassign the Site Manager/Supervisor before deleting.']);

        $tx_used=(int)$wpdb->get_var($wpdb->prepare(
            "SELECT (SELECT COUNT(*) FROM {$this->prefix}material_requisitions WHERE requested_by=%d OR approved_by=%d)+(SELECT COUNT(*) FROM {$this->prefix}site_receipts WHERE received_by=%d)+(SELECT COUNT(*) FROM {$this->prefix}material_returns WHERE return_by=%d)+(SELECT COUNT(*) FROM {$this->prefix}material_inwards WHERE received_by=%d)+(SELECT COUNT(*) FROM {$this->prefix}office_attendance WHERE user_id=%d)",
            $id,$id,$id,$id,$id,$id
        ));
        if($tx_used>0) wp_send_json_error(['message'=>'This User is already used in transactions and cannot be deleted. You can mark the User Inactive instead.']);

        if($wp_user_id){
            if(!function_exists('wp_delete_user')) require_once ABSPATH . 'wp-admin/includes/user.php';
            $deleted = wp_delete_user($wp_user_id, get_current_user_id());
            if(!$deleted) wp_send_json_error(['message'=>'Unable to delete linked WordPress login']);
        }
        $ok = $wpdb->delete($table, ['id'=>$id], ['%d']);
        if($ok === false) wp_send_json_error(['message'=>$wpdb->last_error ?: 'Unable to delete user']);
        wp_send_json_success(['message'=>'User deleted']);
    }

    public function ajax_user_save(){
        $this->verify_ajax();
        // Defensive migration: guarantees the username column is text even if
        // a previous plugin update was cached or its upgrade hook did not run.
        $this->migrate_users_table();
        $this->migrate_phase_0122();
        if(!current_user_can('manage_options') && !in_array('bsm_owner',wp_get_current_user()->roles,true)) wp_send_json_error(['message'=>'Only Administrator/Owner can manage users'],403);
        global $wpdb;
        $table=$this->prefix.'users';
        $d=json_decode(stripslashes($_POST['data']??'{}'),true);
        $id=intval($d['id']??0);
        $raw_login=trim((string)($d['user_id']??''));
        $raw_email=trim((string)($d['email']??''));
        $raw_name=trim((string)($d['name']??''));
        $raw_mobile=trim((string)($d['mobile']??''));
        $login=sanitize_user($raw_login,true);
        $email=sanitize_email($raw_email);
        $name=sanitize_text_field($raw_name);
        $role=sanitize_key($d['role']??'bsm_employee');
        $password=(string)($d['password']??'');
        $designation_id=intval($d['designation_id']??0);
        $designation=$designation_id ? (string)$wpdb->get_var($wpdb->prepare("SELECT designation_name FROM {$this->prefix}designations WHERE id=%d",$designation_id)) : '';
        $mobile=sanitize_text_field($raw_mobile);
        $status=in_array(strtolower((string)($d['status']??'active')),['active','inactive'],true)?strtolower((string)$d['status']):'active';
        $attendance_applicable=!empty($d['attendance_applicable']) && in_array(strtolower((string)$d['attendance_applicable']),['1','yes','true'],true) ? 1 : 0;
        $salary=(float)($d['salary']??0);
        $salary_effective_date=sanitize_text_field($d['salary_effective_date']??'');
        if(!$attendance_applicable){$salary=0;$salary_effective_date='';}
        $allowed=['administrator','bsm_employee'];
        if(!in_array($role,$allowed,true)) $role='bsm_employee';
        $assigned_sites=array_values(array_unique(array_filter(array_map('intval',(array)($d['assigned_sites']??[])))));
        $allowed_views=array_values(array_unique(array_filter(array_map('sanitize_key',(array)($d['allowed_views']??[])))));
        if($role==='administrator'){ $assigned_sites=[]; $allowed_views=[]; }
        if($role==='bsm_employee' && !$allowed_views) wp_send_json_error(['message'=>'Please allow at least one View for Employee.','field'=>'allowed_views']);
        if($raw_name==='') wp_send_json_error(['message'=>'Name is required','field'=>'name']);
        if($raw_email==='') wp_send_json_error(['message'=>'Email is required','field'=>'email']);
        if(!$designation_id) wp_send_json_error(['message'=>'Designation is required','field'=>'designation_id']);
        if($raw_mobile==='') wp_send_json_error(['message'=>'Mobile is required','field'=>'mobile']);
        if(!preg_match('/^[0-9]{10}$/',$raw_mobile)) wp_send_json_error(['message'=>'Mobile Number should be a 10 digit number only','field'=>'mobile']);
        if($attendance_applicable && $salary<=0) wp_send_json_error(['message'=>'Monthly Salary is required when Attendance Applicable is Yes.','field'=>'salary']);
        if($attendance_applicable && !preg_match('/^\d{4}-\d{2}-\d{2}$/',$salary_effective_date)) wp_send_json_error(['message'=>'Salary Effective Date is required when Attendance Applicable is Yes.','field'=>'salary_effective_date']);
        if(!$role) wp_send_json_error(['message'=>'Role is required','field'=>'role']);
        if($raw_login==='') wp_send_json_error(['message'=>'User ID is required','field'=>'user_id']);
        if(!$id && trim($password)==='') wp_send_json_error(['message'=>'Password is required for a new user','field'=>'password']);
        if(!$login) wp_send_json_error(['message'=>'Enter a valid User ID','field'=>'user_id']);
        if(!$email || !is_email($email)) wp_send_json_error(['message'=>'Enter a valid email address','field'=>'email']);

        $row=$id?$wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id=%d",$id),ARRAY_A):null;
        if($id && !$row) wp_send_json_error(['message'=>'User record not found']);
        $dup=$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE user_id=%s AND id<>%d LIMIT 1",$login,$id));
        if($dup) wp_send_json_error(['message'=>'User ID already exists']);

        $wp_user_id=$row ? intval($row['wp_user_id']) : 0;
        if(!$wp_user_id){
            $existing_wp=username_exists($login);
            if($existing_wp){
                $linked=$wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE wp_user_id=%d LIMIT 1",$existing_wp));
                if($linked && intval($linked)!==$id) wp_send_json_error(['message'=>'This User ID already exists in WordPress']);
                $wp_user_id=(int)$existing_wp;
            } else {
                $wp_user_id=wp_create_user($login,$password,$email);
                if(is_wp_error($wp_user_id)) wp_send_json_error(['message'=>$wp_user_id->get_error_message()]);
            }
        } elseif($row && $login !== (string)$row['user_id']) {
            // User ID is the login ID. WordPress does not expose changing user_login
            // through wp_update_user(), so synchronise the linked wp_users row safely.
            $existing_wp=username_exists($login);
            if($existing_wp && intval($existing_wp)!==$wp_user_id){
                wp_send_json_error(['message'=>'This User ID already exists in WordPress']);
            }
            $changed=$wpdb->update(
                $wpdb->users,
                ['user_login'=>$login,'user_nicename'=>sanitize_title($login)],
                ['ID'=>$wp_user_id],
                ['%s','%s'],
                ['%d']
            );
            if($changed===false) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to update User ID']);
            clean_user_cache($wp_user_id);
        }

        $wp_args=['ID'=>$wp_user_id,'display_name'=>$name,'user_email'=>$email,'role'=>$role];
        if($password!=='') $wp_args['user_pass']=$password;
        $res=wp_update_user($wp_args);
        if(is_wp_error($res)) wp_send_json_error(['message'=>$res->get_error_message()]);
        update_user_meta($wp_user_id,'bsm_designation',$designation);
        update_user_meta($wp_user_id,'bsm_mobile',$mobile);
        update_user_meta($wp_user_id,'bsm_status',$status);

        $wp_user=get_userdata($wp_user_id);
        $data=[
            'wp_user_id'=>$wp_user_id,
            'user_id'=>$login,
            // WordPress-compatible secure hash; the plain password is never stored.
            'password_hash'=>$wp_user?$wp_user->user_pass:($row['password_hash']??wp_hash_password($password)),
            'name'=>$name,
            'email'=>$email,
            'role'=>$role,
            'designation_id'=>$designation_id,
            'designation'=>$designation,
            'mobile'=>$mobile,
            'assigned_sites'=>wp_json_encode($assigned_sites),
            'allowed_views'=>wp_json_encode($allowed_views),
            'attendance_applicable'=>$attendance_applicable,
            'salary'=>$salary,
            'salary_effective_date'=>$salary_effective_date?:null,
            'status'=>$status,
            'updated_at'=>current_time('mysql'),
        ];
        if($id){
            $ok=$wpdb->update($table,$data,['id'=>$id]);
            if($ok===false) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to update user']);
        } else {
            $data['created_at']=current_time('mysql');
            $ok=$wpdb->insert($table,$data);
            if(!$ok) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to create user']);
            $id=(int)$wpdb->insert_id;
        }
        if($attendance_applicable && $salary_effective_date){
            $salary_table=$this->prefix.'user_salary_history';
            $existing_salary_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM $salary_table WHERE user_id=%d AND effective_date=%s LIMIT 1",$id,$salary_effective_date));
            $salary_row=['user_id'=>$id,'monthly_salary'=>$salary,'effective_date'=>$salary_effective_date,'updated_at'=>current_time('mysql')];
            if($existing_salary_id){$wpdb->update($salary_table,$salary_row,['id'=>$existing_salary_id]);}
            else {$salary_row['created_at']=current_time('mysql');$wpdb->insert($salary_table,$salary_row);}
        }
        wp_send_json_success(['id'=>$id,'wp_user_id'=>$wp_user_id,'message'=>'User saved in Site Management']);
    }


    public function ajax_salary_template_rows(){
        $this->verify_ajax(); global $wpdb;
        if(!$this->allowance_admin_allowed()) wp_send_json_error(['message'=>'Only HO/Admin can prepare salary'],403);
        $rows=$wpdb->get_results("SELECT u.id,u.user_id employee_code,u.name employee_name,COALESCE(d.designation_name,u.designation,'') designation FROM {$this->prefix}users u LEFT JOIN {$this->prefix}designations d ON d.id=u.designation_id WHERE LOWER(COALESCE(u.status,'active'))='active' ORDER BY u.name",ARRAY_A);
        wp_send_json_success(['rows'=>$rows]);
    }

    private function salary_parse_csv($file){
        $rows=[];$h=fopen($file,'r');if(!$h)return $rows;
        while(($r=fgetcsv($h))!==false)$rows[]=$r;fclose($h);return $rows;
    }
    private function salary_xlsx_col($ref){$letters=preg_replace('/[^A-Z]/','',strtoupper($ref));$n=0;for($i=0;$i<strlen($letters);$i++)$n=$n*26+(ord($letters[$i])-64);return max(0,$n-1);}
    private function salary_parse_xlsx($file){
        $out=[];if(!class_exists('ZipArchive'))return $out;$z=new ZipArchive();if($z->open($file)!==true)return $out;
        $shared=[];$sx=$z->getFromName('xl/sharedStrings.xml');if($sx){$xml=@simplexml_load_string($sx);if($xml)foreach($xml->si as $si){$t='';if(isset($si->t))$t=(string)$si->t;else foreach($si->r as $r)$t.=(string)$r->t;$shared[]=$t;}}
        $sheet=$z->getFromName('xl/worksheets/sheet1.xml');$z->close();if(!$sheet)return $out;$xml=@simplexml_load_string($sheet);if(!$xml)return $out;
        foreach($xml->sheetData->row as $row){$arr=[];foreach($row->c as $c){$idx=$this->salary_xlsx_col((string)$c['r']);$type=(string)$c['t'];$v=(string)$c->v;if($type==='s')$v=$shared[(int)$v]??'';elseif($type==='inlineStr')$v=(string)$c->is->t;$arr[$idx]=$v;}if($arr){$max=max(array_keys($arr));$line=[];for($i=0;$i<=$max;$i++)$line[]=$arr[$i]??'';$out[]=$line;}}
        return $out;
    }
    private function salary_normalize_header($x){return strtolower(preg_replace('/[^a-z0-9]+/','',strtolower(trim((string)$x))));}
    private function salary_parse_upload($file,$name,$month){
        global $wpdb;$ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));$raw=$ext==='xlsx'?$this->salary_parse_xlsx($file):$this->salary_parse_csv($file);$errors=[];$result=[];
        if(!$raw){return [[],['Unable to read the uploaded file. Please use CSV or XLSX.']];}
        $headers=array_shift($raw);$map=[];foreach($headers as $i=>$h)$map[$this->salary_normalize_header($h)]=$i;
        $required=['month','employeeid','employeename','presentdays','basicsalary','allowance','overtime','incentive','bonus','otherearnings','deduction','advancededuction','otherdeduction','netsalarypaid','remarks'];
        foreach($required as $h)if(!array_key_exists($h,$map))$errors[]='Missing column: '.$h;
        if($errors)return [[], $errors];
        $numFields=['presentdays','basicsalary','allowance','overtime','incentive','bonus','otherearnings','deduction','advancededuction','otherdeduction','netsalarypaid'];
        foreach($raw as $ri=>$r){$get=function($k)use($r,$map){$i=$map[$k]??-1;return $i>=0?trim((string)($r[$i]??'')):'';};$code=$get('employeeid');if($code==='')continue;
            $editable=['presentdays','basicsalary','allowance','overtime','incentive','bonus','otherearnings','deduction','advancededuction','otherdeduction','netsalarypaid','remarks'];$hasEntry=false;foreach($editable as $ef){if($get($ef)!==''){$hasEntry=true;break;}}if(!$hasEntry)continue;
            $rowMonth=$get('month');if($rowMonth!=='' && $rowMonth!==$month){$errors[]='Row '.($ri+2).': Month must be '.$month;continue;}
            $u=$wpdb->get_row($wpdb->prepare("SELECT u.id,u.user_id,u.name,COALESCE(d.designation_name,u.designation,'') designation FROM {$this->prefix}users u LEFT JOIN {$this->prefix}designations d ON d.id=u.designation_id WHERE u.user_id=%s LIMIT 1",$code),ARRAY_A);
            if(!$u){$errors[]='Row '.($ri+2).': Employee ID '.$code.' not found';continue;}
            $vals=[];$bad=false;foreach($numFields as $f){$v=$get($f);if($v==='')$v=0;$v=str_replace([',','₹','Rs.','Rs'],'',$v);if(!is_numeric($v)){$errors[]='Row '.($ri+2).': Invalid number in '.$f;$bad=true;break;}$vals[$f]=(float)$v;if($vals[$f]<0){$errors[]='Row '.($ri+2).': Negative amount is not allowed in '.$f;$bad=true;break;}}if($bad)continue;
            $gross=$vals['basicsalary']+$vals['allowance']+$vals['overtime']+$vals['incentive']+$vals['bonus']+$vals['otherearnings'];$ded=$vals['deduction']+$vals['advancededuction']+$vals['otherdeduction'];$net=max(0,$gross-$ded);$paid=min($net,$vals['netsalarypaid']);$bal=max(0,$net-$paid);
            $result[]=['salary_month'=>$month,'employee_id'=>(int)$u['id'],'employee_code'=>$u['user_id'],'employee_name'=>$u['name'],'designation'=>$u['designation'],'present_days'=>$vals['presentdays'],'basic_salary'=>$vals['basicsalary'],'allowance'=>$vals['allowance'],'overtime'=>$vals['overtime'],'incentive'=>$vals['incentive'],'bonus'=>$vals['bonus'],'other_earnings'=>$vals['otherearnings'],'deduction'=>$vals['deduction'],'advance_deduction'=>$vals['advancededuction'],'other_deduction'=>$vals['otherdeduction'],'gross_salary'=>$gross,'total_deduction'=>$ded,'net_salary_payable'=>$net,'opening_paid'=>$paid,'net_salary_paid'=>$paid,'balance_payable'=>$bal,'remarks'=>$get('remarks')];
        }
        $seen=[];foreach($result as $x){if(isset($seen[$x['employee_id']]))$errors[]='Duplicate employee in upload: '.$x['employee_name'];$seen[$x['employee_id']]=1;}
        return [$result,$errors];
    }
    public function ajax_salary_upload_preview(){
        $this->verify_ajax();if(!$this->allowance_admin_allowed())wp_send_json_error(['message'=>'Only HO/Admin can prepare salary'],403);$month=sanitize_text_field($_POST['salary_month']??'');if(!preg_match('/^\d{4}-\d{2}$/',$month))wp_send_json_error(['message'=>'Select Salary Month']);if(empty($_FILES['file']))wp_send_json_error(['message'=>'Select salary Excel/CSV file']);[$rows,$errors]=$this->salary_parse_upload($_FILES['file']['tmp_name'],$_FILES['file']['name'],$month);wp_send_json_success(['rows'=>$rows,'errors'=>$errors]);
    }
    private function salary_pdf_escape($s){$s=(string)$s;$s=function_exists('iconv')?(@iconv('UTF-8','Windows-1252//TRANSLIT',$s)?:$s):$s;return str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$s);}
    private function salary_money($v){return 'Rs. '.number_format((float)$v,2,'.',',');}
    private function generate_salary_pdf($id){
        global $wpdb;
        $p=$this->prefix;
        $x=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}salary_slips WHERE id=%d",$id),ARRAY_A);
        if(!$x) return '';
        $companyRow=$wpdb->get_row("SELECT * FROM {$p}company_settings ORDER BY id DESC LIMIT 1",ARRAY_A)?:[];
        $company=trim((string)($companyRow['company_name']??''))?:'BizSkill';
        $addressParts=[];
        foreach(['address','city','pin_code'] as $f){$v=trim((string)($companyRow[$f]??''));if($v!=='')$addressParts[]=$v;}
        $companyAddress=implode(', ',$addressParts);
        $phone=trim((string)($companyRow['phone']??''));
        $email=trim((string)($companyRow['email']??''));
        $gstin=trim((string)($companyRow['gstin']??''));
        $period=$x['salary_month'];
        if(preg_match('/^(\d{4})-(\d{2})$/',$period,$m)){
            $monthNames=['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'May','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
            $periodLabel=($monthNames[$m[2]]??$m[2]).' '.$m[1];
        } else $periodLabel=$period;
        $present=rtrim(rtrim(number_format((float)$x['present_days'],2,'.',''),'0'),'.');

        // A4 portrait: 595 x 842 pt. All graphics are vector so the slip stays sharp when printed.
        $c='';
        $num=function($v){return rtrim(rtrim(sprintf('%.3F',(float)$v),'0'),'.');};
        $rgb=function($hex){$hex=ltrim($hex,'#');return [hexdec(substr($hex,0,2))/255,hexdec(substr($hex,2,2))/255,hexdec(substr($hex,4,2))/255];};
        $setFill=function($hex)use(&$c,$rgb){[$r,$g,$b]=$rgb($hex);$c.=sprintf("%.3F %.3F %.3F rg\n",$r,$g,$b);};
        $setStroke=function($hex)use(&$c,$rgb){[$r,$g,$b]=$rgb($hex);$c.=sprintf("%.3F %.3F %.3F RG\n",$r,$g,$b);};
        $rect=function($x0,$y0,$w,$h,$fill=null,$stroke=null,$lw=.7)use(&$c,$setFill,$setStroke){if($fill)$setFill($fill);if($stroke)$setStroke($stroke);$c.=sprintf("%.2F w %.2F %.2F %.2F %.2F re %s\n",$lw,$x0,$y0,$w,$h,$fill&&$stroke?'B':($fill?'f':'S'));};
        $line=function($x1,$y1,$x2,$y2,$stroke='#D7E5F7',$lw=.6)use(&$c,$setStroke){$setStroke($stroke);$c.=sprintf("%.2F w %.2F %.2F m %.2F %.2F l S\n",$lw,$x1,$y1,$x2,$y2);};
        $clean=function($v){$v=(string)$v;$v=function_exists('iconv')?(@iconv('UTF-8','Windows-1252//TRANSLIT',$v)?:$v):$v;return $v;};
        $width=function($txt,$size,$bold=false)use($clean){$txt=$clean($txt);return strlen($txt)*$size*($bold?.535:.50);};
        $text=function($txt,$x0,$y0,$size=10,$bold=false,$color='#14233B',$align='left',$maxW=0)use(&$c,$setFill,$clean,$width){$txt=$clean($txt);if($maxW>0){$avg=max(1,$size*.50);$maxChars=max(3,(int)floor($maxW/$avg));if(strlen($txt)>$maxChars)$txt=substr($txt,0,max(0,$maxChars-3)).'...';}$w=$width($txt,$size,$bold);if($align==='right')$x0-=$w;elseif($align==='center')$x0-=$w/2;$setFill($color);$font=$bold?'F2':'F1';$esc=str_replace(['\\','(',')'],['\\\\','\\(','\\)'],$txt);$c.="BT /{$font} {$size} Tf 1 0 0 1 ".sprintf('%.2F %.2F',$x0,$y0)." Tm ({$esc}) Tj ET\n";};
        $money=function($v){return 'Rs. '.number_format((float)$v,2,'.',',');};

        // Header
        $text($company,36,798,22,true,'#0B2A57');
        $text('SALARY SLIP',36,754,22,true,'#0B2A57');
        $text('FOR THE PERIOD: '.strtoupper($periodLabel),36,735,9,false,'#38506F');
        $rect(36,719,523,2,'#1677E8',null,0);
        if($companyAddress!=='') $text($companyAddress,559,800,8,false,'#40556F','right',225);
        if($phone!=='') $text('Phone: '.$phone,559,788,8,false,'#40556F','right',225);
        if($email!=='') $text('Email: '.$email,559,776,8,false,'#40556F','right',225);
        if($gstin!=='') $text('GSTIN: '.$gstin,559,764,8,false,'#40556F','right',225);

        // Employee details card
        $rect(36,642,523,62,'#F7FBFF','#BED8F7',.8);
        $text('EMPLOYEE DETAILS',50,688,10,true,'#0F63C9');
        $line(50,680,545,680,'#CFE0F3',.6);
        $text('Employee',50,662,8,false,'#61758D');$text($x['employee_name'],118,662,10,true,'#14233B', 'left',155);
        $text('Employee ID',300,662,8,false,'#61758D');$text($x['employee_code'],366,662,10,true,'#14233B','left',175);
        $text('Designation',50,648,8,false,'#61758D');$text($x['designation']?:'-',118,648,9,true,'#14233B','left',155);
        $text('Present Days',300,648,8,false,'#61758D');$text($present,366,648,10,true,'#14233B');

        // Earnings and deductions tables.
        $leftX=36;$rightX=306;$boxW=253;$top=622;$headerH=30;$rowH=24;
        $rect($leftX,$top-$headerH,$boxW,$headerH,'#EAF4FF','#BED8F7',.8);
        $rect($rightX,$top-$headerH,$boxW,$headerH,'#EAF4FF','#BED8F7',.8);
        $text('EARNINGS',$leftX+14,$top-20,11,true,'#0F63C9');
        $text('DEDUCTIONS',$rightX+14,$top-20,11,true,'#0F63C9');
        $earn=[['Basic Salary',$x['basic_salary']],['Allowance',$x['allowance']],['Overtime',$x['overtime']],['Incentive',$x['incentive']],['Bonus',$x['bonus']],['Other Earnings',$x['other_earnings']]];
        $ded=[['Deduction',$x['deduction']],['Advance Deduction',$x['advance_deduction']],['Other Deduction',$x['other_deduction']]];
        $tableY=$top-$headerH;
        $rect($leftX,$tableY-(count($earn)+1)*$rowH,$boxW,(count($earn)+1)*$rowH,'#FFFFFF','#BED8F7',.8);
        $rect($rightX,$tableY-(count($earn)+1)*$rowH,$boxW,(count($earn)+1)*$rowH,'#FFFFFF','#BED8F7',.8);
        for($i=0;$i<count($earn);$i++){
            $yy=$tableY-($i+1)*$rowH;
            if($i>0){$line($leftX,$yy+$rowH,$leftX+$boxW,$yy+$rowH,'#DCE9F7',.5);$line($rightX,$yy+$rowH,$rightX+$boxW,$yy+$rowH,'#DCE9F7',.5);}
            $text((string)($i+1),$leftX+15,$yy+8,8,false,'#4C617B');
            $text($earn[$i][0],$leftX+38,$yy+8,8.5,false,'#223650');
            $text($money($earn[$i][1]),$leftX+$boxW-12,$yy+8,8.5,false,'#14233B','right');
            if($i<count($ded)){
                $text((string)($i+1),$rightX+15,$yy+8,8,false,'#4C617B');
                $text($ded[$i][0],$rightX+38,$yy+8,8.5,false,'#223650');
                $text($money($ded[$i][1]),$rightX+$boxW-12,$yy+8,8.5,false,'#14233B','right');
            }
        }
        $totalY=$tableY-(count($earn)+1)*$rowH;
        $rect($leftX,$totalY,$boxW,$rowH,'#EAF4FF','#BED8F7',.5);
        $rect($rightX,$totalY,$boxW,$rowH,'#EAF4FF','#BED8F7',.5);
        $text('Gross Salary',$leftX+38,$totalY+8,9.5,true,'#14233B');
        $text($money($x['gross_salary']),$leftX+$boxW-12,$totalY+8,9.5,true,'#14233B','right');
        $text('Total Deduction',$rightX+38,$totalY+8,9.5,true,'#14233B');
        $text($money($x['total_deduction']),$rightX+$boxW-12,$totalY+8,9.5,true,'#14233B','right');

        // Salary summary
        $summaryY=340;
        $rect(36,$summaryY,523,78,'#F7FBFF','#BED8F7',.8);
        $text('SALARY SUMMARY',50,$summaryY+55,10,true,'#0F63C9');
        $line(166,$summaryY+12,166,$summaryY+66,'#C5DDF6',.8);
        $line(315,$summaryY+12,315,$summaryY+66,'#C5DDF6',.8);
        $line(441,$summaryY+12,441,$summaryY+66,'#C5DDF6',.8);
        $text('Net Salary Payable',180,$summaryY+50,8,false,'#61758D');
        $text($money($x['net_salary_payable']),180,$summaryY+28,13,true,'#14233B');
        $text('Net Salary Paid',329,$summaryY+50,8,false,'#61758D');
        $text($money($x['net_salary_paid']),329,$summaryY+28,13,true,'#14233B');
        $rect(452,$summaryY+10,95,58,'#E3F1FF',null,0);
        $text('Balance Payable',499.5,$summaryY+49,8,true,'#0F63C9','center');
        $text($money($x['balance_payable']),499.5,$summaryY+27,11.5,true,'#0B59B8','center',90);

        // Remarks
        $rect(36,284,523,40,'#FAFCFF','#D2E3F5',.7);
        $text('REMARKS',50,308,9,true,'#0F63C9');
        $text($x['remarks']?:'-',50,292,8.5,false,'#344B67','left',490);

        // Signatures / footer
        $line(55,225,205,225,'#8093AA',.6);$line(390,225,540,225,'#8093AA',.6);
        $text('Prepared By',130,210,9,true,'#14233B','center');
        $text('Accounts Department',130,197,8,false,'#61758D','center');
        $text('Authorized Signatory',465,210,9,true,'#14233B','center');
        $text('For '.$company,465,197,8,false,'#61758D','center',170);
        $line(36,167,559,167,'#1677E8',1.1);
        $text('This is a computer generated salary slip and does not require a signature.',297.5,150,7.5,false,'#71839A','center');
        $text('Generated by BizSkill',559,136,7.5,false,'#1677E8','right');

        $objs=[];
        $objs[]="<< /Type /Catalog /Pages 2 0 R >>";
        $objs[]="<< /Type /Pages /Kids [3 0 R] /Count 1 >>";
        $objs[]="<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R /F2 6 0 R >> >> /Contents 4 0 R >>";
        $objs[]="<< /Length ".strlen($c)." >>\nstream\n{$c}\nendstream";
        $objs[]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
        $objs[]="<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $pdf="%PDF-1.4\n";$offs=[0];
        foreach($objs as $i=>$o){$offs[]=strlen($pdf);$n=$i+1;$pdf.="{$n} 0 obj\n{$o}\nendobj\n";}
        $xref=strlen($pdf);$pdf.="xref\n0 ".(count($objs)+1)."\n0000000000 65535 f \n";
        for($i=1;$i<=count($objs);$i++)$pdf.=sprintf("%010d 00000 n \n",$offs[$i]);
        $pdf.="trailer\n<< /Size ".(count($objs)+1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        $up=wp_upload_dir();$dir=trailingslashit($up['basedir']).'bizskill-salary-slips/'.$x['salary_month'];if(!wp_mkdir_p($dir))return '';
        $fn='salary-slip-'.sanitize_file_name($x['employee_code']).'-'.$x['salary_month'].'.pdf';$path=trailingslashit($dir).$fn;
        if(file_put_contents($path,$pdf)===false)return '';
        $url=trailingslashit($up['baseurl']).'bizskill-salary-slips/'.$x['salary_month'].'/'.$fn;
        $wpdb->update($p.'salary_slips',['pdf_url'=>$url,'pdf_design_version'=>2],['id'=>$id]);
        return $url;
    }
    public function ajax_salary_commit(){
        $this->verify_ajax();global $wpdb;if(!$this->allowance_admin_allowed())wp_send_json_error(['message'=>'Only HO/Admin can generate salary slips'],403);$month=sanitize_text_field($_POST['salary_month']??'');$rows=json_decode(stripslashes($_POST['rows']??'[]'),true);if(!preg_match('/^\d{4}-\d{2}$/',$month)||!is_array($rows)||!$rows)wp_send_json_error(['message'=>'No valid salary data to submit']);$now=current_time('mysql');$uid=get_current_user_id();$made=0;
        foreach($rows as $r){$eid=(int)($r['employee_id']??0);if(!$eid)continue;$existing=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->prefix}salary_slips WHERE employee_id=%d AND salary_month=%s",$eid,$month),ARRAY_A);if($existing){$actual_existing_payment=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$this->prefix}vendor_payments WHERE payment_type='salary' AND salary_slip_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",(int)$existing['id']));if($actual_existing_payment>0.01)wp_send_json_error(['message'=>'Salary already has payment for '.$existing['employee_name'].'. It cannot be regenerated.']);}
            $gross=(float)$r['basic_salary']+(float)$r['allowance']+(float)$r['overtime']+(float)$r['incentive']+(float)$r['bonus']+(float)$r['other_earnings'];$ded=(float)$r['deduction']+(float)$r['advance_deduction']+(float)$r['other_deduction'];$net=max(0,$gross-$ded);$existing_payments=$existing?(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(SUM(amount),0) FROM {$this->prefix}vendor_payments WHERE payment_type='salary' AND salary_slip_id=%d AND LOWER(COALESCE(status,''))<>'cancelled'",(int)$existing['id'])):0;$open=0;$paid=min($net,max(0,$existing_payments));$bal=max(0,$net-$paid);$st=$bal<=0.01?'paid':($paid>0?'partly_paid':'pending_payment');$data=['salary_month'=>$month,'employee_id'=>$eid,'employee_code'=>sanitize_text_field($r['employee_code']??''),'employee_name'=>sanitize_text_field($r['employee_name']??''),'designation'=>sanitize_text_field($r['designation']??''),'present_days'=>(float)$r['present_days'],'basic_salary'=>(float)$r['basic_salary'],'allowance'=>(float)$r['allowance'],'overtime'=>(float)$r['overtime'],'incentive'=>(float)$r['incentive'],'bonus'=>(float)$r['bonus'],'other_earnings'=>(float)$r['other_earnings'],'deduction'=>(float)$r['deduction'],'advance_deduction'=>(float)$r['advance_deduction'],'other_deduction'=>(float)$r['other_deduction'],'gross_salary'=>$gross,'total_deduction'=>$ded,'net_salary_payable'=>$net,'opening_paid'=>0,'net_salary_paid'=>$paid,'balance_payable'=>$bal,'remarks'=>sanitize_textarea_field($r['remarks']??''),'status'=>$st,'updated_at'=>$now];if($existing){$wpdb->update($this->prefix.'salary_slips',$data,['id'=>(int)$existing['id']]);$id=(int)$existing['id'];}else{$data['created_by']=$uid;$data['created_at']=$now;$wpdb->insert($this->prefix.'salary_slips',$data);$id=(int)$wpdb->insert_id;}if(!$id)wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to save Salary Slip']);$this->generate_salary_pdf($id);$made++;}
        wp_send_json_success(['message'=>$made.' Salary Slip(s) generated','count'=>$made]);
    }
    public function ajax_salary_slips(){
        $this->verify_ajax();global $wpdb;
        // v0.1.349: derive salary Paid/Balance/Status directly from real Salary Payment rows.
        // Do not trust legacy values stored in salary_slips.
        $mode=sanitize_key($_POST['mode']??'all');$page=max(1,(int)($_POST['page']??1));$export=!empty($_POST['export_all']);$month=sanitize_text_field($_POST['salary_month']??'');$employee=(int)($_POST['employee_id']??0);$search=sanitize_text_field($_POST['search']??'');$where='1=1';$args=[];
        if($mode==='my'){$pid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}users WHERE wp_user_id=%d",get_current_user_id()));if(!$pid)wp_send_json_error(['message'=>'Employee profile not found']);$where.=' AND s.employee_id=%d';$args[]=$pid;}elseif(!$this->allowance_admin_allowed())wp_send_json_error(['message'=>'Only HO/Admin can view all salary slips'],403);
        if($month!==''){$where.=' AND s.salary_month=%s';$args[]=$month;}if($employee){$where.=' AND s.employee_id=%d';$args[]=$employee;}if($search!==''){$n='%'.$wpdb->esc_like($search).'%';$where.=' AND (s.employee_name LIKE %s OR s.employee_code LIKE %s OR s.designation LIKE %s OR s.salary_month LIKE %s)';for($i=0;$i<4;$i++)$args[]=$n;}
        $paidExpr="COALESCE((SELECT SUM(vp.amount) FROM {$this->prefix}vendor_payments vp WHERE LOWER(COALESCE(vp.payment_type,''))='salary' AND vp.salary_slip_id=s.id AND LOWER(COALESCE(vp.status,'paid'))<>'cancelled'),0)";
        $balExpr="GREATEST(0, s.net_salary_payable - LEAST(s.net_salary_payable, GREATEST(0, $paidExpr)))";
        if($mode==='pending')$where.=" AND $balExpr>0.01";
        $base=" FROM {$this->prefix}salary_slips s WHERE $where";
        $count="SELECT COUNT(*) $base";
        $sql="SELECT s.*, 0 opening_paid_calc, LEAST(s.net_salary_payable,GREATEST(0,$paidExpr)) net_salary_paid_calc, $balExpr balance_payable_calc, CASE WHEN $balExpr<=0.01 THEN 'paid' WHEN $paidExpr>0 THEN 'partly_paid' ELSE 'pending_payment' END status_calc $base ORDER BY s.salary_month DESC,s.employee_name ASC";
        if($args){$count=$wpdb->prepare($count,$args);$sql=$wpdb->prepare($sql,$args);}
        $total=(int)$wpdb->get_var($count);if(!$export)$sql.=$wpdb->prepare(' LIMIT %d OFFSET %d',20,($page-1)*20);$rows=$wpdb->get_results($sql,ARRAY_A);
        foreach($rows as &$sr){
            $sr['opening_paid']=0;
            $sr['net_salary_paid']=(float)($sr['net_salary_paid_calc']??0);
            $sr['balance_payable']=(float)($sr['balance_payable_calc']??0);
            $sr['status']=(string)($sr['status_calc']??'pending_payment');
            unset($sr['opening_paid_calc'],$sr['net_salary_paid_calc'],$sr['balance_payable_calc'],$sr['status_calc']);
            // Keep stored fields repaired for PDFs and other legacy screens.
            $wpdb->update($this->prefix.'salary_slips',['opening_paid'=>0,'net_salary_paid'=>$sr['net_salary_paid'],'balance_payable'=>$sr['balance_payable'],'status'=>$sr['status']],['id'=>(int)$sr['id']]);
            if((int)($sr['pdf_design_version']??0)<2){$url=$this->generate_salary_pdf((int)$sr['id']);if($url){$sr['pdf_url']=$url;$sr['pdf_design_version']=2;}}
        }unset($sr);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export?1:max(1,(int)ceil($total/20))]);
    }

    private function allowance_admin_allowed(){
        if(current_user_can('manage_options')) return true;
        global $wpdb;
        $role=(string)$wpdb->get_var($wpdb->prepare("SELECT role FROM {$this->prefix}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()));
        return $role!=='' && $role!=='bsm_employee';
    }

    public function ajax_allowance_submit(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;
        // Always attribute allowance claims to the authenticated plugin user, even for admins.
        $plugin_user_id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE wp_user_id=%d AND status='active' LIMIT 1",get_current_user_id()));
        if(!$plugin_user_id) wp_send_json_error(['message'=>'Your login is not linked to an active Employee record. Please contact the administrator.']);
        $employee_id=$plugin_user_id;
        $site_id=(int)($_POST['site_id']??0);
        $date=sanitize_text_field($_POST['allowance_date']??'');
        $type_id=(int)($_POST['allowance_type_id']??0);
        $amount=(float)($_POST['amount']??0);
        $purpose=sanitize_textarea_field($_POST['purpose']??'');
        if(!$employee_id) wp_send_json_error(['message'=>'Employee is required']);
        $site=$site_id?$wpdb->get_row($wpdb->prepare("SELECT id,customer_id FROM {$p}sites WHERE id=%d AND status='active'",$site_id),ARRAY_A):null;
        if(!$site) wp_send_json_error(['message'=>'Site Name is required']);
        $customer_id=(int)($site['customer_id']??0);
        if(!$customer_id) wp_send_json_error(['message'=>'Customer is not linked with the selected Site']);
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) wp_send_json_error(['message'=>'Date is required']);
        if(!$type_id || !(int)$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$p}allowance_types WHERE id=%d AND status='active'",$type_id))) wp_send_json_error(['message'=>'Allowance Type is required']);
        if($amount<=0) wp_send_json_error(['message'=>'Amount must be greater than zero']);
        if(trim($purpose)==='') wp_send_json_error(['message'=>'Purpose is required']);
        $document_url='';
        $document_name='';
        if(!empty($_FILES['supporting_document']) && !empty($_FILES['supporting_document']['name'])){
            require_once ABSPATH.'wp-admin/includes/file.php';
            $file=$_FILES['supporting_document'];
            if(!empty($file['size']) && (int)$file['size']>10*1024*1024) wp_send_json_error(['message'=>'Supporting Document must be 10 MB or less']);
            $up=wp_handle_upload($file,['test_form'=>false,'mimes'=>['pdf'=>'application/pdf','jpg|jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp']]);
            if(isset($up['error'])) wp_send_json_error(['message'=>'Supporting Document: '.$up['error']]);
            $document_url=esc_url_raw($up['url']);
            $document_name=sanitize_file_name($file['name']);
        }
        $limitRow=$wpdb->get_row($wpdb->prepare("SELECT id,limit_amount FROM {$p}employee_allowance_limits WHERE employee_id=%d AND allowance_type_id=%d AND status='active' LIMIT 1",$employee_id,$type_id),ARRAY_A);
        if(!$limitRow) wp_send_json_error(['message'=>'Allowance limit is not configured for this Employee and Allowance Type']);
        $limit_amount=(float)$limitRow['limit_amount'];
        // The allowance limit applies per employee + allowance type + date.
        // All non-rejected/non-cancelled claims already submitted for the same day count toward the limit.
        $same_day_claimed=(float)$wpdb->get_var($wpdb->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM {$p}employee_allowances WHERE employee_id=%d AND allowance_type_id=%d AND allowance_date=%s AND status NOT IN ('rejected','cancelled')",
            $employee_id,$type_id,$date
        ));
        $same_day_available=max(0,$limit_amount-$same_day_claimed);
        if(($same_day_claimed+$amount)>$limit_amount+0.001){
            wp_send_json_error(['message'=>'Daily Allowance Limit exceeded. Maximum available for this date is ₹'.number_format($same_day_available,2)]);
        }
        $now=current_time('mysql');
        $ok=$wpdb->insert($p.'employee_allowances',['allowance_no'=>'','employee_id'=>$employee_id,'site_id'=>$site_id,'customer_id'=>$customer_id,'allowance_date'=>$date,'allowance_type_id'=>$type_id,'amount'=>$amount,'purpose'=>$purpose,'document_url'=>$document_url,'document_name'=>$document_name,'status'=>'submitted','created_by'=>$plugin_user_id,'created_at'=>$now,'updated_at'=>$now]);
        if(!$ok) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to submit allowance']);
        $id=(int)$wpdb->insert_id;
        $no='ALW-'.current_time('ym').'-'.str_pad((string)$id,5,'0',STR_PAD_LEFT);
        $wpdb->update($p.'employee_allowances',['allowance_no'=>$no],['id'=>$id]);
        wp_send_json_success(['id'=>$id,'message'=>'Allowance submitted for approval']);
    }

    public function ajax_allowance_list(){
        $this->verify_ajax(); global $wpdb; $p=$this->prefix;
        $mode=sanitize_key($_POST['mode']??'submitted');
        $status=$mode==='payment'?'approved':($mode==='all'?'':'submitted');
        $page=max(1,(int)($_POST['page']??1));$export_all=!empty($_POST['export_all']);$offset=($page-1)*20;
        $search=sanitize_text_field($_POST['search']??'');
        $employee_id=(int)($_POST['employee_id']??0);$type_id=(int)($_POST['allowance_type_id']??0);
        $from=sanitize_text_field($_POST['date_from']??'');$to=sanitize_text_field($_POST['date_to']??'');
        $where='1=1';$args=[];
        if($status!==''){$where.=' AND a.status=%s';$args[]=$status;}
        if($employee_id){$where.=' AND a.employee_id=%d';$args[]=$employee_id;}
        if($type_id){$where.=' AND a.allowance_type_id=%d';$args[]=$type_id;}
        if($from!==''){$where.=' AND a.allowance_date>=%s';$args[]=$from;}
        if($to!==''){$where.=' AND a.allowance_date<=%s';$args[]=$to;}
        if(!$this->allowance_admin_allowed()){
            $uid=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()));
            $where.=' AND a.employee_id=%d';$args[]=$uid;
        }
        if($search!==''){$n='%'.$wpdb->esc_like($search).'%';$where.=' AND (a.allowance_no LIKE %s OR u.name LIKE %s OR t.allowance_type_name LIKE %s OR a.purpose LIKE %s OR s.site_name LIKE %s OR c.customer_name LIKE %s)';for($i=0;$i<6;$i++)$args[]=$n;}
        $fromSql=" FROM {$p}employee_allowances a LEFT JOIN {$p}users u ON u.id=a.employee_id LEFT JOIN {$p}allowance_types t ON t.id=a.allowance_type_id LEFT JOIN {$p}sites s ON s.id=a.site_id LEFT JOIN {$p}customers c ON c.id=a.customer_id LEFT JOIN {$p}users ap ON ap.id=a.approved_by WHERE $where";
        $count="SELECT COUNT(*) $fromSql";
        $sql="SELECT a.*,u.name employee_name,t.allowance_type_name,s.site_name,c.customer_name,ap.name approved_by_name $fromSql ORDER BY a.allowance_date DESC,a.id DESC";
        if(!$export_all)$sql.=$wpdb->prepare(' LIMIT %d OFFSET %d',20,$offset);
        if($args){$count=$wpdb->prepare($count,$args);$sql=$wpdb->prepare($sql,$args);}
        $total=(int)$wpdb->get_var($count);$rows=$wpdb->get_results($sql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20)),'total'=>$total]);
    }

    public function ajax_allowance_decide(){
        $this->verify_ajax(); if(!$this->allowance_admin_allowed()) wp_send_json_error(['message'=>'Only HO/Admin can approve allowances'],403);
        global $wpdb;$p=$this->prefix;$id=(int)($_POST['id']??0);$decision=sanitize_key($_POST['decision']??'');
        if(!$id || !in_array($decision,['approve','reject'],true)) wp_send_json_error(['message'=>'Invalid request']);
        $row=$wpdb->get_row($wpdb->prepare("SELECT * FROM {$p}employee_allowances WHERE id=%d",$id),ARRAY_A);
        if(!$row || ($row['status']??'')!=='submitted') wp_send_json_error(['message'=>'Allowance is no longer pending approval']);
        $approver=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()));$now=current_time('mysql');
        if($decision==='approve') $data=['status'=>'approved','approved_by'=>$approver,'approved_at'=>$now,'updated_at'=>$now];
        else $data=['status'=>'rejected','rejected_by'=>$approver,'rejected_at'=>$now,'updated_at'=>$now];
        $wpdb->update($p.'employee_allowances',$data,['id'=>$id]);
        wp_send_json_success(['message'=>$decision==='approve'?'Allowance approved':'Allowance rejected']);
    }

    public function ajax_allowance_limits(){
        $this->verify_ajax();
        global $wpdb;$p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));$export=!empty($_POST['export_all']);$offset=($page-1)*20;
        $search=sanitize_text_field($_POST['search']??'');$employee_id=(int)($_POST['employee_id']??0);$type_id=(int)($_POST['allowance_type_id']??0);$status=sanitize_key($_POST['status']??'');
        if(!$this->allowance_admin_allowed()){
            $own=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}users WHERE wp_user_id=%d LIMIT 1",get_current_user_id()));
            if(!$own || ($employee_id && $employee_id!==$own)) wp_send_json_error(['message'=>'You can only view your own allowance limit'],403);
            $employee_id=$own;$export=false;
        }
        $where='1=1';$args=[];
        if($employee_id){$where.=' AND l.employee_id=%d';$args[]=$employee_id;}
        if($type_id){$where.=' AND l.allowance_type_id=%d';$args[]=$type_id;}
        if(in_array($status,['active','inactive'],true)){$where.=' AND l.status=%s';$args[]=$status;}
        if($search!==''){$n='%'.$wpdb->esc_like($search).'%';$where.=' AND (u.name LIKE %s OR t.allowance_type_name LIKE %s)';$args[]=$n;$args[]=$n;}
        $from=" FROM {$p}employee_allowance_limits l LEFT JOIN {$p}users u ON u.id=l.employee_id LEFT JOIN {$p}allowance_types t ON t.id=l.allowance_type_id LEFT JOIN (SELECT employee_id,allowance_type_id,SUM(amount) claimed FROM {$p}employee_allowances WHERE status NOT IN ('rejected','cancelled') GROUP BY employee_id,allowance_type_id) c ON c.employee_id=l.employee_id AND c.allowance_type_id=l.allowance_type_id WHERE $where";
        $count="SELECT COUNT(*) $from";
        $sql="SELECT l.*,u.name employee_name,t.allowance_type_name,COALESCE(c.claimed,0) claimed_amount,GREATEST(0,l.limit_amount-COALESCE(c.claimed,0)) available_amount $from ORDER BY u.name,t.allowance_type_name";
        if($args){$count=$wpdb->prepare($count,$args);$sql=$wpdb->prepare($sql,$args);}
        $total=(int)$wpdb->get_var($count);if(!$export)$sql.=$wpdb->prepare(' LIMIT %d OFFSET %d',20,$offset);$rows=$wpdb->get_results($sql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export?1:max(1,(int)ceil($total/20))]);
    }

    public function ajax_office_attendance_day(){
        $this->verify_ajax();
        $this->migrate_office_attendance_table();
        global $wpdb; $p=$this->prefix;
        $date=sanitize_text_field($_POST['date']??'');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) $date=current_time('Y-m-d');
        $base_select="SELECT u.id user_id,u.name,u.designation_id,COALESCE(d.designation_name,u.designation,'') designation,COALESCE(a.attendance_status,'') attendance_status";
        $has_image_url=(bool)$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$p}office_attendance` LIKE %s",'image_url'));
        $has_image_name=(bool)$wpdb->get_var($wpdb->prepare("SHOW COLUMNS FROM `{$p}office_attendance` LIKE %s",'image_name'));
        $base_select.=$has_image_url?",COALESCE(a.image_url,'') image_url":",'' image_url";
        $base_select.=$has_image_name?",COALESCE(a.image_name,'') image_name":",'' image_name";
        $join=" FROM {$p}users u LEFT JOIN {$p}designations d ON d.id=u.designation_id LEFT JOIN {$p}office_attendance a ON a.user_id=u.id AND a.attendance_date=%s";
        $rows=$wpdb->get_results($wpdb->prepare($base_select.$join." WHERE LOWER(TRIM(COALESCE(u.status,'active')))='active' AND u.attendance_applicable=1 ORDER BY u.name,u.id",$date),ARRAY_A);
        if(!$rows){
            $rows=$wpdb->get_results($wpdb->prepare($base_select.$join." WHERE LOWER(TRIM(COALESCE(u.status,'active')))='active' ORDER BY u.name,u.id",$date),ARRAY_A);
        }
        if(!$rows){
            $rows=$wpdb->get_results($wpdb->prepare($base_select.$join." ORDER BY u.name,u.id",$date),ARRAY_A);
        }
        foreach($rows as &$attendance_row){if($this->ops_holiday_dates((int)$attendance_row['user_id'],$date,$date))$attendance_row['attendance_status']='H';}unset($attendance_row);
        wp_send_json_success(['date'=>$date,'rows'=>$rows]);
    }

    public function ajax_office_attendance_save(){
        $this->verify_ajax();
        $this->migrate_office_attendance_table();
        global $wpdb; $p=$this->prefix;
        $date=sanitize_text_field($_POST['date']??'');
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) wp_send_json_error(['message'=>'Please select a valid date.']);
        $rows=json_decode(stripslashes($_POST['rows']??'[]'),true);
        if(!is_array($rows) || !$rows) wp_send_json_error(['message'=>'No attendance rows found.']);
        $eligible=array_map('intval',$wpdb->get_col("SELECT id FROM {$p}users WHERE LOWER(TRIM(COALESCE(status,'active')))='active' AND attendance_applicable=1"));
        if(!$eligible){
            $eligible=array_map('intval',$wpdb->get_col("SELECT id FROM {$p}users WHERE LOWER(TRIM(COALESCE(status,'active')))='active'"));
        }
        if(!$eligible){
            $eligible=array_map('intval',$wpdb->get_col("SELECT id FROM {$p}users"));
        }
        $eligible_set=array_fill_keys($eligible,true); $saved=0; $uid=get_current_user_id(); $now=current_time('mysql');
        require_once ABSPATH.'wp-admin/includes/file.php';
        foreach($rows as $r){
            $user_id=(int)($r['user_id']??0); $st=strtoupper(sanitize_text_field($r['status']??''));
            if(!$user_id || !isset($eligible_set[$user_id])) continue;
            if($this->ops_holiday_dates($user_id,$date,$date)) continue;
            if(!in_array($st,['P','A'],true)) wp_send_json_error(['message'=>'Please mark Present or Absent for every employee.']);
            $id=(int)$wpdb->get_var($wpdb->prepare("SELECT id FROM {$p}office_attendance WHERE attendance_date=%s AND user_id=%d LIMIT 1",$date,$user_id));
            $data=['attendance_status'=>$st,'updated_at'=>$now];
            $file_key='attendance_image_'.$user_id;
            if(!empty($_FILES[$file_key]) && !empty($_FILES[$file_key]['name'])){
                $file=$_FILES[$file_key];
                if((int)($file['size']??0)>5*1024*1024) wp_send_json_error(['message'=>'Attendance image must be 5 MB or smaller.']);
                $check=wp_check_filetype_and_ext($file['tmp_name'],$file['name']);
                $type=(string)($check['type']??$file['type']??'');
                if(strpos($type,'image/')!==0) wp_send_json_error(['message'=>'Please upload an image file only.']);
                $up=wp_handle_upload($file,['test_form'=>false,'mimes'=>['jpg|jpeg'=>'image/jpeg','png'=>'image/png','webp'=>'image/webp','gif'=>'image/gif']]);
                if(isset($up['error'])) wp_send_json_error(['message'=>$up['error']]);
                $data['image_url']=esc_url_raw($up['url']);
                $data['image_name']=sanitize_file_name($file['name']);
            }
            if($id){$wpdb->update($p.'office_attendance',$data,['id'=>$id]);}
            else {$data['attendance_date']=$date;$data['user_id']=$user_id;$data['created_by']=$uid;$data['created_at']=$now;$wpdb->insert($p.'office_attendance',$data);}
            $saved++;
        }
        wp_send_json_success(['message'=>'Attendance saved successfully','saved'=>$saved]);
    }

    public function ajax_office_attendance_master(){
        $this->verify_ajax();
        global $wpdb; $p=$this->prefix;
        $page=max(1,(int)($_POST['page']??1));$export_all=!empty($_POST['export_all']);$per=$export_all?0:20;$offset=$export_all?0:(($page-1)*20);
        $search=sanitize_text_field($_POST['search']??'');$date_from=sanitize_text_field($_POST['date_from']??'');$date_to=sanitize_text_field($_POST['date_to']??'');$user_id=(int)($_POST['user_id']??0);$status=strtoupper(sanitize_text_field($_POST['status']??''));
        $holiday_status="CASE WHEN EXISTS(SELECT 1 FROM {$p}holidays h WHERE h.holiday_date=a.attendance_date AND (COALESCE(h.employee_ids,'[]')='[]' OR JSON_CONTAINS(COALESCE(NULLIF(h.employee_ids,''),'[]'),CAST(a.user_id AS CHAR)))) THEN 'H' ELSE a.attendance_status END";
        $where='1=1';$args=[];
        if($date_from){$where.=' AND a.attendance_date>=%s';$args[]=$date_from;} if($date_to){$where.=' AND a.attendance_date<=%s';$args[]=$date_to;} if($user_id){$where.=' AND a.user_id=%d';$args[]=$user_id;} if(in_array($status,['P','A','H'],true)){$where.=" AND ($holiday_status)=%s";$args[]=$status;}
        if($search!==''){$n='%'.$wpdb->esc_like($search).'%';$where.=" AND (u.name LIKE %s OR COALESCE(d.designation_name,u.designation,'') LIKE %s OR a.attendance_status LIKE %s)";$args[]=$n;$args[]=$n;$args[]=$n;}
        $from=" FROM {$p}office_attendance a INNER JOIN {$p}users u ON u.id=a.user_id LEFT JOIN {$p}designations d ON d.id=u.designation_id WHERE $where";
        $count="SELECT COUNT(*) $from";$sql="SELECT a.id,a.attendance_date,a.user_id,u.name,COALESCE(d.designation_name,u.designation,'') designation,($holiday_status) attendance_status,COALESCE(a.image_url,'') image_url,COALESCE(a.image_name,'') image_name $from ORDER BY a.attendance_date DESC,u.name";
        if(!$export_all)$sql.=$wpdb->prepare(' LIMIT %d OFFSET %d',$per,$offset); if($args){$count=$wpdb->prepare($count,$args);$sql=$wpdb->prepare($sql,$args);} $total=(int)$wpdb->get_var($count);$rows=$wpdb->get_results($sql,ARRAY_A);
        wp_send_json_success(['rows'=>$rows,'total'=>$total,'page'=>$page,'pages'=>$export_all?1:max(1,(int)ceil($total/20))]);
    }

    public function ajax_monthly_salary(){
        $this->verify_ajax();
        global $wpdb; $p=$this->prefix;
        $month=sanitize_text_field($_POST['month']??''); if(!preg_match('/^\d{4}-\d{2}$/',$month)) $month=current_time('Y-m');
        $start=$month.'-01';$end=date('Y-m-t',strtotime($start));
        $users=$wpdb->get_results("SELECT u.id user_id,u.name,COALESCE(d.designation_name,u.designation,'') designation,u.salary,u.salary_effective_date FROM {$p}users u LEFT JOIN {$p}designations d ON d.id=u.designation_id WHERE u.attendance_applicable=1 AND LOWER(COALESCE(u.status,'active'))='active' ORDER BY u.name",ARRAY_A);
        $rows=[];
        foreach($users as $u){
            $uid=(int)$u['user_id'];
            $atts=$wpdb->get_results($wpdb->prepare("SELECT attendance_date,attendance_status FROM {$p}office_attendance WHERE user_id=%d AND attendance_date BETWEEN %s AND %s ORDER BY attendance_date",$uid,$start,$end),ARRAY_A);
            $holidays=$this->ops_holiday_dates($uid,$start,$end);
            $attendance_by_date=[];foreach($atts as $a)$attendance_by_date[$a['attendance_date']]=$a['attendance_status'];
            foreach($holidays as $hd=>$unused)if(!isset($attendance_by_date[$hd]))$atts[]=['attendance_date'=>$hd,'attendance_status'=>'H'];
            $present=0;$absent=0;$payable=0.0;$marked=count($attendance_by_date);
            foreach($atts as $a){if($a['attendance_status']==='P')$present++;else if($a['attendance_status']==='A'&&!isset($holidays[$a['attendance_date']]))$absent++;}
            $days_in_month=(int)date('t',strtotime($start));
            $latest=(float)$wpdb->get_var($wpdb->prepare("SELECT monthly_salary FROM {$p}user_salary_history WHERE user_id=%d AND effective_date<=%s ORDER BY effective_date DESC,id DESC LIMIT 1",$uid,$end));
            if($latest<=0 && !empty($u['salary_effective_date']) && $u['salary_effective_date']<=$end)$latest=(float)$u['salary'];
            // Monthly salary is prorated by calendar days in the selected month.
            // Example: September has 30 days; one Present day on a ₹10,000 salary = ₹333.33.
            if($days_in_month>0){
                foreach($atts as $a){
                    if($a['attendance_status']!=='P'&&!isset($holidays[$a['attendance_date']]))continue;
                    $sal=(float)$wpdb->get_var($wpdb->prepare("SELECT monthly_salary FROM {$p}user_salary_history WHERE user_id=%d AND effective_date<=%s ORDER BY effective_date DESC,id DESC LIMIT 1",$uid,$a['attendance_date']));
                    if($sal<=0 && !empty($u['salary_effective_date']) && $u['salary_effective_date']<=$a['attendance_date'])$sal=(float)$u['salary'];
                    if($sal>0)$payable += $sal/$days_in_month;
                }
            }
            $rows[]=['user_id'=>$uid,'name'=>$u['name'],'designation'=>$u['designation'],'monthly_salary'=>round($latest,2),'present_days'=>$present,'holiday_days'=>count($holidays),'absent_days'=>$absent,'marked_days'=>$marked,'days_in_month'=>$days_in_month,'payable_salary'=>round($payable,2)];
        }
        wp_send_json_success(['month'=>$month,'rows'=>$rows]);
    }

    public function ajax_company_logo_upload(){
        $this->verify_ajax();
        if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Permission denied'],403);

        // Receive the logo as Base64 text instead of relying on PHP/WordPress file uploads.
        // This avoids server temp-folder / uploads-folder permission restrictions.
        $filename=sanitize_file_name($_POST['filename']??'logo');
        $declared_mime=strtolower(sanitize_text_field($_POST['mime']??''));
        $base64=(string)($_POST['data']??'');
        if($base64==='') wp_send_json_error(['message'=>'Please select a logo file']);

        if(strpos($base64,'data:')===0){
            $parts=explode(',',$base64,2);
            $base64=$parts[1]??'';
            if(preg_match('#^data:([^;]+);base64$#i',$parts[0]??'',$m)) $declared_mime=strtolower($m[1]);
        }
        $base64=preg_replace('/\s+/','',$base64);
        $bytes=base64_decode($base64,true);
        if($bytes===false || $bytes==='') wp_send_json_error(['message'=>'Logo file could not be read.']);
        if(strlen($bytes)>1024*1024) wp_send_json_error(['message'=>'Logo file must be 1 MB or smaller.']);

        $allowed=['image/png','image/jpeg','image/webp'];
        $img=@getimagesizefromstring($bytes);
        $mime=strtolower((string)($img['mime']??$declared_mime));
        if(!$img || !in_array($mime,$allowed,true)) wp_send_json_error(['message'=>'Logo must be a valid PNG, JPG or WEBP image.']);

        $data_uri='data:'.$mime.';base64,'.base64_encode($bytes);
        global $wpdb;
        $table=$this->prefix.'company_settings';
        $col=$wpdb->get_row("SHOW COLUMNS FROM `{$table}` LIKE 'logo_url'",ARRAY_A);
        if(!$col){
            $wpdb->query("ALTER TABLE `{$table}` ADD COLUMN `logo_url` LONGTEXT NULL");
        }elseif(stripos((string)($col['Type']??''),'longtext')===false){
            $wpdb->query("ALTER TABLE `{$table}` MODIFY `logo_url` LONGTEXT NULL");
        }
        $id=(int)$wpdb->get_var("SELECT id FROM {$table} ORDER BY id DESC LIMIT 1");
        $payload=['logo_url'=>$data_uri,'updated_at'=>current_time('mysql')];
        if(!$id){
            $payload['company_name']=get_bloginfo('name');
            $ok=$wpdb->insert($table,$payload);
            if($ok===false) wp_send_json_error(['message'=>'Logo could not be saved in Company Settings: '.$wpdb->last_error]);
            $id=(int)$wpdb->insert_id;
        }else{
            $ok=$wpdb->update($table,$payload,['id'=>$id]);
            if($ok===false) wp_send_json_error(['message'=>'Logo could not be saved in Company Settings: '.$wpdb->last_error]);
        }
        wp_send_json_success(['id'=>$id,'message'=>'Logo saved']);
    }

    public function ajax_company_upi_qr_upload(){
        $this->verify_ajax();
        if(!current_user_can('manage_options')) wp_send_json_error(['message'=>'Permission denied'],403);
        $this->migrate_company_payment_details();
        $declared_mime=strtolower(sanitize_text_field($_POST['mime']??''));
        $base64=(string)($_POST['data']??'');
        if($base64==='') wp_send_json_error(['message'=>'Please select a UPI QR image']);
        if(strpos($base64,'data:')===0){$parts=explode(',',$base64,2);$base64=$parts[1]??'';if(preg_match('#^data:([^;]+);base64$#i',$parts[0]??'',$m))$declared_mime=strtolower($m[1]);}
        $bytes=base64_decode(preg_replace('/\s+/','',$base64),true);
        if($bytes===false || $bytes==='') wp_send_json_error(['message'=>'UPI QR image could not be read.']);
        if(strlen($bytes)>2*1024*1024) wp_send_json_error(['message'=>'UPI QR image must be 2 MB or smaller.']);
        $allowed=['image/png','image/jpeg','image/webp'];$img=@getimagesizefromstring($bytes);$mime=strtolower((string)($img['mime']??$declared_mime));
        if(!$img || !in_array($mime,$allowed,true)) wp_send_json_error(['message'=>'UPI QR must be a valid PNG, JPG or WEBP image.']);
        $data_uri='data:'.$mime.';base64,'.base64_encode($bytes);
        global $wpdb;$table=$this->prefix.'company_settings';$id=(int)$wpdb->get_var("SELECT id FROM {$table} ORDER BY id DESC LIMIT 1");
        $payload=['upi_qr_url'=>$data_uri,'updated_at'=>current_time('mysql')];
        if(!$id){$payload['company_name']=get_bloginfo('name');$ok=$wpdb->insert($table,$payload);$id=(int)$wpdb->insert_id;}else{$ok=$wpdb->update($table,$payload,['id'=>$id]);}
        if($ok===false) wp_send_json_error(['message'=>'UPI QR could not be saved: '.$wpdb->last_error]);
        wp_send_json_success(['id'=>$id,'message'=>'UPI QR saved']);
    }

    public function ajax_consumption_item_upload(){
        $this->verify_ajax();
        $this->migrate_consumption_item_documents();
        if(empty($_FILES['file'])) wp_send_json_error(['message'=>'No file selected']);
        $item_row_id=(int)($_POST['consumption_item_id']??0);
        if(!$item_row_id) wp_send_json_error(['message'=>'Invalid consumption item']);
        global $wpdb;
        $row=$wpdb->get_row($wpdb->prepare("SELECT id FROM {$this->prefix}site_consumption_items WHERE id=%d",$item_row_id),ARRAY_A);
        if(!$row) wp_send_json_error(['message'=>'Consumption item not found']);
        require_once ABSPATH.'wp-admin/includes/file.php';
        $up=wp_handle_upload($_FILES['file'],['test_form'=>false]);
        if(isset($up['error'])) wp_send_json_error(['message'=>$up['error']]);
        $name=sanitize_file_name($_FILES['file']['name']);
        $ok=$wpdb->update($this->prefix.'site_consumption_items',['document_url'=>esc_url_raw($up['url']),'document_name'=>$name],['id'=>$item_row_id]);
        if($ok===false) wp_send_json_error(['message'=>$wpdb->last_error?:'Unable to save document']);
        wp_send_json_success(['url'=>$up['url'],'name'=>$name]);
    }

    public function ajax_upload(){
        $this->verify_ajax(); if(empty($_FILES['file'])) wp_send_json_error(['message'=>'No file']);
        if(sanitize_key($_POST['module']??'')==='material_dispatches' && sanitize_text_field($_POST['document_type']??'')==='Dispatch Photo'){
            global $wpdb;$dispatch_id=(int)($_POST['reference_id']??0);
            if(!$dispatch_id||!$wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->prefix}material_dispatches WHERE id=%d",$dispatch_id)))wp_send_json_error(['message'=>'Dispatch not found.']);
            $guard=$this->record_guard('material_dispatches',$dispatch_id);if(!$guard['can_edit'])wp_send_json_error(['message'=>$guard['edit_message']?:'You cannot update this dispatch.']);
            $check=wp_check_filetype_and_ext($_FILES['file']['tmp_name'],$_FILES['file']['name']);
            if(!in_array($check['type']??'',['image/jpeg','image/png','image/webp'],true)||!@getimagesize($_FILES['file']['tmp_name']))wp_send_json_error(['message'=>'Upload a JPG, PNG or WebP photo.']);
            if((int)($_FILES['file']['size']??0)>10*1024*1024)wp_send_json_error(['message'=>'Photo exceeds 10 MB.']);
        }
        if(sanitize_key($_POST['module']??'')==='vendor_payments' && sanitize_text_field($_POST['document_type']??'')==='Payment Document'){
            $allowed=['image/jpeg','image/png','image/webp','application/pdf'];
            $check=wp_check_filetype_and_ext($_FILES['file']['tmp_name'],$_FILES['file']['name']);
            if(!in_array($check['type']??'',$allowed,true))wp_send_json_error(['message'=>'Unsupported payment attachment type']);
            if((int)($_FILES['file']['size']??0)>10*1024*1024)wp_send_json_error(['message'=>'Payment attachment exceeds 10 MB']);
        }
        // A supplier invoice can only be attached after its Bill Type is explicitly selected.
        if(sanitize_key($_POST['module']??'')==='material_inwards' && sanitize_text_field($_POST['document_type']??'')==='Supplier Invoice'){
            global $wpdb;
            $inward_id=(int)($_POST['reference_id']??0);
            $type=sanitize_key((string)($_POST['bill_type']??''));
            if(!in_array($type,['gst_bill','non_gst_bill'],true)) wp_send_json_error(['message'=>'Please select Bill Type before uploading the invoice.']);
            $purchase=$wpdb->get_row($wpdb->prepare("SELECT id,bill_type FROM {$this->prefix}material_inwards WHERE id=%d",$inward_id),ARRAY_A);
            if(!$purchase) wp_send_json_error(['message'=>'Purchase not found']);
            // Do not silently reclassify historical, tax-bearing purchases during attachment upload.
            if(!empty($purchase['bill_type']) && $purchase['bill_type']!==$type) wp_send_json_error(['message'=>'Bill Type differs from the saved purchase. Edit the purchase first.']);
            if(empty($purchase['bill_type'])){
                $tax=(float)$wpdb->get_var($wpdb->prepare("SELECT COALESCE(cgst_amount,0)+COALESCE(sgst_amount,0)+COALESCE(igst_amount,0)+COALESCE(other_expense_gst,0) FROM {$this->prefix}material_inwards WHERE id=%d",$inward_id));
                if($type==='non_gst_bill' && $tax>0.001) wp_send_json_error(['message'=>'This purchase already has GST amounts. Edit and confirm its tax treatment before selecting Non GST Bill.']);
                $wpdb->update($this->prefix.'material_inwards',['bill_type'=>$type],['id'=>$inward_id]);
            }
        }
        require_once ABSPATH.'wp-admin/includes/file.php';$up=wp_handle_upload($_FILES['file'],['test_form'=>false]);if(isset($up['error']))wp_send_json_error(['message'=>$up['error']]);global $wpdb;$wpdb->insert($this->prefix.'documents',['module'=>sanitize_key($_POST['module']??''),'reference_id'=>intval($_POST['reference_id']??0),'document_type'=>sanitize_text_field($_POST['document_type']??'Document'),'file_url'=>esc_url_raw($up['url']),'original_filename'=>sanitize_file_name($_FILES['file']['name']),'uploaded_by'=>get_current_user_id(),'uploaded_at'=>current_time('mysql')]);wp_send_json_success(['url'=>$up['url'],'name'=>$_FILES['file']['name']]);
    }

    public function ajax_login(){
        // Login must work before a WordPress session exists. Do not use the normal
        // application nonce here because cached public/login pages can contain a stale
        // logged-out nonce and WordPress then rejects the request with HTTP 403.
        // Authentication itself is performed by wp_signon().
        nocache_headers();
        $user_id=trim((string)wp_unslash($_POST['user_id']??''));
        $password=(string)wp_unslash($_POST['password']??'');
        if($user_id==='' || $password===''){
            wp_send_json_error(['message'=>'Enter User ID and Password'],400);
        }

        // Allow the User ID field to contain either the WordPress login ID or email.
        $login=sanitize_user($user_id,true);
        if(is_email($user_id)){
            $by_email=get_user_by('email',sanitize_email($user_id));
            if($by_email && !empty($by_email->user_login)) $login=$by_email->user_login;
        }
        if($login==='') $login=sanitize_text_field($user_id);

        $creds=['user_login'=>$login,'user_password'=>$password,'remember'=>true];
        $u=wp_signon($creds,is_ssl());
        if(is_wp_error($u)){
            wp_send_json_error(['message'=>'Invalid User ID or Password'],401);
        }
        wp_set_current_user($u->ID);
        wp_send_json_success(['message'=>'Login successful','redirect'=>home_url('/')]);
    }
    public function ajax_logout(){ $this->verify_ajax(); wp_logout(); wp_send_json_success(['message'=>'Logged out']); }
    public function ajax_project_tracker() {
        $this->verify_ajax(); global $wpdb;
        $site=(int)($_POST['site_id']??0);
        $sites=$wpdb->get_results("SELECT id,site_name,site_code,site_sections,status FROM {$this->prefix}sites ORDER BY site_name",ARRAY_A);
        $tasks=$wpdb->get_results("SELECT id,site_id,section,task_name,sort_order FROM {$this->prefix}project_tasks ORDER BY sort_order,id",ARRAY_A);
        $subs=$wpdb->get_results("SELECT id,task_id,subtask_name,completion,sort_order FROM {$this->prefix}project_subtasks ORDER BY sort_order,id",ARRAY_A);
        $by_task=[]; foreach($subs as $sub){$by_task[(int)$sub['task_id']][]=$sub;}
        $by_site=[];
        foreach($tasks as $t){
            $t['subtasks']=$by_task[(int)$t['id']]??[];
            $t['completion']=count($t['subtasks'])?round(array_sum(array_map(fn($x)=>(float)$x['completion'],$t['subtasks']))/count($t['subtasks']),2):0;
            $by_site[(int)$t['site_id']][]=$t;
        }
        $out=[];
        foreach($sites as $row){
            if($site && (int)$row['id']!==$site)continue;
            $row['tasks']=$by_site[(int)$row['id']]??[];
            $sections=array_values(array_unique(array_filter(array_map('trim',explode(',',(string)$row['site_sections'])))));
            $row['sections']=$sections;
            $progress=[];
            foreach($sections as $section){$matching=array_values(array_filter($row['tasks'],fn($t)=>$t['section']===$section));if($matching)$progress[]=array_sum(array_column($matching,'completion'))/count($matching);}
            $row['completion']=count($progress)?round(array_sum($progress)/count($progress),2):0;
            $row['has_tasks']=count($progress)>0;
            $out[]=$row;
        }
        wp_send_json_success(['sites'=>$out]);
    }

    public function ajax_project_tracker_save() {
        $this->verify_ajax(); global $wpdb;
        $type=sanitize_key($_POST['type']??'');
        $id=(int)($_POST['id']??0);
        $site=(int)($_POST['site_id']??0);
        $tasks=$this->prefix.'project_tasks';$subs=$this->prefix.'project_subtasks';
        $siteSections=fn($sid)=>array_map('trim',explode(',',(string)$wpdb->get_var($wpdb->prepare("SELECT site_sections FROM {$this->prefix}sites WHERE id=%d",$sid))));
        if($type==='edit_task_full'){
            if(!$id)wp_send_json_error(['message'=>'Invalid Task']);
            $task=$wpdb->get_row($wpdb->prepare("SELECT site_id FROM $tasks WHERE id=%d",$id));
            $section=sanitize_text_field(wp_unslash($_POST['section']??''));
            $taskName=substr(trim(sanitize_text_field(wp_unslash($_POST['task_name']??''))),0,255);
            if(!$task||!$taskName||!in_array($section,$siteSections($task->site_id),true))wp_send_json_error(['message'=>'Valid Section and Task Name are required']);
            $existing=isset($_POST['existing_subtasks'])&&is_array($_POST['existing_subtasks'])?wp_unslash($_POST['existing_subtasks']):[];
            $new=isset($_POST['new_subtasks'])&&is_array($_POST['new_subtasks'])?wp_unslash($_POST['new_subtasks']):[];
            if(count($existing)+count($new)>100)wp_send_json_error(['message'=>'Maximum 100 Sub-tasks per Task']);
            $actualIds=array_map('intval',$wpdb->get_col($wpdb->prepare("SELECT id FROM $subs WHERE task_id=%d",$id)));
            $givenIds=[];$cleanExisting=[];$cleanNew=[];
            foreach($existing as $item){
                if(!is_array($item))wp_send_json_error(['message'=>'Invalid Sub-task data']);
                $sid=(int)($item['id']??0);
                $name=substr(trim(sanitize_text_field($item['name']??'')),0,255);
                if(!$sid||!in_array($sid,$actualIds,true)||in_array($sid,$givenIds,true)||!$name)wp_send_json_error(['message'=>'Invalid existing Sub-task']);
                $givenIds[]=$sid;$cleanExisting[]=['id'=>$sid,'name'=>$name];
            }
            $a=$actualIds;$b=$givenIds;sort($a);sort($b);
            if($a!==$b)wp_send_json_error(['message'=>'Sub-task list changed. Refresh and try again']);
            foreach($new as $name){
                if(!is_string($name))wp_send_json_error(['message'=>'Invalid new Sub-task']);
                $name=substr(trim(sanitize_text_field($name)),0,255);
                if(!$name)wp_send_json_error(['message'=>'New Sub-task name is required']);
                $cleanNew[]=$name;
            }
            $wpdb->query('START TRANSACTION');
            $ok=$wpdb->update($tasks,['section'=>$section,'task_name'=>$taskName],['id'=>$id],['%s','%s'],['%d']);
            foreach($cleanExisting as $item){
                if($ok===false)break;
                $ok=$wpdb->update($subs,['subtask_name'=>$item['name'],'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['id'=>$item['id'],'task_id'=>$id],['%s','%s','%d'],['%d','%d']);
            }
            foreach($cleanNew as $name){
                if($ok===false)break;
                $ok=$wpdb->insert($subs,['task_id'=>$id,'subtask_name'=>$name,'completion'=>0,'sort_order'=>0,'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['%d','%s','%f','%d','%s','%d']);
            }
            if($ok===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to save Task and Sub-tasks']);}
            $wpdb->query('COMMIT');wp_send_json_success(['message'=>'Saved']);
        }
        if(in_array($type,['edit_task','edit_subtask','delete_task','delete_subtask'],true)){
            if(!$id)wp_send_json_error(['message'=>'Invalid record']);
            if($type==='edit_task'){
                $task=$wpdb->get_row($wpdb->prepare("SELECT site_id FROM $tasks WHERE id=%d",$id));
                $section=sanitize_text_field(wp_unslash($_POST['section']??''));
                $name=substr(trim(sanitize_text_field(wp_unslash($_POST['name']??''))),0,255);
                if(!$task||!$name||!in_array($section,$siteSections($task->site_id),true))wp_send_json_error(['message'=>'Valid Section and Task Name are required']);
                if($wpdb->update($tasks,['section'=>$section,'task_name'=>$name],['id'=>$id],['%s','%s'],['%d'])===false)wp_send_json_error(['message'=>'Unable to update Task']);
            }elseif($type==='edit_subtask'){
                $name=substr(trim(sanitize_text_field(wp_unslash($_POST['name']??''))),0,255);
                $taskName=substr(trim(sanitize_text_field(wp_unslash($_POST['task_name']??''))),0,255);
                $parentTaskId=(int)$wpdb->get_var($wpdb->prepare("SELECT task_id FROM $subs WHERE id=%d",$id));
                if(!$name||!$taskName||!$parentTaskId)wp_send_json_error(['message'=>'Valid Task and Sub-task Names are required']);
                $wpdb->query('START TRANSACTION');
                $updatedTask=$wpdb->update($tasks,['task_name'=>$taskName],['id'=>$parentTaskId],['%s'],['%d']);
                $updatedSub=$wpdb->update($subs,['subtask_name'=>$name,'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['id'=>$id],['%s','%s','%d'],['%d']);
                if($updatedTask===false||$updatedSub===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to update Task or Sub-task']);}
                $wpdb->query('COMMIT');
            }elseif($type==='delete_subtask'){
                if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM $subs WHERE id=%d",$id)))wp_send_json_error(['message'=>'Sub-task not found']);
                $wpdb->query('START TRANSACTION');
                $wpdb->delete($this->prefix.'project_progress_history',['subtask_id'=>$id],['%d']);
                $ok=$wpdb->delete($subs,['id'=>$id],['%d']);
                if($ok!==1){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to delete Sub-task']);}
                $wpdb->query('COMMIT');
            }else{
                if(!$wpdb->get_var($wpdb->prepare("SELECT id FROM $tasks WHERE id=%d",$id)))wp_send_json_error(['message'=>'Task not found']);
                $wpdb->query('START TRANSACTION');
                $wpdb->query($wpdb->prepare("DELETE FROM {$this->prefix}project_progress_history WHERE subtask_id IN (SELECT id FROM $subs WHERE task_id=%d)",$id));
                $wpdb->delete($subs,['task_id'=>$id],['%d']);
                $ok=$wpdb->delete($tasks,['id'=>$id],['%d']);
                if($ok!==1){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to delete Task']);}
                $wpdb->query('COMMIT');
            }
            wp_send_json_success(['message'=>'Saved']);
        }
        if($type==='task_with_subtasks'||$type==='subtasks'){
            $rawNames=isset($_POST['subtask_names'])&&is_array($_POST['subtask_names'])?wp_unslash($_POST['subtask_names']):[];
            if(!$rawNames||count($rawNames)>100)wp_send_json_error(['message'=>'Enter between 1 and 100 Sub-task names']);
            $names=[];
            foreach($rawNames as $rawName){
                if(!is_string($rawName))wp_send_json_error(['message'=>'Invalid Sub-task name']);
                $clean=substr(trim(sanitize_text_field($rawName)),0,255);
                if($clean==='')wp_send_json_error(['message'=>'Sub-task name cannot be blank']);
                $names[]=$clean;
            }
            if($type==='task_with_subtasks'){
                $section=sanitize_text_field(wp_unslash($_POST['section']??''));
                $name=substr(trim(sanitize_text_field(wp_unslash($_POST['name']??''))),0,255);
                if(!$site||!$name||!$section||!in_array($section,$siteSections($site),true))wp_send_json_error(['message'=>'Select a valid Site, Section and Task name']);
            }else{
                $taskId=(int)($_POST['task_id']??0);
                if(!$taskId||!$wpdb->get_var($wpdb->prepare("SELECT id FROM $tasks WHERE id=%d",$taskId)))wp_send_json_error(['message'=>'Task not found']);
            }
            $wpdb->query('START TRANSACTION');
            if($type==='task_with_subtasks'){
                $ok=$wpdb->insert($tasks,['site_id'=>$site,'section'=>$section,'task_name'=>$name,'created_at'=>current_time('mysql')],['%d','%s','%s','%s']);
                if($ok===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to create Task']);}
                $taskId=(int)$wpdb->insert_id;
            }
            foreach($names as $subName){
                $ok=$wpdb->insert($subs,['task_id'=>$taskId,'subtask_name'=>$subName,'completion'=>0,'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['%d','%s','%f','%s','%d']);
                if($ok===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to save all Sub-tasks']);}
            }
            $wpdb->query('COMMIT');
            wp_send_json_success(['message'=>'Saved','task_id'=>$taskId,'subtask_count'=>count($names)]);
        }
        if($type==='task_with_subtask'){
            $section=sanitize_text_field(wp_unslash($_POST['section']??''));
            $name=substr(sanitize_text_field(wp_unslash($_POST['name']??'')),0,255);
            $subname=substr(sanitize_text_field(wp_unslash($_POST['subtask_name']??'')),0,255);
            $raw=$_POST['completion']??0;
            if(!$site||!$name||!$subname||!$section||!in_array($section,$siteSections($site),true)||!is_numeric($raw)||(float)$raw<0||(float)$raw>100)wp_send_json_error(['message'=>'Enter a valid Section, Task, Sub-task and Progress']);
            $progress=round((float)$raw,2);
            $wpdb->query('START TRANSACTION');
            $ok=$wpdb->insert($tasks,['site_id'=>$site,'section'=>$section,'task_name'=>$name,'created_at'=>current_time('mysql')],['%d','%s','%s','%s']);
            $taskId=(int)$wpdb->insert_id;
            if($ok!==false)$ok=$wpdb->insert($subs,['task_id'=>$taskId,'subtask_name'=>$subname,'completion'=>$progress,'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['%d','%s','%f','%s','%d']);
            if($ok===false){$wpdb->query('ROLLBACK');wp_send_json_error(['message'=>'Unable to save Task and Sub-task']);}
            $subId=(int)$wpdb->insert_id;
            if($progress>0)$wpdb->insert($this->prefix.'project_progress_history',['subtask_id'=>$subId,'old_progress'=>0,'new_progress'=>$progress,'changed_by'=>get_current_user_id(),'changed_at'=>current_time('mysql')]);
            $wpdb->query('COMMIT');
            wp_send_json_success(['message'=>'Saved','task_id'=>$taskId]);
        }
        if($type==='task'){
            $section=sanitize_text_field(wp_unslash($_POST['section']??''));
            $name=substr(sanitize_text_field(wp_unslash($_POST['name']??'')),0,255);
            if(!$site||!$name||!$section||!in_array($section,$siteSections($site),true))wp_send_json_error(['message'=>'Select a valid Site, assigned Section and Task name']);
            if($wpdb->insert($tasks,['site_id'=>$site,'section'=>$section,'task_name'=>$name,'created_at'=>current_time('mysql')],['%d','%s','%s','%s'])===false)wp_send_json_error(['message'=>'Unable to create Task']);
        } elseif($type==='subtask'){
            $task=(int)($_POST['task_id']??0);
            $name=substr(sanitize_text_field(wp_unslash($_POST['name']??'')),0,255);
            if(!$name||!$wpdb->get_var($wpdb->prepare("SELECT id FROM $tasks WHERE id=%d",$task)))wp_send_json_error(['message'=>'Select a Task and enter Sub-task name']);
            $raw=$_POST['completion']??0;
            if(!is_numeric($raw)||(float)$raw<0||(float)$raw>100)wp_send_json_error(['message'=>'Completion must be between 0 and 100']);
            $progress=round((float)$raw,2);
            if($wpdb->insert($subs,['task_id'=>$task,'subtask_name'=>$name,'completion'=>$progress,'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['%d','%s','%f','%s','%d'])===false)wp_send_json_error(['message'=>'Unable to create Sub-task']);
            if($progress>0)$wpdb->insert($this->prefix.'project_progress_history',['subtask_id'=>(int)$wpdb->insert_id,'old_progress'=>0,'new_progress'=>$progress,'changed_by'=>get_current_user_id(),'changed_at'=>current_time('mysql')]);
        } elseif($type==='progress'){
            $raw=$_POST['completion']??'';
            if(!is_numeric($raw)||(float)$raw<0||(float)$raw>100)wp_send_json_error(['message'=>'Completion must be between 0 and 100']);
            $old=$wpdb->get_var($wpdb->prepare("SELECT completion FROM $subs WHERE id=%d",$id));
            if($old===null)wp_send_json_error(['message'=>'Sub-task not found']);
            $new=round((float)$raw,2);
            if($wpdb->update($subs,['completion'=>$new,'updated_at'=>current_time('mysql'),'updated_by'=>get_current_user_id()],['id'=>$id],['%f','%s','%d'],['%d'])===false)wp_send_json_error(['message'=>'Unable to update Progress']);
            if((float)$old!==$new)$wpdb->insert($this->prefix.'project_progress_history',['subtask_id'=>$id,'old_progress'=>$old,'new_progress'=>$new,'changed_by'=>get_current_user_id(),'changed_at'=>current_time('mysql')]);
        } else wp_send_json_error(['message'=>'Invalid operation']);
        wp_send_json_success(['message'=>'Saved']);
    }

}

BSM_Site_Management::instance();

