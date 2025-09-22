import type { RegistrationUser } from "@/types/user";
import { customFetch } from "../customFetch";
import type { LoginResponse } from "../auth";

export default async function registerUserEvent(
  registrationCode: string,
  user: RegistrationUser,
): Promise<LoginResponse> {
  const resp = await customFetch(`/api/register/${registrationCode}`, {
    method: "POST",
    body: JSON.stringify(user),
  });

  return await resp.json();
}
