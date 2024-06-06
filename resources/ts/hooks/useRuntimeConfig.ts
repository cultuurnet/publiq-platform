import { usePage } from "@inertiajs/react";
import type { PageProps } from "../types/PageProps";

export const useRuntimeConfig = () => usePage<PageProps>().props.config;
