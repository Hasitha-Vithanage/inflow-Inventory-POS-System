<template>
  <div class="main-header">
    <div class="logo">
       <router-link to="/app/dashboard">
        <img v-if="currentUser && currentUser.logo" :src="'/images/'+currentUser.logo" alt width="60" height="60">
        <img v-else src="/images/logo.png" alt width="60" height="60">
       </router-link>
    </div>

    <button @click="sideBarToggle" class="menu-toggle header-btn btn-gray ml-3">
      <indent-decrease v-if="getSideBarToggleProperties.isSideNavOpen" :size="24" />
      <indent-increase v-else :size="24" />
    </button>

    <div style="margin: auto"></div>

    <div class="header-part-right nav-right">
      <router-link 
        v-if="currentUserPermissions && currentUserPermissions.includes('Pos_view')"
        class="header-btn btn-gray btn-pos-text ml-2"
        to="/app/pos"
        title="POS"
      >
        <span class="font-weight-bold">POS</span>
      </router-link>

      <button 
        class="header-btn btn-gray" 
        @click="toggleDarkMode" 
        :title="getThemeMode.dark ? 'Light Mode' : 'Dark Mode'"
      >
        <sun v-if="getThemeMode.dark" size="18"></sun>
        <moon v-else size="18"></moon>
      </button>

      <button class="header-btn btn-gray ml-2" @click="toggleCustomizer" title="Customize">
        <sliders size="18"></sliders>
      </button>

      <button class="header-btn btn-gray d-none d-sm-inline-flex" @click="handleFullScreen" title="Fullscreen">
        <maximize size="18"></maximize>
      </button>

      <!-- Language Dropdown -->
      <div class="dropdown" v-if="show_language">
        <b-dropdown
          id="lang-dd"
          right
          toggle-class="header-btn btn-gray btn-language-text"
          no-caret
        >
          <template slot="button-content">
             <div class="d-flex align-items-center" v-if="currentLanguage">
                <img
                  :src="`/flags/${currentLanguage.flag}`"
                  :alt="currentLanguage.name"
                  class="flag-icon-header"
                />
                <span class="lang-name-header ml-2">{{ currentLanguage.name }}</span>
             </div>
             <globe v-else size="18"></globe>
          </template>
          <vue-perfect-scrollbar
            :settings="{ suppressScrollX: true, wheelPropagation: false }"
            class="dropdown-scroll"
          >
            <div class="lang-menu">
              <a 
                v-for="lang in getAvailableLanguages" 
                :key="lang.locale" 
                @click="SetLocal(lang.locale)"
                class="lang-item"
              >
                <img
                  :src="`/flags/${lang.flag}`"
                  :alt="lang.name"
                  class="flag-icon"
                />
                <span>{{ lang.name }}</span>
              </a>
            </div>
          </vue-perfect-scrollbar>
        </b-dropdown>
      </div>

      <!-- Notifications -->
      <div class="dropdown">
        <b-dropdown
          id="notif-dd"
          right
          toggle-class="header-btn btn-gray"
          no-caret
        >
          <template slot="button-content">
            <span class="badge badge-primary" v-if="notifs_alert > 0">1</span>
            <bell size="18"></bell>
          </template>
          <vue-perfect-scrollbar
            :settings="{ suppressScrollX: true, wheelPropagation: false }"
            class="dropdown-scroll"
          >
            <div class="notification-item" v-if="notifs_alert > 0">
              <div class="notif-icon">
                <bell size="18" class="text-primary"></bell>
              </div>
              <div class="notif-content" v-if="currentUserPermissions && currentUserPermissions.includes('Reports_quantity_alerts')">
                <router-link tag="a" to="/app/reports/quantity_alerts">
                  <p>{{ notifs_alert }} {{ $t('ProductQuantityAlerts') }}</p>
                </router-link>
              </div>
            </div>
          </vue-perfect-scrollbar>
        </b-dropdown>
      </div>

      <!-- User Dropdown -->
      <div class="dropdown">
        <b-dropdown
          id="user-dd"
          right
          toggle-class="header-btn btn-gray"
          no-caret
          variant="link"
        >
          <template slot="button-content">
            <div class="user-avatar">
              <img
                v-if="currentUser && currentUser.avatar"
                :src="'/images/avatar/' + currentUser.avatar"
                alt="user"
              />
              <img v-else src="/images/avatar/avatar-default.jpg" alt="user" />
            </div>
          </template>
          <div class="user-dropdown-menu">
            <div class="dropdown-header">
               <lock size="18" class="mr-2"></lock>
              <span v-if="currentUser">{{ currentUser.username }}</span>
            </div>
            <router-link to="/app/profile" class="dropdown-item">
               <user size="18" class="mr-2"></user>
              {{ $t('profil') }}
            </router-link>
            <router-link
              v-if="currentUserPermissions && currentUserPermissions.includes('setting_system')"
              to="/app/settings/System_settings"
              class="dropdown-item"
            >
               <settings size="18" class="mr-2"></settings>
              {{ $t('Settings') }}
            </router-link>
            <a class="dropdown-item" href="#" @click.prevent="logoutUser">
               <log-out size="18" class="mr-2"></log-out>
              {{ $t('logout') }}
            </a>
          </div>
        </b-dropdown>
      </div>
    </div>
  </div>

  <!-- header top menu end -->
