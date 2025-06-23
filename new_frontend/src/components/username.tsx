import type { User } from "@/types/user";
import type { ReactNode } from "react";

export default function Username({ user, prefix }: { user: User, prefix?: ReactNode }) {
    return <span className="text-green-glow">
        {prefix && <>{prefix} </>}
        {(user.firstname || user.lastname) && <>{user.firstname} {user.lastname}</>}
        {!user.firstname && !user.lastname && user.username}
    </span>;
}