/**
 * Vendored from streamich/react-use
 * https://github.com/streamich/react-use/blob/master/src/useTitle.ts
 */

import { useEffect, useRef } from "react";

export interface UseTitleOptions {
  restoreOnUnmount?: boolean;
}

const DEFAULT_USE_TITLE_OPTIONS: UseTitleOptions = {
  restoreOnUnmount: false,
};

function useTitle(
  title: string,
  options: UseTitleOptions = DEFAULT_USE_TITLE_OPTIONS,
) {
  const prevTitleRef = useRef(document.title);

  if (document.title !== title) {
    document.title = title;
  }

  useEffect(() => {
    if (options && options.restoreOnUnmount) {
      return () => {
        document.title = prevTitleRef.current;
      };
    }
  }, []);
}

export function useNexusTitle(title: string) {
  useTitle(`${title} | Partyhall`);
}

export default typeof document !== "undefined" ? useTitle : () => {};