</template>
<script>
import Util from "./../../../utils";
// import Sidebar from "./Sidebar";
import { isMobile } from "mobile-device-detect";
import { mapGetters, mapActions } from "vuex";
import { mixin as clickaway } from "vue-clickaway";
// import { setTimeout } from 'timers';

import { 
  Sun, 
  Moon, 
  Maximize, 
  Globe, 
  Bell, 
  User, 
  Settings, 
  LogOut,
  Lock,
  IndentDecrease,
  IndentIncrease,
  Sliders
} from "lucide-vue";

export default {
  mixins: [clickaway],
  components: {
    Sun, 
    Moon, 
    Maximize, 
    Globe, 
    Bell, 
    User, 
    Settings, 
    LogOut,
    Lock,
    IndentDecrease,
    IndentIncrease,
    Sliders
  },
 
  data() {
  
    return {
     
      isDisplay: true,
      isStyle: true,
      isSearchOpen: false,
      isMouseOnMegaMenu: true,
      isMegaMenuOpen: false,
      is_Load:false,
     
    };
  },
 
   computed: {
     
     ...mapGetters([
       "currentUser",
      "getSideBarToggleProperties",
      "currentUserPermissions",
      "notifs_alert",
      "show_language",
      "getAvailableLanguages"
    ]),
    ...mapGetters("config", ["getThemeMode"]),

    currentLanguage() {
      if (!this.getAvailableLanguages || !this.$i18n.locale) return null;
      return this.getAvailableLanguages.find(lang => lang.locale === this.$i18n.locale) || this.getAvailableLanguages[0];
    }
  },

  methods: {
    
    ...mapActions([
      "changeSecondarySidebarProperties",
      "changeSidebarProperties",
      "logout",
    ]),
    ...mapActions("config", ["changeThemeMode"]),

    SetLocal(locale) {
      this.$i18n.locale = locale;
      this.$store.dispatch("setLanguage", locale);
      Fire.$emit("ChangeLanguage");
      window.location.reload();
    },

    handleFullScreen() {
      Util.toggleFullScreen();
    },

    toggleDarkMode() {
      this.changeThemeMode();
      // Apply dark theme class to body element
      if (this.getThemeMode.dark) {
        document.body.classList.add('dark-theme');
      } else {
        document.body.classList.remove('dark-theme');
      }
    },

    logoutUser() {
      this.logout();
    },

    closeMegaMenu() {
      this.isMegaMenuOpen = false;
    },
    toggleMegaMenu() {
      this.isMegaMenuOpen = !this.isMegaMenuOpen;
    },
    toggleSearch() {
      this.isSearchOpen = !this.isSearchOpen;
    },

    toggleCustomizer() {
      Fire.$emit("toggle-customizer");
    },

    sideBarToggle(el) {
      if (
        this.getSideBarToggleProperties.isSideNavOpen &&
        this.getSideBarToggleProperties.isSecondarySideNavOpen &&
        isMobile
      ) {
        this.changeSidebarProperties();
        this.changeSecondarySidebarProperties();
      } else if (
        this.getSideBarToggleProperties.isSideNavOpen &&
        this.getSideBarToggleProperties.isSecondarySideNavOpen
      ) {
        this.changeSecondarySidebarProperties();
      } else if (this.getSideBarToggleProperties.isSideNavOpen) {
        this.changeSidebarProperties();
      } else if (
        !this.getSideBarToggleProperties.isSideNavOpen &&
        !this.getSideBarToggleProperties.isSecondarySideNavOpen &&
        !this.getSideBarToggleProperties.isActiveSecondarySideNav
      ) {
        this.changeSidebarProperties();
      } else if (
        !this.getSideBarToggleProperties.isSideNavOpen &&
        !this.getSideBarToggleProperties.isSecondarySideNavOpen
      ) {

        this.changeSidebarProperties();
        this.changeSecondarySidebarProperties();
      }
    }
  },

  mounted() {
    // Apply dark theme class on mount if dark mode is enabled
    if (this.getThemeMode.dark) {
      document.body.classList.add('dark-theme');
    } else {
      document.body.classList.remove('dark-theme');
    }
  }
};
</script>

<style>

/* Non-scoped styles for Bootstrap Vue dropdown buttons */

/* Modern Header Buttons - Squircle Pastel Style */
.header-btn {
  width: 44px !important;
  height: 44px !important;
  border-radius: 14px !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
  border: none !important;
  cursor: pointer !important;
  position: relative !important;
  padding: 0 !important;
  text-decoration: none !important;
  outline: none !important;
}

.header-btn i {
  font-size: 20px !important;
  line-height: 1 !important;
}

.header-btn:hover {
  transform: translateY(-3px) !important;
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
}

.header-btn:active {
  transform: translateY(-1px) !important;
}


