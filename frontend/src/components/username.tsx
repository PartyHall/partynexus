import type { MinimalUser, User } from "@/types/user";
import type { ReactNode } from "react";

type Props = {
    user: User | MinimalUser;
    prefix?: ReactNode;
    noStyle?: boolean;
};

export default function Username({ user, prefix, noStyle }: Props) {
    let attr = {};

    if (!noStyle) {
        attr = {
            className: "text-green-glow",
        };
    }

    return <span {...attr}>
        {prefix && <>{prefix} </>}
        {(user.firstname || user.lastname) && <>{user.firstname} {user.lastname}</>}
        {!user.firstname && !user.lastname && user.username}
    </span>;
}