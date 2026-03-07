<template>
  <div class="main-content">
    <breadcumb :page="$t('Collections')" :folder="$t('Store')" />

    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <b-card v-else>
      <!-- Header actions -->
      <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
          <div class="d-flex align-items-center">
            <div class="position-relative">
              <i class="search-icon i-Search-People text-muted position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); z-index: 1;"></i>
              <b-form-input
                v-model.trim="q"
                :placeholder="$t('Search') + '…'"
                @input="onSearch"
                style="padding-left: 35px; min-width: 250px;"
              />
            </div>
            
            <b-button variant="outline-primary" class="ml-2" @click="refresh" :disabled="busy">
              <i class="i-Reload"></i>
            </b-button>
          </div>


        <div class="mt-2 mt-md-0">
          <router-link :to="{ name: 'StoreCollectionsCreate' }" class="btn btn-primary">
            <i class="i-Add"></i> {{ $t('New') }}
          </router-link>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="thead-light">
            <tr>
              <th style="width: 70px">#</th>
              <th>{{ $t('Title') }}</th>
              <th class="d-none d-md-table-cell">{{ $t('Slug') }}</th>
              <th class="text-center d-none d-md-table-cell" style="width: 120px">{{ $t('Limit') }}</th>
              <th class="text-center d-none d-lg-table-cell" style="width: 120px">{{ $t('Products') }}</th>
              <th class="text-right" style="width: 300px">{{ $t('Actions') }}</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="(c, idx) in filtered" :key="c.id">
              <td class="text-muted">
                <code class="small">#{{ displayOrder(idx) }}</code>
              </td>

              <td>
                <div class="fw-600">{{ c.title }}</div>
                <div class="text-muted small" v-if="c.description" style="max-width: 520px;">
                  {{ c.description }}
                </div>
              </td>

              <td class="d-none d-md-table-cell">
                <span class="badge badge-outline-secondary">{{ c.slug }}</span>
              </td>

              <td class="text-center d-none d-md-table-cell">
                {{ displayLimit(c.limit) }}
              </td>

              <td class="text-center d-none d-lg-table-cell">
                <span class="badge badge-pill badge-light">
                  {{ c.products_count != null ? c.products_count : '—' }}
                </span>
              </td>

              <td class="text-right">
                <div class="d-flex align-items-center justify-content-end">
                
                  <!-- Edit -->
                  <router-link
                    :to="{ name: 'StoreCollectionsEdit', params: { id: c.id } }"
                    class="cursor-pointer"
                    :class="{ disabled: busy }"
                    title="Edit"
                  >
                    <Edit size="20" class="text-success"></Edit>
                  </router-link>

                  <!-- Delete -->
                  <a
                    class="cursor-pointer"
                    :class="{ disabled: busyId === c.id }"
                    @click="destroy(c)"
                    title="Delete"
                  >
                    <XCircle size="20" class="text-danger"></XCircle>
                  </a>
                </div>
              </td>
            </tr>

            <tr v-if="!filtered.length">
              <td colspan="6" class="text-center text-muted py-4">
                {{ $t('No_items') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </b-card>
  </div>
</template>

<script>
import { Edit, XCircle } from "lucide-vue";

export default {
  metaInfo: {
    title: "Store Collections Index"
  },
  components: {
    Edit, XCircle
  },

  data () {
    return {
      isLoading: true,
      busy: false,
      busyId: null,
      q: '',
      collections: []
    }
  },

  computed: {
    sorted () {
      const arr = this.collections.slice()
      return arr.sort((a, b) => {
        const ao = (a.sort_order != null ? a.sort_order : 0)
        const bo = (b.sort_order != null ? b.sort_order : 0)
        if (ao !== bo) return ao - bo
        return String(a.title || '').localeCompare(String(b.title || ''))
      })
    },
    filtered () {
      const term = (this.q || '').toLowerCase()
      if (!term) return this.sorted
      return this.sorted.filter(c =>
        String(c.title || '').toLowerCase().includes(term) ||
        String(c.slug || '').toLowerCase().includes(term)
      )
    }
  },

  mounted () {
    this.fetch()
  },

  methods: {
    makeToast (variant, msg, title) {
      if (this.$root && this.$root.$bvToast) {
        this.$root.$bvToast.toast(msg, { title: title, variant: variant, solid: true })
      }
    },

    displayLimit (val) {
      return (val !== null && val !== undefined) ? val : 8
    },

    async fetch () {
      this.isLoading = true
      try {
        const resp = await axios.get('/admin/store/collections')
        let payload = (resp && resp.data && Array.isArray(resp.data.data)) ? resp.data.data : (resp && resp.data ? resp.data : [])
        if (!Array.isArray(payload)) payload = []
        this.collections = payload
      } catch (e) {
        this.makeToast('danger', this.$t('Failed_to_load'), this.$t('Failed'))
      } finally {
        this.isLoading = false
      }
    },

    refresh () { this.fetch() },
    onSearch () {},

    displayOrder (idx) { return idx + 1 },

    async toggleActive (c) {
      try {
        this.busyId = c.id
        const desired = c.is_active ? 1 : 0
        await axios.put('/admin/store/collections/' + c.id, { is_active: desired })
        this.makeToast('success', this.$t('Updated'), this.$t('Success'))
      } catch (e) {
        c.is_active = !c.is_active
        this.makeToast('danger', this.$t('InvalidData'), this.$t('Failed'))
      } finally {
        this.busyId = null
      }
    },

    move (idx, dir) {
      if (idx < 0 || idx >= this.filtered.length) return
      const j = idx + dir
      if (j < 0 || j >= this.filtered.length) return

      const current = this.filtered.slice()
      const row = current.splice(idx, 1)[0]
      current.splice(j, 0, row)
      this.collections = current
      this.persistOrder()
    },

    async persistOrder () {
      if (!this.collections.length) return
      this.busy = true
      try {
        const requests = []
        for (let i = 0; i < this.collections.length; i++) {
          const c = this.collections[i]
          const newOrder = (i + 1) * 10
          if (c.sort_order === newOrder) continue
          requests.push(axios.put('/admin/store/collections/' + c.id, { sort_order: newOrder }))
        }
        await Promise.all(requests)
        this.makeToast('success', this.$t('Order_saved'), this.$t('Success'))
        await this.fetch()
      } catch (e) {
        this.makeToast('danger', this.$t('Could_not_save_order'), this.$t('Failed'))
      } finally {
        this.busy = false
      }
    },

    async destroy (c) {
      const r = this.$swal
        ? await this.$swal({
            title: this.$t('Delete_Title'),
            text: this.$t('Delete_Text'),
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: this.$t('Delete_cancelButtonText'),
            confirmButtonText: this.$t('Delete_confirmButtonText')
          })
        : { value: window.confirm(this.$t('Confirm_Delete_This_Item')) }
      if (!(r && (r.value === true || r.isConfirmed === true))) return
      try {
        this.busyId = c.id
        await axios.delete('/admin/store/collections/' + c.id)
        this.makeToast('success', this.$t('Deleted_successfully'), this.$t('Success'))
        this.collections = this.collections.filter(x => x.id !== c.id)
      } catch (e) {
        this.makeToast('danger', this.$t('Delete_failed'), this.$t('Failed'))
      } finally {
        this.busyId = null
      }
    }
  }
}
</script>

<style scoped>
.table td, .table th { vertical-align: middle; }
.badge-outline-secondary {
  color: #6c757d; border: 1px solid #dee2e6; background-color: #fff;
}
.fw-600 { font-weight: 600; }
</style>
