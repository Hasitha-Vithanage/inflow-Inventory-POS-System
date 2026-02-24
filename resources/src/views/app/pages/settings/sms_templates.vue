<template>
  <div class="main-content">
    <breadcumb :page="$t('sms_templates')" :folder="$t('Settings')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <div id="section_notifications_template" v-else>

   <!-- Notification Client -->
  <div class="row mt-5">
    <div class="col-md-12">

      <div class="card">
        <div class="card-header">
          <h4>{{$t('Notification_Client')}}</h4>
        </div>
        <!--begin::form-->
        <div class="card-body">

          <b-tabs active-nav-item-class="nav nav-tabs" content-class="mt-3">

            <!-- Sell -->
            <b-tab :title="$t('Sale')">

              <form @submit.prevent="update_sms_body('sale')">
                  <div class="row">
                    <div class=" col-md-12">
                      <span> <strong>{{$t('Available_Tags')}} : </strong></span>
                      <p>
                        {contact_name},{business_name},{invoice_number},{invoice_url},{total_amount},{paid_amount},{due_amount}
                      </p>
                    </div>
                    <hr>
                    <div class="form-group col-md-12">
                      <label for="sms_body_sale">{{$t('sms_body')}} </label>
                      <textarea type="text" v-model="sms_body_sale" class="form-control" style=" height: 200px!important;"
                        name="sms_body_sale" id="sms_body_sale" :placeholder="$t('sms_body')"></textarea>
                    </div>

                  </div>

                  <div class="row mt-3">
                    <div class="col-md-6">
                      <button type="submit" :disabled="Submit_Processing" class="btn btn-primary">
                        <span v-if="Submit_Processing" class="spinner-border spinner-border-sm" role="status"
                          aria-hidden="true"></span> <check-circle size="16" class="mr-2" :stroke-width="1.5"></check-circle> {{$t('submit')}}
                      </button>
                    </div>
                  </div>
              </form>

            </b-tab>

            <!-- Quotation -->
            <b-tab :title="$t('Quote')">

              <form @submit.prevent="update_sms_body('quotation')">
                  <div class="row">
                    <div class=" col-md-12">
                      <span> <strong>{{$t('Available_Tags')}} : </strong></span>
                      <p>
                        {contact_name},{business_name},{quotation_number},{quotation_url},{total_amount}
                      </p>
                    </div>
                    <hr>
                    <div class="form-group col-md-12">
                      <label for="sms_body_quotation">{{$t('sms_body')}} </label>
                      <textarea type="text" v-model="sms_body_quotation" class="form-control"
                        style=" height: 200px!important;" name="sms_body_quotation" id="sms_body_quotation"
                        :placeholder="$t('sms_body')"></textarea>
                    </div>

                  </div>

                  <div class="row mt-3">
                    <div class="col-md-6">
                      <button type="submit" :disabled="Submit_Processing" class="btn btn-primary">
                        <span v-if="Submit_Processing" class="spinner-border spinner-border-sm" role="status"
                          aria-hidden="true"></span> <check-circle size="16" class="mr-2" :stroke-width="1.5"></check-circle> {{$t('submit')}}
                      </button>
                    </div>
                  </div>
              </form>

            </b-tab>

            <!-- Payment Received -->
            <b-tab :title="$t('PaiementsReceived')">

              <form @submit.prevent="update_sms_body('payment_received')">
                  <div class="row">
                    <div class=" col-md-12">
                      <span> <strong>{{$t('Available_Tags')}} : </strong></span>
                      <p>
                        {contact_name},{business_name},{payment_number},{paid_amount}
                      </p>
                    </div>
                    <hr>
                    <div class="form-group col-md-12">
                      <label for="sms_body_payment_received">{{$t('sms_body')}} </label>
                      <textarea type="text" v-model="sms_body_payment_received" class="form-control"
                        style=" height: 200px!important;" name="sms_body_payment_received" id="sms_body_payment_received"
                        :placeholder="$t('sms_body')"></textarea>
                    </div>

                  </div>

                  <div class="row mt-3">
                    <div class="col-md-6">
                      <button type="submit" :disabled="Submit_Processing" class="btn btn-primary">
                        <span v-if="Submit_Processing" class="spinner-border spinner-border-sm" role="status"
                          aria-hidden="true"></span> <check-circle size="16" class="mr-2" :stroke-width="1.5"></check-circle> {{$t('submit')}}
                      </button>
                    </div>
                  </div>
              </form>

            </b-tab>

            <!-- Payment Received -->
            <b-tab title="Subscription Reminder">
              <form @submit.prevent="update_sms_body('subscription_reminder')">
                <div class="row">
                  <div class="col-md-12">
                    <span><strong>{{$t('Available_Tags')}}: </strong></span>
                    <p>
                      {client_name}, {business_name}, {next_billing_date}
                    </p>
                  </div>
                  <hr>
                  <div class="form-group col-md-12">
                    <label for="sms_body_subscription_reminder">{{$t('sms_body')}}</label>
                    <textarea type="text" v-model="sms_body_subscription_reminder" class="form-control"
                      style="height: 200px!important;" name="sms_body_subscription_reminder" id="sms_body_subscription_reminder"
                      :placeholder="$t('sms_body')"></textarea>
                  </div>
                </div>
                <div class="row mt-3">
                  <div class="col-md-6">
                    <button type="submit" :disabled="Submit_Processing" class="btn btn-primary">
                      <span v-if="Submit_Processing" class="spinner-border spinner-border-sm" role="status"
                        aria-hidden="true"></span>
                      <i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}
                    </button>
                  </div>
                </div>
              </form>
                        </b-tab>

            <!-- New Order Lifecycle Tabs -->
            <b-tab title="Order Placed">
              <form @submit.prevent="update_sms_body('order_placed')">
                <div class="row">
                  <div class="col-md-12">
                    <span><strong>{{$t('Available_Tags')}}: </strong></span><p>{contact_name}, {business_name}, {invoice_number}, {invoice_url}, {total_amount}</p>
                  </div><hr>
                  <div class="form-group col-md-12">
                    <label>SMS Body</label>
                    <textarea v-model="sms_body_order_placed" class="form-control" style="height: 150px!important;"></textarea>
                  </div>
                </div>
                <div class="row mt-3"><div class="col-md-6"><button type="submit" :disabled="Submit_Processing" class="btn btn-primary"><i class="i-Yes me-2"></i> {{$t('submit')}}</button></div></div>
              </form>
            </b-tab>

            <b-tab title="Order Confirmed">
              <form @submit.prevent="update_sms_body('order_confirmed')">
                <div class="row">
                  <div class="col-md-12">
                    <span><strong>{{$t('Available_Tags')}}: </strong></span><p>{contact_name}, {business_name}, {invoice_number}, {invoice_url}, {total_amount}</p>
                  </div><hr>
                  <div class="form-group col-md-12">
                    <label>SMS Body</label>
                    <textarea v-model="sms_body_order_confirmed" class="form-control" style="height: 150px!important;"></textarea>
                  </div>
                </div>
                <div class="row mt-3"><div class="col-md-6"><button type="submit" :disabled="Submit_Processing" class="btn btn-primary"><i class="i-Yes me-2"></i> {{$t('submit')}}</button></div></div>
              </form>
            </b-tab>

            <b-tab title="Order Packed">
              <form @submit.prevent="update_sms_body('order_packed')">
                <div class="row">
                  <div class="col-md-12">
                    <span><strong>{{$t('Available_Tags')}}: </strong></span><p>{contact_name}, {business_name}, {invoice_number}, {invoice_url}, {total_amount}</p>
                  </div><hr>
                  <div class="form-group col-md-12">
                    <label>SMS Body</label>
                    <textarea v-model="sms_body_order_packed" class="form-control" style="height: 150px!important;"></textarea>
                  </div>
                </div>
                <div class="row mt-3"><div class="col-md-6"><button type="submit" :disabled="Submit_Processing" class="btn btn-primary"><i class="i-Yes me-2"></i> {{$t('submit')}}</button></div></div>
              </form>
            </b-tab>

            <b-tab title="Order Shipped / Dispatched">
              <form @submit.prevent="update_sms_body('order_shipped')">
                <div class="row">
                  <div class="col-md-12">
                    <span><strong>{{$t('Available_Tags')}}: </strong></span><p>{contact_name}, {business_name}, {invoice_number}, {tracking_number}, {shipping_company}, {invoice_url}</p>
                  </div><hr>
                  <div class="form-group col-md-12">
                    <label>SMS Body</label>
                    <textarea v-model="sms_body_order_shipped" class="form-control" style="height: 150px!important;"></textarea>
                  </div>
                </div>
                <div class="row mt-3"><div class="col-md-6"><button type="submit" :disabled="Submit_Processing" class="btn btn-primary"><i class="i-Yes me-2"></i> {{$t('submit')}}</button></div></div>
              </form>
            </b-tab>

          </b-tabs>


        </div>
      </div>
    </div>
  </div>


  <!-- {{-- Notification Supplier --}} -->
  <div class="row mt-5">
    <div class="col-md-12">

      <div class="card">
        <div class="card-header">
          <h4>{{$t('Notification_Supplier')}}</h4>
        </div>
        <!--begin::form-->
        <div class="card-body">

          <b-tabs active-nav-item-class="nav nav-tabs" content-class="mt-3">

            <!-- Purchase -->
            <b-tab :title="$t('Purchase')">

              <form @submit.prevent="update_sms_body('purchase')">
                <div class="row">
                  <div class=" col-md-12">
                    <span> <strong>{{$t('Available_Tags')}} : </strong></span>
                    <p>
                      {contact_name},{business_name},{invoice_number},{invoice_url},{total_amount},{paid_amount},{due_amount}
                    </p>
                  </div>
                  <hr>
                  <div class="form-group col-md-12">
                    <label for="sms_body_purchase">{{$t('sms_body')}} </label>
                    <textarea type="text" v-model="sms_body_purchase" class="form-control"
                      style=" height: 200px!important;" name="sms_body_purchase" id="sms_body_purchase"
                      :placeholder="$t('sms_body')"></textarea>
                  </div>

                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <button type="submit" :disabled="Submit_Processing" class="btn btn-primary">
                      <span v-if="Submit_Processing" class="spinner-border spinner-border-sm" role="status"
                        aria-hidden="true"></span> <i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}
                    </button>
                  </div>
                </div>
              </form>

            </b-tab>

            <!-- Payment Sent -->
            <b-tab :title="$t('PaiementsSent')">

              <form @submit.prevent="update_sms_body('payment_sent')">
                <div class="row">
                  <div class=" col-md-12">
                    <span> <strong>{{$t('Available_Tags')}} : </strong></span>
                    <p>
                      {contact_name},{business_name},{payment_number},{paid_amount}
                    </p>
                  </div>
                  <hr>
                  <div class="form-group col-md-12">
                    <label for="sms_body_payment_sent">{{$t('sms_body')}}</label>
                    <textarea type="text" v-model="sms_body_payment_sent" class="form-control"
                      style=" height: 200px!important;" name="sms_body_payment_sent" id="sms_body_payment_sent"
                      :placeholder="$t('sms_body')"></textarea>
                  </div>

                </div>

                <div class="row mt-3">
                  <div class="col-md-6">
                    <button type="submit" :disabled="Submit_Processing" class="btn btn-primary">
                      <span v-if="Submit_Processing" class="spinner-border spinner-border-sm" role="status"
                        aria-hidden="true"></span> <i class="i-Yes me-2 font-weight-bold"></i> {{$t('submit')}}
                    </button>
                  </div>
                </div>
              </form>

            </b-tab>

          </b-tabs>

        </div>
      </div>
    </div>
  </div>

  <!--  Notification Preferences Card --------------- -->
  <div class="row mt-4">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h4 class="mb-0">Notification Preferences</h4>
          <small class="text-muted">Enable or disable Email, SMS, and WhatsApp per order event</small>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered text-center">
              <thead class="thead-light">
                <tr>
                  <th class="text-left">Event</th>
                  <th><i class="nav-icon i-Mail"></i> Email</th>
                  <th><i class="nav-icon i-SMS"></i> SMS</th>
                  <th> WhatsApp</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(channels, event) in notification_preferences" :key="event">
                  <td class="text-left font-weight-bold" style="text-transform: capitalize">
                    {{ event.replace(/_/g, ' ') }}
                  </td>
                  <td><b-form-checkbox v-model="notification_preferences[event].email" switch size="lg" /></td>
                  <td><b-form-checkbox v-model="notification_preferences[event].sms" switch size="lg" /></td>
                  <td><b-form-checkbox v-model="notification_preferences[event].whatsapp" switch size="lg" /></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row mt-3">
            <div class="col-md-6">
              <button @click="saveNotificationPreferences" :disabled="savingPrefs" class="btn btn-primary">
                <span v-if="savingPrefs" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                <i class="i-Yes me-2"></i> Save Preferences
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

  </div><!-- end v-else -->
