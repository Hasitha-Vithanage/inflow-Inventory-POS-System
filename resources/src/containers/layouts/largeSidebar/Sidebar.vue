<template>
  <div
    class="side-content-wrap"
    @mouseenter="isMenuOver = true"
    @mouseleave="isMenuOver = false"
    @touchstart="isMenuOver = true"
  >
    <vue-perfect-scrollbar
      :settings="{ suppressScrollX: true, wheelPropagation: false }"
      :class="{ open: getSideBarToggleProperties.isSideNavOpen }"
      ref="myData"
      class="sidebar-left rtl-ps-none ps scroll"
    >
      <div>
        <ul class="navigation-left">
          <li
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'dashboard' }"
            class="nav-item"
            data-item="dashboard"
          >
            <router-link tag="a" class="nav-item-hold" to="/app/dashboard">
              <layout-dashboard class="nav-icon" size="20"></layout-dashboard>
              <span class="nav-text">{{ $t("dashboard") }}</span>
            </router-link>
          </li>

          

          <!-- Store (parent) -->
         <li
            v-show="currentUserPermissions && (
                      currentUserPermissions.includes('Store_settings_view') ||
                      currentUserPermissions.includes('Orders_view') ||
                      currentUserPermissions.includes('Collections_view') ||
                      currentUserPermissions.includes('Banners_view') ||
                      currentUserPermissions.includes('Subscribers_view') ||
                      currentUserPermissions.includes('Messages_view')
                    )"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'Store' }"
            class="nav-item"
            data-item="Store"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <shopping-bag class="nav-icon" size="20"></shopping-bag>
              <span class="nav-text">{{$t('Store')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('Customers_view')
                        ||currentUserPermissions.includes('Suppliers_view')
                        || currentUserPermissions.includes('customers_import')
                        || currentUserPermissions.includes('Suppliers_import')
                        || currentUserPermissions.includes('Suppliers_import'))"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'People' }"
            class="nav-item"
            data-item="People"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <users class="nav-icon" size="20"></users>
              <span class="nav-text">{{$t('People')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('users_view')
                        || currentUserPermissions.includes('permissions_view'))"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'User_Management' }"
            class="nav-item"
            data-item="User_Management"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <shield-check class="nav-icon" size="20"></shield-check>
              <span class="nav-text">{{$t('User_Management')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

        
          <li
            v-show="currentUserPermissions 
            && (currentUserPermissions.includes('products_add')
            || currentUserPermissions.includes('products_view') 
            || currentUserPermissions.includes('product_import') 
            || currentUserPermissions.includes('opening_stock_import') 
            || currentUserPermissions.includes('barcode_view')
             || currentUserPermissions.includes('brand') 
             || currentUserPermissions.includes('unit')  
             || currentUserPermissions.includes('count_stock')  
             || currentUserPermissions.includes('category')
             || currentUserPermissions.includes('subcategory'))"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'products' }"
            data-item="products"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <package class="nav-icon" size="20"></package>
              <span class="nav-text">{{$t('Products')}}</span>
            </a>
            <div class="triangle"></div>
          </li>
          <li
            v-show="currentUserPermissions 
              && (currentUserPermissions.includes('adjustment_view')
              || currentUserPermissions.includes('adjustment_add'))"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'adjustments' }"
            data-item="adjustments"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <file-edit class="nav-icon" size="20"></file-edit>
              <span class="nav-text">{{$t('StockAdjustement')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('Purchases_view') 
                        || currentUserPermissions.includes('Purchases_add'))"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'purchases' }"
            data-item="purchases"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <receipt class="nav-icon" size="20"></receipt>
              <span class="nav-text">{{$t('Purchases')}}</span>
            </a>
            <div class="triangle"></div>
          </li>
          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('Sales_view') 
                        || currentUserPermissions.includes('Sales_add')
                        || currentUserPermissions.includes('Pos_view')
                        || currentUserPermissions.includes('customer_display_screen_setup')
                        || currentUserPermissions.includes('shipment'))"
            class="nav-item"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'sales' }"
            data-item="sales"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <shopping-cart class="nav-icon" size="20"></shopping-cart>
              <span class="nav-text">{{$t('Sales')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

            <li
            v-if="currentUserPermissions && currentUserPermissions.includes('Sale_Returns_view')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'sale_return' }"
            class="nav-item"
            data-item="sale_return"
          >

           <router-link tag="a" class="nav-item-hold" to="/app/sale_return/list">
              <arrow-right-circle class="nav-icon" size="20"></arrow-right-circle>
              <span class="nav-text">{{ $t("SalesReturn") }}</span>
            </router-link>
          </li>

          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('Quotations_view')
                      || currentUserPermissions.includes('Quotations_add'))"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'quotations' }"
            data-item="quotations"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <shopping-basket class="nav-icon" size="20"></shopping-basket>
              <span class="nav-text">{{$t('Quotations')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchase_Returns_view')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'purchase_return' }"
            class="nav-item"
            data-item="purchase_return"
          >
          <router-link tag="a" class="nav-item-hold" to="/app/purchase_return/list">
              <arrow-left-circle class="nav-icon" size="20"></arrow-left-circle>
              <span class="nav-text">{{ $t("PurchasesReturn") }}</span>
            </router-link>
          </li>

           <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('transfer_view')
                     || currentUserPermissions.includes('transfer_add'))"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'transfers' }"
            data-item="transfers"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <arrow-right-left class="nav-icon" size="20"></arrow-right-left>
              <span class="nav-text">{{$t('StockTransfers')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions 
              && (currentUserPermissions.includes('damage_view'))"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'damages' }"
            data-item="damages"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <trash2 class="nav-icon" size="20"></trash2>
              <span class="nav-text">{{ $t('Damages') }}</span>
            </a>
            <div class="triangle"></div>
          </li>

            <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('company')
                     || currentUserPermissions.includes('department')
                     || currentUserPermissions.includes('designation')
                     || currentUserPermissions.includes('office_shift')
                     || currentUserPermissions.includes('view_employee')
                     || currentUserPermissions.includes('attendance')
                     || currentUserPermissions.includes('leave')
                     || currentUserPermissions.includes('holiday')
                     || currentUserPermissions.includes('payroll')
                     )"
                     
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'hrm' }"
            class="nav-item"
            data-item="hrm"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <users class="nav-icon" size="20"></users>
              <span class="nav-text">{{$t('hrm')}}</span>
            </a>
            <div class="triangle"></div>
          </li>
          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('expense_view')
              || currentUserPermissions.includes('expense_add')
              || currentUserPermissions.includes('deposit_view')
              || currentUserPermissions.includes('deposit_add')
              || currentUserPermissions.includes('account')
              || currentUserPermissions.includes('transfer_money')
              || currentUserPermissions.includes('accounting_dashboard')
              || currentUserPermissions.includes('chart_of_accounts')
              || currentUserPermissions.includes('journal_entries')
              || currentUserPermissions.includes('trial_balance')
              || currentUserPermissions.includes('accounting_profit_loss')
              || currentUserPermissions.includes('balance_sheet')
              || currentUserPermissions.includes('accounting_tax_report')
              )"
            @mouseenter="toggleSubMenu"
            class="nav-item"
            :class="{ active: selectedParentMenu == 'accounting' }"
            data-item="accounting"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <wallet class="nav-icon" size="20"></wallet>
              <span class="nav-text">{{$t('Accounting')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          

            <li
            v-if="currentUserPermissions && currentUserPermissions.includes('subscription_product')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'subscription_product' }"
            class="nav-item"
            data-item="subscription_product"
          >

           <router-link tag="a" class="nav-item-hold" to="/app/subscription_product/list">
              <dollar-sign class="nav-icon" size="20"></dollar-sign>
              <span class="nav-text">{{$t('Subscription_Product')}}</span>
            </router-link>
          </li>


          <li
            v-show="currentUserPermissions && currentUserPermissions.includes('service_jobs')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'service' }"
            class="nav-item"
            data-item="service"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <wrench class="nav-icon" size="20"></wrench>
              <span class="nav-text">{{$t('Service_Maintenance')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions && currentUserPermissions.includes('assets')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'assets' }"
            class="nav-item"
            data-item="assets"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <settings class="nav-icon" size="20"></settings>
              <span class="nav-text">{{$t('Assets')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions && currentUserPermissions.includes('projects')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'projects' }"
            class="nav-item"
            data-item="projects"
          >
            <router-link tag="a" class="nav-item-hold" to="/app/projects">
              <folder class="nav-icon" size="20"></folder>
              <span class="nav-text">{{$t('Projects')}}</span>
            </router-link>
          </li>

          <li
            v-show="currentUserPermissions && currentUserPermissions.includes('tasks')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'tasks' }"
            class="nav-item"
            data-item="tasks"
          >
            <router-link tag="a" class="nav-item-hold" to="/app/tasks">
              <check-square class="nav-icon" size="20"></check-square>
              <span class="nav-text">{{$t('Tasks')}}</span>
            </router-link>
          </li>

          <!-- Bookings (simple) -->
          <li
            v-show="currentUserPermissions && currentUserPermissions.includes('bookings')"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'bookings' }"
            class="nav-item"
            data-item="bookings"
          >
            <router-link tag="a" class="nav-item-hold" to="/app/bookings">
              <calendar class="nav-icon" size="20"></calendar>
              <span class="nav-text">{{$t('Bookings')}}</span>
            </router-link>
          </li>

          <li
            v-show="currentUserPermissions && (currentUserPermissions.includes('setting_system')
                        || currentUserPermissions.includes('update_settings')
                        || currentUserPermissions.includes('sms_settings')
                        || currentUserPermissions.includes('quickbooks_settings')
                        || currentUserPermissions.includes('notification_template')
                        || currentUserPermissions.includes('pos_settings')
                        || currentUserPermissions.includes('appearance_settings')
                        || currentUserPermissions.includes('translations_settings')
                        || currentUserPermissions.includes('module_settings')
                        || currentUserPermissions.includes('woocommerce_settings')
                        || currentUserPermissions.includes('payment_gateway')
                        || currentUserPermissions.includes('mail_settings')
                        || currentUserPermissions.includes('warehouse')
                        || currentUserPermissions.includes('backup')
                        || currentUserPermissions.includes('payment_methods')
                        || currentUserPermissions.includes('currency')
                        || currentUserPermissions.includes('login_device_management'))"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'settings' }"
            class="nav-item"
            data-item="settings"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <settings2 class="nav-icon" size="20"></settings2>
              <span class="nav-text">{{$t('Settings')}}</span>
            </a>
            <div class="triangle"></div>
          </li>

          <li
            v-show="currentUserPermissions && 
                     (currentUserPermissions.includes('Reports_payments_Sales') 
                     || currentUserPermissions.includes('Reports_payments_Purchases')
                     || currentUserPermissions.includes('Reports_payments_Sale_Returns')
                     || currentUserPermissions.includes('Reports_payments_purchase_Return')
                     || currentUserPermissions.includes('Warehouse_report')
                     || currentUserPermissions.includes('Reports_profit')
                     || currentUserPermissions.includes('inventory_valuation')
                     || currentUserPermissions.includes('expenses_report')
                     || currentUserPermissions.includes('deposits_report')
                     || currentUserPermissions.includes('Reports_purchase') 
                     || currentUserPermissions.includes('Reports_quantity_alerts')
                     || currentUserPermissions.includes('Reports_sales') 
                     || currentUserPermissions.includes('product_sales_report')
                     || currentUserPermissions.includes('product_purchases_report')
                     || currentUserPermissions.includes('Reports_suppliers')
                     || currentUserPermissions.includes('Top_Suppliers_Report')
                     || currentUserPermissions.includes('Reports_customers')
                     || currentUserPermissions.includes('Top_products')
                     || currentUserPermissions.includes('inactive_customers_report')
                     || currentUserPermissions.includes('Top_customers')
                     || currentUserPermissions.includes('report_device_management')
                     || currentUserPermissions.includes('users_report')
                     || currentUserPermissions.includes('product_report')
                      || currentUserPermissions.includes('zeroSalesProducts')
                      || currentUserPermissions.includes('Dead_Stock_Report')
                       || currentUserPermissions.includes('Stock_Aging_Report')
                       || currentUserPermissions.includes('Stock_Transfer_Report')
                       || currentUserPermissions.includes('discount_summary_report')
                       || currentUserPermissions.includes('Stock_Adjustment_Report')
                      || currentUserPermissions.includes('customer_loyalty_points_report')
                      || currentUserPermissions.includes('tax_summary_report')
                      || currentUserPermissions.includes('draft_invoices_report')
                      || currentUserPermissions.includes('report_transactions')
                      || currentUserPermissions.includes('cash_flow_report')
                      || currentUserPermissions.includes('report_attendance_summary')
                       || currentUserPermissions.includes('seller_report')
                      || currentUserPermissions.includes('report_sales_by_category')
                       || currentUserPermissions.includes('report_sales_by_brand')
                      || currentUserPermissions.includes('report_error_logs')
                      || currentUserPermissions.includes('cash_register_report')
                     || currentUserPermissions.includes('stock_report')
                     || currentUserPermissions.includes('negative_stock_report')
                     || currentUserPermissions.includes('return_ratio_report')
                     || currentUserPermissions.includes('service_jobs')
                     || currentUserPermissions.includes('service_jobs_report')
                     || currentUserPermissions.includes('checklist_completion_report')
                     || currentUserPermissions.includes('customer_maintenance_history_report'))"
            @mouseenter="toggleSubMenu"
            :class="{ active: selectedParentMenu == 'reports' }"
            class="nav-item"
            data-item="reports"
            :data-submenu="true"
          >
            <a class="nav-item-hold" href="#">
              <line-chart class="nav-icon" size="20"></line-chart>
              <span class="nav-text">{{$t('Reports')}}</span>
            </a>
            <div class="triangle"></div>
          </li>    
                
        </ul>
      </div>
    </vue-perfect-scrollbar>

    <vue-perfect-scrollbar
      :class="{ open: getSideBarToggleProperties.isSecondarySideNavOpen }"
      :settings="{ suppressScrollX: true, wheelPropagation: false }"
      class="sidebar-left-secondary ps rtl-ps-none"
    >
      <div ref="sidebarChild">


        <!-- Store (children) -->
       <ul
        class="childNav d-none"
        data-parent="Store"
        :class="{ 'd-block': selectedParentMenu == 'Store' }"
      >
        <!-- Visit Online Store (external link) -->
        <li class="nav-item">
          <a class="nav-item-hold" href="/online_store" target="_blank">
            <store class="nav-icon" size="18"></store>
            <span class="item-name">{{ $t('Visit_Online_Store') }}</span>
          </a>
        </li>

        <!-- Settings -->
        <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Store_settings_view')"
        >
          <router-link tag="a" class="nav-item-hold" to="/app/Store/Settings">
            <settings class="nav-icon" size="18"></settings>
            <span class="item-name">{{ $t('Settings') }}</span>
          </router-link>
        </li>

        <!-- Orders -->
        <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Orders_view')"
        >
          <router-link tag="a" class="nav-item-hold" to="/app/Store/Orders">
            <receipt class="nav-icon" size="18"></receipt>
            <span class="item-name">{{ $t('Online_Orders') }}</span>
          </router-link>
        </li>

        <!-- Collections -->
        <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Collections_view')"
        >
          <router-link tag="a" class="nav-item-hold" to="/app/Store/Collections">
            <check-square class="nav-icon" size="18"></check-square>
            <span class="item-name">{{ $t('Collections') }}</span>
          </router-link>
        </li>

        <!-- Banners -->
        <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Banners_view')"
        >
          <router-link tag="a" class="nav-item-hold" to="/app/Store/Banners">
            <wallet class="nav-icon" size="18"></wallet>
            <span class="item-name">{{ $t('Banners') }}</span>
          </router-link>
        </li>

        <!-- Subscribers -->
        <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Subscribers_view')"
        >
          <router-link tag="a" class="nav-item-hold" to="/app/Store/Subscribers">
            <users class="nav-icon" size="18"></users>
            <span class="item-name">{{ $t('Subscribers') }}</span>
          </router-link>
        </li>

        <!-- Messages -->
        <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Messages_view')"
        >
          <router-link tag="a" class="nav-item-hold" to="/app/Store/Messages">
            <message-square class="nav-icon" size="18"></message-square>
            <span class="item-name">{{ $t('Messages') }}</span>
          </router-link>
        </li>
      </ul>


        <ul
          class="childNav d-none"
          data-parent="products"
          :class="{ 'd-block': selectedParentMenu == 'products' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('products_add')"
          >
            <router-link tag="a" class to="/app/products/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('AddProduct')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('products_view')"
          >
            <router-link tag="a" class to="/app/products/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('productsList')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('product_import')"
          >
            <router-link tag="a" class to="/app/products/import">
              <download class="nav-icon" size="18"></download>
              <span class="item-name">{{ $t('import_products') }}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('opening_stock_import')"
          >
            <router-link tag="a" class to="/app/products/opening_stock_import">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('Opening_Stock')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('barcode_view')"
          >
            <router-link tag="a" class to="/app/products/barcode">
              <barcode class="nav-icon" size="18"></barcode>
              <span class="item-name">{{$t('Printbarcode')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('count_stock')"
          >
            <router-link tag="a" class to="/app/products/count_stock">
              <clipboard-check class="nav-icon" size="18"></clipboard-check>
              <span class="item-name">{{$t('CountStock')}}</span>
            </router-link>
          </li>
           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('category')"
          >
            <router-link tag="a" class to="/app/products/Categories">
              <layers class="nav-icon" size="18"></layers>
              <span class="item-name">{{$t('Categories')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('subcategory')"
          >
            <router-link tag="a" class to="/app/products/SubCategories">
              <package class="nav-icon" size="18"></package>
              <span class="item-name">{{$t('SubCategory')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('brand')"
          >
            <router-link tag="a" class to="/app/products/Brands">
              <bookmark class="nav-icon" size="18"></bookmark>
              <span class="item-name">{{$t('Brand')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('unit')"
          >
            <router-link tag="a" class to="/app/products/Units">
              <hash class="nav-icon" size="18"></hash>
              <span class="item-name">{{$t('Units')}}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="accounting"
          :class="{ 'd-block': selectedParentMenu == 'accounting' }"
        >
          <!-- NEW FEATURE - SAFE ADDITION: Advanced Accounting under Accounting -->
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('accounting_dashboard')">
            <router-link tag="a" class to="/app/accounting-v2/dashboard">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{ $t("dashboard") }}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('chart_of_accounts')">
            <router-link tag="a" class to="/app/accounting-v2/chart-of-accounts">
              <database class="nav-icon" size="18"></database>
              <span class="item-name">{{ $t('Chart_of_Accounts_Title') }}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('journal_entries')">
            <router-link tag="a" class to="/app/accounting-v2/journal-entries">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{ $t('Journal_Entries_Title') }}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('trial_balance')">
            <router-link tag="a" class to="/app/accounting-v2/reports/trial-balance">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{ $t('Trial_Balance_Title') }}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('accounting_profit_loss')">
            <router-link tag="a" class to="/app/accounting-v2/reports/profit-and-loss">
              <banknote class="nav-icon" size="18"></banknote>
              <span class="item-name">{{ $t('Profit_Loss_Title') }}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('balance_sheet')">
            <router-link tag="a" class to="/app/accounting-v2/reports/balance-sheet">
              <pie-chart class="nav-icon" size="18"></pie-chart>
              <span class="item-name">{{ $t('Balance_Sheet_Title') }}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('accounting_tax_report')">
            <router-link tag="a" class to="/app/accounting-v2/reports/tax-report">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{ $t('Tax_Summary_Report') }}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="adjustments"
          :class="{ 'd-block': selectedParentMenu == 'adjustments' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_add')"
          >
            <router-link tag="a" class to="/app/adjustments/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('CreateAdjustment')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('adjustment_view')"
          >
            <router-link tag="a" class to="/app/adjustments/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('ListAdjustments')}}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="transfers"
          :class="{ 'd-block': selectedParentMenu == 'transfers' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('transfer_add')"
          >
            <router-link tag="a" class to="/app/transfers/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('CreateTransfer')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('transfer_view')"
          >
            <router-link tag="a" class to="/app/transfers/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('ListTransfers')}}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="damages"
          :class="{ 'd-block': selectedParentMenu == 'damages' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('damage_view')"
          >
            <router-link tag="a" class to="/app/damages/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{ $t('Create_Damage') }}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('damage_view')"
          >
            <router-link tag="a" class to="/app/damages/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{ $t('Damages') }}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="accounting"
          :class="{ 'd-block': selectedParentMenu == 'accounting' }"
        >
          

        <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('account')"
          >
            <router-link tag="a" class to="/app/accounts">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('List_accounts')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('transfer_money')"
          >
            <router-link tag="a" class to="/app/transfer_money">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Transfers_Money')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('expense_add')"
          >
            <router-link tag="a" class to="/app/expenses/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('Create_Expense')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('expense_view')"
          >
            <router-link tag="a" class to="/app/expenses/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('ListExpenses')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('deposit_add')"
          >
            <router-link tag="a" class to="/app/deposits/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('Create_deposit')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('deposit_view')"
          >
            <router-link tag="a" class to="/app/deposits/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('List_Deposit')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('expense_view')"
          >
            <router-link tag="a" class to="/app/expenses/category">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Expense_Category')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('deposit_view')"
          >
            <router-link tag="a" class to="/app/deposits/category">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Deposit_Category')}}</span>
            </router-link>
          </li>

          
        </ul>

        
        <ul
          class="childNav d-none"
          data-parent="purchases"
          :class="{ 'd-block': selectedParentMenu == 'purchases' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchases_add')"
          >
            <router-link tag="a" class to="/app/purchases/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('AddPurchase')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchases_view')"
          >
            <router-link tag="a" class to="/app/purchases/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('ListPurchases')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Purchases_add')"
          >
            <router-link tag="a" class to="/app/purchases/import_purchases">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('Import_Purchases')}}</span>
            </router-link>
          </li>
          
        </ul>

        <ul
          class="childNav d-none"
          data-parent="service"
          :class="{ 'd-block': selectedParentMenu == 'service' }"
        >
          <li class="nav-item">
            <router-link tag="a" class to="/app/service/jobs">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Service_Jobs')}}</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link tag="a" class to="/app/service/technicians">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Service_Technicians')}}</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link tag="a" class to="/app/service/checklist-categories">
              <folder class="nav-icon" size="18"></folder>
              <span class="item-name">{{$t('Checklist_Categories')}}</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link tag="a" class to="/app/service/checklists">
              <check-circle class="nav-icon" size="18"></check-circle>
              <span class="item-name">{{$t('Checklist_Items')}}</span>
            </router-link>
          </li>
          <li class="nav-item">
            <router-link tag="a" class to="/app/service/history">
              <calendar class="nav-icon" size="18"></calendar>
              <span class="item-name">{{$t('Maintenance_History')}}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="assets"
          :class="{ 'd-block': selectedParentMenu == 'assets' }"
        >
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('assets')">
            <router-link tag="a" class to="/app/assets/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('Add_Asset')}}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('assets')">
            <router-link tag="a" class to="/app/assets/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Assets_List')}}</span>
            </router-link>
          </li>
          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('assets')">
            <router-link tag="a" class to="/app/assets/category">
              <folder class="nav-icon" size="18"></folder>
              <span class="item-name">{{$t('Asset_Category')}}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="sales"
          :class="{ 'd-block': selectedParentMenu == 'sales' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Sales_add')"
          >
            <router-link tag="a" class to="/app/sales/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('AddSale')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Sales_view')"
          >
            <router-link tag="a" class to="/app/sales/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('ListSales')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Pos_view')"
          >
            <router-link tag="a" class to="/app/pos">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">POS</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('customer_display_screen_setup')"
          >
            <router-link tag="a" class to="/app/customer-display/setup">
              <barcode class="nav-icon" size="18"></barcode>
              <span class="item-name">{{$t('Customer_Screen')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('shipment')"
          >
            <router-link tag="a" class to="/app/sales/shipment">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('Shipments')}}</span>
            </router-link>
          </li>
        </ul>

        <ul
          class="childNav d-none"
          data-parent="quotations"
          :class="{ 'd-block': selectedParentMenu == 'quotations' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Quotations_add')"
          >
            <router-link tag="a" class to="/app/quotations/store">
              <file-plus class="nav-icon" size="18"></file-plus>
              <span class="item-name">{{$t('AddQuote')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Quotations_view')"
          >
            <router-link tag="a" class to="/app/quotations/list">
              <files class="nav-icon" size="18"></files>
              <span class="item-name">{{$t('ListQuotations')}}</span>
            </router-link>
          </li>
        </ul>

      

       
      <!-- hrm -->
        <ul
          class="childNav d-none"
          data-parent="hrm"
          :class="{ 'd-block': selectedParentMenu == 'hrm' }"
        >
         <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('company')"
          >
            <router-link tag="a" class to="/app/hrm/company">
              <briefcase class="nav-icon" size="18"></briefcase>
              <span class="item-name">{{$t('Company')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('department')"
          >
            <router-link tag="a" class to="/app/hrm/departments">
              <store class="nav-icon" size="18"></store>
              <span class="item-name">{{$t('Departments')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('designation')"
          >
            <router-link tag="a" class to="/app/hrm/designations">
              <aperture class="nav-icon" size="18"></aperture>
              <span class="item-name">{{$t('Designations')}}</span>
            </router-link>
          </li>
           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('office_shift')"
          >
            <router-link tag="a" class to="/app/hrm/office_Shift">
              <clock class="nav-icon" size="18"></clock>
              <span class="item-name">{{$t('Office_Shift')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('view_employee')"
          >
            <router-link tag="a" class to="/app/hrm/employees">
              <hard-hat class="nav-icon" size="18"></hard-hat>
              <span class="item-name">{{$t('Employees')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('attendance')"
          >
            <router-link tag="a" class to="/app/hrm/attendance">
              <clock class="nav-icon" size="18"></clock>
              <span class="item-name">{{$t('Attendance')}}</span>
            </router-link>
          </li>
           <li
            v-if="currentUserPermissions && (currentUserPermissions.includes('leave'))"

           @click.prevent="toggleSidebarDropdwon($event)"
            class="nav-item dropdown-sidemenu"
          >

            <a href="#">
              <calendar-days class="nav-icon" size="18"></calendar-days>
              <span class="item-name">{{$t('Leave_request')}}</span>
              <chevron-down class="dd-arrow" size="14"></chevron-down>
            </a>
            <ul class="submenu">
              <li
              >
                <router-link tag="a" class to="/app/hrm/leaves/list">
                  <contact class="nav-icon" size="18"></contact>
                  <span class="item-name">{{$t('Leave_request')}}</span>
                </router-link>
              </li>
              <li
              >
                <router-link tag="a" class to="/app/hrm/leaves/type">
                  <contact class="nav-icon" size="18"></contact>
                  <span class="item-name">{{$t('Leave_type')}}</span>
                </router-link>
              </li>
              
            </ul>
          </li>
           
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('holiday')"
          >
            <router-link tag="a" class to="/app/hrm/holidays">
              <bell class="nav-icon" size="18"></bell>
              <span class="item-name">{{$t('Holidays')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('payroll')"
          >
            <router-link tag="a" class to="/app/hrm/payrolls">
              <circle-dollar-sign class="nav-icon" size="18"></circle-dollar-sign>
              <span class="item-name">{{$t('Payroll')}}</span>
            </router-link>
          </li>

        </ul>


         <!-- People -->
        <ul
          class="childNav d-none"
          data-parent="People"
          :class="{ 'd-block': selectedParentMenu == 'People' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Customers_view')"
          >
            <router-link tag="a" class to="/app/People/Customers">
              <users class="nav-icon" size="18"></users>
              <span class="item-name">{{$t('Customers')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Customers_add')"
          >
            <router-link tag="a" class to="/app/People/Customers/create">
              <plus class="nav-icon" size="18"></plus>
              <span class="item-name">{{$t('Add')}} {{$t('Customer')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('customers_import')"
          >
            <router-link tag="a" class to="/app/People/Customers_import">
              <download class="nav-icon" size="18"></download>
              <span class="item-name">{{$t('Import_Customers')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Customers_view')"
          >
            <router-link tag="a" class to="/app/People/Customers_without_ecommerce">
              <users class="nav-icon" size="18"></users>
              <span class="item-name">{{$t('Customers_without_Login')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Customers_view')"
          >
            <router-link tag="a" class to="/app/People/Customers_ecommerce">
              <users class="nav-icon" size="18"></users>
              <span class="item-name">{{$t('Customers_with_Login')}}</span>
            </router-link>
          </li>


          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_view')"
          >
            <router-link tag="a" class to="/app/People/Suppliers">
              <users class="nav-icon" size="18"></users>
              <span class="item-name">{{$t('Suppliers')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_add')"
          >
            <router-link tag="a" class to="/app/People/Suppliers/create">
              <plus class="nav-icon" size="18"></plus>
              <span class="item-name">{{$t('Add')}} {{$t('Supplier')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Suppliers_import')"
          >
            <router-link tag="a" class to="/app/People/Suppliers_import">
              <download class="nav-icon" size="18"></download>
              <span class="item-name">{{$t('Import_Suppliers')}}</span>
            </router-link>
          </li>

        </ul>

        <ul
          class="childNav d-none"
          data-parent="User_Management"
          :class="{ 'd-block': selectedParentMenu == 'User_Management' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('users_view')"
          >
            <router-link tag="a" class to="/app/User_Management/Users">
              <users class="nav-icon" size="18"></users>
              <span class="item-name">{{$t('Users')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('permissions_view')"
          >
            <router-link tag="a" class to="/app/User_Management/permissions">
              <key class="nav-icon" size="18"></key>
              <span class="item-name">{{$t('GroupPermissions')}}</span>
            </router-link>
          </li>

        </ul>

        <ul
          class="childNav d-none"
          data-parent="settings"
          :class="{ 'd-block': selectedParentMenu == 'settings' }"
        >
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('setting_system')"
          >
            <router-link tag="a" class to="/app/settings/System_settings">
              <settings class="nav-icon" size="18"></settings>
              <span class="item-name">{{$t('SystemSettings')}}</span>
            </router-link>
          </li>

        

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('woocommerce_settings')"
          >
            <router-link tag="a" class :to="{ name: 'woocommerce_settings' }">
              <link2 class="nav-icon" size="18"></link2>
              <span class="item-name">{{$t('WooCommerce_Settings')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('quickbooks_settings')"
          >
            <router-link tag="a" class to="/app/settings/quickbooks_sync">
              <circle-dollar-sign class="nav-icon" size="18"></circle-dollar-sign>
              <span class="item-name">{{$t('Quickbooks_Sync')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('appearance_settings')"
          >
            <router-link tag="a" class to="/app/settings/appearance_settings">
              <settings2 class="nav-icon" size="18"></settings2>
              <span class="item-name">{{$t('Dynamic_Appearance')}} </span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('translations_settings')"
          >
            <router-link tag="a" class to="/app/settings/translations_settings">
              <settings2 class="nav-icon" size="18"></settings2>
              <span class="item-name">{{$t('Languages')}} </span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('payment_methods')"
          >
            <router-link tag="a" class to="/app/settings/payment_methods">
              <circle-dollar-sign class="nav-icon" size="18"></circle-dollar-sign>
              <span class="item-name">{{$t('Payment_Methods')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('sms_settings')"
          >
            <router-link tag="a" class to="/app/settings/sms_settings">
              <message-square class="nav-icon" size="18"></message-square>
              <span class="item-name">{{$t('sms_settings')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('notification_template')"
          >
            <router-link tag="a" class to="/app/settings/sms_templates">
              <message-square class="nav-icon" size="18"></message-square>
              <span class="item-name">{{$t('sms_templates')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('mail_settings')"
          >
            <router-link tag="a" class to="/app/settings/mail_settings">
              <mail class="nav-icon" size="18"></mail>
              <span class="item-name">{{$t('mail_settings')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('notification_template')"
          >
            <router-link tag="a" class to="/app/settings/email_templates">
              <mail class="nav-icon" size="18"></mail>
              <span class="item-name">{{$t('email_templates')}}</span>
            </router-link>
          </li>

          <!-- POS Settings (System Settings -> POS Settings tab) -->
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('pos_settings')"
          >
            <router-link tag="a" class to="/app/settings/pos_settings">
              <settings2 class="nav-icon" size="18"></settings2>
              <span class="item-name">{{$t('Pos_Settings')}}</span>
            </router-link>
          </li>

          <!-- POS Receipt page (dedicated POS receipt settings view) -->
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('pos_settings')"
          >
            <router-link tag="a" class to="/app/settings/pos_receipt">
              <calculator class="nav-icon" size="18"></calculator>
              <span class="item-name">{{$t('POS_Receipt')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('module_settings')"
          >
            <router-link tag="a" class to="/app/settings/module_settings">
              <settings2 class="nav-icon" size="18"></settings2>
              <span class="item-name">{{$t('module_settings')}}</span>
            </router-link>
          </li>

         

            <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('update_settings')"
          >
            <router-link tag="a" class to="/app/settings/update_settings">
              <upload class="nav-icon" size="18"></upload>
              <span class="item-name">{{$t('update_settings')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('payment_gateway')"
          >
            <router-link tag="a" class to="/app/settings/payment_gateway">
              <circle-dollar-sign class="nav-icon" size="18"></circle-dollar-sign>
              <span class="item-name">{{$t('Payment_Gateway')}}</span>
            </router-link>
          </li>

          

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('warehouse')"
          >
            <router-link tag="a" class to="/app/settings/Warehouses">
              <warehouse class="nav-icon" size="18"></warehouse>
              <span class="item-name">{{$t('Warehouses')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('currency')"
          >
            <router-link tag="a" class to="/app/settings/Currencies">
              <dollar-sign class="nav-icon" size="18"></dollar-sign>
              <span class="item-name">{{$t('Currencies')}}</span>
            </router-link>
          </li>
         
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('backup')"
          >
            <router-link tag="a" class to="/app/settings/Backup">
              <database class="nav-icon" size="18"></database>
              <span class="item-name">{{$t('Backup')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('login_device_management')"
          >
            <router-link tag="a" class to="/app/settings/login_devices">
              <lock class="nav-icon" size="18"></lock>
              <span class="item-name">{{$t('Login_Device_Management')}}</span>
            </router-link>
          </li>

        </ul>

        <ul
          class="childNav d-none"
          data-parent="reports"
          :class="{ 'd-block': selectedParentMenu == 'reports' }"
        >
          <li
            v-if="currentUserPermissions &&
             (currentUserPermissions.includes('Reports_payments_Purchases') 
           || currentUserPermissions.includes('Reports_payments_Sales')
           || currentUserPermissions.includes('Reports_payments_Sale_Returns')
           || currentUserPermissions.includes('Reports_payments_purchase_Return'))"
            @click.prevent="toggleSidebarDropdwon($event)"
            class="nav-item dropdown-sidemenu"
          >
            <a href="#">
              <credit-card class="nav-icon" size="18"></credit-card>
              <span class="item-name">{{$t('Payments')}}</span>
              <chevron-down class="dd-arrow" size="14"></chevron-down>
            </a>
            <ul class="submenu">
              <li
                v-if="currentUserPermissions && currentUserPermissions.includes('Reports_payments_Purchases')"
              >
                <router-link tag="a" class to="/app/reports/payments_purchase">
                  <contact class="nav-icon" size="18"></contact>
                  <span class="item-name">{{$t('Purchases')}}</span>
                </router-link>
              </li>
              <li
                v-if="currentUserPermissions && currentUserPermissions.includes('Reports_payments_Sales')"
              >
                <router-link tag="a" class to="/app/reports/payments_sale">
                  <contact class="nav-icon" size="18"></contact>
                  <span class="item-name">{{$t('Sales')}}</span>
                </router-link>
              </li>
              <li
                v-if="currentUserPermissions && currentUserPermissions.includes('Reports_payments_Sale_Returns')"
              >
                <router-link tag="a" class to="/app/reports/payments_sales_returns">
                  <contact class="nav-icon" size="18"></contact>
                  <span class="item-name">{{$t('SalesReturn')}}</span>
                </router-link>
              </li>
              <li
                v-if="currentUserPermissions && currentUserPermissions.includes('Reports_payments_purchase_Return')"
              >
                <router-link tag="a" class to="/app/reports/payments_purchases_returns">
                  <contact class="nav-icon" size="18"></contact>
                  <span class="item-name">{{$t('PurchasesReturn')}}</span>
                </router-link>
              </li>
            </ul>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('report_transactions')"
          >
            <router-link tag="a" class to="/app/reports/report_transactions">
              <dollar-sign class="nav-icon" size="18"></dollar-sign>
              <span class="item-name">{{$t('Report_Transactions')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('cash_flow_report')"
          >
            <router-link tag="a" class to="/app/reports/cash_flow_report">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{$t('Cash_Flow_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('seller_report')"
          >
            <router-link tag="a" class to="/app/reports/seller_report">
              <user class="nav-icon" size="18"></user>
              <span class="item-name">{{$t('Seller_report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('report_attendance_summary')"
          >
            <router-link tag="a" class :to="{ name: 'attendance_report' }">
              <clock class="nav-icon" size="18"></clock>
              <span class="item-name">{{$t('attendance_summary')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Reports_profit')"
          >
            <router-link tag="a" class to="/app/reports/profit_and_loss">
              <banknote class="nav-icon" size="18"></banknote>
              <span class="item-name">{{$t('ProfitandLoss')}}</span>
            </router-link>
          </li>
          
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('cash_register_report')"
          >
            <router-link tag="a" class :to="{ name: 'cash_register_report' }">
              <circle-dollar-sign class="nav-icon" size="18"></circle-dollar-sign>
              <span class="item-name">{{$t('Cash_Register_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('inventory_valuation')"
          >
            <router-link tag="a" class to="/app/reports/inventory_valuation_summary">
              <pie-chart class="nav-icon" size="18"></pie-chart>
              <span class="item-name">{{$t('Inventory_Valuation')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('expenses_report')"
          >
            <router-link tag="a" class to="/app/reports/expenses_report">
              <receipt class="nav-icon" size="18"></receipt>
              <span class="item-name">{{$t('Expense_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('deposits_report')"
          >
            <router-link tag="a" class to="/app/reports/deposits_report">
              <vault class="nav-icon" size="18"></vault>
              <span class="item-name">{{$t('Deposits_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Reports_quantity_alerts')"
          >
            <router-link tag="a" class to="/app/reports/quantity_alerts">
              <bell class="nav-icon" size="18"></bell>
              <span class="item-name">{{$t('ProductQuantityAlerts')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Warehouse_report')"
          >
            <router-link tag="a" class to="/app/reports/warehouse_report">
              <warehouse class="nav-icon" size="18"></warehouse>
              <span class="item-name">{{$t('Warehouse_report')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('stock_report')"
          >
            <router-link tag="a" class to="/app/reports/stock_report">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{$t('stock_report')}}</span>
            </router-link>
          </li>
          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('negative_stock_report')"
          >
            <router-link tag="a" class to="/app/reports/negative_stock_report">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{$t('Negative_Stock_Report')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('product_report')"
          >
            <router-link tag="a" class to="/app/reports/product_report">
              <barcode class="nav-icon" size="18"></barcode>
              <span class="item-name">{{$t('product_report')}}</span>
            </router-link>
          </li>

          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('zeroSalesProducts')">
            <router-link tag="a" class :to="{ name: 'zero_sales_products_report' }">
              <trash2 class="nav-icon" size="18"></trash2>
              <span class="item-name">{{$t('Zero_Sales_Products_Report')}}</span>
            </router-link>
          </li>

          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('Dead_Stock_Report')">
            <router-link tag="a" class :to="{ name: 'dead_stock_report' }">
              <trash2 class="nav-icon" size="18"></trash2>
              <span class="item-name">{{$t('Dead_Stock_Report')}}</span>
            </router-link>
          </li>

          <li
          class="nav-item"
          v-if="currentUserPermissions && currentUserPermissions.includes('Stock_Aging_Report')"
        >
          <router-link tag="a" class :to="{ name: 'stock_aging_report' }">
            <clock class="nav-icon" size="18"></clock>
            <span class="item-name">{{$t('Stock_Aging_Report')}}</span>
          </router-link>
        </li>

        <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('Stock_Transfer_Report')">
        <router-link tag="a" class :to="{ name: 'stock_transfer_report' }">
          <arrow-left class="nav-icon" size="18"></arrow-left>
          <span class="item-name">{{$t('Stock_Transfer_Report')}}</span>
        </router-link>
      </li>

      <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('Stock_Adjustment_Report')">
        <router-link tag="a" :to="{ name: 'stock_adjustment_report' }">
          <file-edit class="nav-icon" size="18"></file-edit>
          <span class="item-name">{{$t('Stock_Adjustment_Report')}}</span>
        </router-link>
      </li>


        <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('discount_summary_report')">
          <router-link tag="a" class :to="{ name: 'discount_summary_report' }">
            <receipt class="nav-icon" size="18"></receipt>
            <span class="item-name">{{$t('Discount_Summary_Report')}}</span>
          </router-link>
        </li>
      <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('customer_loyalty_points_report')">
        <router-link tag="a" class :to="{ name: 'customer_loyalty_points_report' }">
          <heart class="nav-icon" size="18"></heart>
          <span class="item-name">{{$t('Customer_Loyalty_Points_Report')}}</span>
        </router-link>
      </li>

        <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('tax_summary_report')">
          <router-link tag="a" class :to="{ name: 'tax_summary_report' }">
            <files class="nav-icon" size="18"></files>
            <span class="item-name">{{$t('Tax_Summary_Report')}}</span>
          </router-link>
        </li>



        <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('draft_invoices_report')">
          <router-link tag="a" class :to="{ name: 'draft_invoices_report' }">
            <receipt class="nav-icon" size="18"></receipt>
            <span class="item-name">{{$t('Draft_Invoices_Report')}}</span>
          </router-link>
        </li>


          

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('return_ratio_report')"
          >
            <router-link tag="a" class to="/app/reports/return_ratio_report">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{$t('Return_Ratio_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Reports_sales')"
          >
            <router-link tag="a" class to="/app/reports/sales_report">
              <layout-dashboard class="nav-icon" size="18"></layout-dashboard>
              <span class="item-name">{{$t('SalesReport')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('product_sales_report')"
          >
            <router-link tag="a" class to="/app/reports/product_sales_report">
              <line-chart class="nav-icon" size="18"></line-chart>
              <span class="item-name">{{$t('product_sales_report')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('report_sales_by_category')"
          >
            <router-link tag="a" class to="/app/reports/report_sales_by_category">
              <tag class="nav-icon" size="18"></tag>
              <span class="item-name">{{$t('Sales_by_Category')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('report_sales_by_brand')"
          >
            <router-link tag="a" class to="/app/reports/report_sales_by_brand">
              <store class="nav-icon" size="18"></store>
              <span class="item-name">{{$t('Sales_by_Brand')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Reports_purchase')"
          >
            <router-link tag="a" class to="/app/reports/purchase_report">
              <arrow-right-left class="nav-icon" size="18"></arrow-right-left>
              <span class="item-name">{{$t('PurchasesReport')}}</span>
            </router-link>
          </li>

            <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('product_purchases_report')"
          >
            <router-link tag="a" class to="/app/reports/product_purchases_report">
              <shopping-basket class="nav-icon" size="18"></shopping-basket>
              <span class="item-name">{{$t('Product_purchases_report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Reports_customers')"
          >
            <router-link tag="a" class to="/app/reports/customers_report">
              <user class="nav-icon" size="18"></user>
              <span class="item-name">{{$t('CustomersReport')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('inactive_customers_report')"
          >
            <router-link tag="a" class to="/app/reports/inactive_customers">
              <user-minus class="nav-icon" size="18"></user-minus>
              <span class="item-name">{{$t('Inactive_Customers_Report')}}</span>
            </router-link>
          </li>

          <li class="nav-item" v-if="currentUserPermissions && currentUserPermissions.includes('Top_Suppliers_Report')">
            <router-link tag="a" class :to="{ name: 'top_suppliers_report' }">
              <contact class="nav-icon" size="18"></contact>
              <span class="item-name">{{$t('Top_Suppliers_Report')}}</span>
            </router-link>
          </li>


          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Reports_suppliers')"
          >
            <router-link tag="a" class to="/app/reports/providers_report">
              <user class="nav-icon" size="18"></user>
              <span class="item-name">{{$t('SuppliersReport')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Top_products')"
          >
            <router-link tag="a" class to="/app/reports/top_selling_products">
              <trophy class="nav-icon" size="18"></trophy>
              <span class="item-name">{{$t('Top_Selling_Products')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('Top_customers')"
          >
            <router-link tag="a" class to="/app/reports/top_customers">
              <trophy class="nav-icon" size="18"></trophy>
              <span class="item-name">{{$t('Top_customers')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('users_report')"
          >
            <router-link tag="a" class to="/app/reports/users_report">
              <user class="nav-icon" size="18"></user>
              <span class="item-name">{{$t('Users_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('report_device_management')"
          >
            <router-link tag="a" class to="/app/reports/login_activity_report">
              <lock class="nav-icon" size="18"></lock>
              <span class="item-name">{{$t('Login_Activity_Report')}}</span>
            </router-link>
          </li>

           <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('report_error_logs')"
          >
            <router-link tag="a" class to="/app/reports/report_error_logs">
              <bug class="nav-icon" size="18"></bug>
              <span class="item-name">{{$t('Error_Logs')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('service_jobs_report')"
          >
            <router-link tag="a" class :to="{ name: 'service_jobs_report' }">
              <wrench class="nav-icon" size="18"></wrench>
              <span class="item-name">{{$t('Service_Jobs_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('checklist_completion_report')"
          >
            <router-link tag="a" class :to="{ name: 'checklist_completion_report' }">
              <check-square class="nav-icon" size="18"></check-square>
              <span class="item-name">{{$t('Checklist_Completion_Report')}}</span>
            </router-link>
          </li>

          <li
            class="nav-item"
            v-if="currentUserPermissions && currentUserPermissions.includes('customer_maintenance_history_report')"
          >
            <router-link tag="a" class :to="{ name: 'customer_maintenance_history_report' }">
              <calendar class="nav-icon" size="18"></calendar>
              <span class="item-name">{{$t('Customer_Maintenance_History_Report')}}</span>
            </router-link>
          </li>


          


        </ul>
      </div>
    </vue-perfect-scrollbar>
    <div
      @click="removeOverlay()"
      class="sidebar-overlay"
      :class="{ open: getSideBarToggleProperties.isSecondarySideNavOpen }"
    ></div>
  </div>
  <!--=============== Left side End ================-->
</template>

<script>
import Topnav from "./TopNav";
import { isMobile } from "mobile-device-detect";
import { mapGetters, mapActions } from "vuex";
import { 
  LayoutDashboard, ShoppingBag, Users, ShieldCheck, Package, 
  FileEdit, Receipt, ShoppingCart, ArrowRightCircle, ShoppingBasket, 
  ArrowLeftCircle, ArrowRightLeft, Trash2, Wallet, DollarSign, 
  Wrench, Settings, Folder, CheckSquare, Calendar, 
  Settings2, LineChart, Store, Files, Download, 
  Barcode, ClipboardCheck, Layers, Bookmark, Hash, 
  Database, Banknote, PieChart, FilePlus, CreditCard, 
  Heart, Tag, CheckCircle, UserMinus, Contact, 
  Trophy, Lock, Bug, Briefcase, Aperture, 
  Clock, CalendarDays, Bell, CircleDollarSign, ChevronDown, 
  ChevronRight, ArrowDown, MessageSquare, ArrowLeft, Key, Plus, 
  HardHat, Link2, Mail, Calculator, Upload, Warehouse, XCircle, Vault
} from "lucide-vue";

export default {
  components: {
    Topnav,
    LayoutDashboard, ShoppingBag, Users, ShieldCheck, Package, 
    FileEdit, Receipt, ShoppingCart, ArrowRightCircle, ShoppingBasket, 
    ArrowLeftCircle, ArrowRightLeft, Trash2, Wallet, DollarSign, 
    Wrench, Settings, Folder, CheckSquare, Calendar, 
    Settings2, LineChart, Store, Files, Download, 
    Barcode, ClipboardCheck, Layers, Bookmark, Hash, 
    Database, Banknote, PieChart, FilePlus, CreditCard, 
    Heart, Tag, CheckCircle, UserMinus, Contact, 
    Trophy, Lock, Bug, Briefcase, Aperture, 
    Clock, CalendarDays, Bell, CircleDollarSign, ChevronDown, 
    ChevronRight, ArrowDown, MessageSquare, ArrowLeft, Key, Plus, 
    HardHat, Link2, Mail, Calculator, Upload, Warehouse, XCircle, Vault
  },

  data() {
    return {
      isDisplay: true,
      isMenuOver: false,
      isStyle: true,
      selectedParentMenu: "",
      isMobile,
    };
  },
  mounted() {
    this.toggleSelectedParentMenu();
    window.addEventListener("resize", this.handleWindowResize);
    document.addEventListener("click", this.returnSelectedParentMenu);
    this.handleWindowResize();
  },

  beforeDestroy() {
    document.removeEventListener("click", this.returnSelectedParentMenu);
    window.removeEventListener("resize", this.handleWindowResize);
  },

  computed: {
    ...mapGetters(["getSideBarToggleProperties", "currentUserPermissions"])
  },

  methods: {
    ...mapActions([
      "changeSecondarySidebarProperties",
      "changeSecondarySidebarPropertiesViaMenuItem",
      "changeSecondarySidebarPropertiesViaOverlay",
      "changeSidebarProperties"
    ]),

    handleWindowResize() {
      if (window.innerWidth <= 1200) {
        if (this.getSideBarToggleProperties.isSideNavOpen) {
          this.changeSidebarProperties();
        }
        if (this.getSideBarToggleProperties.isSecondarySideNavOpen) {
          this.changeSecondarySidebarProperties();
        }
      } else {
        if (!this.getSideBarToggleProperties.isSideNavOpen) {
          this.changeSidebarProperties();
        }
      }
    },
    toggleSelectedParentMenu() {
      const currentParentUrl = this.$route.path
        .split("/")
        .filter(x => x !== "")[1];
      if (currentParentUrl !== undefined || currentParentUrl !== null) {
        this.selectedParentMenu = currentParentUrl.toLowerCase();
      } else {
        this.selectedParentMenu = "dashboard";
      }
    },
    toggleSubMenu(e) {
      let hasSubmenu = e.target.dataset.submenu;
      let parent = e.target.dataset.item;

      if (hasSubmenu) {
        this.selectedParentMenu = parent;

        this.changeSecondarySidebarPropertiesViaMenuItem(true);
      } else {
        this.selectedParentMenu = parent;
        this.changeSecondarySidebarPropertiesViaMenuItem(false);
      }
    },

    removeOverlay() {
      this.changeSecondarySidebarPropertiesViaOverlay();
      if (window.innerWidth <= 1200) {
        this.changeSidebarProperties();
      }
      this.toggleSelectedParentMenu();
    },
    returnSelectedParentMenu() {
      if (!this.isMenuOver) {
        this.toggleSelectedParentMenu();
      }
    },

    toggleSidebarDropdwon(event) {
      let dropdownMenus = this.$el.querySelectorAll(".dropdown-sidemenu.open");

      event.currentTarget.classList.toggle("open");

      dropdownMenus.forEach(dropdown => {
        dropdown.classList.remove("open");
      });
    }
  }
};
</script>

<style>

.navigation-left::after{
  content:"";
  display:block;
  height: calc(80px + env(safe-area-inset-bottom, 0px));
}

</style>

