export type Collection<T> = {
    member: T[];
    totalItems: number;
}

export type EnumValue = {
    "@id": string;
    id: string;
    label: string;
    name: string;
    value: string;
};