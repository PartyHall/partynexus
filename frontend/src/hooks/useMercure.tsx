import { useAuthStore } from "@/stores/auth";
import { enqueueSnackbar } from "notistack";
import { createContext, useContext, useEffect, useState } from "react";

type MercureListener = {
  topic: string;
  callback: (data: any) => void;
};

type MercureProps = {
  listeners: MercureListener[];
};

type MercureContextProps = MercureProps & {
  addListener: (topic: string, callback: (data: any) => void) => void;
};

const defaultProps: MercureProps = {
  listeners: [],
};

const MercureContext = createContext<MercureContextProps>({
  ...defaultProps,
  addListener: () => {},
});

type Props = {
  children: React.ReactNode;
};

export default function MercureProvider({ children }: Props) {
  const [context, setContext] = useState<MercureProps>(defaultProps);
  const token = useAuthStore((state) => state.token);

  const createEventSource = () => {
    const url = new URL(
      `${window.location.protocol}//${window.location.host}/.well-known/mercure`,
    );

    context.listeners.forEach(({ topic }) => {
      url.searchParams.append("topic", topic);
    });

    const es = new EventSource(url, { withCredentials: true });
    // es.onmessage = (x) => console.log(x);

    es.onopen = () => console.log("Mercure connection opened");
    es.onerror = (e) => {
      const target = e.target as EventSource;

      // Should not be required ?
      // SSE auto reconnects in browser
      if (target.readyState === EventSource.CLOSED) {
        console.log("Mercure connection closed, reconnecting...");
        es.close();

        // setTimeout(createEventSource, 500);

        return;
      }

      enqueueSnackbar("Mercure connection lost!", { variant: "error" });
    };

    context.listeners.forEach(({ topic, callback }) => {
      es.addEventListener(topic, callback);
    });

    return es;
  };

  useEffect(() => {
    if (!token || context.listeners.length === 0) {
      return;
    }

    const es = createEventSource();

    return () => {
      if (es) {
        es.close();
      }
    };
  }, [token, context.listeners]);

  const addListener = (topic: string, callback: (data: any) => void) => {
    if (context.listeners.some((listener) => listener.topic === topic)) {
      // console.warn(`Listener for topic "${topic}" already exists.`);

      return;
    }

    setContext((prev) => ({
      ...prev,
      listeners: [...prev.listeners, { topic, callback }],
    }));
  };

  return (
    <MercureContext.Provider
      value={{
        ...context,
        addListener,
      }}
    >
      {children}
    </MercureContext.Provider>
  );
}

export function useMercure() {
  return useContext<MercureContextProps>(MercureContext);
}

export function useMercureListener(
  topic: string,
  callback: (data: any) => void,
) {
  const { addListener } = useMercure();

  useEffect(() => {
    addListener(topic, (x) => {
      if (!x.data) {
        console.warn(`No data received for topic "${topic}"`);

        return;
      }

      callback(JSON.parse(x.data));
    });
  }, [topic, callback]);
}
