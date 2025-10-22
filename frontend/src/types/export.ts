import type { EnumValue } from ".";

export type Export = {
  id: number;
  startedAt: string;
  endedAt: string | null;
  progress: EnumValue;
  status: EnumValue;
  timelapse: boolean;
};
