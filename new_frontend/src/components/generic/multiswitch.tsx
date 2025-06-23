// Ugly ass component
// chatgpt + stupid brain work
// Should be properly rewritten some day
// will do the job for now

// @TODO: Make the focus-within stuff to show focus properly

import React from "react";
import { IconX, IconCheck, IconQuestionMark } from "@tabler/icons-react";

type SwitchValue = boolean | null;

interface TriStateSwitchProps {
  id: string;
  value: SwitchValue;
  onChange: (next: SwitchValue) => void;
  label?: string;
  disabled?: boolean;
  inputProps?: React.InputHTMLAttributes<HTMLInputElement>;
  className?: string;
}

export default function TriStateSwitchRadio({
  id,
  value,
  onChange,
  label,
  disabled = false,
  inputProps = {},
  className = "",
}: TriStateSwitchProps) {
  const knobStyle: React.CSSProperties = {
    left: value === false ? "calc(0% + 5px)" : value === null ? "calc(50%)" : "calc(100% - 5px)",
    transform:
      value === false
        ? "translateX(0%)"
        : value === null
          ? "translateX(-50%)"
          : "translateX(-100%)",
  };

  const trackColor =
    value === true
      ? "bg-success-400"
      : value === false
        ? "bg-error-500"
        : "bg-synthbg-500";

  const radio = (
    key: string,
    val: SwitchValue,
    srLabel: string,
    posClass: string
  ) => (
    <label
      key={key}
      className={`absolute inset-y-0 ${posClass} w-1/3 cursor-pointer`}
    >
      <span className="sr-only">{srLabel}</span>
      <input
        type="radio"
        name={id}
        value={String(val)}
        checked={value === val}
        disabled={disabled}
        onChange={() => onChange(val)}
        className="sr-only"
        {...inputProps}
      />
    </label>
  );

  return (
    <div className="flex items-center gap-2 w-full justify-between">
      {label && (
        <span id={`${id}-label`} className="text-sm select-none">
          {label}
        </span>
      )}

      <div
        role="radiogroup"
        aria-labelledby={label ? `${id}-label` : undefined}
        className={`relative inline-flex items-center w-12 h-7 ${className} w-[75px]`}
      >
        <span
          aria-hidden
          className={`absolute inset-0 rounded-full transition-colors duration-200 ${trackColor}`}
        />

        <div className="flex flex-row justify-between items-center absolute left-[5px] top-[5px] botton-[5px] right-[5px] text-white z-500 pointer-events-none">
          <IconX aria-hidden="true" size={18} />
          <IconQuestionMark aria-hidden="true" size={18} />
          <IconCheck aria-hidden="true" size={18} />
        </div>

        <span
          aria-hidden="true"
          style={knobStyle}
          className="absolute top-1/2 -translate-y-1/2 w-5 h-5 rounded-full bg-synthbg-800 shadow transition-[left,transform] duration-200"
        />

        {radio("false", false, "Désactivé", "left-0")}
        {radio("null", null, "Indéterminé", "left-1/3")}
        {radio("true", true, "Activé", "right-0")}
      </div>
    </div>
  );
}
