import { createLink } from "@tanstack/react-router";
import { forwardRef } from "react";

type Props = {
    children: React.ReactNode;
    className?: string;
    noGlow?: boolean;
    customGlow?: string;
};

export default function Card({ children, className, noGlow, customGlow }: Props) {
    const glow = `shadow-md ${customGlow ? customGlow : 'box-purple-glow'}`;

    return <div
        className={`
            ${className}
            bg-synthbg-800
            rounded-md
            p-4
            ${noGlow ? '' : glow}
        `}
    >
        {children}
    </div>
}

interface CustomCardLinkProps extends React.AnchorHTMLAttributes<HTMLAnchorElement> {
    className?: string;
    noGlow?: boolean;
}

const CustomCardLink = forwardRef<HTMLAnchorElement, CustomCardLinkProps>(
    ({ className, noGlow, ...props }, ref) => {
        const glow = `shadow-md box-purple-glow`;

        return (
            <a
                ref={ref}
                {...props}
                className={`
                    no-underline
                    no-hover
                    ${className}
                    bg-synthbg-800
                    rounded-md
                    p-4
                    transition-shadow
                    duration-300
                    ${noGlow ? '' : glow}
                `}
            />
        )
    },
)

export const CardLink = createLink(CustomCardLink)