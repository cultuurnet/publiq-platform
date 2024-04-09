import { useEffect } from "react";
import { router } from "@inertiajs/react";
import { VisitOptions } from "@inertiajs/core/types";

export const usePolling = (
  condition: boolean,
  visitOptions: VisitOptions = {}
) =>
  useEffect(() => {
    if (!condition) {
      return;
    }

    const timeout = setInterval(() => router.reload(visitOptions), 2000);

    return () => clearInterval(timeout);
  }, [condition]);
