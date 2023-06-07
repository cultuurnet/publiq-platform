import React from "react";
import type { Integration } from "../Pages/Integrations/Index";
import { IconButton } from "./IconButton";
import { faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Heading } from "./Heading";
import { Card } from "./Card";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";

type Props = Integration;

export const IntegrationCard = ({ id, name, type, description }: Props) => {
  const { t } = useTranslation();

  return (
    <Card
      title={
        <div className="inline-flex w-full justify-between">
          <div className="inline-flex gap-3 items-center">
            <Heading level={2}>{name}</Heading>
            <span>{type}</span>
          </div>
          <div className="inline-flex gap-3">
            <IconButton icon={faPencil} />
            <IconButton icon={faTrash} className="text-red-500" />
          </div>
        </div>
      }
      description={description}
      className="w-full"
    >
      <div className="flex flex-col gap-2">
        <section className="inline-flex gap-3 items-center">
          <Heading level={3}>{t("integrations.test")}</Heading>
          <span className="bg-gray-200 p-1">{id}</span>
        </section>
        <section className="inline-flex gap-3 items-center">
          <Heading level={3}>{t("integrations.live")}</Heading>
          <span>{t("integrations.status.not_active")}</span>
        </section>
        <section className="inline-flex gap-3 items-center">
          <Heading level={3}>{t("integrations.documentation.title")}</Heading>
          <Link href="#">
            {t("integrations.documentation.action_title", {
              product: "Entry API",
            })}
          </Link>
        </section>
      </div>
    </Card>
  );
};
