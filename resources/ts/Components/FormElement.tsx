import type {
  ComponentProps,
  ReactElement} from "react";
import React, {
  cloneElement,
  memo,
  useId,
} from "react";
import { classNames } from "../utils/classNames";
import type { Theme } from "../types/Theme";

export type LabelPosition = keyof typeof InputStyle;
type LabelSize = keyof Theme["fontSize"];
type LabelWeight = keyof Theme["fontWeight"];

type WrapperProps = {
  labelPosition?: LabelPosition;
  children: JSX.Element;
};

const Wrapper = ({ labelPosition, children }: WrapperProps) => {
  if (labelPosition !== "right") {
    return children;
  }

  return <div className="flex">{children}</div>;
};

const getFlexDirection = (labelPosition: LabelPosition | undefined) => {
  if (!labelPosition) return "flex-col";
  if (labelPosition === "top") return "flex-col";
  if (labelPosition === "left") return "flex-row";
  if (labelPosition === "right") return "flex-row-reverse";
};

const getAlignItems = (labelPosition: LabelPosition | undefined) => {
  if (labelPosition === "right") return "self-center";
  return;
};

type LabelProps = ComponentProps<"label"> & {
  id: string;
  label: string | ReactElement;
  labelSize: LabelSize;
  labelWeight?: LabelWeight;
};

const Label = memo(
  ({ id, labelSize, labelWeight = "medium", label, className }: LabelProps) => (
    <label
      htmlFor={id}
      className={classNames(
        "cursor-pointer",
        `font-${labelWeight}`,
        labelSize ? `text-${labelSize}` : "",
        className
      )}
    >
      {label}
    </label>
  )
);

Label.displayName = "Label";

const InputStyle = {
  top: "",
  bottom: "",
  left: "w-[35%]",
  right: "flex self-center",
};

type Props = {
  label?: string | ReactElement;
  labelPosition?: LabelPosition;
  labelSize?: LabelSize;
  labelWeight?: LabelWeight;
  error?: string;
  info?: string;
  component: ReactElement;
  elementId?: string;
} & ComponentProps<"div">;

export const FormElement = ({
  label,
  labelPosition = "top",
  labelSize = "sm",
  labelWeight = "medium",
  component,
  error,
  info,
  elementId,
  className,
}: Props) => {
  const id = useId();

  const clonedComponent = cloneElement(component, { ...component.props, id });

  return (
    <div id={elementId} className={classNames("flex flex-col ", className)}>
      <Wrapper labelPosition={labelPosition}>
        <>
          <div
            className={classNames(
              "flex gap-2",
              getFlexDirection(labelPosition),
              getAlignItems(labelPosition)
            )}
          >
            {label && (
              <Label
                id={id}
                label={label}
                labelSize={labelSize}
                labelWeight={labelWeight}
                className={classNames(labelPosition === "left" ? "w-40" : "")}
              />
            )}
            <div className={InputStyle[labelPosition]}>{clonedComponent}</div>
          </div>
        </>
      </Wrapper>
      {error && <span className="text-red-500 mt-1 text-sm">{error}</span>}
      {info && <span className="text-gray-500 mt-1 text-sm">{info}</span>}
    </div>
  );
};
