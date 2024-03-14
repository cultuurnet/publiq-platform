import React from "react";
import { classNames } from "../utils/classNames";
import { Link } from "@inertiajs/react";

const getPageFromLink = (url: string) => new URL(url).searchParams.get("page");

export const Pagination = ({ links }: { links: string[] }) => {
  const page = new URL(document.location.href).searchParams.get("page");
  const currentPage = page ? parseInt(page) : 1;

  if (links.length < 2) {
    return null;
  }

  return (
    <div className="inline-flex">
      {links.map((link, index) => (
        <Link
          key={link}
          href={link}
          className={classNames(
            "text-publiq-gray-900 border border-publiq-gray-900 w-10 h-10 m-1 rounded-lg flex justify-center items-center",
            currentPage - 1 === index
              ? "bg-publiq-blue-dark text-white hover:bg-publiq-blue-dark"
              : "hover:bg-publiq-blue-dark hover:bg-opacity-10"
          )}
        >
          {getPageFromLink(link)}
        </Link>
      ))}
    </div>
  );
};
