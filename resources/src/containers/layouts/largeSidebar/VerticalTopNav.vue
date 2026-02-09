<template>
  <div class="vertical-top-nav" :class="{ 'is-collapsed': getVerticalSidebarCollapsed }">
    <div class="nav-left">
      <!-- Menu Toggle -->
      <!-- Menu Toggle -->
      <button @click="toggleSidebar" class="menu-toggle header-btn btn-gray header-icon" type="button" aria-label="Toggle menu">
        <indent-decrease v-if="!getVerticalSidebarCollapsed" :size="24" />
        <indent-increase v-else :size="24" />
      </button>
    </div>

    <div class="nav-right">
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

      <button class="header-btn btn-gray" @click="toggleCustomizer" title="Customize">
        <sliders size="18"></sliders>
      </button>

      <button class="header-btn btn-gray" @click="handleFullScreen" title="Fullscreen">
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
</template>

<script>
import Util from "./../../../utils";
import { mapGetters, mapActions } from "vuex";

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
  name: "VerticalTopNav",
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
    return {};
  },

  computed: {
    ...mapGetters([
      "currentUser",
      "currentUserPermissions",
      "notifs_alert",
      "show_language",
      "getAvailableLanguages",
      "getVerticalSidebarCollapsed"
    ]),
    ...mapGetters("config", ["getThemeMode"]),

    currentLanguage() {
      if (!this.getAvailableLanguages || !this.$i18n.locale) return null;
      return this.getAvailableLanguages.find(lang => lang.locale === this.$i18n.locale) || this.getAvailableLanguages[0];
    }
  },

  methods: {
    ...mapActions(["logout"]),
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

    toggleCustomizer() {
      Fire.$emit("toggle-customizer");
    },

    toggleSidebar(event) {
      console.log('Menu toggle clicked!');
      console.log('Screen width:', window.innerWidth);
      console.log('Event:', event);
      // Emit event to VerticalSidebar to toggle collapse state
      Fire.$emit("toggleVerticalSidebar");
      console.log('Event emitted: toggleVerticalSidebar');
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
.vertical-top-nav .dropdown-menu {
  border-radius: 16px !important;
  border: none !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
  padding: 8px !important;
  min-width: 200px !important;
  margin-top: 12px !important;
  border: 1px solid rgba(0,0,0,0.05) !important;
}

.vertical-top-nav #notif-dd .dropdown-menu { min-width: 320px !important; }
.vertical-top-nav #lang-dd .dropdown-menu { min-width: 220px !important; }

/* Dark mode dropdown menu */
body.dark-theme .vertical-top-nav .dropdown-menu {
  background: #1f2937 !important;
  border: 1px solid #374151 !important;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3) !important;
}
</style>

<style scoped>
.vertical-top-nav {
  position: fixed;
  top: 0;
  left: 240px;
  right: 0;
  height: 70px;
  background: #fff;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  z-index: 100;
  transition: all 0.3s ease;
}

.vertical-top-nav.is-collapsed {
  left: 0px;
  padding-left: 32px;
}

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
  padding: 10px;
}

.lang-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 15px;
  border-radius: 6px;
  cursor: pointer;
  color: #333;
  text-decoration: none;
  transition: background 0.3s;
}

.lang-item:hover {
  background: #f5f5f5;
}

.flag-icon {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  object-fit: cover;
}

.notification-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 15px 20px;
  border-bottom: 1px solid #f0f0f0;
  transition: background 0.3s;
  cursor: pointer;
}

.notification-item:hover {
  background: #f9fafb;
}

.notification-item:last-child {
  border-bottom: none;
}

.notif-icon {
  font-size: 24px;
  line-height: 1;
  flex-shrink: 0;
}

.notif-content {
  flex: 1;
}

.notif-content p {
  margin: 0;
  font-size: 14px;
  color: #666;
  line-height: 1.5;
}

.notif-content a {
  color: #2d8cff;
  text-decoration: none;
  display: block;
}

.notif-content a:hover {
  color: #5a2a80;
}

.user-dropdown-menu {
  min-width: 200px;
}

.dropdown-header {
  padding: 15px;
  border-bottom: 1px solid #e0e0e0;
  font-weight: 600;
  color: #333;
}

.dropdown-item {
  padding: 12px 20px;
  color: #666;
  text-decoration: none;
  display: block;
  transition: all 0.3s;
}

