import { Button, Flex, Typography } from "antd";
import { useAuth } from "../hooks/auth";
import { useTitle } from "ahooks";
import { useTranslation } from "react-i18next";

export default function MyAccountPage() {
    const {api} = useAuth();
    const {t} = useTranslation();
    const {logout} = useAuth();

    useTitle(t('my_account.title') + ' - PartyHall');

    /**
     * @TODO: Fetch the user from api.tokenUser.iri
     * @TODO: Form to allow the user to update its email & username
     */
    return <Flex vertical gap={8}>
        <Typography.Title className="blue-glow">{t('my_account.title')}</Typography.Title>
        <Typography.Text>My iri: {api.tokenUser?.iri}</Typography.Text>
        <Typography.Text>My username: {api.tokenUser?.username}</Typography.Text>
        <Button onClick={logout}>{t('my_account.logout')}</Button>
    </Flex>
}