import React, { useRef, useState } from "react";
import type { Integration } from "../Pages/Integrations/Index";
import { ButtonIcon } from "./ButtonIcon";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Heading } from "./Heading";
import { Card } from "./Card";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";
import { StatusLight } from "./StatusLight";
import { ButtonIconCopy } from "./ButtonIconCopy";
import { Tooltip } from "./Tooltip";
type Props = Integration & {
  onDelete: (id: string) => void;
};

export const IntegrationCard = ({
  id,
  name,
  type,
  description,
  status,
  onDelete,
}: Props) => {
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
      title={
        <div className="inline-flex w-full justify-between">
          <div className="inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
            <Heading level={2}>{name}</Heading>
            <span className="bg-publiq-blue-dark text-white text-xs font-medium mr-2 px-2.5 py-0.5 rounded ">
              {type}
            </span>
          </div>
          <div className="inline-flex gap-2 max-md:self-start">
            <ButtonIcon icon={faPencil} className="text-icon-gray" />
            <ButtonIcon
              icon={faTrash}
              className="text-icon-gray"
              onClick={() => onDelete(id)}
            />
          </div>
        </div>
      }
      description={description}
    >
      <div className="flex w-full flex-col gap-4">
        <section className="flex max-md:flex-col max-md:items-start gap-3 md:items-center">
          <Heading level={3} className="font-semibold">
            {t("integrations.test")}
          </Heading>
          <div className="inline-flex gap-2 items-center bg-gray-200 py-2 px-3 whitespace-nowrap max-sm:w-full justify-between">
            <span className=" overflow-hidden text-ellipsis" ref={codeFieldRef}>
              {id}
            </span>
            <Tooltip visible={isVisible} text={t("tooltip.copy")}>
              <ButtonIconCopy onClick={handleCopyToClipboard} />
            </Tooltip>
          </div>
        </section>
        <section className="inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
          <Heading className="font-semibold" level={3}>
            {t("integrations.live")}
          </Heading>
          <div className="flex align-center gap-1 ">
            <StatusLight status={status} />
            <span>{t(`integrations.status.${status}`)}</span>
          </div>
        </section>
        <section className="inline-flex gap-3 max-md:flex-col max-md:items-start md:items-center">
          <Heading className="font-semibold" level={3}>
            {t("integrations.documentation.title")}
          </Heading>
          <Link href="#">
            {t("integrations.documentation.action_title", {
              product: t(`integrations.products.${type}`),
            })}
          </Link>
        </section>
      </div>
    </Card>
  );
};
