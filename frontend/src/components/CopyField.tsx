import { IconCopy } from "@tabler/icons-react";
import { Input } from "antd";

export default function CopyField({ text }: { text: string }) {
    return <Input.Search
        enterButton={<IconCopy size={20} />}
        value={text}
        onSearch={() => navigator.clipboard.writeText(text)}
    />;
}