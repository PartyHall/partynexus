import { customFetch } from "@/api/customFetch";
import type { Collection, EnumValue } from "@/types";
import { useQuery } from "@tanstack/react-query";
import type { FieldError } from "react-hook-form";
import { Checkbox } from "./checkbox";
import { useEffect, useState } from "react";

type EnumCheckboxesProps = {
  enumName: string;
  label?: string;
  error?: FieldError | string;
  className?: string;
  disabled?: boolean;
  align?: "left" | "right";
  defaultValues?: string[];
  onChange?: (values: string[]) => void;
};

export default function EnumCheckboxes({
  enumName,
  disabled,
  error,
  className,
  align,
  defaultValues,
  onChange,
}: EnumCheckboxesProps) {
  const {
    isPending,
    error: loadError,
    data,
  } = useQuery({
    queryKey: ["enum", enumName],
    queryFn: async (): Promise<Collection<EnumValue>> =>
      await (await customFetch(`/api/${enumName}`)).json(),
  });

  const [checkedValues, setCheckedValues] = useState<string[]>(
    defaultValues ?? [],
  );

  useEffect(() => {
    if (onChange) {
      onChange(checkedValues);
    }
  }, [checkedValues]);

  return (
    <>
      {!isPending && (
        <fieldset
          disabled={disabled}
          className={`flex flex-col ${align === "right" ? "items-end" : "items-start"} ${className}`}
        >
          {data &&
            Object.values(data.member).map((entry) => (
              <label
                key={entry.id}
                className={`flex items-center gap-2 ${align === "right" ? "flex-row-reverse" : ""}`}
              >
                <Checkbox
                  id={"checkbox_" + entry.value}
                  checked={checkedValues.includes(entry.value)}
                  onChange={() =>
                    setCheckedValues(
                      checkedValues.includes(entry.value)
                        ? checkedValues.filter((v) => v !== entry.value)
                        : [...checkedValues, entry.value],
                    )
                  }
                  disabled={disabled || isPending || !!loadError}
                />
                <span>{entry.label}</span>
              </label>
            ))}
        </fieldset>
      )}

      {loadError && (
        <span className="text-red-glow">
          Error loading options: {loadError.message}
        </span>
      )}
      {error && (
        <span className="text-sm text-red-glow">
          {typeof error === "string" ? error : error.message}
        </span>
      )}
    </>
  );
}
