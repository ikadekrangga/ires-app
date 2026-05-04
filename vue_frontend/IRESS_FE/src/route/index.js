import { createRouter, createWebHistory } from "vue-router";

import Dashboard from "../views/DashboardView.vue";
import Jobs from "../views/JobsView.vue";
import Manual from "../views/ManualInsight.vue";
import Accounts from "../views/ViewAccounts.vue";

const routes = [
  { path: "/", component: Dashboard },
  { path: "/jobs", component: Jobs },
  { path: "/manual", component: Manual },
  { path: "/Accounts", component: Accounts },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
