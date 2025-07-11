import type { Appliance } from "./appliance";

export type User = {
    '@id': string;

    id: number;
    username: string;
    firstname: string;
    lastname: string;
    email: string;
    language: string;
    bannedAt: string | null;
    passwordSet: boolean;
    appliances: Appliance[];
};

export type MinimalUser = {
    '@id': string;
    id: number;
    username: string;
    firstname: string;
    lastname: string;
};

export type MagicPassword = {
    '@id': string;
    id: number;
    user: string;
    createdAt: string;
    code: string;
    used: boolean;
    url: string;
}

export type UserAuthLog = {
    '@id': string;
    id: number;
    user: string;
    ip: string;
    authedAt: string;
};