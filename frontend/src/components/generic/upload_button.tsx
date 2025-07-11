import type { ReactNode, RefObject } from "react";
import Button from "./button";
import { IconUpload } from "@tabler/icons-react";

type Props = {
    children?: ReactNode;
    onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
    onBlur?: React.FocusEventHandler<HTMLInputElement>;
    name?: string;
    ref: RefObject<HTMLInputElement>;
};

export default function UploadButton({ children, onChange, onBlur, name, ref }: Props) {
    return <>
        <input
            type="file"
            ref={ref}
            onChange={onChange}
            onBlur={onBlur}
            name={name}
            className="hidden"
            multiple={false}
        />

        <Button onClick={() => ref?.current.click()}>
            {
                children || <>
                    <IconUpload size={18} />
                    <span className="ml-2">Upload</span>
                </>
            }
        </Button>
    </>
}