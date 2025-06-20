import type { FieldValues, Path, UseFormSetError } from "react-hook-form";
import { HttpError } from "./http_error";

export interface Violation {
    propertyPath: string;
    message: string;
    code: string;
}

export interface FormValidationError {
    field: Record<string, string[]>;
    global: string[];
}

export class ValidationError extends HttpError {
    public violations: FormValidationError;

    constructor(violations: Violation[]) {
        super({ message: 'Validation error', submessage: 'Invalid input', status: 422, body: violations });
        this.name = 'ValidationError';
        this.violations = ValidationError.parseViolations(violations);
    }

    //#region Chatgpt-ass magical methods
    private static parseViolations(violations: Violation[]): FormValidationError {
        return violations.reduce<FormValidationError>(
            (acc, v) => {
                const rawPath = (v.propertyPath ?? '').trim();

                if (rawPath === '') {
                    acc.global.push(v.message);
                } else {
                    const rhfPath = rawPath.replace(/\[(\d+)\]/g, '.$1');
                    acc.field[rhfPath] = [...(acc.field[rhfPath] ?? []), v.message];
                }

                return acc;
            },
            { field: {}, global: [] }
        );
    }

    public applyToReactHookForm<T extends FieldValues>(setError: UseFormSetError<T>): string[] {
        Object.entries(this.violations.field).forEach(([path, messages]) => {
            messages.forEach((msg) =>
                setError(path as Path<T>, { type: 'server', message: msg })
            );
        });

        return this.violations.global;
    }
    //#endregion
}