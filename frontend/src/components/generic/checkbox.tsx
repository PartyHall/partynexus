import { IconCheck } from "@tabler/icons-react";
import React from "react";

interface CheckboxProps extends React.InputHTMLAttributes<HTMLInputElement> {
  checked?: boolean;
  onChange?: (e: React.ChangeEvent<HTMLInputElement>) => void;
  label?: string;
  className?: string;
  labelClassName?: string;
  id?: string;
}

export const Checkbox: React.FC<CheckboxProps> = ({
  checked,
  onChange,
  label,
  className,
  labelClassName,
  id,
  ...rest
}) => {
  return (
    <label
      className={`inline-flex items-center cursor-pointer gap-2 ${className} focus-within:ring-2 focus-within:ring-primary-400 focus-within:ring-offset-2 focus-within:ring-offset-synthbg-900`}
      role="checkbox"
      htmlFor={id}
    >
      <input
        id={id}
        type="checkbox"
        checked={checked}
        onChange={onChange}
        className={`absolute opacity-0 w-0 h-0 pointer-events-none`}
        aria-checked={checked}
        {...rest}
      />
      <span
        aria-hidden="true"
        className={`
                relative
                inline-block
                w-5
                h-5
                rounded-sm
                bg-synthbg-500
            `}
      >
        {checked && <IconCheck className="absolute top-0.5 left-0.5 w-4 h-4" />}
      </span>
      {label && <span className={labelClassName}>{label}</span>}
    </label>
  );
};
