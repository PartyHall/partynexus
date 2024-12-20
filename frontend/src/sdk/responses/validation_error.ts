export default class ValidationError {
    fieldName: string;
    errors: string[];

    constructor(fieldName: string, errors: string[]) {
        this.fieldName = fieldName;
        this.errors = errors;
    }

    getText(): string {
        return this.errors.join(', ');
    }
}

export class ValidationErrors {
    public errors: ValidationError[];

    constructor(errors: ValidationError[]) {
        this.errors = errors;
    }
}
