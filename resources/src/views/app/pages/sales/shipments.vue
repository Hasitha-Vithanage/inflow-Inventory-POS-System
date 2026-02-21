<template>
  <div class="main-content">
    <breadcumb :page="$t('Shipments')" :folder="$t('Operations')" />

    <!-- ── Barcode / Reference Search (always visible at top) ── -->
    <div class="card mb-3 shipment-scan-card">
      <div class="card-body py-2 px-3">
        <div id="shipment_scan_box" class="autocomplete shipment-scan-autocomplete">
          <div class="input-with-icon">
            <img src="/assets_setup/scan.png" alt="Scan" class="scan-icon" style="cursor: pointer;" @click="showScanModal('search')" />
            <input
              ref="scanInput"
              v-model="scanQuery"
              :placeholder="$t('Scan_or_search_order_reference')"
              class="autocomplete-input"
              @input="onScanInput"
              @keyup.enter="onScanEnter"
              autocomplete="off"
              spellcheck="false"
            />
            <span v-if="scanLoading" class="scan-spinner"></span>
            <span
              v-if="scanQuery"
              class="scan-clear"
              @click="clearScan"
              title="Clear"
            >&times;</span>
          </div>
          <!-- Dropdown suggestions -->
          <ul class="autocomplete-result-list" v-show="scanSuggestions.length > 0 && !scanLoading">
            <li
              class="autocomplete-result"
              v-for="item in scanSuggestions"
              :key="item.id"
              @mousedown.prevent="selectSuggestion(item)"
            >
              <span class="suggestion-ref">{{ item.Ref }}</span>
              <span class="suggestion-meta">{{ item.client_name }} &bull; 
                <span :class="shippingBadgeClass(item.shipping_status)">{{ formatShippingStatus(item.shipping_status) }}</span>
              </span>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- ── Status Tabs ── -->
    <div class="card mb-0 shipment-tabs-card">
      <div class="card-body py-0 px-0">
        <ul class="shipment-tabs nav">
          <li class="nav-item" v-for="tab in tabs" :key="tab.value">
            <a
              class="nav-link shipment-tab-link"
              :class="{ active: activeTab === tab.value }"
              href="#"
              @click.prevent="setTab(tab.value)"
            >
              {{ tab.label }}
              <span v-if="tab.count !== null" class="tab-badge">{{ tab.count }}</span>
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- ── Loading / Empty states ── -->
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <div v-else>
      <!-- Table -->
      <div class="card shipment-list-card">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover shipment-table mb-0">
              <thead>
                <tr>
                  <th>{{ $t('date') }}</th>
                  <th>{{ $t('Reference') }}</th>
                  <th>{{ $t('Customer') }}</th>
                  <th>{{ $t('warehouse') }}</th>
                  <th>{{ $t('Shipping_status') }}</th>
                  <th>{{ $t('Shipping_Method') }}</th>
                  <th>{{ $t('Tracking') }}</th>
                  <th class="text-right">{{ $t('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="sales.length === 0">
                  <td colspan="8" class="text-center py-4 text-muted">{{ $t('NodataAvailable') }}</td>
                </tr>
                <tr v-for="sale in sales" :key="sale.id" class="shipment-row">
                  <td>{{ formatDate(sale.date) }}</td>
                  <td><span class="ref-badge">{{ sale.Ref }}</span></td>
                  <td>{{ sale.client_name }}</td>
                  <td>{{ sale.warehouse_name }}</td>
                  <td>
                    <span :class="shippingBadgeClass(sale.shipping_status)" class="badge">
                      {{ formatShippingStatus(sale.shipping_status) }}
                    </span>
                  </td>
                  <td>{{ sale.shipping_method_name || '—' }}</td>
                  <td>
                    <span v-if="sale.tracking_number" class="tracking-pill">
                      <i class="i-Barcode text-14 mr-1"></i>{{ sale.tracking_number }}
                    </span>
                    <span v-else class="text-muted small">—</span>
                  </td>
                  <td class="text-right">
                    <a
                      v-if="currentUserPermissions && currentUserPermissions.includes('shipment')"
                      @click="openEditShipment(sale)"
                      class="btn btn-sm btn-outline-primary mr-1"
                      title="Edit Shipment"
                      v-b-tooltip.hover
                    >
                      <Edit size="14"></Edit>
                    </a>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-between align-items-center px-3 py-2 shipment-pagination">
            <div class="text-muted small">
              {{ $t('Showing') }} {{ (serverParams.page - 1) * limit + 1 }}–{{ Math.min(serverParams.page * limit, totalRows) }} {{ $t('of') }} {{ totalRows }}
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
              <select class="form-control form-control-sm" v-model.number="limit" @change="onPerPageChange" style="width:80px;">
                <option v-for="n in [10,25,50,100]" :key="n" :value="n">{{ n }}</option>
              </select>
              <b-pagination
                v-model="serverParams.page"
                :total-rows="totalRows"
                :per-page="limit"
                size="sm"
                class="mb-0"
                @input="onPageChange"
              ></b-pagination>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Edit Shipment Modal ── -->
    <validation-observer ref="shipment_ref">
      <b-modal
        hide-footer
        size="md"
        id="modal_shipment"
        :title="$t('Edit') + ' — ' + (editedSale.Ref || '')"
        @hide="onModalHide"
      >
        <b-form @submit.prevent="submitShipment">
          <b-row>
            <!-- Customer / Ref summary -->
            <b-col md="12" class="mb-3">
              <div class="shipment-modal-summary">
                <div><strong>{{ $t('Customer').replace(/_/g, ' ') }}:</strong> {{ editedSale.client_name }}</div>
                <div><strong>{{ $t('warehouse').replace(/_/g, ' ') }}:</strong> {{ editedSale.warehouse_name }}</div>
                <div v-if="editedSale.shipping_method_name && editedSale.shipping_method_name !== '---'">
                  <strong>{{ $t('Shipping_Method').replace(/_/g, ' ') }}:</strong> {{ editedSale.shipping_method_name }}
                </div>
              </div>
            </b-col>

            <!-- Pickup block banner -->
            <b-col md="12" v-if="isPickupOrder">
              <div class="alert alert-warning d-flex align-items-center" style="border-radius:8px;gap:10px;">
                <i class="i-Shop text-20"></i>
                <span><strong>Store Pickup order</strong> — shipping details cannot be edited for pickup orders.</span>
              </div>
            </b-col>

            <!-- Modal loading overlay -->
            <b-col md="12" v-if="modalLoading" class="text-center py-3">
              <div class="spinner spinner-primary"></div>
            </b-col>

            <template v-if="!isPickupOrder && !modalLoading">

              <!-- ==== TRACKING SECTION (prominent, scan-first) ==== -->
              <b-col md="12">
                <div class="tracking-section">
                  <div class="tracking-section-label">{{ $t('Tracking_Number').replace(/_/g, ' ') }}</div>
                  <validation-provider name="tracking_number" :rules="{ min: 5 }">
                    <b-form-group slot-scope="{ valid, errors }">
                      <div class="input-with-icon tracking-input-wrapper">
                        <img src="/assets_setup/scan.png" alt="Scan" class="scan-icon" style="cursor: pointer;" @click="showScanModal('tracking')" />
                        <input
                          ref="trackingInput"
                          v-model="shipmentForm.tracking_number"
                          class="autocomplete-input tracking-input"
                          :placeholder="$t('Scan_or_enter_tracking_number').replace(/_/g, ' ')"
                          autocomplete="off"
                          spellcheck="false"
                          inputmode="text"
                          @keyup.enter="$refs.trackingInput && $refs.trackingInput.blur()"
                        />
                        <span
                          v-if="shipmentForm.tracking_number"
                          class="scan-clear"
                          @click="shipmentForm.tracking_number = ''"
                        >&times;</span>
                      </div>
                      <small v-if="errors[0]" class="text-danger">{{ errors[0] }}</small>
                      <small v-if="shipmentForm.tracking_number && shipmentForm.tracking_number.length < 5" class="text-warning">
                        {{ $t('Tracking_too_short') || 'Tracking number must be at least 5 characters' }}
                      </small>
                    </b-form-group>
                  </validation-provider>
                </div>
              </b-col>

              <!-- ==== Shipping Company ==== -->
              <b-col md="12">
                <b-form-group :label="$t('Shipping_company').replace(/_/g, ' ')">
                  <v-select
                    v-model="shipmentForm.shipping_company_id"
                    :reduce="c => c.id"
                    label="name"
                    :placeholder="$t('Choose_company').replace(/_/g, ' ')"
                    :options="shippingCompanies"
                  />
                </b-form-group>
              </b-col>

              <!-- Auto-ship info banner -->
              <b-col md="12" v-if="willAutoShip">
                <div class="auto-ship-banner">
                  <i class="i-Shipping-Delivery text-16 mr-1"></i>
                  <strong>Auto-Ship:</strong> Saving with company + tracking will mark this order as <strong>Dispatched</strong>.
                </div>
              </b-col>

              <!-- ==== Shipping Status ==== -->
              <b-col md="12">
                <validation-provider name="Status" :rules="{ required: true }">
                  <b-form-group slot-scope="{ valid, errors }" :label="$t('Shipping_status').replace(/_/g, ' ') + ' *'">
                    <v-select
                      :class="{ 'is-invalid': !!errors.length }"
                      v-model="shipmentForm.status"
                      :reduce="o => o.value"
                      :placeholder="$t('Choose_Status').replace(/_/g, ' ')"
                      :options="availableStatuses"
                    />
                    <b-form-invalid-feedback>{{ errors[0] }}</b-form-invalid-feedback>
                  </b-form-group>
                </validation-provider>
              </b-col>

              <!-- Delivered To -->
              <b-col md="12">
                <b-form-group :label="$t('delivered_to').replace(/_/g, ' ')">
                  <b-form-input
                    v-model="shipmentForm.delivered_to"
                    :placeholder="$t('delivered_to').replace(/_/g, ' ')"
                  />
                </b-form-group>
              </b-col>

              <!-- Shipping Address -->
              <b-col md="12">
                <b-form-group :label="$t('Adress')">
                  <textarea
                    v-model="shipmentForm.shipping_address"
                    rows="2"
                    class="form-control"
                    :placeholder="$t('Enter_Address')"
                  />
                </b-form-group>
              </b-col>

              <!-- Shipping Details -->
              <b-col md="12">
                <b-form-group :label="$t('Please_provide_any_details')">
                  <textarea
                    v-model="shipmentForm.shipping_details"
                    rows="2"
                    class="form-control"
                    :placeholder="$t('Please_provide_any_details')"
                  />
                </b-form-group>
              </b-col>

              <b-col md="12" class="mt-2">
                <b-button variant="primary" type="submit" :disabled="SubmitProcessing || trackingTooShort">
                  <i class="i-Yes me-2 font-weight-bold"></i> {{ $t('submit') }}
                </b-button>
                <div v-if="SubmitProcessing" class="spinner sm spinner-primary mt-3"></div>
              </b-col>

            </template>
          </b-row>
        </b-form>
      </b-modal>
    </validation-observer>

    <!-- ── Barcode Scanner Modal ── -->
    <b-modal hide-footer id="open_scan" size="md" title="Barcode Scanner">
      <qrcode-scanner
        :qrbox="250" 
        :fps="10" 
        style="width: 100%; height: calc(100vh - 56px);"
        @result="onScan"
      />
    </b-modal>

  </div>
</template>


<script>
import { mapGetters } from 'vuex';
import NProgress from 'nprogress';
import { Edit } from "lucide-vue";

export default {
  components: {
    Edit
  },
  metaInfo: {
    title: 'Shipments'
  },

  data() {
    return {
      isLoading: true,
      SubmitProcessing: false,

      // Scan / search
      scanQuery: '',
      scanLoading: false,
      scanSuggestions: [],
      scanDebounce: null,
      scanTarget: '', // 'search' or 'tracking'

      // Tab state
      activeTab: 'packed',   // default = Packed
      tabCounts: {},

      // Pagination / table
      serverParams: { page: 1 },
      limit: 10,
      totalRows: 0,
      sales: [],

      // Shipping companies
      shippingCompanies: [],

      // Edit modal
      editedSale: {},
      shipmentForm: {
        Ref: '',
        sale_id: null,
        status: '',
        delivered_to: '',
        shipping_address: '',
        shipping_details: '',
        tracking_number: '',
        shipping_company_id: null,
      },
      shipmentId: null,
      modalLoading: false,
    };
  },

  computed: {
    ...mapGetters(['currentUserPermissions', 'currentUser']),

    tabs() {
      return [
        { label: this.$t('All'),              value: '',                 count: null },
        { label: 'Pending',                   value: 'pending',          count: this.tabCounts.pending   || null },
        { label: 'Processing',                value: 'processing',       count: this.tabCounts.processing   || null },
        { label: 'Packed',                    value: 'packed',           count: this.tabCounts.packed     || null },
        { label: 'Dispatched',                value: 'dispatched',       count: this.tabCounts.dispatched || null },
        { label: 'In Transit',                value: 'in_transit',       count: this.tabCounts.in_transit   || null },
        { label: 'Out for Delivery',          value: 'out_for_delivery', count: this.tabCounts.out_for_delivery || null },
        { label: 'Ready for Pickup',          value: 'ready_for_pickup', count: this.tabCounts.ready_for_pickup || null },
        { label: 'Delivered',                 value: 'delivered',        count: this.tabCounts.delivered  || null },
      ];
    },

    // True when shipping method name contains pickup/collection (case-insensitive)
    isPickupOrder() {
      const name = (this.editedSale.shipping_method_name || '').toLowerCase();
      return name.includes('pickup') || name.includes('collection');
    },

    // True when both tracking + company are filled (triggers auto-ship rule)
    willAutoShip() {
      return !!(this.shipmentForm.tracking_number &&
                this.shipmentForm.tracking_number.length >= 5 &&
                this.shipmentForm.shipping_company_id && 
                !['dispatched','delivered','returned','in_transit','out_for_delivery'].includes(this.shipmentForm.status));
    },

    // Prevent submit if tracking is started but too short
    trackingTooShort() {
      const t = this.shipmentForm.tracking_number || '';
      return t.length > 0 && t.length < 5;
    },

    // Dynamic dropdown options based on pickup or delivery
    availableStatuses() {
      if (this.isPickupOrder) {
        return [
          { label: 'Pending',              value: 'pending' },
          { label: 'Processing',           value: 'processing' },
          { label: 'Ready for Pickup',     value: 'ready_for_pickup' },
          { label: 'Delivered',            value: 'delivered' },
          { label: 'Returned',             value: 'returned' },
        ];
      }
      return [
        { label: 'Pending',              value: 'pending' },
        { label: 'Processing',           value: 'processing' },
        { label: 'Packed',               value: 'packed' },
        { label: 'Dispatched',           value: 'dispatched' },
        { label: 'In Transit',           value: 'in_transit' },
        { label: 'Out for Delivery',     value: 'out_for_delivery' },
        { label: 'Delivered',            value: 'delivered' },
        { label: 'Returned',             value: 'returned' },
      ];
    }
  },

  methods: {
    // ── Helpers ─────────────────────────────────────────────────────────
    formatDate(val) {
      if (!val) return '';
      return val.toString().slice(0, 10);
    },

    shippingBadgeClass(status) {
      const map = {
        pending:          'badge-outline-warning',
        processing:       'badge-outline-warning',
        packed:           'badge-outline-info',
        dispatched:       'badge-outline-info',
        in_transit:       'badge-outline-primary',
        out_for_delivery: 'badge-outline-primary',
        ready_for_pickup: 'badge-outline-success',
        delivered:        'badge-outline-success',
        returned:         'badge-outline-danger',
      };
      return map[status] || 'badge-outline-dark';
    },

    formatShippingStatus(status) {
      if (!status) return 'N/A';
      const labels = {
        pending: 'Pending',
        processing: 'Processing',
        packed: 'Packed',
        dispatched: 'Dispatched',
        in_transit: 'In Transit',
        out_for_delivery: 'Out for Delivery',
        ready_for_pickup: 'Ready for Pickup',
        delivered: 'Delivered',
        returned: 'Returned'
      };
      return labels[status] || status;
    },

    makeToast(variant, msg, title) {
      this.$root.$bvToast.toast(msg, { title, variant, solid: true });
    },

    // ── Tabs ─────────────────────────────────────────────────────────────
    setTab(value) {
      this.activeTab = value;
      this.serverParams.page = 1;
      this.getSales(1);
    },

    // ── Pagination ───────────────────────────────────────────────────────
    onPageChange(page) {
      this.serverParams.page = page;
      this.getSales(page);
    },

    onPerPageChange() {
      this.serverParams.page = 1;
      this.getSales(1);
    },

    // ── Load Shipping Companies ───────────────────────────────────────
    loadShippingCompanies() {
      axios
        .get('shipping_companies', { params: { limit: -1, SortField: 'name', SortType: 'asc' } })
        .then(res => {
          // Filter to active companies only
          this.shippingCompanies = (res.data.companies || []).filter(c => c.is_active);
        })
        .catch(() => {});
    },

    // ── Fetch Sales ──────────────────────────────────────────────────────
    getSales(page) {
      NProgress.start();
      NProgress.set(0.1);
      axios
        .get('sales', {
          params: {
            page:             page,
            limit:            this.limit,
            SortField:        'id',
            SortType:         'desc',
            shipping_status:  this.activeTab,
            // Only fetch orders that actually have a shipping_status (have a shipping method)
            statut:           '',
          },
        })
        .then(res => {
          this.sales     = res.data.sales;
          this.totalRows = res.data.totalRows;
          NProgress.done();
          this.isLoading = false;
        })
        .catch(() => {
          NProgress.done();
          this.isLoading = false;
        });
    },

    // Load rough counts for tab badges (fire & forget, no NProgress)
    loadTabCounts() {
      const statuses = ['pending', 'processing', 'packed', 'dispatched', 'in_transit', 'out_for_delivery', 'ready_for_pickup', 'delivered'];
      statuses.forEach(status => {
        axios
          .get('sales', { params: { limit: 1, page: 1, SortField: 'id', SortType: 'desc', shipping_status: status } })
          .then(res => {
            this.$set(this.tabCounts, status, res.data.totalRows);
          })
          .catch(() => {});
      });
    },

    // ── Barcode Scanner Modal ────────────────────────────
    showScanModal(target) {
      this.scanTarget = target;
      this.$bvModal.show('open_scan');
    },

    onScan(decodedText, decodedResult) {
      if (this.scanTarget === 'search') {
        this.scanQuery = decodedText;
        this.onScanEnter(); // Immediately execute search if we scan
      } else if (this.scanTarget === 'tracking') {
        this.shipmentForm.tracking_number = decodedText;
      }
      this.$bvModal.hide('open_scan');
    },

    // ── Barcode / Scan Search ────────────────────────────────────────────
    onScanInput() {
      clearTimeout(this.scanDebounce);
      const q = this.scanQuery.trim();
      if (!q) {
        this.scanSuggestions = [];
        return;
      }
      this.scanDebounce = setTimeout(() => {
        this.fetchSuggestions(q);
      }, 220);
    },

    fetchSuggestions(q) {
      this.scanLoading = true;
      axios
        .get('sales', {
          params: {
            page:      1,
            limit:     8,
            SortField: 'id',
            SortType:  'desc',
            search:    q,
          },
        })
        .then(res => {
          this.scanSuggestions = res.data.sales || [];
          // If we get exactly one result whose Ref matches exactly, open immediately
          if (
            this.scanSuggestions.length === 1 &&
            this.scanSuggestions[0].Ref.toLowerCase() === q.toLowerCase()
          ) {
            this.selectSuggestion(this.scanSuggestions[0]);
          }
        })
        .catch(() => {
          this.scanSuggestions = [];
        })
        .finally(() => {
          this.scanLoading = false;
        });
    },

    onScanEnter() {
      const q = this.scanQuery.trim();
      if (!q) return;

      // Check existing suggestions for exact Ref match first
      const exactMatch = this.scanSuggestions.find(
        s => s.Ref.toLowerCase() === q.toLowerCase()
      );
      if (exactMatch) {
        this.selectSuggestion(exactMatch);
        return;
      }

      // Otherwise trigger a search
      this.fetchSuggestions(q);
    },

    selectSuggestion(sale) {
      this.scanSuggestions = [];
      this.scanQuery = '';
      this.openEditShipment(sale);
      // Re-focus for next scan after short delay
      this.$nextTick(() => {
        setTimeout(() => {
          if (this.$refs.scanInput) this.$refs.scanInput.focus();
        }, 300);
      });
    },

    clearScan() {
      this.scanQuery       = '';
      this.scanSuggestions = [];
      if (this.$refs.scanInput) this.$refs.scanInput.focus();
    },

    // ── Edit Modal ───────────────────────────────────────────────────────
    openEditShipment(sale) {
      this.editedSale  = { ...sale };
      this.shipmentId  = null;
      this.modalLoading = true;
      this.shipmentForm = {
        Ref: '',
        sale_id: sale.id,
        status: sale.shipping_status || 'packed',
        delivered_to: '',
        shipping_address: '',
        shipping_details: '',
        tracking_number: '',
        shipping_company_id: null,
      };
      // Load existing Shipment record for this sale (now also returns tracking info)
      axios
        .get(`shipments/${sale.id}`)
        .then(res => {
          const d = res.data.shipment;
          this.shipmentForm.Ref                 = d.Ref              || '';
          this.shipmentForm.status              = d.status           || sale.shipping_status || 'packed';
          this.shipmentForm.delivered_to        = d.delivered_to     || '';
          this.shipmentForm.shipping_address    = d.shipping_address || '';
          this.shipmentForm.shipping_details    = d.shipping_details || '';
          this.shipmentForm.tracking_number     = d.tracking_number  || '';
          this.shipmentForm.shipping_company_id = d.shipping_company_id || null;
        })
        .catch(() => {})
        .finally(() => {
          this.modalLoading = false;
          // Autofocus tracking number input after data loads
          this.$nextTick(() => {
            setTimeout(() => {
              if (this.$refs.trackingInput) this.$refs.trackingInput.focus();
            }, 100);
          });
        });

      this.$nextTick(() => {
        this.$bvModal.show('modal_shipment');
      });
    },

    onModalHide() {
      // Re-focus scan input when modal closes for rapid scanning
      this.$nextTick(() => {
        setTimeout(() => {
          if (this.$refs.scanInput) this.$refs.scanInput.focus();
        }, 150);
      });
    },

    submitShipment() {
      this.$refs.shipment_ref.validate().then(success => {
        if (!success) {
          this.makeToast('danger', this.$t('Please_fill_the_form_correctly'), this.$t('Failed'));
          return;
        }
        this.updateShipment();
      });
    },

    updateShipment() {
      this.SubmitProcessing = true;
      // Use the PATCH endpoint which handles auto-ship rule + tracking fields on Sale
      axios
        .patch(`sales/${this.editedSale.id}/shipping_status`, {
          shipping_status:     this.willAutoShip ? 'dispatched' : this.shipmentForm.status,
          tracking_number:     this.shipmentForm.tracking_number     || null,
          shipping_company_id: this.shipmentForm.shipping_company_id || null,
          delivered_to:        this.shipmentForm.delivered_to        || null,
          shipping_address:    this.shipmentForm.shipping_address    || null,
          shipping_details:    this.shipmentForm.shipping_details    || null,
        })
        .then(res => {
          const autoShipped = res.data.auto_shipped;
          const msg = autoShipped
            ? (this.$t('Order_marked_dispatched') || 'Order automatically marked as Dispatched!')
            : this.$t('Updated_in_successfully');
          this.makeToast('success', msg, this.$t('Success'));
          this.$bvModal.hide('modal_shipment');
          this.SubmitProcessing = false;
          this.getSales(this.serverParams.page);
          this.loadTabCounts();
        })
        .catch(() => {
          this.makeToast('danger', this.$t('InvalidData'), this.$t('Failed'));
          this.SubmitProcessing = false;
        });
    },
  },

  created() {
    this.getSales(1);
    this.loadTabCounts();
    this.loadShippingCompanies();
  },

  mounted() {
    // Autofocus scan input
    this.$nextTick(() => {
      if (this.$refs.scanInput) this.$refs.scanInput.focus();
    });
  },
};
</script>


<style scoped>
/* ── Scan Box ──────────────────────────────────────────────────────── */
.shipment-scan-card {
  border-radius: 8px;
  border: 1.5px solid #e8ecf4;
  box-shadow: 0 1px 6px 0 rgba(32,45,80,.06);
}

.shipment-scan-autocomplete {
  position: relative;
  width: 100%;
}

.shipment-scan-autocomplete .autocomplete-input {
  width: 100%;
  padding: 10px 38px 10px 48px;
  border: none;
  outline: none;
  font-size: 15px;
  background: transparent;
  color: inherit;
  letter-spacing: 0.01em;
}

.shipment-scan-autocomplete .input-with-icon {
  display: flex;
  align-items: center;
  position: relative;
}

.shipment-scan-autocomplete .scan-icon {
  position: absolute;
  left: 10px;
  width: 26px;
  height: 26px;
  object-fit: contain;
  opacity: 0.8;
  cursor: pointer;
  z-index: 10;
}

.scan-spinner {
  position: absolute;
  right: 36px;
  width: 18px;
  height: 18px;
  border: 2px solid #c5c5c5;
  border-top-color: #3f51b5;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.scan-clear {
  position: absolute;
  right: 12px;
  font-size: 18px;
  color: #aaa;
  cursor: pointer;
  line-height: 1;
  user-select: none;
  padding: 2px 4px;
}
.scan-clear:hover { color: #555; }

.shipment-scan-autocomplete .autocomplete-result-list {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: #fff;
  border: 1px solid #e0e4ef;
  border-radius: 0 0 8px 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,.10);
  z-index: 999;
  list-style: none;
  margin: 0;
  padding: 0;
  max-height: 280px;
  overflow-y: auto;
}

.shipment-scan-autocomplete .autocomplete-result {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 9px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f3f4f8;
  transition: background 0.12s;
}
.shipment-scan-autocomplete .autocomplete-result:last-child { border-bottom: none; }
.shipment-scan-autocomplete .autocomplete-result:hover { background: #f0f4ff; }

.suggestion-ref {
  font-weight: 600;
  font-family: 'Courier New', monospace;
  color: #3f51b5;
  font-size: 13px;
}
.suggestion-meta {
  font-size: 12px;
  color: #888;
}

/* ── Tabs ──────────────────────────────────────────────────────────── */
.shipment-tabs-card {
  border-radius: 8px 8px 0 0;
  border-bottom: none;
  border: 1.5px solid #e8ecf4;
  border-bottom: none;
}

.shipment-tabs {
  display: flex;
  padding: 0 4px;
  border-bottom: 1px solid #e8ecf4;
  background: #fafbff;
  border-radius: 8px 8px 0 0;
  gap: 2px;
  overflow-x: auto;
  flex-wrap: nowrap;
}

.shipment-tab-link {
  padding: 11px 20px;
  color: #6c757d;
  font-size: 13px;
  font-weight: 500;
  border: none;
  border-bottom: 3px solid transparent;
  margin-bottom: -1px;
  cursor: pointer;
  white-space: nowrap;
  border-radius: 0;
  transition: color 0.15s, border-color 0.15s;
}
.shipment-tab-link:hover { color: #3f51b5; }
.shipment-tab-link.active {
  color: #3f51b5;
  border-bottom-color: #3f51b5;
  font-weight: 600;
  background: transparent;
}

.tab-badge {
  display: inline-block;
  background: #3f51b5;
  color: #fff;
  font-size: 10px;
  border-radius: 8px;
  padding: 1px 6px;
  margin-left: 5px;
  vertical-align: middle;
  font-weight: 600;
}
.shipment-tab-link:not(.active) .tab-badge { background: #b0bec5; }

/* ── Table ──────────────────────────────────────────────────────────── */
.shipment-list-card {
  border-radius: 0 0 8px 8px;
  border: 1.5px solid #e8ecf4;
  border-top: none;
}

.shipment-table th {
  background: #f5f7fc;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6c757d;
  border-top: none;
  padding: 10px 12px;
}

.shipment-table td {
  vertical-align: middle;
  padding: 10px 12px;
  font-size: 13.5px;
}

.shipment-row:hover { background: #f8f9ff; }

.ref-badge {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  background: #eef2ff;
  color: #3f51b5;
  padding: 2px 7px;
  border-radius: 4px;
  font-weight: 600;
}

/* ── Pagination bar ─────────────────────────────────────────────────── */
.shipment-pagination {
  border-top: 1px solid #f0f1f7;
  background: #fafbff;
  border-radius: 0 0 8px 8px;
}

/* ── Edit Modal Summary ─────────────────────────────────────────────── */
.shipment-modal-summary {
  background: #f5f7fc;
  border-radius: 6px;
  padding: 10px 14px;
  font-size: 13px;
  line-height: 1.8;
  color: #555;
}
.shipment-modal-summary strong { color: #333; }

/* ── Tracking pill (table) ──────────────────────────────────────────── */
.tracking-pill {
  display: inline-flex;
  align-items: center;
  background: #f0f4ff;
  color: #3f51b5;
  font-family: 'Courier New', monospace;
  font-size: 11.5px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
  letter-spacing: 0.03em;
  white-space: nowrap;
  max-width: 160px;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ── Tracking input section (modal) ────────────────────────────────── */
.tracking-section {
  background: #f8f9ff;
  border: 2px solid #dee2f7;
  border-radius: 10px;
  padding: 12px 14px 4px;
  margin-bottom: 12px;
  transition: border-color 0.15s;
}
.tracking-section:focus-within {
  border-color: #3f51b5;
}
.tracking-section-label {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #3f51b5;
  margin-bottom: 6px;
}
.tracking-input {
  font-family: 'Courier New', monospace !important;
  font-size: 16px !important;
  font-weight: 600 !important;
  letter-spacing: 0.05em;
  color: #2c3e50;
  padding-top: 6px !important;
  padding-bottom: 6px !important;
}

/* Modal specific scanner styling */
.tracking-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.tracking-input-wrapper .scan-icon {
  position: absolute;
  left: 10px;
  width: 24px;
  height: 24px;
  z-index: 10;
  opacity: 0.8;
  cursor: pointer;
}

.tracking-input-wrapper .tracking-input {
  padding-left: 44px !important;
  border: 1px solid #ced4da;
  border-radius: 4px;
  width: 100%;
}


/* ── Auto-ship banner ───────────────────────────────────────────────── */
.auto-ship-banner {
  background: #e8f5e9;
  border: 1.5px solid #a5d6a7;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  color: #1b5e20;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

</style>
