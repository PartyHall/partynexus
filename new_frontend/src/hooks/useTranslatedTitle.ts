import { useTranslation } from "react-i18next";
import useTitle from "./useTitle";

export default function useTranslatedTitle(titleKey: string, options?: any) {
    const { t } = useTranslation();

    useTitle(`${t(titleKey, options)} - PartyHall`);
}