import { PnListUser, User } from '../sdk/responses/user';

export default function getUsername(user: PnListUser | User) {
    if (!user.firstname && !user.lastname) {
        return user.username;
    }

    return `${user.firstname ?? ''} ${user.lastname ?? ''}`.trim();
}
