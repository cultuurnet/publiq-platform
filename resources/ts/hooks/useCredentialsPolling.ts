import { useEffect } from "react";
import { router } from "@inertiajs/react";

export const useCredentialsPolling = () =>
  useEffect(() => {
    const timeout = setInterval(
      () => router.reload({ only: ["credentials", "integration"] }),
      2000
    );

    return () => clearTimeout(timeout);
  }, []);
