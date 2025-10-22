import { setUserRegistrationEnabled } from "@/api/events";
import Switch from "@/components/generic/switch";
import Title from "@/components/generic/title";
import { useEvent, useEventStore } from "@/stores/event";
import { enqueueSnackbar } from "notistack";
import { useTranslation } from "react-i18next";

export default function SettingsUserRegistration() {
  const { t } = useTranslation();
  const event = useEvent();
  const { setEvent } = useEventStore();

  const changeEnabledState = async (enabled: boolean) => {
    try {
      const newEvent = await setUserRegistrationEnabled(event.id, enabled);
      setEvent(newEvent);

      enqueueSnackbar(t("generic.changes_saved"), { variant: "success" });
    } catch (err) {
      console.error(err);
      enqueueSnackbar(t("generic.error_occurred"), { variant: "error" });
    }
  };

  return (
    <div className="flex flex-col gap-2">
      <Title level={2} noMargin>
        {t("events.settings.user_registration.title")}
      </Title>

      <p>{t("events.settings.user_registration.desc")}</p>

      <Switch
        id="user_registration"
        checked={event.userRegistrationEnabled}
        label={t("generic.enabled") + " ?"}
        onChange={changeEnabledState}
      />

      <div className="text-center">
        <a href={event.userRegistrationUrl} rel="noopener" target="_blank">
          {t("events.settings.user_registration.link")}
        </a>
      </div>

      <p className="text-sm italic text-center mt-2">
        {t("events.settings.user_registration.appliance_qr")}
      </p>
    </div>
  );
}
