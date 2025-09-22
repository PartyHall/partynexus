import { create } from "zustand";

export type StoreType = {
  oauth?: {
    loginUrl: string;
    buttonIcon: string;
    buttonText: string;
    buttonCss: string;
  };
  spotify_enabled: boolean;
};

export const useSettingsStore = create<StoreType>()(() => ({
  oauth: undefined,
  spotify_enabled: false,
}));
