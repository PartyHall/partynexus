import { useTranslation } from "react-i18next";
import { useNexusTitle } from "./useTitle";

export default function useTranslatedTitle(titleKey: string, suffix?: string, options?: any) {
    const { t } = useTranslation();
    const mainTitle = t(titleKey, options).toString();
    const fullTitle = suffix ? `${mainTitle} - ${t(suffix, options).toString()}` : mainTitle;

    useNexusTitle(fullTitle);
}