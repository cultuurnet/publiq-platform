import React from "react";
import NotFoundError from "../../images/NotFoundError.svg";

export const ErrorImage = () => {
  return (
    <img
      className="object-contain ml-16 max-h-[20rem] max-sm:max-h-[15rem]"
      src={NotFoundError}
    />
  );
};
