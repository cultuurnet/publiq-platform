import React from "react";
import NotFoundError from "../../images/NotFoundError.svg";

export const ErrorImage = () => {
  return (
    <img
      className="object-contain ml-16 max-h-80 max-sm:max-h-60"
      src={NotFoundError}
    />
  );
};
