<template>
  <div class="main-content">
    <breadcumb :page="'Notification Settings'" :folder="$t('Settings')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <div v-else class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Notification Preferences</h4>
            <small class="text-muted">Enable or disable Email, SMS, and WhatsApp notifications per order event</small>
          </div>
          <div class="card-body">

            <div class="alert alert-info mb-4">
              <strong>How it works:</strong> Disable a channel for an event to prevent those notifications from being sent automatically. All channels are enabled by default.
            </div>

            <div class="table-responsive">
              <table class="table table-bordered text-center align-middle">
                <thead class="thead-light">
                  <tr>
                    <th class="text-left" style="width: 35%">Order Event</th>
                    <th style="width: 21%"><i class="nav-icon i-Mail mr-1"></i> Email</th>
                    <th style="width: 22%"><i class="nav-icon i-Phone mr-1"></i> SMS</th>
                    <th style="width: 22%">&#128241; WhatsApp</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(channels, event) in preferences" :key="event">
                    <td class="text-left py-3">
                      <strong>{{ eventLabels[event] || event }}</strong>
                      <br>
                      <small class="text-muted">{{ eventDescriptions[event] || '' }}</small>
                    </td>
                    <td>
                      <label class="switch switch-primary">
                        <input type="checkbox" v-model="preferences[event].email">
                        <span class="slider"></span>
                      </label>
                    </td>
                    <td>
                      <label class="switch switch-primary">
                        <input type="checkbox" v-model="preferences[event].sms">
                        <span class="slider"></span>
                      </label>
                    </td>
                    <td>
                      <label class="switch switch-primary">
                        <input type="checkbox" v-model="preferences[event].whatsapp">
                        <span class="slider"></span>
                      </label>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="row mt-4">
              <div class="col-12 d-flex justify-content-end">
                <button @click="save" :disabled="saving" class="btn btn-primary btn-lg px-5">
                  <span v-if="saving" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                  <check-circle v-else size="16" class="mr-2" :stroke-width="1.5"></check-circle>
                  Save Preferences
                </button>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { CheckCircle } from "lucide-vue";
import NProgress from "nprogress";

export default {
  metaInfo: { title: "Notification Settings" },
  components: { CheckCircle },

  data() {
    return {
      isLoading: true,
      saving: false,
      eventLabels: {
        order_placed:    "Order Placed",
        order_confirmed: "Order Confirmed",
        order_packed:    "Order Packed",
        order_shipped:   "Order Shipped",
      },
      eventDescriptions: {
        order_placed:    "Sent when a new order is created",
        order_confirmed: "Sent when an order is confirmed",
        order_packed:    "Sent when an order is marked as packed",
        order_shipped:   "Sent when an order is dispatched for delivery",
      },
      preferences: {
        order_placed:    { email: true, sms: true, whatsapp: true },
        order_confirmed: { email: true, sms: true, whatsapp: true },
        order_packed:    { email: true, sms: true, whatsapp: true },
        order_shipped:   { email: true, sms: true, whatsapp: true },
      },
    };
  },

  methods: {
    load() {
      axios.get("notification_preferences")
        .then(response => {
          if (response.data.notification_preferences) {
            const saved = response.data.notification_preferences;
            Object.keys(this.preferences).forEach(event => {
              if (saved[event]) {
                this.preferences[event] = { ...this.preferences[event], ...saved[event] };
              }
            });
          }
          this.isLoading = false;
        })
        .catch(() => { this.isLoading = false; });
    },

    save() {
      this.saving = true;
      NProgress.start();
      axios.put("notification_preferences", { notification_preferences: this.preferences })
        .then(() => {
          this.makeToast("success", "Notification preferences saved successfully.", this.$t("Success"));
          NProgress.done();
          this.saving = false;
        })
        .catch(() => {
          this.makeToast("danger", "Failed to save preferences.", this.$t("Failed"));
          NProgress.done();
          this.saving = false;
        });
    },
  },

  created() { this.load(); },
};
</script>
