import concludeEvent from "@/api/events/conclude";
import ConfirmButton from "@/components/generic/confirm_button";
import Title from "@/components/generic/title";
import { enqueueSnackbar } from "notistack";
import { useTranslation } from "react-i18next";

export default function SettingsConclude({
  eventId,
  eventName,
}: {
  eventId: string;
  eventName: string;
}) {
  const { t } = useTranslation();

  const doConcludeEvent = async () => {
    try {
      await concludeEvent(eventId);
    } catch (err) {
      console.error(err);
      enqueueSnackbar(t("generic.error.generic"), { variant: "error" });
    }
  };

  return (
    <div className="flex flex-col gap-2">
      <Title level={2} noMargin>
        {t("events.settings.conclude")}
      </Title>
      <p>{t("events.settings.conclude_description")}</p>

      <ConfirmButton
        className="w-full mt-4"
        tTitle={"events.settings.conclude"}
        tDescription={"events.settings.conclude_confirm_desc"}
        tDescriptionArgs={{ eventName }}
        onConfirm={doConcludeEvent}
      >
        {t("events.settings.conclude")}
      </ConfirmButton>
    </div>
  );
}
