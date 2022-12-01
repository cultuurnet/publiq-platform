import React, { FC, useId } from 'react';
import { classNames } from '../utils/classNames';

type LabelPosition = 'top' | 'left' | 'right';

type LabelSize = 'base' | 'lg' | 'xl';

type WrapperProps = {
  labelPosition?: LabelPosition;
  children: JSX.Element;
};

const Wrapper = ({ labelPosition, children }: WrapperProps) => {
  if (labelPosition !== 'right') {
    return children;
  }

  return <div className="flex">{children}</div>;
};

const getFlexDirection = (labelPosition: LabelPosition | undefined) => {
  if (!labelPosition) return 'flex-col';
  if (labelPosition === 'top') return 'flex-col';
  if (labelPosition === 'left') return 'flex-row';
  if (labelPosition === 'right') return 'flex-row-reverse';
};

const getAlignItems = (labelPosition: LabelPosition | undefined) => {
  if (labelPosition === 'right') return 'items-center';
  return;
};

type Props = {
  label?: string;
  labelPosition?: LabelPosition;
  labelSize?: LabelSize;
  error?: string;
  Component: FC<{ id: string }>;
};

export const FormElement: FC<Props> = ({
  label,
  labelPosition = 'top',
  labelSize = 'base',
  Component,
  error,
}) => {
  const id = useId();

  return (
    <div className="flex flex-col">
      <Wrapper labelPosition={labelPosition}>
        <div
          className={classNames(
            'flex gap-1',
            getFlexDirection(labelPosition),
            getAlignItems(labelPosition),
          )}
        >
          {label && (
            <label
              htmlFor={id}
              className={labelSize ? `text-${labelSize}` : ''}
            >
              {label}
            </label>
          )}
          <Component id={id} />
        </div>
      </Wrapper>
      {error && <span className="text-red-500">{error}</span>}
    </div>
  );
};
