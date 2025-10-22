export class HttpError extends Error {
  submessage?: string;
  status: number;
  body?: unknown;

  constructor({
    message,
    submessage,
    status,
    body,
  }: {
    message: string;
    submessage?: string;
    status: number;
    body?: unknown;
  }) {
    super(message);

    this.submessage = submessage;
    this.name = "HttpError";
    this.status = status;
    this.body = body;
  }
}

export class HttpServerError extends HttpError {
  constructor({
    message = "Internal server error",
    submessage,
    status = 500,
    body,
  }: {
    message?: string;
    submessage?: string;
    status?: number;
    body?: unknown;
  } = {}) {
    super({ message, submessage, status, body });
    this.name = "HttpServerError";
  }
}

export class ProblemDetailsError extends HttpError {
  constructor(data: any) {
    super({
      message: data.title,
      submessage: data.detail,
      status: data.status,
      body: JSON.stringify(data),
    });
  }
}

export function makeError(status: number): HttpError {
  return new HttpError({
    message: `errors.${status}.title`,
    submessage: `errors.${status}.message`,
    status: status,
  });
}
