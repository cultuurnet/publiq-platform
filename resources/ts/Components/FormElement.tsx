import React, {
  cloneElement,
  ComponentProps,
  memo,
  ReactElement,
  useId,
} from "react";
import { classNames } from "../utils/classNames";

export type LabelPosition = "top" | "left" | "right";

type LabelSize = "base" | "lg" | "xl";

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
  label: string;
  labelSize: LabelSize;
};

const Label = memo(({ id, labelSize, label, className }: LabelProps) => (
  <label
    htmlFor={id}
    className={classNames(
      "font-semibold",
      labelSize ? `text-${labelSize}` : "",
      className
    )}
  >
    {label}
  </label>
));

Label.displayName = "Label";

const InputStyle = {
  top: "",
  bottom: "",
  left: "w-[35%]",
  right: "flex self-center",
};

type Props = {
  label?: string;
  clickableLabel?: string;
  clickableLabelLink?: string;
  labelPosition?: LabelPosition;
  labelSize?: LabelSize;
  error?: string;
  info?: string;
  component: ReactElement;
} & ComponentProps<"div">;

export const FormElement = ({
  label,
  clickableLabel,
  clickableLabelLink,
  labelPosition = "top",
  labelSize = "base",
  component,
  error,
  info,
  className,
}: Props) => {
  const id = useId();

  const clonedComponent = cloneElement(component, { ...component.props, id });

  return (
    <div className={classNames("inline-flex flex-col ", className)}>
      <Wrapper labelPosition={labelPosition}>
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
              className={classNames(labelPosition === "left" ? "w-40" : "")}
            />
          )}
          <div className={InputStyle[labelPosition]}>{clonedComponent}</div>
        </div>
        {clickableLabel && (
          <a
            href={clickableLabelLink}
            target="_blank"
            rel="noreferrer"
            className={classNames(
              "font-semibold text-publiq-blue-dark hover:underline pl-1",
              labelSize ? `text-${labelSize}` : ""
            )}
          >
            {clickableLabel}
          </a>
        )}
      </Wrapper>
      {error && <span className="text-red-500 mt-1">{error}</span>}
      {info && <span className="text-gray-500 mt-1">{info}</span>}
    </div>
  );
};
