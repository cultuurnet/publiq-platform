import React from "react";
import { SecondaryButton } from "./SecondaryButton";
import { classNames } from "../utils/classNames";

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
        <SecondaryButton
          key={link}
          href={link}
          className={classNames(
            currentPage - 1 === index &&
              "bg-publiq-blue-dark hover:bg-blue-800 text-white"
          )}
        >
          {getPageFromLink(link)}
        </SecondaryButton>
      ))}
    </div>
  );
};
