export class Collection<T> {
    items: T[];

    total: number;

    private constructor(items: T[], total: number) {
        this.items = items;
        this.total = total;
    }

    static fromJson<T>(data: Record<string, any> | null, parse: (x: Record<string, any> | null) => T | null): Collection<T> | null {
        if (!data) {
            return null;
        }

        const items = data
            .member
            .map((x: Record<string, any>) => parse(x))
            .filter((x: T | null) => !!x);

        return new Collection<T>(items, data.totalItems);
    }

    static getEmpty<T>(): Collection<T> {
        return new Collection<T>(
            [],
            0,
        );
    }
}