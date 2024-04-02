import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const IconSearchApi = ({ className }: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fillRule="evenodd"
      strokeLinecap="round"
      strokeLinejoin="round"
      strokeMiterlimit={10}
      clipRule="evenodd"
      viewBox="0 0 188 169"
      className={className}
    >
      <g strokeWidth={5} transform="translate(-34 -43.6)">
        <g fill="none" stroke="#bfc4ce">
          <path d="M36.5 56.2H194v127.4H36.5z" />
          <path d="M75.2 46.1c-3.5 0-6.4 2.9-6.4 6.4v3.8h12.8v-3.8c0-3.6-2.9-6.4-6.4-6.4zm40 0c-3.5 0-6.4 2.9-6.4 6.4v3.8h12.8v-3.8c0-3.6-2.9-6.4-6.4-6.4zm40 0c-3.5 0-6.4 2.9-6.4 6.4v3.8h12.8v-3.8c0-3.6-2.9-6.4-6.4-6.4zm-58.7 75.3h1.9c2.7 0 4.9-2.2 4.9-4.9v-4.4c0-2.7 2.2-4.9 4.9-4.9h1.9" />
          <path d="M110 135.5h-1.9c-2.7 0-4.9-2.2-4.9-4.9v-4.4c0-2.7-2.2-4.9-4.9-4.9h-1.9m37.4.1h-1.9c-2.7 0-4.9-2.2-4.9-4.9v-4.4c0-2.7-2.2-4.9-4.9-4.9h-1.7" />
          <path d="M120.4 135.5h1.9c2.7 0 4.9-2.2 4.9-4.9v-4.4c0-2.7 2.2-4.9 4.9-4.9h1.9M49.5 69.6h25.7v25.7H49.5zm0 40h25.7v25.7H49.5zm35.7-40h25.7v25.7H85.2zm35.8 0h25.7v25.7H121zm36 0h25.7v25.7H157zm0 40h25.7v25.7H157zm-107.5 38h25.7v25.7H49.5zm35.7 0h25.7v25.7H85.2zm35.8 0h25.7v25.7H121zm36 0h25.7v25.7H157z" />
        </g>
        <circle
          cx={190.3}
          cy={180.7}
          r={28.9}
          fill="#ec865f"
          stroke="#ec865f"
        />
        <path
          fill="none"
          stroke="#fff"
          d="M197.8 175.7c0 3.2-1.5 6.1-3.9 7.9-1.6 1.2-3.7 2-5.9 2-5.4 0-9.8-4.4-9.8-9.8s4.4-9.8 9.8-9.8c5.4-.1 9.8 4.3 9.8 9.7zm4.6 19.8-8.5-11.9"
        />
      </g>
    </svg>
  );
};
