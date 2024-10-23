import { Flex, Typography } from "antd";
import { ReactNode } from "react";

export default function KeyVal({label, children}: {label: string, children: ReactNode}) {
    return <Flex align="center" gap={4}>
        <Typography.Text className="blue-glow">{label}:</Typography.Text>
        <Typography.Text>{children}</Typography.Text>
    </Flex>
}