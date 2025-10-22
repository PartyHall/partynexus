import type { Event } from "@/types/event";
import { create } from "zustand";

export type StoreType = {
  event: Event | null;
  setEvent: (event: Event | null) => void;
};

export const useEventStore = create<StoreType>()((set) => ({
  event: null,
  setEvent: (event: Event | null) => set({ event }),
}));

export const useEvent = () => {
  const event = useEventStore((state) => state.event);
  if (!event) {
    throw new Error("Event not set in store");
  }

  return event;
};
