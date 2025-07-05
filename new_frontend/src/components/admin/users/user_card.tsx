import Card, { CardLink } from "@/components/generic/card";
import type { User } from "@/types/user";

type Props = { user: User };

export default function UserCard({ user }: Props) {
    return <CardLink noGlow to="/admin/users/$id" params={{ id: ''+user.id }}>
        <div className="flex flex-col gap-2">
            {
                (user.firstname || user.lastname)
                && <h3 className="text-lg font-semibold">{user.firstname} {user.lastname}</h3>
            }
            {
                !user.firstname && !user.lastname
                && <h3 className="text-lg font-semibold">{user.username}</h3>
            }

            <p className="text-sm text-gray-500">{user.username}</p>
        </div>
    </CardLink>
}