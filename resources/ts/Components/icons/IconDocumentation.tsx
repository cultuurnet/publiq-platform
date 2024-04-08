import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const IconDocumentation = ({ className }: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 255 255"
      className={className}
    >
      <style>
        {
          ".st0{fill:none;stroke:#bfc4ce;stroke-linecap:round;stroke-linejoin:round}.st0,.st3{stroke-width:5;stroke-miterlimit:10}.st3,.st5{fill:none;stroke-linecap:round;stroke-linejoin:round}.st3{stroke:#ec865f}.st5{stroke:#bfc4ce;stroke-width:4.9997;stroke-miterlimit:9.9995}"
        }
      </style>
      <path
        d="M120.6 69.2h37.7v122h-37.7zm37.7 0H196v122h-37.7z"
        className="st0"
      />
      <path
        d="M132 94.7h14.9v28.8H132zm37.7 42.9h14.9v28.8h-14.9z"
        className="st0"
      />
      <path d="M146.9 144.4H132m14.9 9.6H132" className="st3" />
      <path
        d="M59.725 184.706 80.597 64.505l37.144 6.45L96.87 191.156z"
        className="st5"
      />
      <path
        d="m82.54 119.974 4.927-28.376 14.68 2.55-4.927 28.375z"
        className="st5"
      />
      <path
        d="m93.7 143.2-14.6-2.6m13 12-14.7-2.5m92.3-43h14.9"
        className="st3"
      />
    </svg>
  );
};