/* Neutral Gray Color Scheme */
.btn-gray {
  background: rgba(107, 114, 128, 0.08) !important;
  color: #374151 !important;
}

.btn-gray:hover {
  background: rgba(107, 114, 128, 0.15) !important;
  color: #111827 !important;
}

/* Wider buttons for text */
.btn-pos-text, .btn-language-text {
  width: auto !important;
  padding: 0 16px !important;
}

.flag-icon-header {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.lang-name-header {
  font-weight: 600;
  font-size: 14px;
}

/* Dark Mode Adjustments */
body.dark-theme .btn-gray {
  background: rgba(156, 163, 175, 0.12) !important;
  color: #d1d5db !important;
}

body.dark-theme .btn-gray:hover {
  background: rgba(156, 163, 175, 0.2) !important;
  color: #ffffff !important;
}

/* Dropdown menu styling */
.main-header .dropdown-menu {
  border-radius: 16px !important;
  border: none !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
  padding: 8px !important;
  min-width: 200px !important;
  margin-top: 12px !important;
  border: 1px solid rgba(0,0,0,0.05) !important;
}

.main-header #notif-dd .dropdown-menu { min-width: 320px !important; }
.main-header #lang-dd .dropdown-menu { min-width: 220px !important; }

/* Dark mode dropdown menu */
body.dark-theme .main-header .dropdown-menu {
  background: #1f2937 !important;
  border: 1px solid #374151 !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
}
</style>

<style scoped>
.nav-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.badge {
  position: absolute;
  top: -2px;
  right: -2px;
  min-width: 18px;
  height: 18px;
  padding: 0 5px;
  border-radius: 9px;
  font-size: 10px;
  font-weight: 700;
  line-height: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #ef4444; 
  color: white;
  border: 2px solid white;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

body.dark-theme .badge {
  border-color: #1f2937;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.dropdown-scroll {
  max-height: 300px;
  overflow-y: auto;
}

.lang-menu {
  padding: 8px;
}

.lang-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 10px;
  cursor: pointer;
  color: #374151;
  text-decoration: none;
  transition: all 0.2s;
  margin-bottom: 4px;
}

.lang-item:hover {
  background: #f3f4f6;
  color: #111827;
}

.flag-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 12px 16px;
  border-radius: 10px;
  transition: background 0.2s;
  cursor: pointer;
  margin-bottom: 4px;
}

.notification-item:hover {
  background: #f3f4f6;
}

.notification-item:last-child {
  margin-bottom: 0;
}

.notif-icon {
  font-size: 20px;
  padding: 8px;
  background: rgb(51 59 153 / 10%);
  border-radius: 10px;
  color: #2d8cff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.notif-icon i {
  color: #2d8cff !important;
}

.notif-content {
  flex: 1;
}

.notif-content p {
  margin: 0;
  font-size: 14px;
  color: #4b5563;
  line-height: 1.5;
  font-weight: 500;
}

.notif-content a {
  color: inherit;
  text-decoration: none;
}

.user-dropdown-menu {
  min-width: 200px;
}

.dropdown-header {
  padding: 16px;
  border-bottom: 1px solid #f3f4f6;
  font-weight: 700;
  color: #111827;
  display: flex;
  align-items: center;
  font-size: 15px;
}

.dropdown-item {
  padding: 10px 16px;
  color: #4b5563;
  text-decoration: none;
  display: flex;
  align-items: center;
  transition: all 0.2s;
  font-weight: 500;
  margin-top: 4px;
  border-radius: 8px;
}

.dropdown-item:hover {
  background: #f3f4f6;
  color: #111827;
}

/* Dark Mode */
body.dark-theme .nav-icon-btn {
  background: #1f2937;
  border-color: #374151;
  color: #9ca3af;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

body.dark-theme .nav-icon-btn:hover {
  background: #374151;
  color: #fff;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
}

body.dark-theme .lang-item {
  color: #d1d5db;
}

body.dark-theme .lang-item:hover {
  background: #374151;
  color: #fff;
}

body.dark-theme .notification-item:hover {
  background: #374151;
}

body.dark-theme .notif-content p {
  color: #d1d5db;
}

body.dark-theme .dropdown-header {
  border-bottom-color: #374151;
  color: #f3f4f6;
}

body.dark-theme .dropdown-item {
  color: #d1d5db;
}

body.dark-theme .dropdown-item:hover {
  background: #374151;
  color: #fff;
}

body.dark-theme .badge {
  border-color: #1f2937;
}

body.dark-theme .user-avatar {
  border-color: #374151;
}

/* Mobile adjustments */
@media (max-width: 768px) {
  /* Hide fullscreen button on mobile */
  .fullscreen-btn {
    display: none !important;
  }

  .btn-text {
    display: none;
  }

  .btn-primary {
    padding: 0;
    width: 44px;
    height: 44px;
    justify-content: center;
  }
}

/* Remove outline from header icons when clicked */
.header-icon:focus,
.header-icon:active {
  outline: none !important;
  box-shadow: none !important;
}

.header-icon:focus-visible {
  outline: none !important;
}
</style>



