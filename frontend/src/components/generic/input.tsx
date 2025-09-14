import { cloneElement, isValidElement, useState, type InputHTMLAttributes, type MouseEventHandler, type ReactNode } from "react";
import { Controller, type Control, type FieldError } from "react-hook-form";
import Button from "./button";
import { IconAsterisk, IconCopy, IconEye, IconEyeOff } from "@tabler/icons-react";
import { enqueueSnackbar } from "notistack";
import dayjs from "dayjs";

type InputProps = {
    label?: string;
    placeholder?: string;
    icon?: ReactNode;
    action?: ReactNode;
    error?: FieldError | string;
    hideRequired?: boolean;
} & InputHTMLAttributes<HTMLInputElement>;

export default function Input({ icon, action, label, error, className, hideRequired, ...props }: InputProps) {
    if (props.type === 'hidden') {
        return <input className={`block w-full ${icon ? '!pl-10' : ''} ${action ? '!pr-8' : ''} disabled:bg-synthbg-600! disabled:text-synthfg-400!`} {...props} />
    }

    return (
        <label className={`flex flex-col w-full gap-0.5̀ ${className || ''}`}>
            {
                label
                && <span>
                    {label}:
                    {!hideRequired && props.required && <span className="text-red-glow"> * </span>}
                </span>
            }
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

export function PasswordInput({ ...props }: Omit<InputProps, 'type' | 'icon'>) {
    const [visible, setVisible] = useState<boolean>(false);

    return <Input
        type={visible ? 'text' : 'password'}
        icon={<IconAsterisk />}
        action={[
            <Button onClick={() => setVisible(!visible)} type="button" className="h-full bg-transparent!">
                { visible ? <IconEyeOff /> : <IconEye /> }
            </Button>
        ]}
        {...props}
    />;
}

// Fuck react hook form
// I need to move to Tanstack form at some point
// but i'm already learning enough libs for now
export function DateTimeInput({ label, name, control, disabled, error, required }: { label?: string, name: string, control: Control<any>, disabled?: boolean, error?: string | FieldError, required?: boolean }) {
    return (
        <Controller
            control={control}
            name={name}
            render={({ field }) => <Input
                type="datetime-local"
                label={label}
                value={field.value ? dayjs(field.value).format('YYYY-MM-DDTHH:mm') : ''}
                error={error}
                onChange={(e) => field.onChange(dayjs(e.target.value).toISOString())}
                disabled={disabled}
                required={required}
            />
            }
        />
    )
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