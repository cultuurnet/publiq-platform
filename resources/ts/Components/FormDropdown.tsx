import React, { ReactNode, useState } from "react";
import { Heading } from "./Heading";
import { ButtonIcon } from "./ButtonIcon";
import {
  faChevronRight,
  faChevronDown,
} from "@fortawesome/free-solid-svg-icons";

type Props = {
  children: React.ReactNode;
  title: string;
  actions: ReactNode;
};

export const FormDropdown = ({ title, children, actions }: Props) => {
  const [isVisible, setIsVisible] = useState(false);

  return (
    <div className="flex flex-col gap-4 shadow-md shadow-slate-200 max-md:px-5 px-10 py-5">
      <div className="flex gap-2 items-center">
        <Heading className="font-semibold" level={2}>
          {title}
        </Heading>
        {isVisible ? (
          <>
            <ButtonIcon
              icon={faChevronDown}
              onClick={() => setIsVisible(false)}
              className="text-icon-gray"
            />
            {actions}
          </>
        ) : (
          <ButtonIcon
            icon={faChevronRight}
            className="text-icon-gray"
            onClick={() => setIsVisible(true)}
          />
        )}
      </div>
      {isVisible && (
        <>
          <div className="flex flex-col gap-6 border-t py-4">{children}</div>
        </>
      )}
    </div>
  );
};
