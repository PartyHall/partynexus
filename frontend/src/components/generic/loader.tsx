import "@/assets/css/loader.css";

type Props = {
  loading?: boolean;
  className?: string;
};

export default function Loader({ loading, className }: Props) {
  if (!loading) {
    return;
  }

  return <span className={`loader ${className ?? ""}`}></span>;
}
