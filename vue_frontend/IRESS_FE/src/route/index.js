import { createRouter, createWebHistory } from "vue-router";

import Dashboard from "../views/DashboardView.vue";
import Jobs from "../views/JobsView.vue";
import Manual from "../views/ManualInsight.vue";
import Accounts from "../views/ViewAccounts.vue";
import Login from "../views/LoginView.vue";
import Register from "../views/RegisterView.vue";

const routes = [
  { path: "/", component: Dashboard, meta: { requiresAuth: true } },
  { path: "/jobs", component: Jobs, meta: { requiresAuth: true } },
  { path: "/manual", component: Manual, meta: { requiresAuth: true } },
  { path: "/Accounts", component: Accounts, meta: { requiresAuth: true } },
  { path: "/login", component: Login, meta: { requiresGuest: true } },
  { path: "/register", component: Register, meta: { requiresGuest: true } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');

  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else if (to.meta.requiresGuest && token) {
    next('/');
  } else {
    next();
  }
});

export default router;
