import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const IconStyleguide = ({ className }: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 255 255"
      className={className}
    >
      <style>
        {
          ".st0,.st1,.st2{fill:none;stroke:#bfc4ce;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10}.st1,.st2{fill:#ec865f;stroke:#ec865f;stroke-width:3}.st2{fill:#bfc4ce;stroke:#bfc4ce}"
        }
      </style>
      <path
        d="M80.9 128.8c-.5-26.3 20.8-47.9 47-47.9 13 0 24.7 5.3 33.2 13.8s13.8 20.2 13.8 33.2v34.6c0 6.8-5.5 12.4-12.4 12.4h-33.8c-25.7.1-47.3-20.3-47.8-46.1z"
        className="st0"
      />
      <path
        d="M155.9 162h7.1v-7.1c0-6.2-7.4-9.4-11.9-5.1h0c-4.7 4.4-1.6 12.2 4.8 12.2z"
        className="st0"
      />
      <circle cx={124.5} cy={153.9} r={9.3} className="st2" />
      <circle cx={104} cy={138.6} r={9.3} className="st1" />
      <circle cx={105} cy={113.4} r={9.3} className="st2" />
      <circle cx={127} cy={99.8} r={9.3} className="st1" />
      <circle cx={150.8} cy={113.4} r={9.3} className="st2" />
    </svg>
  );
};
