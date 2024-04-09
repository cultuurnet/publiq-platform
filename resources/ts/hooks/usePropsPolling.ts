import { useEffect } from "react";
import { router, usePage } from "@inertiajs/react";
import { get } from "lodash";

export const usePropsPolling = (polledProps: string[] = []) => {
  const rootProps = polledProps.map((key) => key.split(".")[0]);
  const page = usePage();
  const arePropsPresent = polledProps
    .map((key) => get(page.props, key))
    .every(Boolean);

  useEffect(() => {
    if (arePropsPresent) {
      return;
    }

    const timeout = setInterval(() => router.reload({ only: rootProps }), 2000);

    return () => clearInterval(timeout);
  }, [arePropsPresent]);
};
