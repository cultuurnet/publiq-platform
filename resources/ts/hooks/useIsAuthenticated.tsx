import { usePageProps } from "./usePageProps";

export const useIsAuthenticated = () => {
  const pageProps = usePageProps();

  return pageProps.auth.authenticated;
};
