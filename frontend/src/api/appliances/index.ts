import type { Appliance } from "@/types/appliance";
import { customFetch } from "../customFetch";

export async function getAppliance(id: number | string): Promise<Appliance> {
  const resp = await customFetch(`/api/appliances/${id}`, { method: "GET" });

  return await resp.json();
}
