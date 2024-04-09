import { useEffect } from "react";
import { router, usePage } from "@inertiajs/react";
import { get } from "lodash";
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

export const usePropsPolling = (polledProps: string[] = []) => {
  const rootProps = polledProps.map((key) => key.split(".")[0]);
  const page = usePage();
  const arePropsPresent = polledProps
    .map((key) => get(page.props, key))
    .every(Boolean);

  return usePolling(arePropsPresent, { only: rootProps });
};
