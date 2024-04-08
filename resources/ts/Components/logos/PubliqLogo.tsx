import type { ComponentProps } from "react";
import React from "react";
import { classNames } from "../../utils/classNames";

type Props = {
  color: string;
  width: number;
  height: number;
} & ComponentProps<"svg">;

export const PubliqLogo = ({
  color,
  width,
  height,
  className,
  ...props
}: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      xmlSpace="preserve"
      id="Layer_1"
      x={0}
      y={0}
      viewBox="0 0 340.4 278.5"
      width={`${width}`}
      height={`${height}`}
      className={classNames(`fill-current text-${color}`, className)}
      {...props}
    >
      <path d="M37.4 60.1v61.6h14.1V97.6c2.7 2.9 6.5 4.6 11.4 4.6C73.1 102.2 83 94 83 80.6c0-13.4-9.9-21.5-20.1-21.5-4.5 0-9.8 2.1-12.2 7v-5.9H37.4zm22.7 11.8c4.7 0 8.6 3.9 8.6 8.6s-3.9 8.6-8.6 8.6c-4.7 0-8.6-3.6-8.6-8.3 0-4.7 3.9-8.9 8.6-8.9zM90.8 60.1v21.7c0 12.8 5.4 20.3 16.4 20.3 3.6 0 10.2-2 11.6-7.3v6.5h14.1V60.1h-14.1v21.7c0 5.3-4.1 6.7-7.3 6.7-2.9 0-6.7-1.7-6.7-7.2V60.1h-14zM143.5 38.5v62.9h13.3v-5.9c2.4 4.9 7.7 6.7 12.2 6.7 10.2 0 20.1-8.1 20.1-21.5s-9.9-21.6-20.1-21.6c-5 0-8.8 1.9-11.4 4.9V38.5h-14.1zm22.6 33.7c4.7 0 8.6 3.9 8.6 8.6 0 4.7-3.9 8.6-8.6 8.6-4.7 0-8.6-3.9-8.6-8.6 0-4.7 3.9-8.6 8.6-8.6zM197.3 38.5h14.1v62.9h-14.1zM229 38.5c-4.6 0-8.5 3.5-8.5 8 0 4.4 3.9 8 8.5 8 4.7 0 8.4-3.6 8.4-8 0-4.6-3.7-8-8.4-8zM222 60.1h14.1v41.2H222zM289.8 121.7V60.1h-13.3V66c-2.4-4.9-7.7-7-12.2-7-10.2 0-20.1 8.1-20.1 21.5s9.9 21.6 20.1 21.6c5 0 8.8-1.7 11.4-4.6v24.1h14.1zm-22.6-32.6c-4.7 0-8.6-3.9-8.6-8.6s3.9-8.6 8.6-8.6c4.7 0 8.6 4.2 8.6 8.8 0 4.7-3.9 8.4-8.6 8.4z" />
      <path d="M340.3 0H0v230.2h13.7V13.7h299.4v162h-223l-54.5 54.5h213.8l91 48.3V0z" />
    </svg>
  );
};
