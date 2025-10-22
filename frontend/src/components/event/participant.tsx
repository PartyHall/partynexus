import Username from "@/components/username";
import type { MinimalUser } from "@/types/user";
import { IconCrown, IconUserX } from "@tabler/icons-react";
import { Tooltip } from "@/components/generic/tooltip";
import { useTranslation } from "react-i18next";
import type { Event } from "@/types/event";
import { removeParticipantFromEvent } from "@/api/events/participants";
import { enqueueSnackbar } from "notistack";
import { useRouter } from "@tanstack/react-router";
import ConfirmButton from "../generic/confirm_button";

type Props = {
  participant: MinimalUser;
  owner?: boolean;
  event: Event;
  canRemove?: boolean;
};

export default function Participant({
  event,
  participant,
  owner,
  canRemove,
}: Props) {
  const { t } = useTranslation();
  const { invalidate } = useRouter();

  const removeParticipant = async () => {
    try {
      await removeParticipantFromEvent(event, participant["@id"]);
      await invalidate();
      enqueueSnackbar(t("events.editor.participants.remove_success"), {
        variant: "success",
      });
    } catch (e) {
      console.error(e);
      enqueueSnackbar(t("generic.error.generic"), { variant: "error" });
    }
  };

  return (
    <li className="flex flex-row items-center justify-between p-1 border-b last:border-b-0 border-synthbg-500">
      <div className="flex flex-col gap-0">
        <Username user={participant} noStyle />
        <span className="pl-5 text-sm text-primary-200">
          {participant.username}
        </span>
      </div>
      {owner && <IconCrown className="icon-blue-glow" size={18} />}

      {!owner && canRemove && (
        <Tooltip content={t("events.editor.participants.remove")}>
          <ConfirmButton
            tTitle={"events.editor.participants.remove"}
            tDescription={"events.editor.participants.remove_disclaimer"}
            tDescriptionArgs={{
              username:
                participant.firstname || participant.lastname
                  ? `${participant.firstname} ${participant.lastname}`
                  : participant.username,
            }}
            onConfirm={removeParticipant}
          >
            <IconUserX size={18} />
          </ConfirmButton>
        </Tooltip>
      )}
    </li>
  );
}
