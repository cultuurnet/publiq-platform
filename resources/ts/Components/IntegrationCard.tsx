import React, { useRef, useState } from "react";
import type { Integration } from "../Pages/Integrations/Index";
import { ButtonIcon } from "./ButtonIcon";
import { faPencil } from "@fortawesome/free-solid-svg-icons";
import { Heading } from "./Heading";
import { Card } from "./Card";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";
import { StatusLight } from "./StatusLight";
import { ButtonIconCopy } from "./ButtonIconCopy";
import { Tooltip } from "./Tooltip";
import { IntegrationStatus } from "../types/IntegrationStatus";
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";
import {
  integrationIconClasses,
  useIntegrationTypes,
} from "./IntegrationTypes";
import { IconSearchApi } from "./icons/IconSearchApi";

type Props = Integration & {
  onEdit: (id: string) => void;
  email: string;
};

const productTypeToPath = {
  "entry-api": "/uitdatabank/entry-api/introduction",
  "search-api": "/uitdatabank/search-api/introduction",
  widgets: "/widgets/aan-de-slag",
};

const OpenWidgetBuilderButton = ({ id, type }: Pick<Props, "id" | "type">) => {
  const { t } = useTranslation();
  if (type !== "widgets") {
    return null;
  }

  return (
    <ButtonLinkSecondary href={`/integrations/${id}/widget`} target={"_blank"}>
      {t("integrations.open_widget")}
    </ButtonLinkSecondary>
  );
};

export const IntegrationCard = ({
  id,
  name,
  type,
  status,
  email,
  onEdit,
}: Props) => {
  const { t } = useTranslation();

  const integrationTypes = useIntegrationTypes();
  const codeFieldRef = useRef<HTMLSpanElement>(null);

  const [isVisible, setIsVisible] = useState(false);

  function handleCopyToClipboard() {
    navigator.clipboard.writeText(codeFieldRef.current?.innerText ?? "");
    setIsVisible(true);
    const timeoutId = setTimeout(() => {
      setIsVisible(false);
      clearTimeout(timeoutId);
    }, 1000);
  }

  const CardIcon = integrationTypes.find((i) => i.type === type)?.Icon as
    | typeof IconSearchApi
    | undefined;

  return (
    <Card
      title={name}
      border
      icon={CardIcon && <CardIcon className={integrationIconClasses} />}
      clickableHeading
      id={id}
      iconButton={
        <ButtonIcon
          icon={faPencil}
          className="text-icon-gray"
          onClick={() => onEdit(id)}
        />
      }
    >
      <div className="flex flex-col gap-4 mx-8 my-6">
        <section className="flex max-md:flex-col max-md:items-start gap-3 md:items-center">
          <Heading level={5} className="font-semibold min-w-[10rem]">
            {t("integrations.test")}
          </Heading>
          <div className="flex gap-2 items-center bg-[#fdf3ef] rounded px-3 p-1">
            <span
              className="overflow-hidden text-ellipsis text-publiq-orange"
              ref={codeFieldRef}
            >
              {id}
            </span>
            <Tooltip
              visible={isVisible}
              text={t("tooltip.copy")}
              className={"w-auto"}
            >
              <ButtonIconCopy
                onClick={handleCopyToClipboard}
                className={"text-publiq-orange"}
              />
            </Tooltip>
          </div>
          {status !== IntegrationStatus.Active && (
            <OpenWidgetBuilderButton id={id} type={type} />
          )}
        </section>
        <section className="inline-flex gap-3 max-md:flex-col max-md:items-start md:items-start">
          <Heading className="font-semibold min-w-[10rem]" level={5}>
            {t("integrations.live")}
          </Heading>
          <div className="flex align-center gap-1">
            <StatusLight type={type} status={status} id={id} email={email} />
            {status === IntegrationStatus.Active && (
              <OpenWidgetBuilderButton id={id} type={type} />
            )}
          </div>
        </section>
        <section className="inline-flex gap-3 max-md:flex-col items-start">
          <Heading className="font-semibold min-w-[10rem]" level={5}>
            {t("integrations.documentation.title")}
          </Heading>
          <div className="flex flex-col gap-2">
            <Link
              href={t("integrations.documentation.action_url", {
                product: productTypeToPath[type],
              })}
            >
              {t("integrations.documentation.action_title", {
                product: t(`integrations.products.${type}`),
              })}
            </Link>
            {type === "entry-api" && (
              <Link href="https://docs.publiq.be/docs/uitdatabank/entry-api%2Frequirements-before-going-live">
                {t("integrations.documentation.requirements")}
              </Link>
            )}
          </div>
        </section>
      </div>
    </Card>
  );
};
