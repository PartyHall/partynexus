import type { User } from "@/types/user";
import { customFetch } from "../customFetch";
import type { Collection } from "@/types";

export async function getUser(id: number|string): Promise<User> {
    const resp = await customFetch(`/api/users/${id}`, { method: 'GET' });

    return await resp.json();
}

type GetCollectionParams = {
    search: string;
    pageParam?: number;
    showBanned?: boolean;
};

export async function getUsers({
    search = '',
    pageParam = 1,
    showBanned = false
}: GetCollectionParams): Promise<Collection<User>> {
    const params = new URLSearchParams({
        page: pageParam.toString(),
        showBanned: showBanned ? 'true' : 'false',
    });

    if (search && search.trim().length > 0) {
        params.set('username', search);
    }

    const resp = await customFetch(`/api/users?${params.toString()}`);

    return await resp.json();
}

export type UpsertUser = {
    username: string;
    email: string;
    firstname: string;
    lastname: string;
    language: string;
}

export async function createUser(user: UpsertUser): Promise<User> {
    const resp = await customFetch('/api/users', {
        method: 'POST',
        body: JSON.stringify(user),
    });

    return await resp.json();
}

export async function updateUser(id: number|string, user: UpsertUser): Promise<User> {
    const resp = await customFetch(`/api/users/${id}`, {
        method: 'PATCH',
        body: JSON.stringify(user),
    });

    return await resp.json();
}