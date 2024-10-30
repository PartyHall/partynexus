import { Button, Flex, Typography } from "antd";
import { PnListUser } from "../../sdk/responses/user";

import { useAuth } from "../../hooks/auth";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import UserListCard from "../../components/admin/user_list_card";
import SearchablePaginatedList from "../../components/SearchablePaginatedList";
import { useTitle } from "ahooks";

export default function AdminUsersPage() {
    const { t } = useTranslation();
    const { api } = useAuth();

    const navigate = useNavigate();

    useTitle(`${t('users.title')} - PartyHall`);

    return <Flex vertical style={{ height: '100%' }} gap={8}>
        <Typography.Title className="blue-glow">{t('users.title')}</Typography.Title>

        <SearchablePaginatedList
            doSearch={async (query: string, page: number) => api.users.getCollection(query, page, true)}
            renderElement={(elt: PnListUser) => <UserListCard key={elt.iri} user={elt} />}
            extraActions={<Flex>
                <Button onClick={() => navigate('/admin/users/new')}>{t('users.new_user_bt')}</Button>
            </Flex>}
        />
    </Flex>
}