import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const IconStatuspage = ({ className }: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 255 255"
      className={className}
    >
      <style>
        {
          ".st0{fill:none;stroke:#bfc4ce;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10}"
        }
      </style>
      <path d="M49.5 62.9H207v105H49.5z" className="st0" />
      <path
        d="M59.8 72.3h137.6V159H59.8zM206.2 168l27 24.9H22.5l27-24.9z"
        className="st0"
      />
      <path d="m88.3 122.7 30.8-22.6 8.6 17.5L162.2 90" className="st0" />
      <path
        d="M147.4 109.5c0 7.4-3.6 14-9.1 18.1-3.8 2.8-8.5 4.5-13.6 4.5-12.5 0-22.6-10.1-22.6-22.6s10.1-22.6 22.6-22.6c12.6 0 22.7 10.1 22.7 22.6zm6.4 40-15.5-21.9"
        style={{
          stroke: "#ec865f",
          fill: "none",
          strokeWidth: 5,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 10,
        }}
      />
      <path d="M113.7 183.5h28.2" className="st0" />
    </svg>
  );
};
