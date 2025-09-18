import { useState } from "react";
import type { ButtonProps } from "./button";
import Button from "./button";
import Modal from "./modal";
import { useTranslation } from "react-i18next";

type Props = {
  tTitle: string;
  tDescription: string;
  tDescriptionArgs?: Record<string, any>;
  tConfirmButtonText?: string;
  onConfirm: () => Promise<void>;
  children?: React.ReactNode;
  confirmButtonProps?: Omit<ButtonProps, "onClick" | "disabled">;
  onSuccess?: () => void;
} & Omit<ButtonProps, "onClick">;

export default function ConfirmButton({
  tTitle,
  tDescription,
  tDescriptionArgs,
  tConfirmButtonText,
  onConfirm,
  children,
  onSuccess,
  ...props
}: Props) {
  const { t } = useTranslation();

  const [modalShown, setModalShown] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  return (
    <>
      <Button {...props} onClick={() => setModalShown(true)}>
        {children}
      </Button>

      <Modal
        open={modalShown}
        onOpenChange={() => setModalShown(false)}
        title={t(tTitle)}
        description={t(tDescription, tDescriptionArgs)}
      >
        <div className="flex flex-row justify-end gap-2 mt-4">
          <Button variant="secondary" onClick={() => setModalShown(false)}>
            {t("generic.cancel")}
          </Button>
          <Button
            onClick={async () => {
              setSubmitting(true);
              await onConfirm();
              setSubmitting(false);
              setModalShown(false);

              onSuccess?.();
            }}
            disabled={submitting}
            {...props.confirmButtonProps}
          >
            {t(tConfirmButtonText || "generic.ok")}
          </Button>
        </div>
      </Modal>
    </>
  );
}
