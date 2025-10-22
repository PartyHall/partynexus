import type { User } from "@/types/user";

export default function getUserName(user: User) {
  if (!user.firstname && !user.lastname) {
    return user.username;
  }

  return `${user.firstname} ${user.lastname}`.trim();
}
