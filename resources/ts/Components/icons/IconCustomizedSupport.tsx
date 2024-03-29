import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const IconCustomizedSupport = ({ className }: Props) => {
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
      <circle cx={92.9} cy={123.5} r={20} className="st0" />
      <path
        d="M127.9 160.9c-4.8 6.5-7.7 14.5-7.7 23.2h-70c0-21.5 17.4-38.9 38.9-38.9h7.7c10.7 0 20.5 4.3 27.5 11.4 1.3 1.4 2.5 2.8 3.6 4.3z"
        className="st0"
      />
      <circle cx={92.9} cy={123.5} r={20} className="st0" />
      <path
        d="M205.6 184.2h-85.4c0-8.7 2.9-16.8 7.7-23.2 7.1-9.5 18.4-15.6 31.2-15.6h7.7c10.7 0 20.5 4.3 27.5 11.4 7 6.9 11.3 16.7 11.3 27.4zm-22.7-60.7c0 11-8.9 20-20 20-10 0-18.3-7.4-19.8-17h15.8v-17.4c0-1.8-.3-3.5-.8-5.1 1.5-.4 3.1-.6 4.8-.6 11.1.1 20 9 20 20.1z"
        className="st0"
      />
      <path
        d="M158.9 109.1v17.4h-17.4c-4.8 0-9.1-1.9-12.3-5.1-2.7-2.6-4.4-6.1-4.9-10-.1-.7-.1-1.5-.1-2.2 0-1.9.3-3.8.9-5.6 1.4-4.1 4.2-7.5 7.9-9.6 2.5-1.4 5.4-2.2 8.5-2.2 2.7 0 5.2.6 7.4 1.7 4.4 2.1 7.8 5.9 9.2 10.7.6 1.5.8 3.2.8 4.9zm-22.9 4.6h12.6m-12.6-7.8h12.6M99.8 83.5v11.9h12c3.3 0 6.3-1.3 8.4-3.5 1.8-1.8 3.1-4.2 3.4-6.9.1-.5.1-1 .1-1.5 0-1.3-.2-2.6-.6-3.8-1-2.8-2.9-5.1-5.5-6.6-1.7-1-3.7-1.5-5.9-1.5-1.8 0-3.6.4-5.1 1.1-4 1.9-6.8 6.1-6.8 10.8zm15.3 0h-6.5"
        style={{
          stroke: "#ec865f",
          fill: "none",
          strokeWidth: 5,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 10,
        }}
      />
    </svg>
  );
};
