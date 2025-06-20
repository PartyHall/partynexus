import type { User } from "@/types/user";
import { customFetch } from "../customFetch";

export async function getUser(id: number): Promise<User> {
    const resp = await customFetch(`/api/users/${id}`, { method: 'GET' });

    return await resp.json();
}