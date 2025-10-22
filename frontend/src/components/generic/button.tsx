import { createLink } from "@tanstack/react-router";
import { forwardRef } from "react";

export type ButtonProps = {
  variant?: "secondary" | "danger" | "success" | "warning" | "info";
} & React.ButtonHTMLAttributes<HTMLButtonElement>;

/**
 * @TODO: Make a variant system for outlined / contained / text buttons
 */

export default function Button({ className, ...props }: ButtonProps) {
  let classes = "";

  switch (props.variant) {
    case "secondary":
      classes = "button-secondary";
      break;
    case "danger":
      classes = "button-danger";
      break;
    case "success":
      classes = "button-success";
      break;
    case "warning":
      classes = "button-warning";
      break;
    case "info":
      classes = "button-info";
      break;
    default:
      classes = "button-primary";
      break;
  }

  return (
    <button className={`button ${classes} ${className || ""}`} {...props}>
      {props.children}
    </button>
  );
}

interface CustomButtonLinkProps
  extends React.AnchorHTMLAttributes<HTMLAnchorElement> {
  variant?: "secondary" | "danger" | "success" | "warning" | "info";
}

const CustomButtonLink = forwardRef<HTMLAnchorElement, CustomButtonLinkProps>(
  ({ variant, ...props }, ref) => {
    let classes = "";

    switch (variant) {
      case "secondary":
        classes = "button-secondary";
        break;
      case "danger":
        classes = "button-danger";
        break;
      case "success":
        classes = "button-success";
        break;
      case "warning":
        classes = "button-warning";
        break;
      case "info":
        classes = "button-info";
        break;
      default:
        classes = "button-primary";
        break;
    }
    return (
      <a
        ref={ref}
        {...props}
        className={`flex gap-0.5 no-underline button no-hover ${classes}`}
      />
    );
  },
);

export const ButtonLink = createLink(CustomButtonLink);
