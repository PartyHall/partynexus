/**
 * ChatGPT generated code
 */

import {
  useFloating,
  offset,
  flip,
  shift,
  useHover,
  useInteractions,
  autoUpdate,
} from "@floating-ui/react";
import { useState } from "react";

type TooltipProps = {
  content: React.ReactNode;
  children: React.ReactElement;
};

export function Tooltip({ content, children }: TooltipProps) {
  const [open, setOpen] = useState(false);

  const { refs, floatingStyles, context } = useFloating({
    open,
    onOpenChange: setOpen,
    middleware: [offset(6), flip(), shift()],
    whileElementsMounted: autoUpdate,
  });

  const hover = useHover(context, {
    move: false,
    delay: { open: 100, close: 100 },
  });
  const { getReferenceProps, getFloatingProps } = useInteractions([hover]);

  // Clone the child to attach the reference props
  const child = (
    <span ref={refs.setReference} {...getReferenceProps()}>
      {children}
    </span>
  );

  return (
    <>
      {child}
      {open && (
        <div
          ref={refs.setFloating}
          style={{
            ...floatingStyles,
            backgroundColor: "black",
            color: "white",
            padding: "4px 8px",
            borderRadius: "4px",
            fontSize: "12px",
            zIndex: 9999,
            pointerEvents: "none",
          }}
          {...getFloatingProps()}
        >
          {content}
        </div>
      )}
    </>
  );
}
