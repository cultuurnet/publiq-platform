import React, { cloneElement, memo, ReactElement, useId } from "react";
import { classNames } from "../utils/classNames";

type LabelPosition = "top" | "left" | "right";

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
  if (labelPosition === "right") return "items-center";
  return;
};

type LabelProps = {
  id: string;
  label: string;
  labelSize: LabelSize;
};

const Label = memo(({ id, labelSize, label }: LabelProps) => (
  <label htmlFor={id} className={labelSize ? `text-${labelSize}` : ""}>
    {label}
  </label>
));

Label.displayName = "Label";

type Props = {
  label?: string;
  labelPosition?: LabelPosition;
  labelSize?: LabelSize;
  error?: string;
  component: ReactElement;
};

export const FormElement = ({
  label,
  labelPosition = "top",
  labelSize = "base",
  component,
  error,
}: Props) => {
  const id = useId();

  const clonedComponent = cloneElement(component, { ...component.props, id });

  return (
    <div className="flex flex-col">
      <Wrapper labelPosition={labelPosition}>
        <div
          className={classNames(
            "flex gap-1",
            getFlexDirection(labelPosition),
            getAlignItems(labelPosition)
          )}
        >
          {label && <Label id={id} label={label} labelSize={labelSize} />}
          {clonedComponent}
        </div>
      </Wrapper>
      {error && <span className="text-red-500">{error}</span>}
    </div>
  );
};
