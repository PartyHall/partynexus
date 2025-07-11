import type { ForgottenPassword, User } from "@/types/user";
import { customFetch } from "../customFetch";

export async function generateForgottenPassword(id: number | string): Promise<ForgottenPassword> {
    const resp = await customFetch(`/api/forgotten_passwords`, {
        method: 'POST',
        body: JSON.stringify({ user: `/api/users/${id}` }),
    });

    return await resp.json();
}

export async function banUser(id: number | string): Promise<User> {
    const resp = await customFetch(`/api/users/${id}/ban`, {
        method: 'POST',
        body: JSON.stringify({ }),
    });

    return await resp.json();
}

export async function unbanUser(id: number | string): Promise<User> {
    const resp = await customFetch(`/api/users/${id}/unban`, {
        method: 'POST',
        body: JSON.stringify({ }),
    });

    return await resp.json();
}