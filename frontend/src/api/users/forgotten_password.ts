import type { ForgottenPassword } from "@/types/user";
import { customFetch } from "../customFetch";

export async function checkForgottenPasswordValidity(
  code: string,
): Promise<ForgottenPassword> {
  const resp = await customFetch(`/api/forgotten_passwords/${code}/is-valid`);

  return await resp.json();
}

export async function setNewPassword(
  code: string,
  newPassword: string,
): Promise<void> {
  await customFetch(`/api/forgotten_passwords/${code}/set-password`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ newPassword }),
  });
}
