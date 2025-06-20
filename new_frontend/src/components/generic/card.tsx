type Props = {
    children: React.ReactNode;
    className?: string;
};

export default function Card({ children, className }: Props) {
    return <div className={`
        ${className}
        flex-col-center
        bg-synthbg-800
        rounded-lg
        p-4
        shadow-md
        transition-shadow
        duration-300
        border-synthbg-600
        border
        box-purple-glow
    `}>
        {children}
    </div>
}