import { customFetch } from "@/api/customFetch";
import type { Collection, EnumValue } from "@/types";
import { useQuery } from "@tanstack/react-query";
import type { FieldError } from "react-hook-form";

type EnumSelectProps = {
    enumName: string;
    label: string;
    error?: FieldError | string;
} & React.SelectHTMLAttributes<HTMLSelectElement>;

export default function EnumSelect({ enumName, label, disabled, error, ...props }: EnumSelectProps) {
    const { isPending, error: loadError, data } = useQuery({
        queryKey: ['enum', enumName],
        queryFn: async (): Promise<Collection<EnumValue>> => await (await customFetch(`/api/${enumName}`)).json()
    });

    return (
        <label className="flex flex-col">
            {label}:
            {
                !isPending && <select
                    className="flex-1"
                    disabled={disabled || isPending || !!loadError}
                    {...props}
                >
                    {
                        data && Object.values(data.member).map(entry => (
                            <option key={entry.id} value={entry.value}>
                                {entry.label}
                            </option>
                        ))
                    }
                </select>
            }

            {
                loadError && <span className="text-red-glow">Error loading options: {loadError.message}</span>
            }
            {
                error && (
                    <span className="text-sm text-red-glow">
                        {typeof error === "string" ? error : error.message}
                    </span>
                )
            }
        </label>
    );
}