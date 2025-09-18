import { useRef, type ReactNode } from "react";
import Button from "./button";
import { IconUpload } from "@tabler/icons-react";
import {
  useController,
  type Control,
  type FieldValues,
  type Path,
  type RegisterOptions,
} from "react-hook-form";

type Props<T extends FieldValues> = {
  children?: ReactNode;
  name: Path<T>;
  control: Control<T>;
  rules: RegisterOptions<T, Path<T>>;

  accept?: string;
  disabled?: boolean;
  onChange?: (val: File | null) => void;
  hideFilename?: boolean;
  hideError?: boolean;
};

export default function UploadButton<T extends FieldValues>({
  children,
  name,
  control,
  rules,
  hideError,
  hideFilename,
  onChange,
  accept,
  disabled,
}: Props<T>) {
  const fileInputRef = useRef<HTMLInputElement>(null);
  const { field, fieldState } = useController({ name, control, rules });

  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] || null;
    field.onChange(file || null);

    onChange?.(file || null);
  };

  return (
    <>
      <input
        ref={fileInputRef}
        type="file"
        onChange={handleFileChange}
        name={name}
        className="hidden"
        multiple={false}
        accept={accept}
        id={`file-${name}`}
      />

      {
        /** @TODO: Translate if needed */
        !hideFilename && field.value && (
          <div className="mt-2 text-sm text-gray-600">
            File: {field.value.name}
          </div>
        )
      }

      {!hideError && fieldState.error && (
        <div className="mt-1 text-sm text-red-500">
          {fieldState.error.message}
        </div>
      )}

      <Button
        type="button"
        onClick={() => fileInputRef?.current?.click()}
        disabled={disabled}
      >
        {children || (
          <>
            <IconUpload size={18} />
            <span className="ml-2">Upload</span>
          </>
        )}
      </Button>
    </>
  );
}
