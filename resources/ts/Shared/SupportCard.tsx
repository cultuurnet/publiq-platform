import React from "react";
import { LinkButton } from "./LinkButton";
import { SupportType } from "./SupportTypes";
import { Heading } from "./Heading";

type Props = SupportType;

export const SupportCard = ({
  title,
  description,
  imgUrl,
  actionTitle,
  actionUrl,
}: Props) => {
  return (
    <div className="w-full flex flex-col bg-white shadow hover:bg-publiq-blue-light hover:bg-opacity-5">
      <div className="flex flex-1 max-sm:flex-col ">
        <div className="flex flex-shrink-0">
          <img
            src={imgUrl}
            alt={title}
            className="h-full w-auto aspect-square max-sm:max-h-[12rem] max-sm:w-full object-cover"
          />
        </div>
        <div className="flex flex-col p-4 max-sm:gap-5">
          <div className="flex flex-col gap-3 md:min-h-[10rem]">
            <Heading level={3}>{title}</Heading>
            <p className="max-md:text-sm">{description}</p>
          </div>
          <div className="flex max-sm:self-center">
            <LinkButton
              className="min-w-[15rem] max-sm:min-w-[10rem] max-sm:px-3"
              href={actionUrl}
            >
              {actionTitle}
            </LinkButton>
          </div>
        </div>
      </div>
    </div>
  );
};
