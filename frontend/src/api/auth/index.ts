import { HttpError } from "../http_error";

type LoginResponse = {
  token: string;
  refresh_token: string;
};

export async function login(
  username: string,
  password: string,
): Promise<LoginResponse> {
  const resp = await fetch("/api/login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ username, password }),
  });

  const respData = await resp.json();

  if (!resp.ok) {
    throw new HttpError({
      message: respData.message,
      status: respData.code,
    });
  }

  return respData as LoginResponse;
}
