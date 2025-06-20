import type { JSX } from "react";

type Props = {
    children: React.ReactNode;
    level?: number;
    className?: string;
    center?: boolean;
    noMargin?: boolean;
};

const LevelGlowMap = [
    "text-blue-glow",
    "text-yellow-glow",
    "text-red-glow",
];

const SizeMap = [
    "text-2xl",
    "text-xl",
    "text-l",
];

export default function Title({ children, level, className, center, noMargin }: Props) {
    const Component = `h${level || 1}` as keyof JSX.IntrinsicElements;

    const levelIdx = (level || 1) - 1;

    const levelClassName = LevelGlowMap[levelIdx] || "text-blue-glow";
    const sizeClassName = SizeMap[levelIdx] || "text-2xl";

    const margin = noMargin ? '' : "mb-4 mt-2";

    return (
        <Component className={`${levelClassName} ${sizeClassName} ${margin} font-bold ${center ? "text-center" : ""} ${className || ""}`}>
            {children}
        </Component>
    );
}