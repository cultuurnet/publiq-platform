import React from "react";
import Error from "../../images/Error.svg";

export const ErrorImage = () => {
  return (
    <img
      className="object-contain ml-16 max-h-[20rem] max-sm:max-h-[15rem]"
      src={Error}
    />
  );
};
