export type Collection<T> = {
    member: T[];
    totalItems: number;
    view: {
        first: number;
        last: number;
        previous: number | null;
        next: number | null;
        itemsPerPage: number;
        current: number;
    };
}

export type EnumValue = {
    "@id": string;
    id: string;
    label: string;
    name: string;
    value: string;
};