/** CHATGPT-ASS FILE */

type SwitchProps = {
    id: string;
    checked: boolean;
    onChange: (checked: boolean) => void;
    label?: string;
    disabled?: boolean;
    inputProps?: React.InputHTMLAttributes<HTMLInputElement>;
};

export default function Switch({
    id,
    checked,
    onChange,
    label,
    disabled = false,
    inputProps = {},
}: SwitchProps) {
    return (
        <div className="flex items-center gap-2 w-full justify-between">
            {
                label && (
                    <label htmlFor={id} className="text-sm select-none">
                        {label}
                    </label>
                )
            }
            <label className="relative inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    id={id}
                    checked={checked}
                    disabled={disabled}
                    onChange={onChange ? (e) => onChange(e.target.checked) : undefined}
                    className="sr-only peer"
                    role="switch"
                    aria-checked={checked}
                    {...inputProps}
                />
                <div className="w-11 h-6 bg-synthbg-500 rounded-full peer-checked:bg-primary-400 transition-colors duration-300 peer-focus-visible:ring-2 peer-focus-visible:ring-synthbg-900 peer-focus-visible:ring-offset-2" />
                <span className="absolute left-1 top-1 w-4 h-4 bg-synthbg-800 rounded-full transition-transform duration-300 translate-x-0 peer-checked:translate-x-5 shadow" />
            </label>
        </div>
    );
}