</div><!-- main-content -->
</template>

<script>
import { CheckCircle } from "lucide-vue";
import { mapActions, mapGetters } from "vuex";
import NProgress from "nprogress";

export default {
  metaInfo: {
    title: "SMS Templates"
  },
  data() {
    return {
      
      isLoading: true,
      Submit_Processing :false,
      sms_body_order_placed: '',
      sms_body_order_confirmed: '',
      sms_body_order_packed: '',
      sms_body_order_shipped: '',
      savingPrefs: false,
      notification_preferences: {
        order_placed:    { email: true, sms: true, whatsapp: true },
        order_confirmed: { email: true, sms: true, whatsapp: true },
        order_packed:    { email: true, sms: true, whatsapp: true },
        order_shipped:   { email: true, sms: true, whatsapp: true },
      },
      sms_body_order_placed: '',
      sms_body_order_confirmed: '',
      sms_body_order_packed: '',
      sms_body_order_shipped: '',
      savingPrefs: false,
      notification_preferences: {
        order_placed:    { email: true, sms: true, whatsapp: true },
        order_confirmed: { email: true, sms: true, whatsapp: true },
        order_packed:    { email: true, sms: true, whatsapp: true },
        order_shipped:   { email: true, sms: true, whatsapp: true },
      },
      sms_body_sale: '',
      sms_body_quotation: '',
      sms_body_payment_received: '',
      sms_body_subscription_reminder: '',

      sms_body_purchase: '',
      sms_body_payment_sent:'',

      sms_body:'',
     
    };
  },

  methods: {
    ...mapActions(["refreshUserPermissions"]),

    
     //---------------------------------- update_sms_body_sale ----------------\\
    update_sms_body(sms_body_type) {
        this.Submit_Processing = true;
        NProgress.start();
        NProgress.set(0.1);

        if(sms_body_type == 'sale'){
          this.sms_body = this.sms_body_sale;
        }else if(sms_body_type == 'quotation'){
          this.sms_body = this.sms_body_quotation;
        }else if(sms_body_type == 'payment_received'){
          this.sms_body = this.sms_body_payment_received;
        }else if(sms_body_type == 'purchase'){
          this.sms_body = this.sms_body_purchase;
        }else if(sms_body_type == 'payment_sent'){
          this.sms_body = this.sms_body_payment_sent;
                }else if(sms_body_type == 'order_placed'){
          this.sms_body = this.sms_body_order_placed;
        }else if(sms_body_type == 'order_confirmed'){
          this.sms_body = this.sms_body_order_confirmed;
        }else if(sms_body_type == 'order_packed'){
          this.sms_body = this.sms_body_order_packed;
        }else if(sms_body_type == 'order_shipped'){
          this.sms_body = this.sms_body_order_shipped;
        }else if(sms_body_type == 'subscription_reminder'){
          this.sms_body = this.sms_body_subscription_reminder;
        }
        
        axios
          .put("/update_sms_body",{
            sms_body: this.sms_body,
            sms_body_type: sms_body_type,
          })
          .then(response => {
            Fire.$emit("Event_sms");
            this.makeToast(
              "success",
              this.$t("Successfully_Updated"),
              this.$t("Success")
            );
            NProgress.done();
            this.Submit_Processing = false;
          })
          .catch(error => {
            NProgress.done();
            this.makeToast("danger", this.$t("InvalidData"), this.$t("Failed"));
            this.Submit_Processing = false;
          });
    },


     //---------------------------------- get_sms_template ----------------\\
    get_sms_template() {
      axios
        .get("get_sms_template")
        .then(response => {
          this.sms_body_sale = response.data.sms_body_sale;
          this.sms_body_quotation = response.data.sms_body_quotation;
          this.sms_body_payment_received = response.data.sms_body_payment_received;
          this.sms_body_purchase = response.data.sms_body_purchase;
          this.sms_body_payment_sent = response.data.sms_body_payment_sent;
          this.sms_body_subscription_reminder = response.data.sms_body_subscription_reminder;
            this.sms_body_order_placed = response.data.sms_body_order_placed;
            this.sms_body_order_confirmed = response.data.sms_body_order_confirmed;
            this.sms_body_order_packed = response.data.sms_body_order_packed;
            this.sms_body_order_shipped = response.data.sms_body_order_shipped;

          this.isLoading = false;
        })
        .catch(error => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    },   


   
  }, //end Methods

  //----------------------------- Created function-------------------

      getNotificationPreferences() {
      axios.get("notification_preferences").then(response => {
          if (response.data.notification_preferences) {
            this.notification_preferences = response.data.notification_preferences;
          }
        }).catch(() => {});
    },
    saveNotificationPreferences() {
      this.savingPrefs = true;
      axios.put("notification_preferences", { notification_preferences: this.notification_preferences })
        .then(() => {
          this.makeToast("success", "Notification preferences saved", this.$t("Success"));
          this.savingPrefs = false;
        }).catch(() => {
          this.makeToast("danger", "Failed to save preferences", this.$t("Failed"));
          this.savingPrefs = false;
        });
    },

  created: function() {
    this.get_sms_template();
    this.getNotificationPreferences();


    Fire.$on("Event_sms", () => {
      this.get_sms_template();
    this.getNotificationPreferences();
    });
  }
};
</script>