import type { User } from "@/types/user";
import { customFetch } from "../customFetch";
import type { Collection } from "@/types";

export async function getUser(id: number): Promise<User> {
    const resp = await customFetch(`/api/users/${id}`, { method: 'GET' });

    return await resp.json();
}

type GetCollectionParams = {
    search: string;
    page?: number;
    showBanned?: boolean;
};

export async function getUsers({
    search = '',
    page = 1,
    showBanned = false
}: GetCollectionParams): Promise<Collection<User>> {
    const params = new URLSearchParams({
        page: page.toString(),
        showBanned: showBanned ? 'true' : 'false',
    });

    if (search && search.trim().length > 0) {
        params.set('username', search);
    }

    const resp = await customFetch(`/api/users?${params.toString()}`);

    return await resp.json();
}