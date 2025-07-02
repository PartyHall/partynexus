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