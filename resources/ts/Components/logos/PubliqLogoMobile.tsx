import type { ComponentProps } from "react";
import React from "react";

type Props = ComponentProps<"svg">;

export const PubliqLogoMobile = (props: Props) => {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      xmlnsXlink="http://www.w3.org/1999/xlink"
      width={30}
      height={30}
      viewBox="0 0 64 57"
      {...props}
    >
      <title>{"favicon"}</title>
      <image
        xlinkHref="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEAAAABACAYAAACqaXHeAAAAAXNSR0IArs4c6QAAAERlWElmTU0AKgAAAAgAAYdpAAQAAAABAAAAGgAAAAAAA6ABAAMAAAABAAEAAKACAAQAAAABAAAAQKADAAQAAAABAAAAQAAAAABGUUKwAAACJUlEQVR4Ae2aPVrDMAyG5TT04W8pD0MPADdg5w4MjByPkZNwAwY2RrpQ2pSSYAFOFNdpEjeExpIn+V/fa8V1GgNIEgJCQAgwJqDgYZYx1g8RZ/GoPQZIXwGis95AZPCez6XgOLfrDNqvrq1d75onHgN8LGcaQM/J5UwTF3z72WOPtOTPdV4qj0COgho3E0Wzg7UbbPDsI0AADDa8O3JcIqAjkIMdRiJgsEvXkeP8IoCcApEhPwBW5AgACwi7rEQAuyW3BEsEWEDYZSUC2C05/iVGkkQAgcHSlAhguexENL8I4P02mK3I4n+bOgJ6/Cxmz74HeWaPgNIfBMuJGYCyeMyxB1A+F24CalfS4FtcuwH/vnV3EdBG/OFJoYzaRWlvVjcRQMWrX6ZZ6haB9ckCwLSjtulj6twjeJbqm0DZ5m2g3QFQ8eiaEVHlZl19kzGqxvYo3+0RsMV7OPDfXfwBBCAe4fsBCES8H4CAxLcHEJL49c97UfNfARQ/PkJofmmlf/r2LaloEoNKHyGLrrb6ZlZ+ZxHp09Z5aiujS2eTSG9lacW5w9mhKIwhTq7heR7D9GIJt6r8vnz/cg7raAFvyWnRZQ+su+nc6YVZKGeluzCM+4BUG0JQWpbj1EebGTs8AKisRSSECaAFhHABNITgdxLEwYeQXHeeRwclz8OOACN1y57AAwCCqIDAB0AFBF4AHBD4AbAg8ARgQcAsz1SxMfKEwVX1F/CegCNlUDE1AAAAAElFTkSuQmCC"
        width={64}
        height={64}
        y={-5}
        fillRule="evenodd"
      />
    </svg>
  );
};
