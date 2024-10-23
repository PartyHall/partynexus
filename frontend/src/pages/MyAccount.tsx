import { Button, Flex } from "antd";
import { useAuth } from "../hooks/auth";

export default function MyAccountPage() {
    const {logout} = useAuth();

    return <Flex vertical>
        <Button onClick={logout}>Logout</Button>
    </Flex>
}