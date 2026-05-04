import api from "./api";

export const getDashboard = (params) => {
  return api.get("/dashboard", { params });
};

export const createManualInsight = (data) => {
  return api.post("insights/manual", data);
};
