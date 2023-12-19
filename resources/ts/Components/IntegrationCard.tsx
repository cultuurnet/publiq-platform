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
import { ButtonLinkSecondary } from "./ButtonLinkSecondary";
type Props = Integration & {
  onEdit: (id: string) => void;
};

const productTypeToPath = {
  "entry-api": "/uitdatabank/entry-api/introduction",
  "search-api": "/uitdatabank/search-api/introduction",
  widgets: "/widgets/aan-de-slag",
};

export const IntegrationCard = ({ id, name, type, status, onEdit }: Props) => {
  const { t } = useTranslation();

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

  return (
    <Card
      title={name}
      border
      badge={type}
      clickableHeading
      id={id}
      iconButton={
        <ButtonIcon
          icon={faPencil}
          size="lg"
          className="text-icon-gray"
          onClick={() => onEdit(id)}
        />
      }
    >
      <div className="flex flex-col gap-4 mx-10 my-6">
        <section className="flex max-md:flex-col max-md:items-start gap-3 md:items-center">
          <Heading level={5} className="font-semibold min-w-[10rem]">
            {t("integrations.test")}
          </Heading>
          <div className="flex gap-2 items-center bg-status-green rounded px-3">
            <span
              className=" overflow-hidden text-ellipsis text-status-green-dark"
              ref={codeFieldRef}
            >
              {id}
            </span>
            <Tooltip visible={isVisible} text={t("tooltip.copy")}>
              <ButtonIconCopy onClick={handleCopyToClipboard} />
            </Tooltip>
          </div>
          {type === "widgets" && (
            <ButtonLinkSecondary
              href={`/integrations/${id}/widget`}
              target="_blank"
            >
              {t("integrations.open_widget")}
            </ButtonLinkSecondary>
          )}
        </section>
        <section className="inline-flex gap-3 max-md:flex-col max-md:items-start md:items-start">
          <Heading className="font-semibold min-w-[10rem]" level={5}>
            {t("integrations.live")}
          </Heading>
          <div className="flex align-center gap-1">
            <StatusLight status={status} id={id} />
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
