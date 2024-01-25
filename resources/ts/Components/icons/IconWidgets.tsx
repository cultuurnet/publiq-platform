import React, { ComponentProps } from "react";

type Props = ComponentProps<"svg">;

export const IconWidgets = ({ className }: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 250 250 "
      className={className}
    >
      <style>
        {
          ".st0{fill:none;stroke:#bfc4ce;stroke-width:5;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:10}.st13{fill:#ec865f}"
        }
      </style>
      <path
        d="m177.5 171.4-8.2-8.3v-.2l8.2-8.3m15.3 16.8 8.2-8.3v-.2l-8.2-8.3m-6.7.3-2.9 16.5"
        style={{
          stroke: "#ec865f",
          fill: "none",
          strokeWidth: 5,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 10,
        }}
      />
      <path d="M156.2 64.3h56.9v56.9h-56.9z" className="st0" />
      <path
        d="M94.2 103.3c7-.9 14.1-1.8 21.1-2.6 2.6-.3 5.1-.7 7.7-1 .4 0 .6-.2.6-.6V84.6c0-.1 0-.2-.1-.3-9.8-1.2-19.5-2.4-29.3-3.7v-.1h37v22.9c-12.4 0-24.7 0-37-.1 0 .1 0 .1 0 0z"
        className="st13"
      />
      <path
        d="M96.1 84.5c1.3.1 2.5.2 3.8.3v9.6c0 1.2.9 1.8 2 1.5.9-.2 1.2-.8 1.3-1.8v-9c.9.1 1.7.1 2.5.2.2 0 .4.3.4.5 0 2.9.1 5.9 0 8.8-.1 2.7-1.8 4.4-4.7 4.6-.6.1-1.1.1-1.7 0-2.1-.2-3.5-1.4-3.6-3.4-.1-3.7 0-7.5 0-11.3zm22 13.1c-1.3.1-2.4.2-3.6.3v-9c-.9-.1-1.7-.1-2.6-.2v-2.9c2.9.2 5.7.5 8.6.7v2.6H118c.1 2.8.1 5.7.1 8.5zm-7.2.6c-1.1.1-2.3.2-3.4.3v-8.8h3.4v8.5zm0-9.7h-3.5v-3.1c1.2.1 2.3.2 3.5.3v2.8z"
        className="st13"
      />
      <path
        d="M84.2 64.3h56.9v56.9H84.2zm57.1 70.2v56.9H84.4v-34.7l18.8 14.9 11.9-14.9-28-22.2zm14.4 0h56.9v56.9h-56.9z"
        className="st0"
      />
      <path
        d="m115.1 156.7-11.9 14.9-18.8-14.9-37.5-29.8L58.8 112l28.3 22.5zM44.7 113h0c-3.3 4.1-2.6 10.1 1.5 13.4l.6.5 11.9-15-.6-.5c-4.1-3.2-10.1-2.5-13.4 1.6z"
        className="st0"
      />
      <path
        d="m115.1 156.7 5.7 10.7 5.8 10.7-11.7-3.3-11.7-3.2zm-62.3-37.3 43.7 34.7"
        className="st0"
      />
      <path
        d="m120.8 167.4 5.8 10.7-11.7-3.3z"
        style={{
          fill: "#bfc4ce",
          stroke: "#bfc4ce",
          strokeWidth: 5,
          strokeLinecap: "round",
          strokeLinejoin: "round",
          strokeMiterlimit: 10,
        }}
      />
    </svg>
  );
};