.dropdown-item:hover {
  background: #f5f5f5;
  color: #2d8cff;
}

/* RTL Support */
html[dir="rtl"] .vertical-top-nav {
  left: auto;
  right: 240px;
}

html[dir="rtl"] .vertical-collapsed .vertical-top-nav {
  right: 70px;
  left: auto;
}

/* Dark Mode */
body.dark-theme .vertical-top-nav {
  background: #202020;
  box-shadow: 0 1px 15px rgba(0, 0, 0, 0.2), 0 1px 6px rgba(0, 0, 0, 0.2);
}

body.dark-theme .menu-toggle div {
  background: #e0e0e0;
}

body.dark-theme .menu-toggle:hover {
  background: rgba(118, 75, 162, 0.1);
}

body.dark-theme .menu-toggle:hover div {
  background: #fff;
}

body.dark-theme .nav-icon-btn {
  background: #202020;
  border-color: #2d2d44;
  color: #d0d0d0;
}

body.dark-theme .nav-icon-btn:hover {
  background: #2d2d44;
  border-color: #2d8cff;
  color: #fff;
}

body.dark-theme .lang-item {
  color: #e0e0e0;
}

body.dark-theme .lang-item:hover {
  background: #2d2d44;
}

body.dark-theme .notification-item {
  border-bottom-color: #2d2d44;
}

body.dark-theme .notification-item:hover {
  background: #2d2d44;
}

body.dark-theme .notif-content p {
  color: #d0d0d0;
}

body.dark-theme .notif-content a {
  color: #a78bfa;
}

body.dark-theme .notif-content a:hover {
  color: #c4b5fd;
}

body.dark-theme .dropdown-header {
  border-bottom-color: #2d2d44;
  color: #e0e0e0;
}

body.dark-theme .dropdown-item {
  color: #d0d0d0;
}

body.dark-theme .dropdown-item:hover {
  background: #2d2d44;
  color: #fff;
}

/* Mobile adjustments */
@media (max-width: 768px) {
  .vertical-top-nav {
    left: 0 !important;
    padding: 0 15px;
    z-index: 100 !important;
    position: fixed !important;
  }

  .vertical-top-nav.is-collapsed {
    left: 0 !important;
  }

  /* Hide fullscreen button on mobile */
  .fullscreen-btn {
    display: none !important;
  }

  .nav-left {
    display: flex !important;
    align-items: center;
    z-index: 1201 !important;
    position: relative;
  }

  .menu-toggle {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    width: 44px !important;
    height: 38px !important;
    cursor: pointer !important;
    margin-right: 10px !important;
    background: none !important;
    border: none !important;
    padding: 10px !important;
    outline: none !important;
    z-index: 1202 !important;
    pointer-events: auto !important;
    position: relative !important;
  }

  .menu-toggle:focus,
  .menu-toggle:active {
    outline: none !important;
    box-shadow: none !important;
  }

  .menu-toggle div {
    display: block !important;
    width: 24px !important;
    height: 2px !important;
    background: #47404f !important;
    border-radius: 2px !important;
    pointer-events: none !important;
  }

  .btn-text {
    display: none;
  }

  .btn-primary {
    padding: 8px 12px;
    font-size: 13px;
  }

  /* Make POS button look like icon buttons on mobile */
  .nav-right .btn.btn-primary {
    width: 44px;
    height: 44px;
    padding: 0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    background: #2d8cff;
    color: #fff;
    border: 1px solid #2d8cff;
  }

  .nav-right .btn.btn-primary i {
    font-size: 20px;
    color: #fff;
    line-height: 1;
  }

  html[dir="rtl"] .vertical-top-nav {
    right: 0;
    left: auto;
  }
}

/* Additional mobile breakpoints */
@media (max-width: 480px) {
  .menu-toggle {
    display: flex !important;
    width: 44px !important;
    height: 38px !important;
  }
  
  .menu-toggle:focus,
  .menu-toggle:active {
    outline: none !important;
    box-shadow: none !important;
  }
  
  .menu-toggle div {
    width: 24px !important;
  }
}

@media (max-width: 320px) {
  .menu-toggle {
    display: flex !important;
    width: 44px !important;
    height: 38px !important;
  }
  
  .menu-toggle:focus,
  .menu-toggle:active {
    outline: none !important;
    box-shadow: none !important;
  }
  
  .menu-toggle div {
    width: 24px !important;
  }
}
</style>

