import { useTranslation } from "react-i18next";
import { useNexusTitle } from "./useTitle";

export default function useTranslatedTitle(titleKey: string, options?: any) {
    const { t } = useTranslation();

    useNexusTitle(t(titleKey, options).toString());
}