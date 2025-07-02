import { IconAlertSquareRounded, IconInfoCircle, IconTriangle } from "@tabler/icons-react";
import type { ReactNode } from "react";
import { useTranslation } from "react-i18next";
import { Tooltip } from "./tooltip";

export function KeyVal({ label, tooltip, children }: { label: string, tooltip?: string, children: ReactNode }) {
  const { t } = useTranslation();

  return <p className='flex flex-col'>
    <div className="flex flex-row items-center gap-2">
      {
        tooltip
          ? <Tooltip content={t(tooltip)}>
            <span className="text-red-500">
              <IconAlertSquareRounded size={19} />
            </span>
          </Tooltip>
          : ''
      }
      <span className='flex flex-row font-bold text-pink-glow items-center'>
        {t(label)}:
      </span>

    </div>
    <span className='ml-4'>{children}</span>
  </p>;
}