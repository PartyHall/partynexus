import i18n from "i18next";

export default function setI18NLanguage(lang: string) {
  const userLanguage = lang || "/api/languages/en_US";
  const stripedLanguage = userLanguage.replace("/api/languages/", "");
  i18n.changeLanguage(stripedLanguage);
}
