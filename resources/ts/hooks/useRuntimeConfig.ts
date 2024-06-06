import { usePage } from "@inertiajs/react";
import { PageProps } from "../types/PageProps";

export const useRuntimeConfig = () => usePage<PageProps>().props.config;
