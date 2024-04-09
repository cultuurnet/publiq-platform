import { useEffect } from "react";
import { router } from "@inertiajs/react";
import { VisitOptions } from "@inertiajs/core/types";

export const usePolling = (
  condition: boolean,
  visitOptions: VisitOptions = {},
  interval = 2000
) =>
  useEffect(() => {
    if (!condition) {
      return;
    }

    const timeout = setInterval(() => router.reload(visitOptions), interval);

    return () => clearInterval(timeout);
  }, [condition]);
