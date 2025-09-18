import { create } from "zustand";
import Cookies from "js-cookie";

type TokenUser = {
  id: number;
  iri: string;
  iat: number;
  exp: number;
  roles: string[];
  username: string;
  language: string;
};

export type StoreType = {
  token: string | null;
  refreshToken: string | null;
  tokenUser: TokenUser | null;
  setToken: (token: string | null, refreshToken: string | null) => void;
  doRefresh: () => Promise<void>;
  isGranted: (role: string) => boolean;
};

const decodeToken = (token: string) => JSON.parse(atob(token.split(".")[1]));

export const useAuthStore = create<StoreType>()((set, get) => ({
  token: localStorage.getItem("token") || null,
  refreshToken: localStorage.getItem("refreshToken") || null,
  tokenUser: localStorage.getItem("token")
    ? decodeToken(localStorage.getItem("token")!)
    : null,
  setToken: (token: string | null, refreshToken: string | null) =>
    set(() => {
      if (!token || !refreshToken) {
        localStorage.removeItem("token");
        localStorage.removeItem("refreshToken");
        Cookies.remove("mercureAuthorization");

        return {
          token: null,
          refreshToken: null,
          tokenUser: null,
        };
      }

      localStorage.setItem("token", token);
      localStorage.setItem("refreshToken", refreshToken);
      Cookies.set("mercureAuthorization", token);

      return {
        token,
        refreshToken,
        tokenUser: decodeToken(token),
      };
    }),
  doRefresh: async () => {
    const resp = await fetch("/api/refresh", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ refresh_token: get().refreshToken }),
    });

    if (!resp.ok) {
      get().setToken(null, null);
      return;
    }

    try {
      const { token, refresh_token } = await resp.json();

      get().setToken(token, refresh_token);
    } catch {
      get().setToken(null, null);
      return;
    }
  },
  isGranted: (role: string) => {
    const tokenUser = get().tokenUser;
    if (!tokenUser) {
      return false;
    }

    return (
      tokenUser.roles.includes(role) || tokenUser.roles.includes("ROLE_ADMIN")
    );
  },
}));
