import type { ReactNode } from "@tanstack/react-router";
import { cloneElement, isValidElement, type InputHTMLAttributes, type MouseEventHandler } from "react";
import type { FieldError } from "react-hook-form";
import Button from "./button";
import { IconCopy } from "@tabler/icons-react";
import { enqueueSnackbar } from "notistack";

type InputProps = {
    label?: string;
    placeholder?: string;
    icon?: ReactNode;
    action?: ReactNode;
    error?: FieldError | string;
} & InputHTMLAttributes<HTMLInputElement>;

export default function Input({ icon, action, label, error, className, ...props }: InputProps) {
    return (
        <label className={`flex flex-col w-full gap-0.5̀ ${className || ''}`}>
            {label}{label && ':'}
            <div className="w-full relative">
                {
                    icon
                    && isValidElement(icon)
                    && cloneElement(icon, {
                        // @ts-ignore
                        className: `absolute left-1 pr-1 top-1/2 -translate-y-1/2 pointer-events-none border-right border-r-1 border-synthbg-700`,
                        'aria-hidden': true,
                    })
                }
                <input className={`block w-full ${icon ? '!pl-10' : ''} ${action ? '!pr-8' : ''} disabled:bg-synthbg-600! disabled:text-synthfg-400!`} {...props} />
                {
                    action
                    && <div className="absolute right-0 top-1/2 -translate-y-1/2 border-right border-l-1 border-synthbg-700 h-[100%]">
                        {action}
                    </div>
                }
            </div>
            {
                error && (typeof error === "string" ? error.length > 0 : (error.message?.length || 0) > 0) && (
                    <span className="text-sm text-red-glow mt-0.5">
                        {typeof error === "string" ? error : error.message}
                    </span>
                )
            }
        </label>
    );
}

type CopyInputProps = {
    value: string;
    copiedMessage?: string;
} & Omit<InputProps, 'value' | 'readOnly' | 'disabled' | 'error'>;

export function CopyInput({ value, copiedMessage, ...props }: CopyInputProps) {
    const handleCopy: MouseEventHandler<HTMLButtonElement> = event => {
        navigator.clipboard.writeText(value);
        if (copiedMessage) {
            enqueueSnackbar(copiedMessage, {
                variant: 'success',
            });
        }

        event.preventDefault();
    };

    return <Input
        action={<Button variant="secondary" onClick={handleCopy}><IconCopy size={18} /></Button>}
        value={value}
        readOnly
        disabled
        {...props}
    />;
}