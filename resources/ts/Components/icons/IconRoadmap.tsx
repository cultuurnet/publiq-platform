import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const IconRoadmap = ({ className }: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 255 255"
      className={className}
    >
      <style>
        {
          ".st6,.st7{fill:#ec865f;stroke:#ec865f;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10}.st7{fill:#fff;stroke:#fff}"
        }
      </style>
      <path
        d="M77.3 78.8h0c2 30.9 76.5 24.7 91.5 39.9s-35.9 19.2-71 25.6-34.9 54.8-34.9 54.8H136s-20.7-43.9 12.3-51.3c33.9-7.7 50.2-15.5 42.9-32.2-10.9-24.4-101.3-13-113.9-36.8z"
        style={{
          fill: "none",
          stroke: "#bfc4ce",
          strokeWidth: 5,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 10,
        }}
      />
      <circle cx={78} cy={63.1} r={6.4} className="st6" />
      <path d="M72.5 66.4 78 78.9l5.6-12.7z" className="st6" />
      <circle cx={78} cy={63.1} r={1.7} className="st7" />
      <circle cx={99.3} cy={150.1} r={15.9} className="st6" />
      <path d="m85.7 158.3 13.6 31 13.8-31.4z" className="st6" />
      <circle cx={99.3} cy={150.1} r={4.3} className="st7" />
      <circle cx={181.3} cy={98.8} r={10.1} className="st6" />
      <path d="m172.6 104 8.7 19.8 8.9-20z" className="st6" />
      <circle cx={181.3} cy={98.8} r={2.8} className="st7" />
    </svg>
  );
};
