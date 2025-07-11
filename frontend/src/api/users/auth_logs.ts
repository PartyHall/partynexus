import type { Collection } from "@/types";
import { customFetch } from "../customFetch";
import type { UserAuthLog } from "@/types/user";

export default async function getUserAuthenticationLogs(id: number | string, page: number = 1): Promise<Collection<UserAuthLog>> {
    const resp = await customFetch(`/api/users/${id}/auth-logs?page=${page}`);

    return await resp.json();
}